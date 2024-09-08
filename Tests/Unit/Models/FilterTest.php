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

namespace Turahe\Core\Tests\Unit\Models;

use Illuminate\Support\Facades\Lang;
use Tests\TestCase;
use Turahe\Core\Models\Filter;
use Turahe\Users\Models\User;

class FilterTest extends TestCase
{
    public function test_filter_has_user()
    {
        $filter = Filter::factory()->for(User::factory())->create();

        $this->assertInstanceOf(User::class, $filter->user);
    }

    public function test_filter_can_be_default()
    {
        $user = $this->createUser();
        $filter = Filter::factory()->create();
        $filter->defaults()->createMany([['view' => 'create', 'user_id' => $user->id]]);

        $this->assertCount(1, $filter->defaults);
        $this->assertEquals([
            'view' => 'create',
            'user_id' => $user->id,
        ], [
            'view' => $filter->defaults[0]->view,
            'user_id' => $filter->defaults[0]->user_id,
        ]);
    }

    public function test_filter_is_system_default_when_does_not_have_user()
    {
        $filter = Filter::factory()->make(['user_id' => null]);

        $this->assertTrue($filter->is_system_default);
    }

    public function test_filter_is_not_system_default_when_have_user()
    {
        $filter = Filter::factory()->for(User::factory())->create();

        $this->assertFalse($filter->is_system_default);
    }

    public function test_filter_name_can_be_custom_translated()
    {
        $filter = Filter::factory()->create(['name' => 'Filter name']);

        Lang::addLines([
            'custom.filter.'.$filter->id => 'Custom filter name',
        ], 'en');

        $this->assertEquals('Custom filter name', $filter->name);
    }

    public function test_it_returns_the_original_name_when_no_custom_translation_exists()
    {
        $filter = Filter::factory()->make(['name' => 'Filter name']);

        $this->assertEquals('Filter name', $filter->name);
    }

    public function test_filter_has_rules()
    {
        $rule = [
            'type' => 'rule',
            'query' => [
                'type' => 'text',
                'opereator' => 'equal',
                'rule' => 'test_attribute',
                'operand' => 'Test',
                'value' => 'Test',
            ],
        ];

        $filter = Filter::factory()->make(['rules' => $rules = [
            'condition' => 'and',
            'children' => [$rule],
        ],
        ]);

        $this->assertEquals($rules, $filter->rules);
    }

    public function test_filter_rules_can_be_set_only_by_passing_children()
    {
        $rule = [
            'type' => 'rule',
            'query' => [
                'type' => 'text',
                'opereator' => 'equal',
                'rule' => 'test_attribute',
                'operand' => 'Test',
                'value' => 'Test',
            ],
        ];

        $filter = Filter::factory()->make(['rules' => $rule]);

        $expected = [
            'condition' => 'and',
            'children' => $rule,
        ];

        $this->assertEquals($expected, $filter->rules);
    }
}
