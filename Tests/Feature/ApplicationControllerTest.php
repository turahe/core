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

use Turahe\Core\Tests\TestCase;

class ApplicationControllerTest extends \Orchestra\Testbench\TestCase
{
    public function test_it_always_uses_the_default_app_view()
    {
        $this->signIn();

        $this->get('/')->assertViewIs('core::app');
        $this->get('/non-existent-page')->assertViewIs('core::app');
    }
}
