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

namespace Turahe\Core\Http\Controllers\Api\Resource;

use Illuminate\Http\JsonResponse;
use Turahe\Core\Contracts\Resources\Mediable;
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\Http\Resources\MediaResource;
use Turahe\Core\Models\Media;
use Turahe\Core\Resource\Http\ResourceRequest;
use Turahe\Media\MediaUploader;

class MediaController extends ApiController
{
    /**
     * Upload media to resource.
     */
    public function store(ResourceRequest $request): JsonResponse
    {
        abort_unless($request->resource() instanceof Mediable, 404);

        $this->authorize('update', $record = $request->record());

        try {
            $media = MediaUploader::fromFile($request->file('file'))
                ->upload();
        } catch (\Exception $e) {
            $exception = $this->transformMediaUploadException($e);

            return $this->response(
                ['message' => $exception->getMessage()],
                $exception->getStatusCode()
            );
        }

        $record->attachMedia($media, $record->getMediaTags());

        return $this->response(new MediaResource($media), 201);
    }

    /**
     * Delete media from resource.
     */
    public function destroy(ResourceRequest $request): JsonResponse
    {
        abort_unless($request->resource() instanceof Mediable, 404);

        $this->authorize('update', $request->record());

        Media::findOrFail($request->route('media'))->delete();

        return $this->response('', 204);
    }
}
