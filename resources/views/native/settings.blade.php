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
                        <native:button :label="__('randevu.notifications_disable')" ios-icon="bell.slash" android-icon="notifications_off" @press="disableNotifications" variant="secondary" class="w-full min-h-12" />
                    @else
                        <native:button :label="__('randevu.notifications_enable')" ios-icon="bell" android-icon="notifications" @press="enableNotifications" variant="primary" class="w-full min-h-12" />
                    @endif
                    <native:select :label="__('randevu.notification_default_reminder_label')" :options="$reminderOptions" native:model="reminderDefaultSelection" />
                    <native:select :label="__('randevu.notification_default_anniversary_label')" :options="$anniversaryOptions" native:model="anniversaryDefaultSelection" />
                </native:column>

                @if($notificationPermissionDenied || $exactAlarmPermissionNeeded)
                    <native:divider />
                    <native:column class="w-full gap-2">
                        @if($notificationPermissionDenied)
                            <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant leading-relaxed">{{ __('randevu.notification_permission_denied') }}</native:text>
                            <native:button :label="__('randevu.notification_request_permission')" ios-icon="bell.badge" android-icon="notifications_active" @press="requestNotificationPermission" variant="ghost" class="w-full min-h-12" />
                        @endif
                        @if($exactAlarmPermissionNeeded)
                            <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant leading-relaxed">{{ __('randevu.notification_exact_inexact') }}</native:text>
                            <native:button :label="__('randevu.notification_open_exact_settings')" ios-icon="alarm" android-icon="schedule" @press="openExactAlarmSettings" variant="ghost" class="w-full min-h-12" />
                        @endif
                    </native:column>
                @endif
            </native:column>

            <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant text-center">{{ __('randevu.app_version', ['version' => $this->appVersion()]) }}</native:text>
        </native:column>
    </native:scroll-view>
</native:column>
