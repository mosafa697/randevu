<native:column class="w-full h-full p-4 gap-4 bg-theme-background">
    <native:row class="w-full">
        <native:button :label="__('randevu.back')" @navigate.back />
    </native:row>

    <native:scroll-view class="w-full flex-1">
        <native:column class="w-full gap-4">
            <native:outlined-text-input :label="__('randevu.title_label')" :placeholder="__('randevu.title_placeholder')" native:model="title" class="rounded-[14px]" />
            @if(!empty($errors['title']))
                <native:text class="text-sm text-theme-destructive">{{ $errors['title'] }}</native:text>
            @endif

            <native:button-group :options="[__('randevu.mode_gregorian'), __('randevu.mode_hijri')]" native:model="calendarIndex" @change="calendarChanged" />

            @if($calendar_mode === 'hijri')
                <native:row class="w-full gap-2">
                    <native:select :label="__('randevu.day_label')" :options="$hDayOptions" native:model="h_day" class="flex-1" />
                    <native:select :label="__('randevu.month_label')" :options="$hMonthOptions" native:model="h_month" class="flex-1" />
                    <native:select :label="__('randevu.year_label')" :options="$hYearOptions" native:model="h_year" class="flex-1" />
                </native:row>
            @else
                <native:row class="w-full gap-2">
                    <native:select :label="__('randevu.day_label')" :options="$dayOptions" native:model="day" class="flex-1" />
                    <native:select :label="__('randevu.month_label')" :options="$monthOptions" native:model="month" class="flex-1" />
                    <native:select :label="__('randevu.year_label')" :options="$yearOptions" native:model="year" class="flex-1" />
                </native:row>
            @endif
            @if(!empty($errors['occurs_on']))
                <native:text class="text-sm text-theme-destructive">{{ $errors['occurs_on'] }}</native:text>
            @endif

            <native:text font="heading" class="text-sm font-semibold text-theme-on-surface">{{ __('randevu.period_label') }}</native:text>

            <native:row class="w-full gap-2">
                <native:chip :label="__('randevu.period_years')" :selected="$show_years" @change="toggleYears" />
                <native:chip :label="__('randevu.period_months')" :selected="$show_months" @change="toggleMonths" />
                <native:chip :label="__('randevu.period_days')" :selected="$show_days" @change="toggleDays" />
            </native:row>
            @if(!empty($errors['period_units']))
                <native:text class="text-sm text-theme-destructive">{{ $errors['period_units'] }}</native:text>
            @endif

            <native:outlined-text-input :label="__('randevu.note_label')" :placeholder="__('randevu.note_placeholder')" native:model="note" multiline :min-lines="2" class="rounded-[14px]" />
            @if(!empty($errors['note']))
                <native:text class="text-sm text-theme-destructive">{{ $errors['note'] }}</native:text>
            @endif

            @include('native.partials.color-picker', ['selected' => $color, 'errors' => $errors])

            <native:button :label="__('randevu.save')" @press="save" variant="primary" class="w-full rounded-lg" />
            <native:text class="text-sm text-theme-on-surface-variant">{{ __('randevu.form_hint') }}</native:text>
        </native:column>
    </native:scroll-view>
</native:column>
