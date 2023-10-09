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

namespace Modules\Core\Card;

use Modules\Users\Models\User;
use Modules\Core\Models\Dashboard;

class DashboardService
{
    /**
     * Create new dashboard for the given user.
     */
    public function create(array $attributes, string $userId): Dashboard
    {
        $attributes['created_by'] = $userId;
        $attributes['updated_by'] = $userId;
        $attributes['is_default'] ??= false;
        $attributes['cards'] ??= Dashboard::defaultCards(User::find($userId));

        $dashboard = new Dashboard;
        $dashboard->fill($attributes)->save();

        if ($dashboard->is_default) {
            Dashboard::where('id', '!=', $dashboard->id)->update(['is_default' => false]);
        }

        return $dashboard;
    }

    /**
     * Create default dashboard for the given user.
     */
    public function createDefault(User $user): Dashboard
    {
        return $this->create(['name' => 'Application Dashboard', 'is_default' => true], $user->id);
    }
}
