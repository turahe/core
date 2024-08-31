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

use Illuminate\Support\ServiceProvider;
use Turahe\Core\Contracts\OAuth\StateStorage;
use Turahe\Core\OAuth\State\StateStorageManager;
use Illuminate\Contracts\Support\DeferrableProvider;

class OAuthServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StateStorage::class, function ($app) {
            return new StateStorageManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [StateStorage::class];
    }
}
