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

namespace Turahe\Core\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Turahe\Core\Contracts\Criteria\QueryCriteria;
use Turahe\Users\Models\User;

class VisibleModelsCriteria implements QueryCriteria
{
    /**
     * Create new VisibleModelsCriteria instance.
     */
    public function __construct(protected ?User $user = null) {}

    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $query): void
    {
        $query->visible($this->user ?: Auth::user());
    }
}
