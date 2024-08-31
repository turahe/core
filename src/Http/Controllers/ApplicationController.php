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

use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;

class ApplicationController extends Controller
{
    /**
     * Application main view.
     */
    public function __invoke(): View
    {
        return view('core::app');
    }
}
