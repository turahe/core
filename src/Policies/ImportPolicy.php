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
use Turahe\Core\Models\Import;
use Turahe\Users\Models\User;

class ImportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the import.
     */
    public function delete(User $user, Import $import): bool
    {
        return (int) $import->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can upload fixed skip file.
     */
    public function uploadFixedSkipFile(User $user, Import $import): bool
    {
        return (int) $import->user_id === (int) $user->id;
    }

    /**
     *Determine whether the user can upload fixed skip file
     */
    public function downloadSkipFile(User $user, Import $import): bool
    {
        return (int) $import->user_id === (int) $user->id;
    }
}
