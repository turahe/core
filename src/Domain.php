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

use Illuminate\Support\Str;

class Domain
{
    /**
     * Extract the top level domain name from a given URL
     *
     * The function should be used only when most likely the
     * URL will contain a valid domain name
     *
     * @param  string  $url
     * @return string|null
     */
    public static function extractFromUrl($url)
    {
        if (empty($url)) {
            return $url;
        }

        $pieces = parse_url($url);
        $domain = $pieces['host'] ?? '';

        // Handle non https?:// starting URL's
        if (empty($domain)) {
            $pathPieces = explode('/', $pieces['path'], 2);
            $domain = trim(array_shift($pathPieces));
        }

        $tldMatches = 6;

        if (Str::endsWith($domain, '.localhost')) {
            $tldMatches = 9;
        } elseif (Str::endsWith($domain, ['.invalid', '.example', '.digital'])) {
            $tldMatches = 7;
        }

        if (preg_match(
            '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,'.preg_quote($tldMatches, '/').'})$/i',
            $domain,
            $regs
        )) {
            return $regs['domain'];
        }
    }
}
