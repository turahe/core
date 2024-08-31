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

class TimezoneControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_timezones_endpoints()
    {
        $this->getJson('/api/timezones')->assertUnauthorized();
    }

    public function test_timezones_can_be_retrieved()
    {
        $this->signIn();

        $this->getJson('/api/timezones')->assertOk();
    }
}
