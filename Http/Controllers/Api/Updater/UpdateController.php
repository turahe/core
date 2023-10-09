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

namespace Modules\Core\Http\Controllers\Api\Updater;

use Illuminate\Http\JsonResponse;
use Modules\Core\Updater\Updater;
use App\Installer\RequirementsChecker;
use Illuminate\Support\Facades\Artisan;
use Modules\Core\Http\Controllers\ApiController;

class UpdateController extends ApiController
{
    /**
     * Get information about update.
     */
    public function index(Updater $updater): JsonResponse
    {
        return $this->response([
            'installed_version'        => $updater->getVersionInstalled(),
            'latest_available_version' => $updater->getVersionAvailable(),
            'is_new_version_available' => $updater->isNewVersionAvailable(),
            'purchase_key'             => $updater->getPurchaseKey(),
        ]);
    }

    /**
     * Perform an application update.
     */
    public function update(?string $purchaseKey = null): JsonResponse
    {
        // Update flag
        $requirements = new RequirementsChecker();

        abort_if($requirements->fails('zip'), 409, __('core::update.update_zip_is_required'));

        // Save the purchase key for future usage
        if ($purchaseKey) {
            settings(['purchase_key' => $purchaseKey]);
        }

        Artisan::call('updater:update', [
            '--key' => $purchaseKey,
        ]);

        return $this->response('', 204);
    }
}
