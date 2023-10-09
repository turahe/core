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

namespace Modules\Core\Tests\Unit\Mail\Headers;

use Tests\TestCase;
use Modules\Core\Mail\Headers\Header;
use Illuminate\Contracts\Support\Arrayable;

class HeaderTest extends TestCase
{
    public function test_header_has_name()
    {
        $header = new Header('x-concord-test', 'value');

        $this->assertSame('x-concord-test', $header->getName());
    }

    public function test_header_name_is_aways_in_lowercase()
    {
        $header = new Header('X-Concord-Value', 'value');

        $this->assertSame('x-concord-value', $header->getName());
    }

    public function test_header_has_value()
    {
        $header = new Header('x-concord-test', 'value');

        $this->assertSame('value', $header->getValue());
    }

    public function test_header_is_arrayable()
    {
        $header = new Header('x-concord-test', 'value');

        $this->assertInstanceOf(Arrayable::class, $header);

        $this->assertEquals([
            'name'  => 'x-concord-test',
            'value' => 'value',
        ], $header->toArray());
    }
}
