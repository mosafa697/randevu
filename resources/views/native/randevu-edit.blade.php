<native:column class="w-full h-full bg-theme-background">
    @php($rtl = \App\Services\AppLocale::isRtl())
    <native:row class="w-full px-5 pt-3">
        <native:button :label="$rtl ? '› '.__('randevu.back') : '‹ '.__('randevu.back')" variant="ghost" @navigate.back class="min-h-12 min-w-24" />
    </native:row>

    <native:scroll-view class="w-full flex-1">
        <native:column class="w-full px-5 py-3 gap-4">
            <native:outlined-text-input :label="__('randevu.title_label')" native:model="title" class="min-h-14" />
            @if(!empty($errors['title']))
                <native:text class="text-sm text-theme-destructive">{{ $errors['title'] }}</native:text>
            @endif

            @include('native.partials.segmented', ['name' => 'calendar', 'selected' => $calendar_mode])

            <native:row class="w-full gap-2">
                <native:button :label="__('randevu.quick_today')" variant="secondary" @press="setToday" class="flex-1 min-h-12" />
                <native:button :label="__('randevu.quick_plus7')" variant="secondary" @press="setPlus7" class="flex-1 min-h-12" />
                <native:button :label="__('randevu.quick_plus30')" variant="secondary" @press="setPlus30" class="flex-1 min-h-12" />
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

            <native:row class="w-full gap-2">
                <native:select :label="__('randevu.hour_label')" :options="$hourOptions" native:model="hour" class="flex-1" />
                <native:select :label="__('randevu.minute_label')" :options="$minuteOptions" native:model="minute" class="flex-1" />
            </native:row>

            <native:text font="heading" class="text-sm text-theme-on-surface">{{ __('randevu.period_label') }}</native:text>

            @include('native.partials.period-chips')

            @include('native.partials.phrase-preview', ['previewPhrase' => $this->previewPhrase()])

            <native:outlined-text-input :label="__('randevu.note_label')" native:model="note" multiline :min-lines="2" class="min-h-24" />
            @if(!empty($errors['note']))
                <native:text class="text-sm text-theme-destructive">{{ $errors['note'] }}</native:text>
            @endif

            @include('native.partials.color-picker', ['selected' => $color, 'errors' => $errors])

            @include('native.partials.cover-picker')

            @if(!$confirmingDelete)
                <native:button :label="__('randevu.delete')" font="label" @press="askDelete" variant="destructive" class="w-full min-h-12" />
            @else
                <native:text font="heading" class="text-base text-theme-on-background leading-relaxed">{{ __('randevu.delete_confirm') }}</native:text>
                <native:row class="w-full gap-2">
                    <native:button :label="__('randevu.keep')" font="label" @press="cancelDelete" variant="ghost" class="flex-1 min-h-12" />
                    <native:button :label="__('randevu.delete')" font="label" @press="destroy" variant="destructive" class="flex-1 min-h-12" />
                </native:row>
            @endif

            <native:column class="w-full h-24" />
        </native:column>
    </native:scroll-view>

    <native:column class="w-full px-5 py-3 bg-theme-background safe-area-bottom">
        <native:divider />
        <native:button :label="__('randevu.save_changes')" font="label" @press="update" variant="primary" class="w-full min-h-12" />
    </native:column>
</native:column>
