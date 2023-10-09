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

namespace Modules\Core\Providers;

use Modules\Core\VoIP\VoIPManager;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Contracts\VoIP\VoIPClient;
use Illuminate\Contracts\Support\DeferrableProvider;

class VoIPServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(VoIPClient::class, function ($app) {
            return new VoIPManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [VoIPClient::class];
    }
}
