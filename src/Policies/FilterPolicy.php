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

namespace Turahe\Core\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Turahe\Core\Models\Filter;
use Turahe\Users\Models\User;

class FilterPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the filter.
     */
    public function update(User $user, Filter $filter): bool
    {
        return (int) $filter->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the filter.
     */
    public function delete(User $user, Filter $filter): bool
    {
        return (int) $filter->user_id === (int) $user->id;
    }
}
