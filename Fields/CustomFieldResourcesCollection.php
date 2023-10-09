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

namespace Modules\Core\Fields;

use Illuminate\Database\Eloquent\Collection;

class CustomFieldResourcesCollection extends Collection
{
    /**
     * Cached resource collection
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Query custom fields for resource
     *
     * @param  string  $resourceName
     * @return \Modules\Core\Fields\CustomFieldResourceCollection
     */
    public function forResource($resourceName)
    {
        if (array_key_exists($resourceName, $this->cache)) {
            return $this->cache[$resourceName];
        }

        return $this->cache[$resourceName] = new CustomFieldResourceCollection(
            $this->where('resource_name', $resourceName)
        );
    }
}
