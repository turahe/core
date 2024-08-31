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
use Turahe\Media\MediaUploader;
use Illuminate\Http\JsonResponse;
use Turahe\Core\Models\PendingMedia;
use Turahe\Core\Http\Resources\MediaResource;
use Turahe\Core\Http\Controllers\ApiController;

class PendingMediaController extends ApiController
{
    /**
     * Upload pending media.
     */
    public function store(string $draftId, Request $request): JsonResponse
    {
        try {
            $media = MediaUploader::fromFile($request->file('file'))
                ->upload();

            $media->markAsPending($draftId);
        } catch (\Exception $e) {
            /** @var \Symfony\Component\HttpKernel\Exception\HttpException */
            $exception = $this->transformMediaUploadException($e);

            return $this->response(['message' => $exception->getMessage()], $exception->getStatusCode());
        }

        return $this->response(new MediaResource($media->load('pendingData')), 201);
    }

    /**
     * Destroy pending media.
     */
    public function destroy(string $pendingMediaId): JsonResponse
    {
        PendingMedia::findOrFail($pendingMediaId)->purge();

        return $this->response('', 204);
    }
}
