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
use Modules\Core\Models\OAuthAccount;
use Illuminate\Auth\Access\HandlesAuthorization;

class OAuthAccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the account.
     */
    public function view(User $user, OAuthAccount $account): bool
    {
        return (int) $account->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the account.
     */
    public function delete(User $user, OAuthAccount $account): bool
    {
        return (int) $user->id === (int) $account->user_id;
    }
}
