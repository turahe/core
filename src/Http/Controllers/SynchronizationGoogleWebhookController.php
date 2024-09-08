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

namespace Turahe\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Turahe\Core\Models\Synchronization;

class SynchronizationGoogleWebhookController extends Controller
{
    /**
     *  Handle the webhook request.
     */
    public function handle(Request $request): void
    {
        if ($request->header('x-goog-resource-state') !== 'exists') {
            return;
        }

        $synchronization = Synchronization::where('resource_id', $request->header('x-goog-resource-id'))
            ->findOrFail($request->header('x-goog-channel-id'));

        $synchronization->ping();
    }
}
