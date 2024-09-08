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

use Akaunting\Money\Money;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Akaunting\Money\Currency;
use Turahe\Core\Facades\Menu;
use Turahe\Core\DatabaseState;
use Turahe\Core\Menu\MenuItem;
use Turahe\Core\Facades\Zapier;
use Illuminate\Support\Facades\URL;
use Turahe\Core\Updater\Migration;
use Illuminate\Support\Facades\View;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Workflow\Workflows;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Turahe\Core\Settings\SettingsMenu;
use Turahe\Core\Timeline\Timelineables;
use Illuminate\Console\Scheduling\Schedule;
use Turahe\Core\Facades\MailableTemplates;
use Turahe\Core\Settings\SettingsMenuItem;
use Illuminate\Database\Migrations\Migrator;
use Turahe\Core\Media\PruneStaleMediaAttachments;
use Turahe\Core\Workflow\WorkflowEventsSubscriber;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Turahe\Core\Synchronization\Jobs\PeriodicSynchronizations;
use Turahe\Core\Synchronization\Jobs\RefreshWebhookSynchronizations;

class CoreServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Core';

    protected string $moduleNameLower = 'core';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerTranslations();
//        $this->registerConfig();
//        $this->registerViews();
//        $this->loadMigrationsFrom(__DIR__. './../Database/Migrations');

        $this->app['events']->subscribe(WorkflowEventsSubscriber::class);
        $this->app['events']->listen(RequestHandled::class, Workflows::processQueue(...));
        $this->app['events']->listen(RequestHandled::class, Zapier::processQueue(...));

        View::composer(
            ['core::app', 'core::components/layouts/skin'],
            \Turahe\Core\Http\View\Composers\AppComposer::class
        );

        Workflows::registerEventOnlyTriggersListeners();

        Currency::macro('toMoney', function (string|int|float $value, bool $convert = true) {
            return new Money(! is_float($value) ? (float) $value : $value, $this, $convert);
        });

        Innoclapps::whenReadyForServing(Timelineables::discover(...));
        Innoclapps::booting($this->registerMenuItems(...));
        Innoclapps::booting($this->registerSettingsMenuItems(...));

        $this->registerMacros();
        $this->registerCommands();
        $this->scheduleTasks();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        DatabaseState::register([
            \Turahe\Core\Database\State\EnsureDefaultSettingsArePresent::class,
            \Turahe\Core\Database\State\EnsureMailableTemplatesArePresent::class,
            \Turahe\Core\Database\State\EnsureCountriesArePresent::class,
        ]);

        $this->app->singleton('timezone', \Turahe\Core\Timezone::class);
//        $this->app->when(Migration::class)->needs(Migrator::class)->give(fn () => $this->app['migrator']);
//        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__. './../../../config/config.php',
            $this->moduleNameLower
        );

        foreach (['html_purifier', 'fields', 'settings', 'updater', 'synchronization'] as $config) {
            $this->mergeConfigFrom(
                __DIR__. './../../../config/$config.php',
                $config
            );
        }
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);

        $sourcePath = __DIR__. './../resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__. './../resources/lang', $this->moduleNameLower);
    }

    /**
     * Register the menu items.
     */
    protected function registerMenuItems(): void
    {
        Menu::register(MenuItem::make(__('core::dashboard.insights'), '/dashboard', 'ChartSquareBar')
            ->position(40));

        Menu::register(MenuItem::make(__('core::settings.settings'), '/settings', 'Cog')
            ->canSeeWhen('is-super-admin')
            ->position(100));
    }

    /**
     * Register the core settings menu items.
     */
    protected function registerSettingsMenuItems(): void
    {
        SettingsMenu::register(
            SettingsMenuItem::make(__('core::app.integrations'))->icon('Globe')->order(20)
                ->withChild(SettingsMenuItem::make('Pusher', '/settings/integrations/pusher'), 'pusher')
                ->withChild(SettingsMenuItem::make('Microsoft', '/settings/integrations/microsoft'), 'microsoft')
                ->withChild(SettingsMenuItem::make('Google', '/settings/integrations/google'), 'google')
                ->withChild(SettingsMenuItem::make('Twilio', '/settings/integrations/twilio'), 'twilio')
                ->withChild(SettingsMenuItem::make('Zapier', '/settings/integrations/zapier'), 'zapier'),
            'integrations'
        );

        SettingsMenu::register(
            SettingsMenuItem::make(__('core::settings.security.security'))->icon('ShieldCheck')->order(60)
                ->withChild(SettingsMenuItem::make(__('core::settings.general'), '/settings/security'), 'security')
                ->withChild(SettingsMenuItem::make(__('core::settings.recaptcha.recaptcha'), '/settings/recaptcha'), 'recaptcha'),
            'security'
        );

        SettingsMenu::register(
            SettingsMenuItem::make(__('core::settings.system'))->icon('Cog')->order(70)
                ->withChild(SettingsMenuItem::make(__('core::update.update'), '/settings/update'), 'update')
                ->withChild(SettingsMenuItem::make(__('core::settings.tools.tools'), '/settings/tools'), 'tools')
                ->withChild(SettingsMenuItem::make(__('core::app.system_info'), '/settings/info'), 'system-info')
                ->withChild(SettingsMenuItem::make('Logs', '/settings/logs'), 'system-logs'),
            'system'
        );

        SettingsMenu::register(
            SettingsMenuItem::make(__('core::workflow.workflows'), '/settings/workflows', 'RocketLaunch')->order(40),
            'workflows'
        );

        SettingsMenu::register(
            SettingsMenuItem::make(__('core::mail_template.mail_templates'), '/settings/mailables', 'Mail')->order(50),
            'mailables'
        );

        tap(SettingsMenuItem::make(__('core::fields.fields'))->icon('SquaresPlus')->order(10), function ($item) {
            Innoclapps::registeredResources()
                ->filter(fn ($resource) => $resource::$fieldsCustomizable)
                ->each(function ($resource) use ($item) {
                    $item->withChild(
                        SettingsMenuItem::make(
                            $resource->singularLabel(),
                            "/settings/fields/{$resource->name()}"
                        ),
                        'fields-'.$resource->name()
                    );
                });
            SettingsMenu::register($item, 'fields');
        });
    }

    /**
     * Register the core commands.
     */
    public function registerCommands(): void
    {
        $this->commands([
            \Turahe\Core\Console\Commands\ClearExcelTmpPathCommand::class,
            \Turahe\Core\Console\Commands\ClearHtmlPurifierCacheCommand::class,
            \Turahe\Core\Console\Commands\ClearUpdaterTmpPathCommand::class,
            \Turahe\Core\Console\Commands\FinalizeUpdateCommand::class,
            \Turahe\Core\Console\Commands\IdentificationKeyGenerateCommand::class,
            \Turahe\Core\Console\Commands\UpdateCommand::class,
        ]);
    }

    /**
     * Schedule the document related tasks.
     */
    public function scheduleTasks(): void
    {
        /** @var \Illuminate\Console\Scheduling\Schedule */
        $schedule = $this->app->make(Schedule::class);

        $schedule->call(new PruneStaleMediaAttachments)->name('prune-stale-media-attachments')->daily();
        $schedule->job(PeriodicSynchronizations::class)->cron(config('synchronization.interval'));
        $schedule->job(RefreshWebhookSynchronizations::class)->daily();

        $schedule->call(function () {
            settings()->set(['_cron_job_last_user' => get_current_process_user()])->save();
        })->everyFiveMinutes();

        // Not needed?
        $schedule->call(function () {
            MailableTemplates::seedIfRequired();
        })->daily();
    }

    /**
     * Register application macros
     */
    public function registerMacros(): void
    {
        Request::macro('isForTimeline', fn () => Request::boolean('timeline'));

        Request::macro('getWith', function () {
            return Str::of(Request::instance()->get('with', ''))->explode(';')->filter()->all();
        });

        Str::macro('isBase64Encoded', new \Turahe\Core\Macros\Str\IsBase64Encoded);
        Str::macro('clickable', new \Turahe\Core\Macros\Str\ClickableUrls);

        Arr::macro('toObject', new \Turahe\Core\Macros\Arr\ToObject);
        Arr::macro('valuesAsString', new \Turahe\Core\Macros\Arr\CastValuesAsString);

        Request::macro('isSearching', new \Turahe\Core\Macros\Request\IsSearching);
        Request::macro('isZapier', new \Turahe\Core\Macros\Request\IsZapier);

        Filesystem::macro('deepCleanDirectory', new \Turahe\Core\Macros\Filesystem\DeepCleanDirectory);

        \Turahe\Core\Macros\Criteria\QueryCriteria::register();

        URL::macro('asAppUrl', function ($extra = '') {
            return rtrim(config('app.url'), '/').($extra ? '/'.$extra : '');
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * Get the publishable view paths.
     */
    private function getPublishableViewPaths(): array
    {
        $paths = [];

        foreach ($this->app['config']->get('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }
}
