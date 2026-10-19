package com.randevu.localnotifications

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent

class LocalNotificationAlarmReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent?) {
        val scheduleId = intent?.getStringExtra(LocalNotificationStore.EXTRA_SCHEDULE_ID)
            ?: return

        LocalNotificationStore.deliver(context, scheduleId)
    }
}
