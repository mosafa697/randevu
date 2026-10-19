package com.randevu.localnotifications

import android.Manifest
import android.app.AlarmManager
import android.content.Context
import android.content.pm.PackageManager
import android.os.Build
import android.provider.Settings
import android.net.Uri
import androidx.fragment.app.FragmentActivity
import com.nativephp.mobile.bridge.BridgeFunction
import com.nativephp.mobile.bridge.BridgeResponse
import org.json.JSONArray
import java.util.TimeZone

object RandevuNotificationsFunctions {
    class Sync(private val context: Context) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            val payload = parameters["payload"] as? String
                ?: throw IllegalArgumentException("The schedule payload is required.")

            val schedules = JSONArray(payload)
            val count = LocalNotificationStore.sync(context, schedules)

            return BridgeResponse.success(mapOf("scheduledCount" to count))
        }
    }

    class Status(private val context: Context) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            val notificationsGranted = Build.VERSION.SDK_INT < Build.VERSION_CODES.TIRAMISU ||
                context.checkSelfPermission(Manifest.permission.POST_NOTIFICATIONS) == PackageManager.PERMISSION_GRANTED
            val notificationsEnabled = (context.getSystemService(Context.NOTIFICATION_SERVICE) as android.app.NotificationManager)
                .areNotificationsEnabled()
            val exactAlarmsGranted = Build.VERSION.SDK_INT < Build.VERSION_CODES.S ||
                (context.getSystemService(Context.ALARM_SERVICE) as AlarmManager).canScheduleExactAlarms()

            return BridgeResponse.success(
                mapOf(
                    "notificationsGranted" to (notificationsGranted && notificationsEnabled),
                    "exactAlarmsGranted" to exactAlarmsGranted,
                    "timeZoneId" to TimeZone.getDefault().id
                )
            )
        }
    }

    class RequestPermission(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU &&
                activity.checkSelfPermission(Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED
            ) {
                activity.runOnUiThread {
                    activity.requestPermissions(
                        arrayOf(Manifest.permission.POST_NOTIFICATIONS),
                        RandevuNotificationsFunctions.NOTIFICATION_PERMISSION_REQUEST
                    )
                }
            }

            return BridgeResponse.success(mapOf("requested" to true))
        }
    }

    class OpenExactAlarmSettings(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                val intent = android.content.Intent(Settings.ACTION_REQUEST_SCHEDULE_EXACT_ALARM).apply {
                    data = Uri.parse("package:${activity.packageName}")
                }
                activity.startActivity(intent)
            }

            return BridgeResponse.success(mapOf("opened" to true))
        }
    }

    private const val NOTIFICATION_PERMISSION_REQUEST = 7401
}
