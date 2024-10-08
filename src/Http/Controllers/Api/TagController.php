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
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\Http\Requests\TagRequest;
use Turahe\Core\Http\Resources\TagResource;
use Turahe\Core\Models\Tag;

class TagController extends ApiController
{
    /**
     * Store new tag in storage.
     */
    public function store(string $type, TagRequest $request): JsonResponse
    {
        $tag = Tag::findOrCreate($request->name, $type);

        $tag->swatch_color = $request->swatch_color;
        $tag->save();

        return $this->response(new TagResource($tag));
    }

    /**
     * Update tag in storage.
     */
    public function update(Tag $tag, TagRequest $request): JsonResponse
    {
        $tag->fill([
            'name' => $request->name,
            'swatch_color' => $request->swatch_color,
            'display_order' => $request->input('display_order', $tag->display_order),
        ])->save();

        return $this->response(new TagResource($tag));
    }

    /**
     * Delete the tag from storage.
     */
    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return $this->response('', 204);
    }
}
