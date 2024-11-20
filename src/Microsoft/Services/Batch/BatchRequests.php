<?php

declare(strict_types=1);
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

namespace Turahe\Core\Microsoft\Services\Batch;

use Illuminate\Support\Collection;

class BatchRequests
{
    /**
     * @var array
     */
    protected $requests = [];

    /**
     * Push new batch request
     *
     * @return static
     */
    public function push(BatchRequest $request)
    {
        if (! $request->getId()) {
            // Id's are counted from zero, in this case
            // the method count will always return +1 which gives a unique ID
            // as count does not start from zero
            $request->setId($this->count());
        }

        $this->requests[] = $request;

        return $this;
    }

    /**
     * Get all requests
     */
    public function all(): Collection
    {
        return collect($this->requests);
    }

    /**
     * Count the total number of requests
     */
    public function count(): int
    {
        return count($this->all());
    }
}
