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
use Turahe\Core\Models\Dashboard;
use Turahe\Users\Models\User;

class DashboardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the dashboard.
     */
    public function view(User $user, Dashboard $dashboard): bool
    {
        return (int) $user->id === (int) $dashboard->user_id;
    }

    /**
     * Determine whether the user can update the dashboards.
     */
    public function update(User $user, Dashboard $dashboard): bool
    {
        return (int) $user->id === (int) $dashboard->user_id;
    }

    /**
     * Determine whether the user can delete the dashboard.
     */
    public function delete(User $user, Dashboard $dashboard): bool
    {
        return (int) $user->id === (int) $dashboard->user_id;
    }
}
