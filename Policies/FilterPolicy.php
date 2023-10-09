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

namespace Modules\Core\Policies;

use Modules\Users\Models\User;
use Modules\Core\Models\Filter;
use Illuminate\Auth\Access\HandlesAuthorization;

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
