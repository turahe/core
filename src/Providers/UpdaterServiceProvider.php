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

use GuzzleHttp\Client;
use Turahe\Core\Updater\Patcher;
use Turahe\Core\Updater\Updater;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class UpdaterServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Updater::class, function ($app) {
            return new Updater(new Client, new Filesystem, [
                'purchase_key'      => $app['config']->get('updater.purchase_key'),
                'archive_url'       => $app['config']->get('updater.archive_url'),
                'download_path'     => $app['config']->get('updater.download_path'),
                'version_installed' => $app['config']->get('updater.version_installed'),
                'exclude_folders'   => $app['config']->get('updater.exclude_folders'),
                'exclude_files'     => $app['config']->get('updater.exclude_files'),
                'permissions'       => $app['config']->get('updater.permissions'),
            ]);
        });

        $this->app->singleton(Patcher::class, function ($app) {
            return new Patcher(new Client, new Filesystem, [
                'purchase_key'      => $app['config']->get('updater.purchase_key'),
                'patches_url'       => $app['config']->get('updater.patches_archive_url'),
                'download_path'     => $app['config']->get('updater.download_path'),
                'version_installed' => $app['config']->get('updater.version_installed'),
                'permissions'       => $app['config']->get('updater.permissions'),
            ]);
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [Updater::class, Patcher::class];
    }
}
