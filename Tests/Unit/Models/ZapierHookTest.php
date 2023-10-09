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

namespace Modules\Core\Tests\Unit\Models;

use Tests\TestCase;
use Modules\Users\Models\User;
use Modules\Core\Models\ZapierHook;

class ZapierHookTest extends TestCase
{
    public function test_zapier_hook_has_user()
    {
        $user = $this->createUser();

        $hook = new ZapierHook([
            'hook'          => 'created',
            'action'        => 'create',
            'resource_name' => 'resource',
            'user_id'       => $user->id,
            'zap_id'        => 123,
        ]);

        $hook->save();

        $this->assertInstanceOf(User::class, $hook->user);
    }
}
