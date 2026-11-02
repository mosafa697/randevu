@include('native.partials.segmented', ['name' => 'calendar', 'selected' => $calendar_mode])

<native:row class="w-full gap-2">
    <native:button :label="__('randevu.quick_today')" ios-icon="calendar" android-icon="today" variant="secondary" @press="setToday" class="flex-1 min-h-12" />
    <native:button :label="__('randevu.quick_plus7')" ios-icon="calendar.badge.plus" android-icon="date_range" variant="secondary" @press="setPlus7" class="flex-1 min-h-12" />
    <native:button :label="__('randevu.quick_plus30')" ios-icon="calendar" android-icon="calendar_month" variant="secondary" @press="setPlus30" class="flex-1 min-h-12" />
</native:row>

<native:row class="w-full gap-1 items-center">
    <native:button :a11y-label="__('randevu.calendar_previous_month')" :ios-icon="$calendarPreviousIosIcon" :android-icon="$calendarPreviousAndroidIcon" variant="ghost" @press="previousCalendarMonth" class="w-12 min-h-12 shrink-0" />
    <native:column class="flex-1 min-w-0 items-center">
        <native:button :label="$calendarMonthHeading" :a11y-label="__('randevu.calendar_choose_month_year')" font="label" variant="ghost" @press="toggleCalendarMonthPicker" class="w-full min-h-10" />
        <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} font-semibold text-theme-on-surface-variant text-center">{{ $calendarYearLabel }}</native:text>
    </native:column>
    <native:button :a11y-label="__('randevu.calendar_next_month')" :ios-icon="$calendarNextIosIcon" :android-icon="$calendarNextAndroidIcon" variant="ghost" @press="nextCalendarMonth" class="w-12 min-h-12 shrink-0" />
</native:row>

@if($showCalendarMonthPicker)
    <native:column class="w-full p-3 gap-3 rounded-xl bg-theme-surface-variant">
        <native:select :label="__('randevu.year_label')" :options="$calendarYearOptions" native:model="calendarYearSelection" class="w-full" />
        <native:column class="w-full gap-2">
            <native:row class="w-full gap-2">
                <native:button :label="$calendarMonthNames[0]" :variant="$calendarViewMonth === 1 ? 'primary' : 'ghost'" @press="selectCalendarMonth01" class="flex-1 min-h-12" />
                <native:button :label="$calendarMonthNames[1]" :variant="$calendarViewMonth === 2 ? 'primary' : 'ghost'" @press="selectCalendarMonth02" class="flex-1 min-h-12" />
                <native:button :label="$calendarMonthNames[2]" :variant="$calendarViewMonth === 3 ? 'primary' : 'ghost'" @press="selectCalendarMonth03" class="flex-1 min-h-12" />
            </native:row>
            <native:row class="w-full gap-2">
                <native:button :label="$calendarMonthNames[3]" :variant="$calendarViewMonth === 4 ? 'primary' : 'ghost'" @press="selectCalendarMonth04" class="flex-1 min-h-12" />
                <native:button :label="$calendarMonthNames[4]" :variant="$calendarViewMonth === 5 ? 'primary' : 'ghost'" @press="selectCalendarMonth05" class="flex-1 min-h-12" />
                <native:button :label="$calendarMonthNames[5]" :variant="$calendarViewMonth === 6 ? 'primary' : 'ghost'" @press="selectCalendarMonth06" class="flex-1 min-h-12" />
            </native:row>
            <native:row class="w-full gap-2">
                <native:button :label="$calendarMonthNames[6]" :variant="$calendarViewMonth === 7 ? 'primary' : 'ghost'" @press="selectCalendarMonth07" class="flex-1 min-h-12" />
                <native:button :label="$calendarMonthNames[7]" :variant="$calendarViewMonth === 8 ? 'primary' : 'ghost'" @press="selectCalendarMonth08" class="flex-1 min-h-12" />
                <native:button :label="$calendarMonthNames[8]" :variant="$calendarViewMonth === 9 ? 'primary' : 'ghost'" @press="selectCalendarMonth09" class="flex-1 min-h-12" />
            </native:row>
            <native:row class="w-full gap-2">
                <native:button :label="$calendarMonthNames[9]" :variant="$calendarViewMonth === 10 ? 'primary' : 'ghost'" @press="selectCalendarMonth10" class="flex-1 min-h-12" />
                <native:button :label="$calendarMonthNames[10]" :variant="$calendarViewMonth === 11 ? 'primary' : 'ghost'" @press="selectCalendarMonth11" class="flex-1 min-h-12" />
                <native:button :label="$calendarMonthNames[11]" :variant="$calendarViewMonth === 12 ? 'primary' : 'ghost'" @press="selectCalendarMonth12" class="flex-1 min-h-12" />
            </native:row>
        </native:column>
    </native:column>
@endif

<native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant text-center">{{ __('randevu.calendar_selected', ['date' => $calendarSelectedDateLabel]) }}</native:text>

<native:row class="w-full gap-1">
    @foreach($calendarWeekdayNames as $weekdayName)
        <native:text class="{{ \App\Services\AppFonts::textClass('xs') }} font-semibold text-theme-on-surface-variant flex-1 text-center">{{ $weekdayName }}</native:text>
    @endforeach
</native:row>

<native:column class="w-full gap-1">
    @foreach($calendarWeeks as $calendarWeek)
        <native:row class="w-full gap-1">
            @foreach($calendarWeek as $calendarDay)
                @php($calendarDayIsSelected = $calendarHighlightedDay === $calendarDay)
                @switch($calendarDay)
                    @case(null)
                        <native:column class="flex-1 h-11" />
                        @break
                    @case(1)
                        <native:pressable @press="selectCalendarDay01" :a11y-label="__('randevu.calendar_select_day', ['day' => 1])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">1</native:text>
                        </native:pressable>
                        @break
                    @case(2)
                        <native:pressable @press="selectCalendarDay02" :a11y-label="__('randevu.calendar_select_day', ['day' => 2])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">2</native:text>
                        </native:pressable>
                        @break
                    @case(3)
                        <native:pressable @press="selectCalendarDay03" :a11y-label="__('randevu.calendar_select_day', ['day' => 3])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">3</native:text>
                        </native:pressable>
                        @break
                    @case(4)
                        <native:pressable @press="selectCalendarDay04" :a11y-label="__('randevu.calendar_select_day', ['day' => 4])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">4</native:text>
                        </native:pressable>
                        @break
                    @case(5)
                        <native:pressable @press="selectCalendarDay05" :a11y-label="__('randevu.calendar_select_day', ['day' => 5])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">5</native:text>
                        </native:pressable>
                        @break
                    @case(6)
                        <native:pressable @press="selectCalendarDay06" :a11y-label="__('randevu.calendar_select_day', ['day' => 6])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">6</native:text>
                        </native:pressable>
                        @break
                    @case(7)
                        <native:pressable @press="selectCalendarDay07" :a11y-label="__('randevu.calendar_select_day', ['day' => 7])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">7</native:text>
                        </native:pressable>
                        @break
                    @case(8)
                        <native:pressable @press="selectCalendarDay08" :a11y-label="__('randevu.calendar_select_day', ['day' => 8])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">8</native:text>
                        </native:pressable>
                        @break
                    @case(9)
                        <native:pressable @press="selectCalendarDay09" :a11y-label="__('randevu.calendar_select_day', ['day' => 9])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">9</native:text>
                        </native:pressable>
                        @break
                    @case(10)
                        <native:pressable @press="selectCalendarDay10" :a11y-label="__('randevu.calendar_select_day', ['day' => 10])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">10</native:text>
                        </native:pressable>
                        @break
                    @case(11)
                        <native:pressable @press="selectCalendarDay11" :a11y-label="__('randevu.calendar_select_day', ['day' => 11])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">11</native:text>
                        </native:pressable>
                        @break
                    @case(12)
                        <native:pressable @press="selectCalendarDay12" :a11y-label="__('randevu.calendar_select_day', ['day' => 12])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">12</native:text>
                        </native:pressable>
                        @break
                    @case(13)
                        <native:pressable @press="selectCalendarDay13" :a11y-label="__('randevu.calendar_select_day', ['day' => 13])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">13</native:text>
                        </native:pressable>
                        @break
                    @case(14)
                        <native:pressable @press="selectCalendarDay14" :a11y-label="__('randevu.calendar_select_day', ['day' => 14])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">14</native:text>
                        </native:pressable>
                        @break
                    @case(15)
                        <native:pressable @press="selectCalendarDay15" :a11y-label="__('randevu.calendar_select_day', ['day' => 15])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">15</native:text>
                        </native:pressable>
                        @break
                    @case(16)
                        <native:pressable @press="selectCalendarDay16" :a11y-label="__('randevu.calendar_select_day', ['day' => 16])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">16</native:text>
                        </native:pressable>
                        @break
                    @case(17)
                        <native:pressable @press="selectCalendarDay17" :a11y-label="__('randevu.calendar_select_day', ['day' => 17])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">17</native:text>
                        </native:pressable>
                        @break
                    @case(18)
                        <native:pressable @press="selectCalendarDay18" :a11y-label="__('randevu.calendar_select_day', ['day' => 18])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">18</native:text>
                        </native:pressable>
                        @break
                    @case(19)
                        <native:pressable @press="selectCalendarDay19" :a11y-label="__('randevu.calendar_select_day', ['day' => 19])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">19</native:text>
                        </native:pressable>
                        @break
                    @case(20)
                        <native:pressable @press="selectCalendarDay20" :a11y-label="__('randevu.calendar_select_day', ['day' => 20])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">20</native:text>
                        </native:pressable>
                        @break
                    @case(21)
                        <native:pressable @press="selectCalendarDay21" :a11y-label="__('randevu.calendar_select_day', ['day' => 21])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">21</native:text>
                        </native:pressable>
                        @break
                    @case(22)
                        <native:pressable @press="selectCalendarDay22" :a11y-label="__('randevu.calendar_select_day', ['day' => 22])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">22</native:text>
                        </native:pressable>
                        @break
                    @case(23)
                        <native:pressable @press="selectCalendarDay23" :a11y-label="__('randevu.calendar_select_day', ['day' => 23])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">23</native:text>
                        </native:pressable>
                        @break
                    @case(24)
                        <native:pressable @press="selectCalendarDay24" :a11y-label="__('randevu.calendar_select_day', ['day' => 24])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">24</native:text>
                        </native:pressable>
                        @break
                    @case(25)
                        <native:pressable @press="selectCalendarDay25" :a11y-label="__('randevu.calendar_select_day', ['day' => 25])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">25</native:text>
                        </native:pressable>
                        @break
                    @case(26)
                        <native:pressable @press="selectCalendarDay26" :a11y-label="__('randevu.calendar_select_day', ['day' => 26])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">26</native:text>
                        </native:pressable>
                        @break
                    @case(27)
                        <native:pressable @press="selectCalendarDay27" :a11y-label="__('randevu.calendar_select_day', ['day' => 27])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">27</native:text>
                        </native:pressable>
                        @break
                    @case(28)
                        <native:pressable @press="selectCalendarDay28" :a11y-label="__('randevu.calendar_select_day', ['day' => 28])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">28</native:text>
                        </native:pressable>
                        @break
                    @case(29)
                        <native:pressable @press="selectCalendarDay29" :a11y-label="__('randevu.calendar_select_day', ['day' => 29])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">29</native:text>
                        </native:pressable>
                        @break
                    @case(30)
                        <native:pressable @press="selectCalendarDay30" :a11y-label="__('randevu.calendar_select_day', ['day' => 30])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">30</native:text>
                        </native:pressable>
                        @break
                    @case(31)
                        <native:pressable @press="selectCalendarDay31" :a11y-label="__('randevu.calendar_select_day', ['day' => 31])" class="flex-1 h-11 rounded-full items-center justify-center {{ $calendarDayIsSelected ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} font-semibold {{ $calendarDayIsSelected ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">31</native:text>
                        </native:pressable>
                        @break
                @endswitch
            @endforeach
        </native:row>
    @endforeach
</native:column>

@if(!empty($errors['occurs_on']))
    <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-destructive">{{ $errors['occurs_on'] }}</native:text>
@endif
