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

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\Core\Models\ModelVisibilityGroup;
use Turahe\Core\Models\ModelVisibilityGroupDependent;

class ModelVisibilityGroupDependentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ModelVisibilityGroupDependent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'model_visibility_group_id' => ModelVisibilityGroup::factory(),
        ];
    }
}
