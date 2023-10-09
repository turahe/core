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

namespace Modules\Core\Microsoft\Services\Batch;

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
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return collect($this->requests);
    }

    /**
     * Count the total number of requests
     *
     * @return int
     */
    public function count()
    {
        return count($this->all());
    }
}
