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

use Illuminate\Database\Eloquent\Builder;

class TrashedResourcefulRequest extends ResourcefulRequest
{
    /**
     * Get new query for the current resource.
     */
    public function newQuery(): Builder
    {
        return $this->resource()->newQuery()->onlyTrashed();
    }
}
