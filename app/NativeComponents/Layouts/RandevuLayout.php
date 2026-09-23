<?php

namespace App\NativeComponents\Layouts;

use Native\Mobile\Edge\Layouts\Builders\NavBar;
use Native\Mobile\Edge\Layouts\Builders\Tab;
use Native\Mobile\Edge\Layouts\Builders\TabBar;
use Native\Mobile\Edge\Layouts\NativeLayout;
use Native\Mobile\Edge\NativeComponent;

class RandevuLayout extends NativeLayout
{
    public function navBar(NativeComponent $screen): ?NavBar
    {
        return NavBar::make()->title($screen->navTitle());
    }

    public function tabBar(NativeComponent $screen): ?TabBar
    {
        return TabBar::make()
            ->labelVisibility('labeled')
            ->add(Tab::link(__('randevu.tab_follow'), '/', ios: 'calendar', android: 'calendar_month'))
            ->add(Tab::link(__('randevu.tab_new'), '/create', ios: 'plus', android: 'add'))
            ->add(Tab::link(__('randevu.tab_settings'), '/settings', ios: 'gear', android: 'settings'));
    }
}
