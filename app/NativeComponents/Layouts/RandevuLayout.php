<?php

namespace App\NativeComponents\Layouts;

use App\Services\AppTheme;
use Native\Mobile\Edge\Layouts\Builders\NavBar;
use Native\Mobile\Edge\Layouts\Builders\Tab;
use Native\Mobile\Edge\Layouts\Builders\TabBar;
use Native\Mobile\Edge\Layouts\NativeLayout;
use Native\Mobile\Edge\NativeComponent;

class RandevuLayout extends NativeLayout
{
    public function usesNativeChrome(): bool
    {
        return true;
    }

    public function navBar(NativeComponent $screen): ?NavBar
    {
        // Explicit chrome colors: the drawn bar otherwise falls back to the
        // OS scheme (white text on Android in system dark, .primary on iOS),
        // which goes invisible when the app palette is forced the other way.
        // Theme switching lives on the Settings screen — no header action.
        return NavBar::make()
            ->title($screen->navTitle())
            ->font('heading')
            ->backgroundColor((string) AppTheme::token('background', '#FBF9F4'))
            ->textColor((string) AppTheme::token('on-background', '#2B2740'));
    }

    public function tabBar(NativeComponent $screen): ?TabBar
    {
        return TabBar::make()
            ->dark(AppTheme::current() === 'dark')
            ->labelVisibility('labeled')
            ->textColor((string) AppTheme::token('on-surface-variant', '#69647D'))
            ->activeColor((string) AppTheme::token('primary', '#6F63DB'))
            ->font('label')
            ->add(Tab::link(__('randevu.tab_follow'), '/', ios: 'calendar', android: 'calendar_month'))
            ->add(Tab::link(__('randevu.tab_memories'), '/memories', ios: 'clock', android: 'history'))
            ->add(Tab::link(__('randevu.tab_new'), '/create', ios: 'plus', android: 'add'))
            ->add(Tab::link(__('randevu.tab_settings'), '/settings', ios: 'gear', android: 'settings'));
    }
}
