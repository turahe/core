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

namespace Modules\Core\Tests\Unit\Macros\Str;

use Tests\TestCase;
use Illuminate\Support\Str;

class ClickableUrlsTest extends TestCase
{
    public function test_it_makes_urls_clickable()
    {
        $formatted = Str::clickable('Test https://concordcrm.com Test');

        $this->assertStringContainsString('<a href="https://concordcrm.com" rel="nofollow" target=\'_blank\'>https://concordcrm.com</a>', $formatted);
    }

    public function test_it_makes_multiple_urls_clickable()
    {
        $formatted = Str::clickable('Test https://concordcrm.com Test http://concordcrm.com');

        $this->assertStringContainsString('<a href="https://concordcrm.com" rel="nofollow" target=\'_blank\'>https://concordcrm.com</a>', $formatted);
        $this->assertStringContainsString('<a href="http://concordcrm.com" rel="nofollow" target=\'_blank\'>http://concordcrm.com</a>', $formatted);
    }

    public function test_it_makes_ftp_clickable()
    {
        $formatted = Str::clickable('Test ftp://127.0.01 Test');

        $this->assertStringContainsString('<a href="ftp://127.0.01" rel="nofollow" target=\'_blank\'>ftp://127.0.01</a>', $formatted);
    }

    public function test_it_makes_email_clickable()
    {
        $formatted = Str::clickable('Test email@exampe.com Test');

        $this->assertStringContainsString('<a href="mailto:email@exampe.com">email@exampe.com</a>', $formatted);
    }
}
