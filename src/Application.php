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

namespace Turahe\Core;

use Illuminate\Support\Arr;
use Akaunting\Money\Currency;
use Turahe\Core\Models\Model;
use Illuminate\Support\Collection;
use Turahe\Core\Resource\Resource;
use Turahe\Core\Updater\Migration;
use Illuminate\Support\Facades\File;
use Turahe\Core\Updater\UpdateFinalizer;
use Symfony\Component\Process\PhpExecutableFinder;
use Turahe\Core\Contracts\HasNotificationsSettings;

class Application
{
    /**
     * The application version.
     *
     * @var string
     */
    const VERSION = '1.2.2';

    /**
     * System name that will be used over the system.
     *
     * E.q. for automated actions performed by the application or logs
     *
     * @var string
     */
    const SYSTEM_NAME = 'System';

    /**
     * Indicates the installed file.
     *
     * NOTE: Used in detached.php
     *
     * @var string
     */
    const INSTALLED_FILE = '.installed';

    /**
     * The API prefix for the application.
     *
     * @var string
     */
    const API_PREFIX = 'api';

    /**
     * Indicates whether there is import in progress.
     */
    protected static bool $importStatus = false;

    /**
     * Indicates if the application has "booted".
     */
    protected bool $booted = false;

    /**
     * The array of booting callbacks.
     */
    protected array $bootingCallbacks = [];

    /**
     * The array of booted callbacks.
     */
    protected array $bootedCallbacks = [];

    /**
     * Available notifications.
     */
    protected static array $notifications = [];

    /**
     * Stores the default notifications data when disabling the notifications
     * then later we can use to enable the notifications again with the default values.
     */
    protected static array $disabledNotificationsConfig = [];

    /**
     * Requires maintenance checks cache.
     */
    protected static ?bool $requiresMaintenance = null;

    /**
     * Registered resources.
     */
    public static ?Collection $resources = null;

    /**
     * Cache of resource names keyed by the model name.
     */
    public static $resourcesByModel = [];

    /**
     * Provide data to views.
     */
    public static array $provideToScript = [];

    /**
     * All the custom registered scripts.
     */
    public static array $scripts = [];

    /**
     * All the custom registered styles.
     */
    public static array $styles = [];

    /**
     * Get the version number of the application.
     */
    public function version(): string
    {
        return static::VERSION;
    }

    /**
     * The the system name
     */
    public function systemName(): string
    {
        return static::SYSTEM_NAME;
    }

    /**
     * Determine if the application has booted.
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Boot the application's service providers.
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        // Once the application has booted we will also fire some "booted" callbacks
        // for any listeners that need to do work after this initial booting gets
        // finished. This is useful when ordering the boot-up processes we run.
        $this->fireAppCallbacks($this->bootingCallbacks);

        $this->booted = true;

        $this->fireAppCallbacks($this->bootedCallbacks);
    }

    /**
     * Register a new boot listener.
     */
    public function booting(callable $callback): void
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new "booted" listener.
     */
    public function booted(callable $callback): void
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $this->fireAppCallbacks([$callback]);
        }
    }

    /**
     * Call the booting callbacks for the application.
     */
    protected function fireAppCallbacks(array $callbacks): void
    {
        foreach ($callbacks as $callback) {
            call_user_func($callback, $this);
        }
    }

    /**
     * Get the application favourite colors
     */
    public static function favouriteColors(): array
    {
        return config('core.colors');
    }

    /**
     * Disable the application notifications.
     *
     * broadcasting, mail
     *
     * @see enableNotifications
     */
    public static function disableNotifications(): void
    {
        $defaults = [config('broadcasting.default'), config('mail.driver')];

        // Disable
        config(['broadcasting.default' => 'null']);
        config(['mail.driver' => 'array']);

        // Store the current values so we can use them to enable the notifications again
        static::$disabledNotificationsConfig['broadcasting.default'] = $defaults[0];
        static::$disabledNotificationsConfig['mail.driver'] = $defaults[1];
    }

    /**
     * Check whether the application has broadcasting configured.
     */
    public static function hasBroadcastingConfigured(): bool
    {
        $keyOptions = Arr::only(
            config('broadcasting.connections.pusher'),
            ['key', 'secret', 'app_id']
        );

        return count(array_filter($keyOptions)) === count($keyOptions);
    }

    /**
     * Enable notifications again.
     *
     * Used only after disableNotifications method is called
     *
     * @see disableNotifications
     */
    public static function enableNotifications(): void
    {
        foreach (static::$disabledNotificationsConfig as $key => $value) {
            config([$key => $value]);
        }

        static::$disabledNotificationsConfig = [];
    }

    /**
     * Register the given notifications
     */
    public static function notifications(array $notifications): void
    {
        static::$notifications = array_unique(
            array_merge(static::$notifications, $notifications)
        );
    }

    /**
     * Register the application notifications in the given directory
     */
    public static function notificationsIn(string $directory): void
    {
        static::notifications(
            SubClassDiscovery::make(Notification::class)->in($directory)->find()
        );
    }

    /**
     * Get all the notifications information for front-end
     */
    public static function notificationsPreferences(?HasNotificationsSettings $notifiable = null): array
    {
        return collect(static::$notifications)->filter(function ($notification) {
            return $notification::configurable();
        })->map(function ($notification) use ($notifiable) {
            return array_merge([
                'key'         => $notification::key(),
                'name'        => $notification::name(),
                'description' => $notification::description(),

                'channels' => $channels = collect($notification::availableChannels())
                    ->reject(fn ($channel) => $channel === 'broadcast')->values(),
            ], is_null($notifiable) ? [] : ['availability' => array_merge(
                $channels->mapWithKeys(fn ($channel) => [$channel => true])->all(),
                $notifiable->getNotificationPreference($notification::key())
            )]);
        })->values()->all();
    }

    /**
     * Check whether the notifications are disabled
     */
    public static function notificationsDisabled(): bool
    {
        return count(static::$disabledNotificationsConfig) > 0;
    }

    /**
     * Check whether there is import in progress
     */
    public static function isImportInProgress(): bool
    {
        return static::$importStatus == 'in-progress';
    }

    /**
     * Check whether there is import mapping in progress
     */
    public static function isImportMapping(): bool
    {
        return static::$importStatus == 'mapping';
    }

    /**
     * Change the import status
     */
    public static function setImportStatus(bool|string $status = 'mapping'): void
    {
        static::$importStatus = $status;
    }

    /**
     * Get the import status
     */
    public static function importStatus(): bool|string
    {
        return static::$importStatus;
    }

    /**
     * Check whether the application is installed
     */
    public static function isAppInstalled(): bool
    {
        return file_exists(static::installedFileLocation());
    }

    /**
     * Run callback when the application is already installed
     */
    public static function whenInstalled(callable $callback): void
    {
        if (! static::isAppInstalled()) {
            return;
        }

        call_user_func($callback);
    }

    /**
     * Mark the application as installed
     */
    public static function markAsInstalled(): bool
    {
        if (! file_exists(static::installedFileLocation())) {
            $bytes = file_put_contents(
                static::installedFileLocation(),
                'Installation Date: '.date('Y-m-d H:i:s').PHP_EOL.'Version: '.static::VERSION
            );

            return $bytes !== false;
        }

        return false;
    }

    /**
     * Get the installed file locateion
     */
    public static function installedFileLocation(): string
    {
        return storage_path(static::INSTALLED_FILE);
    }

    /**
     * Get the available registered resources names
     */
    public static function getResourcesNames(): array
    {
        return static::registeredResources()->map(
            fn ($resource) => $resource->name()
        )->all();
    }

    /**
     * Get all the registered resources
     *
     * @return \Illuminate\Support\Collection
     */
    public static function registeredResources()
    {
        return is_null(static::$resources) ? collect([]) : static::$resources;
    }

    /**
     * Get the resource class by a given name.
     */
    public static function resourceByName(string $name): ?Resource
    {
        return static::registeredResources()->first(
            fn ($resource) => $resource::name() === $name
        );
    }

    /**
     * Get the resource class by a given model
     */
    public static function resourceByModel(string|Model $model): ?Resource
    {
        if (is_object($model)) {
            $model = $model::class;
        }

        if (isset(static::$resourcesByModel[$model])) {
            return static::$resourcesByModel[$model];
        }

        $resource = static::registeredResources()->first(
            fn ($value) => $value::$model === $model
        );

        return static::$resourcesByModel[$model] = $resource;
    }

    /**
     * Get the globally searchable resources
     *
     * @return \Illuminate\Support\Collection
     */
    public static function globallySearchableResources()
    {
        return static::registeredResources()->filter(
            fn ($resource) => $resource::$globallySearchable
        );
    }

    /**
     * Register the given resources.
     */
    public static function resources(array $resources): void
    {
        static::$resources = static::registeredResources()
            ->merge($resources)->unique(function ($resource) {
                return is_string($resource) ? $resource : $resource::class;
            })->map(function ($resource) {
                return is_string($resource) ? new $resource : $resource;
            })->sortBy(fn ($resource) => $resource::name());
    }

    /**
     * Register all of the resource classes in the given directory.
     */
    public static function resourcesIn(string $directory): void
    {
        static::resources(
            SubClassDiscovery::make(Resource::class)->in($directory)->find()
        );
    }

    /**
     * Provide data to front-end
     */
    public static function provideToScript(array $data): void
    {
        static::$provideToScript = array_merge_recursive(static::$provideToScript, $data);
    }

    /**
     * Get the data provided to script
     */
    public static function getDataProvidedToScript(): array
    {
        return static::$provideToScript;
    }

    /**
     * Register the given script file with the application.
     */
    public static function script(string $name, string $path): void
    {
        static::$scripts[$name] = $path;
    }

    /**
     * Get all of the additional registered scripts
     */
    public static function scripts(): array
    {
        return static::$scripts;
    }

    /**
     * Register the given CSS file with the application.
     */
    public static function style(string $name, string $path): void
    {
        static::$styles[$name] = $path;
    }

    /**
     * Get all of the additional registered stylesheets
     */
    public static function styles(): array
    {
        return static::$styles;
    }

    /**
     * Get the application currency
     */
    public static function currency(string|Currency|null $currency = null): Currency
    {
        if ($currency instanceof Currency) {
            return $currency;
        }

        return new Currency($currency ?: config('core.currency'));
    }

    /**
     * Check whether the app is ready for serving
     */
    public static function readyForServing(): bool
    {
        return static::isAppInstalled() && ! static::requiresUpdateFinalization();
    }

    /**
     * Check whether the app is ready for serving
     */
    public static function whenReadyForServing(callable $callback): void
    {
        if (! static::readyForServing()) {
            return;
        }

        call_user_func($callback);
    }

    /**
     * Check whether migration is required
     */
    public static function requiresMigration(): bool
    {
        return app(Migration::class)->needed();
    }

    /**
     * Check whether update finalization is required
     */
    public static function requiresUpdateFinalization(): bool
    {
        return app(UpdateFinalizer::class)->needed();
    }

    /**
     * Check whether the app requires maintenance
     */
    public static function requiresMaintenance(): bool
    {
        if (is_null(static::$requiresMaintenance)) {
            static::$requiresMaintenance = static::requiresUpdateFinalization() || static::requiresMigration();
        }

        return static::$requiresMaintenance;
    }

    /**
     * Get the available locales
     */
    public static function locales(): array
    {
        return once(function () {
            return collect(File::directories(lang_path()))
                ->map(fn ($locale) => basename($locale))
                ->reject(fn ($locale) => $locale === 'vendor')
                ->unique()
                ->values()
                ->all();
        });
    }

    /**
     * Check whether process can be run.
     */
    public static function canRunProcess(): bool
    {
        return function_exists('proc_open') && function_exists('proc_close');
    }

    /**
     * Get the PHP executable path
     */
    public static function getPhpExecutablePath(): ?string
    {
        $phpFinder = new PhpExecutableFinder;

        try {
            return $phpFinder->find();
        } catch (\Exception) {
            return null;
        }
    }
}
