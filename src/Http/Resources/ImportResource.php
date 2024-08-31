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
use Turahe\Users\Http\Resources\UserResource;

/** @mixin \Turahe\Core\Models\Import */
class ImportResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'file_name'          => $this->file_name,
            'skip_file_filename' => $this->skip_file_filename,
            'mappings'           => $this->data['mappings'],
            'resource_name'      => $this->resource_name,
            'status'             => $this->status,
            'imported'           => $this->imported,
            'skipped'            => $this->skipped,
            'duplicates'         => $this->duplicates,
            'fields'             => $this->fields(),
            'user_id'            => $this->user_id,
            'user'               => new UserResource($this->whenLoaded('user')),
            'completed_at'       => $this->completed_at,
        ], $request);
    }
}
