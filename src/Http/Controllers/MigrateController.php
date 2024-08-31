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

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Turahe\Core\Facades\Innoclapps;
use Illuminate\Support\Facades\Artisan;

class MigrateController extends Controller
{
    /**
     * Show the migration required action.
     */
    public function show(): View
    {
        abort_unless(Innoclapps::requiresMigration(), 404);

        return view('core::migrate');
    }

    /**
     * Perform migration.
     */
    public function migrate(): void
    {
        abort_unless(Innoclapps::requiresMigration(), 404);

        Artisan::call('migrate --force');
    }
}
