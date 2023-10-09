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

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\JsonResource;
use Modules\Core\Resource\ProvidesCommonData;

/** @mixin \Modules\Core\Models\Role */
class RoleResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'name'        => $this->name,
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
        ], $request);
    }
}
