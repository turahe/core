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
use Illuminate\Support\ServiceProvider;
use Turahe\Core\Contracts\VoIP\VoIPClient;
use Turahe\Core\VoIP\VoIPManager;

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
