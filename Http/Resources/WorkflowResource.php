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

/** @mixin \Modules\Core\Models\Workflow */
class WorkflowResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'title'            => $this->title,
            'description'      => $this->description,
            'is_active'        => $this->is_active,
            'total_executions' => $this->total_executions,
            'trigger_type'     => $this->trigger_type,
            'action_type'      => $this->action_type,
            'data'             => $this->data,
        ], $request);
    }
}
