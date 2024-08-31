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

use Turahe\Core\Models\ModelVisibilityGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModelVisibilityGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ModelVisibilityGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'all',
        ];
    }

    /**
     * Indicate that the visibility group is organization related.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function organizations()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'organization',
            ];
        });
    }

    /**
     * Indicate that the visibility group is users related.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function users()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'users',
            ];
        });
    }
}
