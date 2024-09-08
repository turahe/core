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
use Illuminate\View\View;

class PrivacyPolicy extends Controller
{
    /**
     * Display the privacy policy.
     */
    public function __invoke(): View
    {
        $content = clean(settings('privacy_policy'));

        return view('core::privacy-policy', compact('content'));
    }
}
