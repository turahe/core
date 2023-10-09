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

namespace Modules\Core\Http\View\Composers;

use Illuminate\View\View;
use Modules\Core\Models\Tag;
use Modules\Core\Facades\Menu;
use Modules\Core\Facades\VoIP;
use Modules\Core\Facades\Fields;
use Modules\Core\Facades\ReCaptcha;
use Modules\Core\Resource\Resource;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Facades\Innoclapps;
use App\Installer\RequirementsChecker;
use Modules\Core\Highlights\Highlights;
use Modules\Core\Settings\SettingsMenu;
use Modules\Core\Http\Resources\TagResource;

class AppComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        Innoclapps::boot();

        $config = [];

        $config['apiURL'] = url(\Modules\Core\Application::API_PREFIX);
        $config['url'] = url('/');
        $config['locale'] = app()->getLocale();
        $config['locales'] = Innoclapps::locales();
        $config['fallback_locale'] = config('app.fallback_locale');
        $config['timezone'] = config('app.timezone');
        $config['is_secure'] = request()->secure();

        if (Innoclapps::requiresMaintenance()) {
            $this->addDataToView($view, $config);

            return;
        }

        $config['broadcasting'] = [
            'default'    => config('broadcasting.default'),
            'connection' => config('broadcasting.connections.'.config('broadcasting.default')),
        ];

        $config['options'] = [
            'time_format'  => config('core.time_format'),
            'date_format'  => config('core.date_format'),
            'company_name' => config('app.name'),
            'logo_light'   => config('core.logo.light'),
            'logo_dark'    => config('core.logo.dark'),
        ];

        $config['max_upload_size'] = config('mediable.max_size');
        $config['privacyPolicyUrl'] = privacy_url();

        $config['date_formats'] = config('core.date_formats');
        $config['time_formats'] = config('core.time_formats');

        $config['currency'] = with(Innoclapps::currency(), function ($currency) {
            return array_merge(
                $currency->toArray()[$isoCode = $currency->getCurrency()],
                ['iso_code' => $isoCode]
            );
        });

        $config['reCaptcha'] = [
            'configured' => ReCaptcha::configured(),
            'validate'   => ReCaptcha::shouldShow(),
            'siteKey'    => ReCaptcha::getSiteKey(),
        ];

        // Required in FormField Group for externals forms e.q. web form
        $config['fields'] = [
            'views' => [
                'update' => Fields::UPDATE_VIEW,
                'create' => Fields::CREATE_VIEW,
                'detail' => Fields::DETAIL_VIEW,
            ],
        ];

        // Sensitive settings are not included in this list
        $config['options'] = array_merge($config['options'], [
            'first_day_of_week'       => settings('first_day_of_week'),
            'disable_password_forgot' => forgot_password_is_disabled(),
        ]);

        // Authenticated user config
        if (Auth::check()) {
            if (Auth::user()->isSuperAdmin()) {
                $config['purchase_key'] = config('app.purchase_key');
            }

            $config['resources'] = Innoclapps::registeredResources()->mapWithKeys(function (Resource $resource) {
                return [$resource->name() => $resource->jsonSerialize()];
            });

            $config['settings'] = [
                'menu' => SettingsMenu::all(),
            ];

            $config['tags'] = TagResource::collection(Tag::get());

            $config['fields'] = array_merge($config['fields'], [
                'custom_fields'       => Fields::customFieldable(),
                'custom_field_prefix' => config('fields.custom_fields.prefix'),
                'groups'              => collect(Innoclapps::registeredResources())->filter(
                    fn ($resource) => Fields::has($resource->name())
                )->mapWithKeys(
                    fn (Resource $resource) => [$resource->name() => $resource->name()]
                )->all(),
            ]);

            $config['menu'] = Menu::get();

            $config['highlights'] = Highlights::get();

            $config['notifications_settings'] = Innoclapps::notificationsPreferences();

            $config['soft_deletes'] = [
                'prune_after' => config('core.soft_deletes.prune_after'),
            ];

            $config['contentbuilder'] = [
                'fonts' => config('contentbuilder.fonts'),
            ];

            $config['microsoft'] = [
                'client_id' => config('core.microsoft.client_id'),
            ];

            $config['google'] = [
                'client_id' => config('core.google.client_id'),
            ];

            $config['voip'] = [
                'client'    => config('core.voip.client'),
                'endpoints' => [
                    'call'   => VoIP::callUrl(),
                    'events' => VoIP::eventsUrl(),
                ],
            ];

            $config['favourite_colors'] = Innoclapps::favouriteColors();

            $requirements = new RequirementsChecker;

            $config['requirements'] = [
                'imap' => $requirements->passes('imap'),
                'zip'  => $requirements->passes('zip'),
            ];

            $config['associations'] = [
                'common' => Innoclapps::getResourcesNames(),
            ];
        }

        $this->addDataToView($view, array_merge_recursive($config, Innoclapps::getDataProvidedToScript()));
    }

    /**
     * Add data to the given view
     */
    protected function addDataToView(View $view, array $config): void
    {
        $lang = get_generated_lang(app()->getLocale());

        $view->with(['config' => $config, 'lang' => $lang]);
    }
}
