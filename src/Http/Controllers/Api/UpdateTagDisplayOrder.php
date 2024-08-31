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

use Illuminate\Http\Request;
use Turahe\Core\Models\Tag;
use Illuminate\Http\JsonResponse;
use Turahe\Core\Http\Controllers\ApiController;

class UpdateTagDisplayOrder extends ApiController
{
    /**
     * Save the pipelines display order.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            '*.id'            => 'required|int',
            '*.display_order' => 'required|int',
        ]);

        foreach ($request->all() as $tag) {
            Tag::find($tag['id'])->fill(['display_order' => $tag['display_order']])->save();
        }

        return $this->response('', 204);
    }
}
