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

namespace Modules\Core\Resource\Http;

use Modules\Core\Models\Model;
use Illuminate\Foundation\Http\FormRequest;

class ResourceRequest extends FormRequest
{
    use InteractsWithResources;

    /**
     * Resolve the resource json resource and create appropriate response.
     */
    public function toResponse(mixed $data): mixed
    {
        if (! $this->resource()->jsonResource()) {
            return $data;
        }

        /** @var \Modules\Core\Resource\Http\JsonResource */
        $jsonResource = $this->resource()->createJsonResource($data);

        if ($data instanceof Model) {
            $jsonResource->withActions($this->resource()->resolveActions($this));
        }

        return $jsonResource->toResponse($this)->getData();
    }

    /**
     * Check whether the current request is for create.
     */
    public function isCreateRequest(): bool
    {
        return $this->intent == 'create' || $this instanceof CreateResourceRequest;
    }

    /**
     * Check whether the current request is for update.
     */
    public function isUpdateRequest(): bool
    {
        return $this->intent == 'update' || $this->intent === 'details' || $this instanceof UpdateResourceRequest;
    }

    /**
     * Check whether the current request is via resource.
     */
    public function viaResource(): bool
    {
        return $this->has('via_resource');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
        ];
    }
}
