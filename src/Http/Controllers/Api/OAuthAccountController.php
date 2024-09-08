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

namespace Turahe\Core\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\Http\Resources\OAuthAccountResource;
use Turahe\Core\Models\OAuthAccount;

class OAuthAccountController extends ApiController
{
    /**
     * Get the user connected OAuth accounts.
     */
    public function index(Request $request): JsonResponse
    {
        $collection = OAuthAccountResource::collection(
            OAuthAccount::where('user_id', $request->user()->id)->get()
        );

        return $this->response($collection);
    }

    /**
     * Display the specified oauth account.
     */
    public function show(OAuthAccount $account): JsonResponse
    {
        $this->authorize('view', $account);

        return $this->response(new OAuthAccountResource($account));
    }

    /**
     * Remove the specified account from storage.
     */
    public function destroy(OAuthAccount $account): JsonResponse
    {
        $this->authorize('delete', $account);

        $account->delete();

        return $this->response('', 204);
    }
}
