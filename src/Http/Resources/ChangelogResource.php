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

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Turahe\Core\JsonResource;
use Turahe\Core\Resource\ProvidesCommonData;

/** @mixin \Turahe\Core\Models\Changelog */
class ChangelogResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'description' => $this->description,
            'causer_name' => $this->causer_name,
            'properties'  => $this->properties,
            'module'      => str_starts_with($this->subject_type, config('modules.namespace')) ?
                strtolower(Str::of($this->subject_type)->explode('\\')[1]) :
                null,
        ], $request);
    }
}
