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

namespace Modules\Core\Tests\Feature;

use Tests\TestCase;

class FilePermissionErrorTest extends TestCase
{
    public function test_file_permissions_can_be_viewed()
    {
        $this->signIn();

        $this->get('/errors/permissions')->assertOk();
    }
}
