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

/** @mixin \Modules\Core\Models\Permission */
class PermissionResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'name'    => $this->name,
            'role_id' => $this->whenPivotLoaded('role_has_permissions', function () {
                return $this->pivot->role_id;
            }),
        ], $request);
    }
}
