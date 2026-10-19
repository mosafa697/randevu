package com.randevu.localnotifications

import java.util.Calendar
import java.util.GregorianCalendar
import kotlin.math.ceil
import kotlin.math.floor

internal data class CalendarDate(val year: Int, val month: Int, val day: Int)

internal object LocalNotificationCalendar {
    private val leapYears = setOf(2, 5, 7, 10, 13, 16, 18, 21, 24, 26, 29)

    fun currentDate(calendar: String, timeMillis: Long): CalendarDate {
        val date = Calendar.getInstance().apply { timeInMillis = timeMillis }
        val gregorian = CalendarDate(
            date.get(Calendar.YEAR),
            date.get(Calendar.MONTH) + 1,
            date.get(Calendar.DAY_OF_MONTH)
        )

        return if (calendar == "hijri") hijriFromGregorian(gregorian) else gregorian
    }

    fun gregorianDate(calendar: String, date: CalendarDate): CalendarDate {
        return if (calendar == "hijri") gregorianFromHijri(date) else date
    }

    fun daysInMonth(calendar: String, year: Int, month: Int): Int {
        if (calendar == "hijri") {
            return if (month == 12) {
                if (isHijriLeapYear(year)) 30 else 29
            } else if (month % 2 == 1) {
                30
            } else {
                29
            }
        }

        return GregorianCalendar(year, month - 1, 1).getActualMaximum(Calendar.DAY_OF_MONTH)
    }

    fun isHijriLeapYear(year: Int): Boolean = leapYears.contains(((year - 1) % 30) + 1)

    private fun hijriFromGregorian(date: CalendarDate): CalendarDate {
        val julianDay = gregorianToJulianDay(date)
        var l = julianDay - 1948440 + 10632
        val n = (l - 1) / 10631
        l = l - 10631 * n + 354
        val j = ((10985 - l) / 5316) * ((50 * l) / 17719) +
            (l / 5670) * ((43 * l) / 15238)
        l = l - ((30 - j) / 15) * ((17719 * j) / 50) -
            (j / 16) * ((15238 * j) / 43) + 29
        val month = (24 * l) / 709
        val day = l - (709 * month) / 24
        val year = 30 * n + j - 30

        return CalendarDate(year, month, day)
    }

    private fun gregorianFromHijri(date: CalendarDate): CalendarDate {
        val julianDay = date.day +
            ceil(29.5 * (date.month - 1)).toInt() +
            (date.year - 1) * 354 +
            floor((3.0 + 11 * date.year) / 30).toInt() +
            1948440 - 1
        val a = julianDay + 32044
        val b = (4 * a + 3) / 146097
        val c = a - (146097 * b) / 4
        val d = (4 * c + 3) / 1461
        val e = c - (1461 * d) / 4
        val m = (5 * e + 2) / 153

        return CalendarDate(
            100 * b + d - 4800 + m / 10,
            m + 3 - 12 * (m / 10),
            e - (153 * m + 2) / 5 + 1
        )
    }

    private fun gregorianToJulianDay(date: CalendarDate): Int {
        val a = (14 - date.month) / 12
        val year = date.year + 4800 - a
        val month = date.month + 12 * a - 3

        return date.day + (153 * month + 2) / 5 + 365 * year + year / 4 -
            year / 100 + year / 400 - 32045
    }
}
