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

namespace Turahe\Core\Tests\Unit\Casts;

use Tests\Fixtures\Event;
use Turahe\Core\Casts\ISO8601DateTime;
use Turahe\Core\Tests\TestCase;

class SO8601DateTimeTest extends TestCase
{
    public function test_getter_does_not_cast_when_value_is_empty()
    {
        $cast = new ISO8601DateTime;

        $this->assertNull($cast->get(new Event, 'date', null, []));
        $this->assertNull($cast->get(new Event, 'date', '', []));
    }

    public function test_getter_casts_properly_when_value_has_time()
    {
        $cast = new ISO8601DateTime;

        $this->assertSame(
            '2022-01-20 15:00:00',
            $cast->get(new Event, 'date', '2022-01-20 15:00:00', [])->format('Y-m-d H:i:s')
        );
    }

    public function test_setter_does_not_cast_when_value_is_empty()
    {
        $cast = new ISO8601DateTime;

        $this->assertNull($cast->set(new Event, 'date', null, []));
        $this->assertNull($cast->set(new Event, 'date', '', []));
    }

    public function test_setter_casts_iso_8601_value_properly()
    {
        $cast = new ISO8601DateTime;

        $this->assertSame(
            '2018-03-01 23:00:00',
            $cast->set(new Event, 'date', '2018-03-02T00:00:00+01:00', [])
        );

        $this->assertSame(
            '2018-03-01 23:00:00',
            $cast->set(new Event, 'date', '2018-03-02T00:00:00+0100', [])
        );
    }
}
