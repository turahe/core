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

namespace Turahe\Core\Database\Factories;

use Turahe\Core\Models\Filter;
use Illuminate\Database\Eloquent\Factories\Factory;

class FilterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Filter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'identifier' => 'users',
            'name'       => 'Filter Name',
            'is_shared'  => false,

            'rules' => [
                'condition' => 'and',
                'children'  => [[
                    'type'  => 'rule',
                    'query' => [
                        'type'      => 'text',
                        'opereator' => 'equal',
                        'rule'      => 'test_attribute',
                        'operand'   => 'Test',
                        'value'     => 'Test',
                    ],
                ]],
            ],
        ];
    }

    /**
     * Indicate that the filter is shared.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function shared()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_shared' => true,
            ];
        });
    }
}
