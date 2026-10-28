<native:column class="w-full h-full bg-theme-background">
    <native:scroll-view class="w-full flex-1">
        <native:column class="w-full px-5 py-3 gap-4">
            <native:outlined-text-input :label="__('randevu.title_label')" :placeholder="__('randevu.title_placeholder')" native:model="title" class="min-h-14" />
            @if(!empty($errors['title']))
                <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-destructive">{{ $errors['title'] }}</native:text>
            @endif

            @include('native.partials.segmented', ['name' => 'calendar', 'selected' => $calendar_mode])

            <native:row class="w-full gap-2">
                <native:button :label="__('randevu.quick_today')" ios-icon="calendar" android-icon="today" variant="secondary" @press="setToday" class="flex-1 min-h-12" />
                <native:button :label="__('randevu.quick_plus7')" ios-icon="calendar.badge.plus" android-icon="date_range" variant="secondary" @press="setPlus7" class="flex-1 min-h-12" />
                <native:button :label="__('randevu.quick_plus30')" ios-icon="calendar" android-icon="calendar_month" variant="secondary" @press="setPlus30" class="flex-1 min-h-12" />
            </native:row>

            @if($calendar_mode === 'hijri')
                <native:row class="w-full gap-2">
                    <native:select :label="__('randevu.day_label')" :options="$hDayOptions" native:model="h_day" class="flex-1" />
                    <native:select :label="__('randevu.month_label')" :options="$hMonthOptions" native:model="h_month" class="flex-1" :flexGrow="$this->monthSelectorFlexGrow($hMonthOptions)" />
                    <native:select :label="__('randevu.year_label')" :options="$hYearOptions" native:model="h_year" class="flex-1" />
                </native:row>
            @else
                <native:row class="w-full gap-2">
                    <native:select :label="__('randevu.day_label')" :options="$dayOptions" native:model="day" class="flex-1" />
                    <native:select :label="__('randevu.month_label')" :options="$monthOptions" native:model="month" class="flex-1" :flexGrow="$this->monthSelectorFlexGrow($monthOptions)" />
                    <native:select :label="__('randevu.year_label')" :options="$yearOptions" native:model="year" class="flex-1" />
                </native:row>
            @endif
            @if(!empty($errors['occurs_on']))
                <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-destructive">{{ $errors['occurs_on'] }}</native:text>
            @endif

            <native:row class="w-full gap-2">
                <native:select :label="__('randevu.hour_label')" :options="$hourOptions" native:model="hour" class="flex-1" />
                <native:select :label="__('randevu.minute_label')" :options="$minuteOptions" native:model="minute" class="flex-1" />
            </native:row>

            <native:select :label="__('randevu.reminder_label')" :options="$reminderOptions" native:model="reminderSelection" />
            @if(!empty($errors['reminder_rule']))
                <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-destructive">{{ $errors['reminder_rule'] }}</native:text>
            @endif

            <native:select :label="__('randevu.anniversary_label')" :options="$anniversaryOptions" native:model="anniversaryModeSelection" />
            <native:select :label="__('randevu.anniversary_lead_label')" :options="$anniversaryLeadOptions" native:model="anniversaryLeadSelection" />
            @if(!empty($errors['anniversary_mode']))
                <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-destructive">{{ $errors['anniversary_mode'] }}</native:text>
            @endif

            <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface">{{ __('randevu.period_label') }}</native:text>

            @include('native.partials.period-chips')

            @include('native.partials.phrase-preview', ['previewPhrase' => $this->previewPhrase()])

            <native:outlined-text-input :label="__('randevu.note_label')" :placeholder="__('randevu.note_placeholder')" native:model="note" multiline :min-lines="2" class="min-h-24" />
            @if(!empty($errors['note']))
                <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-destructive">{{ $errors['note'] }}</native:text>
            @endif

            @include('native.partials.color-picker', ['selected' => $color, 'errors' => $errors])

            @include('native.partials.cover-picker')

            <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant leading-relaxed">{{ __('randevu.form_hint') }}</native:text>

            <native:column class="w-full h-24" />
        </native:column>
    </native:scroll-view>

    <native:column class="w-full px-5 py-3 bg-theme-background safe-area-bottom">
        <native:button :label="__('randevu.save')" ios-icon="checkmark" android-icon="check" font="label" @press="save" variant="primary" class="w-full min-h-12" />
    </native:column>
</native:column>
