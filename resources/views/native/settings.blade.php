<native:column class="w-full h-full bg-theme-background">
    <native:scroll-view class="w-full h-full">
        <native:column class="w-full px-5 py-3 gap-6">
            <native:column class="w-full gap-4 p-4 bg-theme-surface rounded-2xl border border-theme-outline-variant">
                <native:column class="w-full gap-2">
                    <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('lg') }} text-theme-on-surface leading-relaxed">{{ __('randevu.language_label') }}</native:text>
                    <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant leading-relaxed">{{ __('randevu.settings_language_helper') }}</native:text>
                    @include('native.partials.segmented', ['name' => 'language', 'selected' => $locale])
                </native:column>

                <native:divider />

                <native:column class="w-full gap-2">
                    <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('lg') }} text-theme-on-surface leading-relaxed">{{ __('randevu.theme_label') }}</native:text>
                    <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant leading-relaxed">{{ __('randevu.settings_theme_helper') }}</native:text>
                    @include('native.partials.segmented', ['name' => 'theme', 'selected' => $theme])
                </native:column>
            </native:column>

            <native:column class="w-full gap-4 p-4 bg-theme-surface rounded-2xl border border-theme-outline-variant">
                <native:column class="w-full gap-2">
                    <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('lg') }} text-theme-on-surface leading-relaxed">{{ __('randevu.notifications_label') }}</native:text>
                    <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant leading-relaxed">{{ __('randevu.notifications_helper') }}</native:text>
                    @if($notificationsEnabled)
                        <native:button :label="__('randevu.notifications_disable')" @press="disableNotifications" variant="secondary" class="w-full min-h-12" />
                    @else
                        <native:button :label="__('randevu.notifications_enable')" @press="enableNotifications" variant="primary" class="w-full min-h-12" />
                    @endif
                    <native:select :label="__('randevu.notification_default_reminder_label')" :options="$reminderOptions" native:model="reminderDefaultSelection" />
                    <native:select :label="__('randevu.notification_default_anniversary_label')" :options="$anniversaryOptions" native:model="anniversaryDefaultSelection" />
                    <native:button :label="__('randevu.notification_save_defaults')" @press="saveNotificationDefaults" variant="ghost" class="w-full min-h-12" />
                </native:column>

                <native:divider />

                <native:column class="w-full gap-2">
                    <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant leading-relaxed">{{ $notificationStatus }}</native:text>
                    @if($exactAlarmStatus !== '')
                        <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant leading-relaxed">{{ $exactAlarmStatus }}</native:text>
                        <native:button :label="__('randevu.notification_open_exact_settings')" @press="openExactAlarmSettings" variant="ghost" class="w-full min-h-12" />
                    @endif
                    @if($notificationsAvailable)
                        <native:button :label="__('randevu.notification_request_permission')" @press="requestNotificationPermission" variant="ghost" class="w-full min-h-12" />
                    @endif
                </native:column>
            </native:column>

            <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant text-center">{{ __('randevu.app_version', ['version' => $this->appVersion()]) }}</native:text>
        </native:column>
    </native:scroll-view>
</native:column>
