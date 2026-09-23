<native:column class="w-full h-full p-4 gap-4 bg-theme-background">
    <native:row class="w-full">
        <native:button :label="__('randevu.back')" @navigate.back />
    </native:row>

    <native:outlined-text-input :label="__('randevu.title_label')" :placeholder="__('randevu.title_placeholder')" native:model="title" />
    @if(!empty($errors['title']))
        <native:text class="text-sm text-theme-destructive">{{ $errors['title'] }}</native:text>
    @endif

    <native:row class="w-full gap-2">
        <native:button :label="__('randevu.mode_gregorian')" @press="useGregorian" />
        <native:button :label="__('randevu.mode_hijri')" @press="useHijri" />
    </native:row>

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

    <native:text class="text-sm font-semibold text-theme-on-surface">{{ __('randevu.period_label') }}</native:text>

    <native:row class="w-full gap-2">
        <native:checkbox :label="__('randevu.period_years')" native:model="show_years" />
        <native:checkbox :label="__('randevu.period_months')" native:model="show_months" />
        <native:checkbox :label="__('randevu.period_days')" native:model="show_days" />
    </native:row>
    @if(!empty($errors['period_units']))
        <native:text class="text-sm text-theme-destructive">{{ $errors['period_units'] }}</native:text>
    @endif

    <native:outlined-text-input :label="__('randevu.note_label')" :placeholder="__('randevu.note_placeholder')" native:model="note" multiline :min-lines="2" />
    @if(!empty($errors['note']))
        <native:text class="text-sm text-theme-destructive">{{ $errors['note'] }}</native:text>
    @endif

    <native:text class="text-sm text-theme-on-surface-variant">{{ __('randevu.form_hint') }}</native:text>

    <native:button :label="__('randevu.save')" @press="save" />
</native:column>
