<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Feature\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\Core\Enums\OrganizationType;
use Turahe\Core\Models\Organization;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $this->faker->addProvider(new \Faker\Provider\id_ID\Company($this->faker));

        $name = $this->faker->company;

        return [
            'name' => $name,
            'code' => name_alias($name),
            'type' => OrganizationType::Company,
        ];
    }
}
