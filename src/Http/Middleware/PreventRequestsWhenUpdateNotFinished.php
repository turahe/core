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

namespace Turahe\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Turahe\Core\Facades\Innoclapps;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class PreventRequestsWhenUpdateNotFinished
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (Innoclapps::requiresUpdateFinalization()) {
            return redirect('/update/finalize');
        }

        return $next($request);
    }
}
