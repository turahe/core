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

namespace Turahe\Core\Table;

use Illuminate\Pagination\LengthAwarePaginator as BaseLengthAwarePaginator;

class LengthAwarePaginator extends BaseLengthAwarePaginator
{
    /**
     * The attributes to merge to the top level pagination data.
     */
    protected array $merge = [];

    /**
     * Set the all time total.
     */
    public function setAllTimeTotal(int $total): static
    {
        return $this->merge(['all_time_total' => $total]);
    }

    /**
     * Add additional data to be merged with the response.
     */
    public function merge(array $data): static
    {
        $this->merge = array_merge($this->merge, $data);

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), $this->merge);
    }
}
