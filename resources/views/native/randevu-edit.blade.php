<native:column class="w-full h-full bg-theme-background">
    <native:scroll-view class="w-full flex-1">
        <native:column class="w-full px-5 py-3 gap-4">
            <native:column class="w-full p-4 gap-4 bg-theme-surface rounded-2xl border border-theme-outline-variant">
                <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('base') }} text-theme-on-surface">{{ __('randevu.section_date_time') }}</native:text>
                @include('native.partials.inline-calendar')
                <native:row class="w-full gap-2">
                    <native:select :label="__('randevu.hour_label')" :options="$hourOptions" native:model="hour" class="flex-1" />
                    <native:select :label="__('randevu.minute_label')" :options="$minuteOptions" native:model="minute" class="flex-1" />
                </native:row>
            </native:column>

            <native:column class="w-full p-4 gap-4 bg-theme-surface rounded-2xl border border-theme-outline-variant">
                <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('base') }} text-theme-on-surface">{{ __('randevu.section_display_note') }}</native:text>
                <native:outlined-text-input :label="__('randevu.title_label')" native:model="title" class="min-h-14" />
                @if(!empty($errors['title']))
                    <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-destructive">{{ $errors['title'] }}</native:text>
                @endif

                <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface">{{ __('randevu.period_label') }}</native:text>
                @include('native.partials.period-chips')
                @include('native.partials.phrase-preview', ['previewPhrase' => $this->previewPhrase()])

                <native:outlined-text-input :label="__('randevu.note_label')" native:model="note" multiline :min-lines="2" class="min-h-24" />
                @if(!empty($errors['note']))
                    <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-destructive">{{ $errors['note'] }}</native:text>
                @endif
            </native:column>

            <native:column class="w-full p-4 gap-4 bg-theme-surface rounded-2xl border border-theme-outline-variant">
                <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('base') }} text-theme-on-surface">{{ __('randevu.section_appearance') }}</native:text>
                @include('native.partials.color-picker', ['selected' => $color, 'errors' => $errors])
                @include('native.partials.cover-picker')
            </native:column>

            <native:column class="w-full p-4 gap-4 bg-theme-surface rounded-2xl border border-theme-outline-variant">
                <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('base') }} text-theme-on-surface">{{ __('randevu.section_notifications') }}</native:text>
                <native:checkbox :label="__('randevu.notification_use_defaults')" native:model="useNotificationDefaults" />
                @if(!$useNotificationDefaults)
                    <native:select :label="__('randevu.reminder_label')" :options="$reminderOptions" native:model="reminderSelection" />
                    @if(!empty($errors['reminder_rule']))
                        <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-destructive">{{ $errors['reminder_rule'] }}</native:text>
                    @endif

                    <native:select :label="__('randevu.anniversary_label')" :options="$anniversaryOptions" native:model="anniversaryModeSelection" />
                    <native:select :label="__('randevu.anniversary_lead_label')" :options="$anniversaryLeadOptions" native:model="anniversaryLeadSelection" />
                    @if(!empty($errors['anniversary_mode']))
                        <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-destructive">{{ $errors['anniversary_mode'] }}</native:text>
                    @endif
                @endif
            </native:column>

            @if(!$confirmingDelete)
                <native:button :label="__('randevu.delete')" ios-icon="trash" android-icon="delete" font="label" @press="askDelete" variant="destructive" class="w-full min-h-12" />
            @else
                <native:column class="w-full p-4 gap-3 bg-theme-surface rounded-2xl border border-theme-outline-variant">
                    <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('base') }} text-theme-on-background leading-relaxed">{{ __('randevu.delete_confirm') }}</native:text>
                    <native:row class="w-full gap-2">
                        <native:button :label="__('randevu.keep')" font="label" @press="cancelDelete" variant="ghost" class="flex-1 min-h-12" />
                        <native:button :label="__('randevu.delete')" ios-icon="trash" android-icon="delete" font="label" @press="destroy" variant="destructive" class="flex-1 min-h-12" />
                    </native:row>
                </native:column>
            @endif

            <native:column class="w-full h-24" />
        </native:column>
    </native:scroll-view>

    <native:column class="w-full px-5 py-3 bg-theme-background safe-area-bottom">
        <native:button :label="__('randevu.save_changes')" ios-icon="checkmark" android-icon="check" font="label" @press="update" variant="primary" class="w-full min-h-12" />
    </native:column>
</native:column>
