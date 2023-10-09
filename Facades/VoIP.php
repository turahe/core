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

namespace Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Request;
use Modules\Core\Contracts\VoIP\VoIPClient;

/**
 * @mixin \Modules\Core\Contracts\VoIP\VoIPClient
 */
class VoIP extends Facade
{
    /**
     * Get the active URL for the calling request.
     */
    public static function getActiveUrl()
    {
        if (app()->isLocal()) {
            if ($url = static::getNgrokUrl()) {
                return $url;
            } elseif ($url = static::getExposeUrl()) {
                return $url;
            }
        }

        return config('app.url');
    }

    /**
     * Get ngrok url if served via ngrok.
     */
    public static function getNgrokUrl(): ?string
    {
        $request = Request::instance();

        if (str_contains($request->server('HTTP_X_ORIGINAL_HOST', ''), 'ngrok')) {
            return $request->server('HTTP_X_FORWARDED_PROTO').'://'.$request->server('HTTP_X_ORIGINAL_HOST');
        }

        // windows ngrok with the option --host-header
        if (str_contains($request->server('HTTP_X_FORWARDED_HOST', ''), 'ngrok')) {
            return $request->server('HTTP_X_FORWARDED_PROTO').'://'.$request->server('HTTP_X_FORWARDED_HOST');
        }

        return null;
    }

    /**
     * Get Expose url if server via Expose.
     */
    public static function getExposeUrl(): ?string
    {
        $request = Request::instance();

        if (! $request->hasHeader('x-expose-request-id')) {
            return null;
        }

        return $request->header('x-forwarded-proto').'://'
            .$request->header('x-forwarded-host');
    }

    /**
     * Get the events URL
     */
    public static function eventsUrl(): string
    {
        /** @var \Illuminate\Routing\UrlGenerator */
        $url = url();

        $url->forceRootUrl(static::getActiveUrl());

        return tap($url->route(config('core.voip.endpoints.events')), function () use ($url) {
            $url->forceRootUrl(null);
        });
    }

    /**
     * Get the new call URL
     */
    public static function callUrl(): string
    {
        /** @var \Illuminate\Routing\UrlGenerator */
        $url = url();

        $url->forceRootUrl(static::getActiveUrl());

        return tap($url->route(config('core.voip.endpoints.call')), function () use ($url) {
            $url->forceRootUrl(null);
        });
    }

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return VoIPClient::class;
    }
}
