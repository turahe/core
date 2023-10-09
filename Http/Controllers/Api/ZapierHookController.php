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

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Core\Models\ZapierHook;
use Modules\Core\Http\Controllers\ApiController;

class ZapierHookController extends ApiController
{
    /**
     * Subscribe to a hook.
     */
    public function store(string $resourceName, string $action, Request $request): JsonResponse
    {
        $hook = new ZapierHook([
            'hook'          => $request->targetUrl,
            'resource_name' => $resourceName,
            'action'        => $action,
            'user_id'       => $request->user()->id,
            // Needs further testing, previously the zapId was only numeric
            // but now includes subscriptions:zapId
            'zap_id' => str_contains($request->zapId, 'subscription:') ?
                explode('subscription:', $request->zapId)[1] :
                $request->zapId,
            'data' => $request->data,
        ]);

        $hook->save();

        return $this->response($hook, 201);
    }

    /**
     * Unsubscribe from hook.
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        ZapierHook::where('user_id', $request->user()->getKey())->findOrFail($id)->delete();

        return $this->response('', 204);
    }
}
