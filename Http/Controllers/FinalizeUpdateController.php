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

namespace Modules\Core\Http\Controllers;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Modules\Core\Facades\Innoclapps;
use Illuminate\Support\Facades\Artisan;

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
