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

use App\Http\Controllers\Controller;

class FilePermissionsError extends Controller
{
    /**
     * Show file permissions error.
     */
    public function __invoke(): string
    {
        // File permissions error flag

        return 'The application could not write data into <strong>'.base_path().'</strong> folder. Please give your web server user (<strong>'.get_current_process_user().'</strong>) write permissions in <code>'.base_path().'</code> folder:<br/><br/></div>
<code><pre style="background: #f0f0f0;
            padding: 15px;
            width: 50%;
            margin-top:0px;
            border-radius: 4px;">
sudo chown '.get_current_process_user().':'.get_current_process_user().' -R '.base_path().'
sudo find '.base_path().' -type d -exec chmod 755 {} \;
sudo find '.base_path().' -type f -exec chmod 644 {} \;
</pre></code>';
    }
}
