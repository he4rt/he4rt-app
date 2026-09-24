<?php

declare(strict_types=1);

namespace App\NativeLayouts;

use App\Icons\Android;
use App\Icons\Ios;
use Native\Mobile\Edge\Layouts\Builders\NavBar;
use Native\Mobile\Edge\Layouts\Builders\Tab;
use Native\Mobile\Edge\Layouts\Builders\TabBar;
use Native\Mobile\Edge\Layouts\NativeLayout;
use Native\Mobile\Edge\NativeComponent;

/**
 * Chrome for the app's main tab section: Início, Timeline, Eventos, Perfil.
 * Attached via Route::nativeGroup(layout: self::class, ...) in routes/web.php.
 */
class TabsLayout extends NativeLayout
{
    public function navBar(NativeComponent $screen): ?NavBar
    {
        return NavBar::make()
            ->titleView(view('native.partials.brand-title'))
            ->displayMode('inline');
    }

    public function tabBar(NativeComponent $screen): ?TabBar
    {
        return TabBar::make()
            ->activeColor(theme('primary'))
            ->add(Tab::link('Início', '/home', ios: Ios::HouseFill, android: Android::Home))
            ->add(Tab::link('Timeline', '/timeline', ios: Ios::BubbleLeftAndBubbleRightFill, android: Android::Forum))
            ->add(Tab::link('Eventos', '/eventos', ios: Ios::Calendar, android: Android::Event))
            ->add(Tab::link('Perfil', '/perfil', ios: Ios::PersonFill, android: Android::AccountCircle));
    }

    public function usesNativeChrome(): bool
    {
        return true;
    }
}
