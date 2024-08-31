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

/** @mixin \Turahe\Core\Models\Filter */
class FilterResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'name'              => $this->name,
            'identifier'        => $this->identifier,
            'rules'             => $this->rules,
            'user_id'           => $this->user_id,
            'is_shared'         => $this->is_shared,
            'is_system_default' => $this->is_system_default,
            'is_readonly'       => $this->is_readonly,
            'defaults'          => $this->defaults->map(fn ($default) => [
                'user_id' => $default->user_id,
                'view'    => $default->view,
            ])->values(),
        ], $request);
    }
}
