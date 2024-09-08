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

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Turahe\Core\DatabaseState;
use Turahe\Core\Workflow\WorkflowEventsSubscriber;
use Turahe\Core\Workflow\Workflows;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {


        $this->app['events']->subscribe(WorkflowEventsSubscriber::class);
        $this->app['events']->listen(RequestHandled::class, Workflows::processQueue(...));

        Workflows::registerEventOnlyTriggersListeners();

        Currency::macro('toMoney', function (string|int|float $value, bool $convert = true) {
            return new Money(! is_float($value) ? (float) $value : $value, $this, $convert);
        });

        $this->registerMacros();
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
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'./../../../config/config.php',
            'core'
        );
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

        Filesystem::macro('deepCleanDirectory', new \Turahe\Core\Macros\Filesystem\DeepCleanDirectory);

        \Turahe\Core\Macros\Criteria\QueryCriteria::register();

        URL::macro('asAppUrl', function ($extra = '') {
            return rtrim(config('app.url'), '/').($extra ? '/'.$extra : '');
        });
    }
}
