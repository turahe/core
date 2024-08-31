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

namespace Turahe\Core\Http\Resources;

use Illuminate\Http\Request;
use Turahe\Core\JsonResource;
use Turahe\Core\Resource\ProvidesCommonData;

/** @mixin \Turahe\Core\Models\Media */
class MediaResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'file_name'      => $this->basename,
            'extension'      => $this->extension,
            'size'           => $this->size,
            'disk_path'      => $this->getDiskPath(),
            'mime_type'      => $this->mime_type,
            'aggregate_type' => $this->aggregate_type,
            'view_url'       => $this->getViewUrl(),
            'preview_url'    => $this->getPreviewUrl(),
            'preview_path'   => $this->previewPath(),
            'download_url'   => $this->getDownloadUrl(),
            'download_path'  => $this->downloadPath(),
            'pending_data'   => $this->whenLoaded('pendingData'),
        ], $request);
    }
}
