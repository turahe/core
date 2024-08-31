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

use Illuminate\View\View;
use Turahe\Core\Models\Media;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaViewController extends Controller
{
    /**
     * Preview media file.
     */
    public function show(string $token): View
    {
        $media = Media::byToken($token)->firstOrFail();

        return view('core::media.preview', compact('media'));
    }

    /**
     * Download media file.
     */
    public function download(string $token): StreamedResponse
    {
        $media = Media::byToken($token)->firstOrFail();

        return Storage::disk($media->disk)->download($media->getDiskPath());
    }

    /**
     * Preview media file.
     */
    public function preview(string $token): StreamedResponse
    {
        $media = Media::byToken($token)->firstOrFail();

        $disk = Storage::disk($media->disk);

        return $disk->response($media->getDiskPath(), null, [
            'Pragma'        => 'public',
            'Cache-Control' => 'max-age=86400, public',
            'Content-Type'  => $media->mime_type,
            'Expires'       => gmdate('D, d M Y H:i:s \G\M\T', time() + 86400 * 7), // 7 days
            'Last-Modified' => gmdate('D, d M Y H:i:s \G\M\T', $disk->lastModified($media->getDiskPath())),
        ]);
    }
}
