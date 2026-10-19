package com.randevu.localnotifications

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent

class LocalNotificationRestoreReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent?) {
        LocalNotificationStore.restore(context)
    }
}
