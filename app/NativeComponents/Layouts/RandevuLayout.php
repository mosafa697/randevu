<?php

namespace App\NativeComponents\Layouts;

use App\Services\AppTheme;
use Native\Mobile\Edge\Layouts\Builders\NavBar;
use Native\Mobile\Edge\Layouts\Builders\NavAction;
use Native\Mobile\Edge\Layouts\Builders\Tab;
use Native\Mobile\Edge\Layouts\Builders\TabBar;
use Native\Mobile\Edge\Layouts\NativeLayout;
use Native\Mobile\Edge\NativeComponent;

class RandevuLayout extends NativeLayout
{
    public function navBar(NativeComponent $screen): ?NavBar
    {
        $isLight = AppTheme::current() === 'light';

        // Explicit chrome colors: the drawn bar otherwise falls back to the
        // OS scheme (white text on Android in system dark, .primary on iOS),
        // which goes invisible when the app palette is forced the other way.
        return NavBar::make()
            ->title($screen->navTitle())
            ->backgroundColor((string) AppTheme::token('background', '#FBF9F4'))
            ->textColor((string) AppTheme::token('on-background', '#2B2740'))
            ->action(
                NavAction::make('toggle-theme')
                    ->icon(
                        ios: $isLight ? 'moon' : 'sun.max',
                        android: $isLight ? 'dark_mode' : 'light_mode'
                    )
                    ->a11yLabel(__('randevu.theme_toggle_a11y'))
                    ->press('toggleTheme')
            );
    }

    public function tabBar(NativeComponent $screen): ?TabBar
    {
        return TabBar::make()
            ->labelVisibility('labeled')
            ->backgroundColor((string) AppTheme::token('background', '#FBF9F4'))
            ->textColor((string) AppTheme::token('on-surface-variant', '#69647D'))
            ->activeColor((string) AppTheme::token('primary', '#6F63DB'))
            ->add(Tab::link(__('randevu.tab_follow'), '/', ios: 'calendar', android: 'calendar_month'))
            ->add(Tab::link(__('randevu.tab_memories'), '/memories', ios: 'clock', android: 'history'))
            ->add(Tab::link(__('randevu.tab_new'), '/create', ios: 'plus', android: 'add'))
            ->add(Tab::link(__('randevu.tab_settings'), '/settings', ios: 'gear', android: 'settings'));
    }
}
