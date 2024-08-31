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

use Turahe\Users\Models\User;
use Turahe\Core\Models\Dashboard;
use Illuminate\Database\Eloquent\Factories\Factory;

class DashboardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Dashboard::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'       => $this->faker->paragraph(1),
            'is_default' => false,
            'user_id'    => User::factory(),
            'cards'      => [],
        ];
    }

    /**
     * Indicate that the dashboard is default.
     */
    public function default(): Factory
    {
        return $this->state(function () {
            return [
                'is_default' => true,
            ];
        });
    }
}
