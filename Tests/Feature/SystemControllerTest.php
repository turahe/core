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

namespace Turahe\Core\Tests\Feature;

use Tests\TestCase;

class SystemControllerTest extends TestCase
{
    public function test_unauthenticated_cannot_access_system_endpoints()
    {
        $this->getJson('/api/system/info')->assertUnauthorized();
        $this->postJson('/api/system/info')->assertUnauthorized();
        $this->getJson('/api/system/logs')->assertUnauthorized();
    }

    public function test_unauthorized_cannot_access_system_endpoints()
    {
        $this->asRegularUser()->signIn();

        $this->getJson('/api/system/info')->assertForbidden();
        $this->postJson('/api/system/info')->assertForbidden();
        $this->getJson('/api/system/logs')->assertForbidden();
    }

    public function test_a_user_can_retrieve_system_info()
    {
        $this->signIn();

        $this->getJson('/api/system/info')->assertOk();
    }

    public function test_a_user_can_download_system_info()
    {
        $this->signIn();

        $this->postJson('/api/system/info')->assertDownload('system-info.xlsx');
    }

    public function test_a_user_can_retrieve_system_logs()
    {
        $this->signIn();

        $this->getJson('/api/system/logs')->assertOk();
    }
}
