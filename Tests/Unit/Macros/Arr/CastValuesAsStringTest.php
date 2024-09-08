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

namespace Turahe\Core\Tests\Unit\Macros\Arr;

use Illuminate\Support\Arr;
use Turahe\Core\Tests\TestCase;

class CastValuesAsStringTest extends TestCase
{
    public function test_it_casts_values_as_string()
    {
        $casts = Arr::valuesAsString([1, 2, 3]);

        $this->assertSame('1', $casts[0]);
        $this->assertSame('2', $casts[1]);
        $this->assertSame('3', $casts[2]);
    }
}
