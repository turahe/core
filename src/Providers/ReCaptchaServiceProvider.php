<?php
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2022-2023.
 *
 *
 */

namespace Turahe\Core\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Turahe\Core\ReCaptcha;

class ReCaptchaServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('recaptcha', function ($app) {
            return (new ReCaptcha(Request::instance()))
                ->setSiteKey($app['config']->get('core.recaptcha.site_key'))
                ->setSecretKey($app['config']->get('core.recaptcha.secret_key'))
                ->setSkippedIps($app['config']->get('core.recaptcha.ignored_ips', []));
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['recaptcha'];
    }
}
