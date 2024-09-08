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

namespace Turahe\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Turahe\Core\Facades\Innoclapps;

class FinalizeUpdateController extends Controller
{
    /**
     * Show the update finalization action.
     */
    public function show(): View
    {
        abort_unless(Innoclapps::requiresUpdateFinalization(), 404);

        return view('core::update.finalize');
    }

    /**
     * Perform update finalization.
     */
    public function finalize(): void
    {
        abort_unless(Innoclapps::requiresUpdateFinalization(), 404);

        Artisan::call('updater:finalize');
    }
}
