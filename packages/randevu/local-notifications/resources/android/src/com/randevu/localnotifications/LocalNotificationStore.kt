package com.randevu.localnotifications

import android.app.AlarmManager
import android.app.Notification
import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.graphics.Color
import android.net.Uri
import android.os.Build
import org.json.JSONArray
import org.json.JSONObject
import java.util.Calendar

internal object LocalNotificationStore {
    const val EXTRA_SCHEDULE_ID = "randevu_schedule_id"

    private const val PREFERENCES_NAME = "randevu_local_notifications"
    private const val SCHEDULES_KEY = "schedules"
    private const val CHANNEL_ID = "randevu_reminders"
    private const val AT_TIME_GRACE_MILLIS = 15 * 60 * 1000L
    private val lock = Any()

    fun sync(context: Context, schedules: JSONArray): Int = synchronized(lock) {
        val preferences = context.getSharedPreferences(PREFERENCES_NAME, Context.MODE_PRIVATE)
        val previous = readSchedules(preferences)
        val replacement = JSONObject()

        for (index in 0 until schedules.length()) {
            val schedule = schedules.getJSONObject(index)
            val scheduleId = schedule.getString("id")

            require(scheduleId.isNotBlank()) { "Notification schedule IDs cannot be blank." }
            require(schedule.has("fireAtMillis")) { "Notification schedule [$scheduleId] is missing fireAtMillis." }
            require(schedule.has("title") && schedule.has("body")) {
                "Notification schedule [$scheduleId] is missing its title or body."
            }

            val now = System.currentTimeMillis()
            val normalized = if (schedule.optString("repeat") == "none") {
                recomputeAppointment(schedule, now)
            } else {
                nextOccurrence(schedule, now)
            }

            if (normalized != null) {
                replacement.put(scheduleId, normalized)
            }
        }

        writeSchedules(preferences, replacement)
        previous.keys().forEachRemaining { scheduleId ->
            cancel(context, scheduleId)
        }
        replacement.keys().forEachRemaining { scheduleId ->
            scheduleAlarm(context, replacement.getJSONObject(scheduleId))
        }

        replacement.length()
    }

    fun restore(context: Context) = synchronized(lock) {
        val preferences = context.getSharedPreferences(PREFERENCES_NAME, Context.MODE_PRIVATE)
        val schedules = readSchedules(preferences)
        val now = System.currentTimeMillis()
        val updated = JSONObject()
        val ids = schedules.keys().asSequence().toList()

        ids.forEach { scheduleId ->
            val current = schedules.getJSONObject(scheduleId)
            val restored = if (current.optString("repeat") == "none") {
                recomputeAppointment(current, now)
            } else {
                nextOccurrence(current, now)
            }

            if (restored != null) {
                updated.put(scheduleId, restored)
            }
        }

        writeSchedules(preferences, updated)
        updated.keys().forEachRemaining { scheduleId ->
            scheduleAlarm(context, updated.getJSONObject(scheduleId))
        }
    }

    fun deliver(context: Context, scheduleId: String) = synchronized(lock) {
        val preferences = context.getSharedPreferences(PREFERENCES_NAME, Context.MODE_PRIVATE)
        val schedules = readSchedules(preferences)
        val schedule = schedules.optJSONObject(scheduleId) ?: return
        val now = System.currentTimeMillis()

        if (schedule.optString("repeat") == "none") {
            val eventAt = schedule.optLong("eventAtMillis")
            val isAtTimeRule = schedule.optString("reminderRule") == "at_time"

            if (eventAt <= now && (!isAtTimeRule || now - eventAt > AT_TIME_GRACE_MILLIS)) {
                schedules.remove(scheduleId)
                writeSchedules(preferences, schedules)

                return
            }

            showNotification(context, schedule)
            schedules.remove(scheduleId)
            writeSchedules(preferences, schedules)

            return
        }

        showNotification(context, schedule)

        val next = nextOccurrence(schedule, now)
        if (next == null) {
            schedules.remove(scheduleId)
        } else {
            schedules.put(scheduleId, next)
        }
        writeSchedules(preferences, schedules)

        if (next != null) {
            scheduleAlarm(context, next)
        }
    }

    private fun recomputeAppointment(schedule: JSONObject, now: Long): JSONObject? {
        val eventDateText = schedule.optString("eventDate")
        val dateParts = eventDateText.split('-').mapNotNull(String::toIntOrNull)

        if (dateParts.size != 3) {
            return null
        }

        val eventTime = parseTime(schedule.optString("eventTime", "09:00"))
        val eventAt = localDateTime(dateParts[0], dateParts[1], dateParts[2], eventTime.first, eventTime.second)
        val rule = schedule.optString("reminderRule")
        val fireAt = when (rule) {
            "at_time" -> eventAt
            "morning_of" -> localDateTime(dateParts[0], dateParts[1], dateParts[2], 9, 0)
            "15m" -> eventAt - 15 * 60_000L
            "1h" -> eventAt - 60 * 60_000L
            "1d" -> shiftLocalDayToTime(eventAt, -1, 9, 0)
            "2d" -> shiftLocalDays(eventAt, -2)
            "1w" -> shiftLocalDays(eventAt, -7)
            else -> return null
        }

        if (fireAt <= now || eventAt <= now || fireAt > eventAt) {
            return null
        }

        return JSONObject(schedule.toString()).put("fireAtMillis", fireAt)
    }

    private fun nextOccurrence(schedule: JSONObject, now: Long): JSONObject? {
        val mode = schedule.optString("repeat")
        val calendarName = schedule.optString("calendar", "gregorian")
        val baseYear = schedule.optInt("baseYear")
        val baseMonth = schedule.optInt("baseMonth")
        val baseDay = schedule.optInt("baseDay")
        val leadDays = schedule.optInt("leadDays")
        val eventTime = parseTime(schedule.optString("eventTime", "09:00"))
        val current = LocalNotificationCalendar.currentDate(calendarName, now)
        val attempts = if (mode == "yearly") 8 else 48

        for (offset in 0 until attempts) {
            val year: Int
            val month: Int

            if (mode == "yearly") {
                year = maxOf(current.year, baseYear + 1) + offset
                month = baseMonth
            } else {
                val currentTotalMonths = current.year * 12 + current.month - 1
                val baseTotalMonths = baseYear * 12 + baseMonth - 1
                val totalMonths = maxOf(currentTotalMonths, baseTotalMonths + 1) + offset
                year = totalMonths / 12
                month = totalMonths % 12 + 1
            }

            val day = minOf(baseDay, LocalNotificationCalendar.daysInMonth(calendarName, year, month))
            val eventDate = LocalNotificationCalendar.gregorianDate(calendarName, CalendarDate(year, month, day))
            val eventAt = localDateTime(
                eventDate.year,
                eventDate.month,
                eventDate.day,
                eventTime.first,
                eventTime.second
            )
            val fireAt = Calendar.getInstance().apply {
                timeInMillis = eventAt
                add(Calendar.DAY_OF_YEAR, -leadDays)
            }.timeInMillis

            if (fireAt > now) {
                val age = if (mode == "yearly") {
                    year - baseYear
                } else {
                    (year - baseYear) * 12 + month - baseMonth
                }.coerceAtLeast(1)

                val next = JSONObject(schedule.toString())
                    .put("fireAtMillis", fireAt)
                    .put("eventAtMillis", eventAt)
                    .put("body", anniversaryBody(schedule.optJSONObject("content"), age))

                return next
            }
        }

        return null
    }

    private fun anniversaryBody(content: JSONObject?, age: Int): String {
        if (content == null) {
            throw IllegalArgumentException("Anniversary notification content is required.")
        }

        val template = content.getString("ageTemplate")
        val shape = when {
            age == 1 -> "nounOne"
            age == 2 -> "nounTwo"
            age <= 10 -> "nounFew"
            else -> "nounMany"
        }
        val unit = content.getString(shape)
        val isArabic = content.optBoolean("isArabic")
        val phrase = if (isArabic && age <= 2) {
            template.replace(":count", "").replace(":unit", unit).replace(Regex("\\s+"), " ").trim()
        } else {
            template.replace(":count", age.toString()).replace(":unit", unit)
        }
        val parts = mutableListOf(phrase, content.getString("absolute"))
        val hijri = content.optString("hijri")

        if (hijri.isNotBlank()) {
            parts.add(hijri)
        }

        return parts.joinToString(content.getString("separator"))
    }

    private fun scheduleAlarm(context: Context, schedule: JSONObject) {
        val fireAt = schedule.getLong("fireAtMillis")
        val now = System.currentTimeMillis()

        if (fireAt <= now) {
            return
        }

        val alarmManager = context.getSystemService(Context.ALARM_SERVICE) as AlarmManager
        val pendingIntent = alarmPendingIntent(context, schedule.getString("id"))

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S && alarmManager.canScheduleExactAlarms()) {
            alarmManager.setExactAndAllowWhileIdle(AlarmManager.RTC_WAKEUP, fireAt, pendingIntent)
        } else {
            alarmManager.setAndAllowWhileIdle(AlarmManager.RTC_WAKEUP, fireAt, pendingIntent)
        }
    }

    private fun showNotification(context: Context, schedule: JSONObject) {
        if (!notificationsEnabled(context)) {
            return
        }

        val notificationManager = context.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        val channelName = schedule.optString("channelName", "Randevu reminders")
        notificationManager.createNotificationChannel(
            NotificationChannel(CHANNEL_ID, channelName, NotificationManager.IMPORTANCE_DEFAULT)
        )

        val route = schedule.optString("route")
        val contentIntent = Intent().apply {
            setClassName(context, "com.nativephp.mobile.ui.MainActivity")
            putExtra("notification_url", route)
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP
        }
        val tapIntent = PendingIntent.getActivity(
            context,
            schedule.getString("id").hashCode(),
            contentIntent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )
        val builder = Notification.Builder(context, CHANNEL_ID)
            .setSmallIcon(context.applicationInfo.icon.takeIf { it != 0 } ?: android.R.drawable.ic_dialog_info)
            .setContentTitle(schedule.getString("title"))
            .setContentText(schedule.getString("body"))
            .setStyle(Notification.BigTextStyle().bigText(schedule.getString("body")))
            .setContentIntent(tapIntent)
            .setAutoCancel(true)
            .setDefaults(Notification.DEFAULT_ALL)

        val color = schedule.optString("color")
        if (color.matches(Regex("^#[0-9A-Fa-f]{6}$"))) {
            builder.setColor(Color.parseColor(color))
        }

        notificationManager.notify(notificationId(schedule.getString("id")), builder.build())
    }

    private fun notificationsEnabled(context: Context): Boolean {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU &&
            context.checkSelfPermission(android.Manifest.permission.POST_NOTIFICATIONS) !=
            android.content.pm.PackageManager.PERMISSION_GRANTED
        ) {
            return false
        }

        return (context.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager)
            .areNotificationsEnabled()
    }

    private fun alarmPendingIntent(context: Context, scheduleId: String): PendingIntent {
        val intent = Intent(context, LocalNotificationAlarmReceiver::class.java).apply {
            action = "${context.packageName}.LOCAL_NOTIFICATION"
            data = Uri.parse("randevu-notification://alarm/$scheduleId")
            putExtra(EXTRA_SCHEDULE_ID, scheduleId)
        }

        return PendingIntent.getBroadcast(
            context,
            scheduleId.hashCode(),
            intent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )
    }

    private fun cancel(context: Context, scheduleId: String) {
        val alarmManager = context.getSystemService(Context.ALARM_SERVICE) as AlarmManager
        alarmManager.cancel(alarmPendingIntent(context, scheduleId))
        context.getSystemService(Context.NOTIFICATION_SERVICE)?.let { service ->
            (service as NotificationManager).cancel(notificationId(scheduleId))
        }
    }

    private fun notificationId(scheduleId: String): Int = scheduleId.hashCode() and Int.MAX_VALUE

    private fun readSchedules(preferences: android.content.SharedPreferences): JSONObject {
        return JSONObject(preferences.getString(SCHEDULES_KEY, "{}") ?: "{}")
    }

    private fun writeSchedules(preferences: android.content.SharedPreferences, schedules: JSONObject) {
        if (!preferences.edit().putString(SCHEDULES_KEY, schedules.toString()).commit()) {
            throw IllegalStateException("Could not persist local notification schedules.")
        }
    }

    private fun parseTime(value: String): Pair<Int, Int> {
        val parts = value.split(':').mapNotNull(String::toIntOrNull)
        require(parts.size == 2 && parts[0] in 0..23 && parts[1] in 0..59) {
            "Invalid local notification time [$value]."
        }

        return parts[0] to parts[1]
    }

    private fun localDateTime(year: Int, month: Int, day: Int, hour: Int, minute: Int): Long {
        return Calendar.getInstance().apply {
            clear()
            set(year, month - 1, day, hour, minute, 0)
            set(Calendar.MILLISECOND, 0)
        }.timeInMillis
    }

    private fun shiftLocalDays(timeMillis: Long, days: Int): Long {
        return Calendar.getInstance().apply {
            timeInMillis = timeMillis
            add(Calendar.DAY_OF_YEAR, days)
        }.timeInMillis
    }

    private fun shiftLocalDayToTime(timeMillis: Long, days: Int, hour: Int, minute: Int): Long {
        val shiftedDate = Calendar.getInstance().apply {
            timeInMillis = timeMillis
            add(Calendar.DAY_OF_YEAR, days)
        }

        return localDateTime(
            shiftedDate.get(Calendar.YEAR),
            shiftedDate.get(Calendar.MONTH) + 1,
            shiftedDate.get(Calendar.DAY_OF_MONTH),
            hour,
            minute
        )
    }
}
