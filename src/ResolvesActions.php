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

namespace Turahe\Core;

use Illuminate\Support\Collection;
use Turahe\Core\Resource\Http\ResourceRequest;

trait ResolvesActions
{
    /**
     * Get the available actions for the resource
     */
    public function resolveActions(ResourceRequest $request): Collection
    {
        $actions = $this->actions($request);

        $collection = is_array($actions) ? new Collection($actions) : $actions;

        return $collection->filter->authorizedToSee()->values();
    }

    /**
     * @codeCoverageIgnore
     *
     * Get the defined resource actions
     */
    public function actions(ResourceRequest $request): array|Collection
    {
        return [];
    }
}
