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

namespace Turahe\Core\Filters;

use Turahe\Core\Models\Filter;

class UserFiltersService
{
    public function get(int $userId, string $identifier)
    {
        return Filter::with(['defaults' => fn ($query) => $query->where('user_id', $userId)])
            ->visibleFor($userId)
            ->ofIdentifier($identifier)
            ->orderBy('name')
            ->get();
    }
}
