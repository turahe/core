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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Turahe\Core\Updater\Patcher;

class UpdateDownloadController extends Controller
{
    /**
     * Download the given patch
     */
    public function downloadPatch(string $token, ?string $purchaseKey = null): BinaryFileResponse
    {
        // Download patch flag

        if ($purchaseKey) {
            settings(['purchase_key' => $purchaseKey]);
        }

        $patcher = app(Patcher::class);

        return $patcher->download($token);
    }
}
