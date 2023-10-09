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

namespace Modules\Core\Filters;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Models\Country as CountryModel;

class Country extends Select
{
    /**
     * Initialize new Country filter.
     */
    public function __construct()
    {
        parent::__construct('country_id', __('core::filters.country'));

        $this->valueKey('id')->labelKey('name')->options($this->countries(...));
    }

    /**
     * Get the filter countries.
     */
    public function countries(): Collection
    {
        return CountryModel::get(['id', 'name']);
    }
}
