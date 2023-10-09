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

namespace Modules\Core;

use Illuminate\Support\Collection;
use Modules\Core\Resource\Http\ResourceRequest;

trait ResolvesFilters
{
    /**
     *  Get the available filters for the user
     */
    public function resolveFilters(ResourceRequest $request): Collection
    {
        $filters = $this->filters($request);

        $collection = is_array($filters) ? new Collection($filters) : $filters;

        return $collection->filter->authorizedToSee()->values();
    }

    /**
     * @codeCoverageIgnore
     *
     * Get the defined filters
     */
    public function filters(ResourceRequest $request): array|Collection
    {
        return [];
    }
}
