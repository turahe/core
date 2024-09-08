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

/** @mixin \Turahe\Core\Models\CustomField */
class CustomFieldResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'field_type' => $this->field_type,
            'resource_name' => $this->resource_name,
            'field_id' => $this->field_id,
            'label' => $this->label,
            'options' => $this->when($this->options->isNotEmpty(), $this->options),
            'is_unique' => $this->is_unique,
        ], $request);
    }
}
