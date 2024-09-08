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

namespace Turahe\Core\Tests\Feature\Filters;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\Fixtures\Event;
use Turahe\Core\Tests\TestCase;
use Turahe\Core\Filters\Checkbox;
use Turahe\Core\Tests\Concerns\TestsFilters;

class CheckboxFilterTest extends TestCase
{
    use TestsFilters;

    protected static $filter = Checkbox::class;

    public function test_checkbox_filter_rule_with_in_operator()
    {
        Event::factory()->count(3)->state(new Sequence(
            ['total_guests' => 1],
            ['total_guests' => 2],
            ['total_guests' => 3]
        ))->create();

        $result = $this->perform('total_guests', 'in', [2, 3]);

        $this->assertEquals($result[0]->total_guests, 2);
        $this->assertEquals($result[1]->total_guests, 3);
        $this->assertCount(2, $result);
    }
}
