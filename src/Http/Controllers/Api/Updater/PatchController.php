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

namespace Turahe\Core\Http\Controllers\Api\Updater;

use App\Installer\RequirementsChecker;
use Illuminate\Http\JsonResponse;
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\Updater\Exceptions\UpdaterException;
use Turahe\Core\Updater\Patcher;

class PatchController extends ApiController
{
    /**
     * Get the available patches for the installed version.
     */
    public function index(Patcher $patcher): JsonResponse
    {
        return $this->response($patcher->getAvailablePatches());
    }

    /**
     * Apply the given patch to the current installed version.
     */
    public function apply(string $token, ?string $purchaseKey = null): JsonResponse
    {
        // Apply patch flag

        $requirements = new RequirementsChecker;

        $patcher = app(Patcher::class);

        if (! empty($purchaseKey)) {
            settings(['purchase_key' => $purchaseKey]);
        }

        abort_unless(
            $requirements->passes('zip'),
            409,
            __('core::update.patch_zip_is_required')
        );

        $patch = $patcher->usePurchaseKey($purchaseKey ?: '')->find($token);

        if ($patch->isApplied()) {
            throw new UpdaterException('This patch is already applied.', 409);
        }

        $patcher->apply($patch);

        return $this->response('', 204);
    }
}
