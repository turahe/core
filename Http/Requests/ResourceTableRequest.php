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

namespace Modules\Core\Http\Requests;

use Modules\Core\Table\Table;
use Modules\Core\Resource\Resource;
use Modules\Core\Contracts\Resources\Tableable;
use Modules\Core\Resource\Http\ResourceRequest;

class ResourceTableRequest extends ResourceRequest
{
    /**
     * Get the class of the resource being requested.
     */
    public function resource(): Resource
    {
        return tap(parent::resource(), function ($resource) {
            abort_if(! $resource instanceof Tableable, 404);
        });
    }

    /**
     * Resolve the resource table for the current request
     */
    public function resolveTable(): Table
    {
        return $this->resource()->resolveTable($this);
    }

    /**
     * Resolve the resource trashed table for the current request
     */
    public function resolveTrashedTable(): Table
    {
        return $this->resource()->resolveTrashedTable($this);
    }
}
