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

class PrivacyPolicyTest extends TestCase
{
    public function test_privacy_policy_can_be_viewed()
    {
        $policy = 'Test - Privacy Policy';

        settings()->set('privacy_policy', $policy)->save();

        $this->get('privacy-policy')->assertSee($policy);
    }
}
