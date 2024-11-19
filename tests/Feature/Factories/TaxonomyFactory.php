<?php

namespace Turahe\Core\Tests\Feature\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\Core\Tests\Models\Taxonomy;

class TaxonomyFactory extends Factory
{
    protected $model = Taxonomy::class;

    public function definition()
    {
        $name = $this->faker->word;
        $code = name_alias(strtoupper($name));

        return [
            'name' => $name,
            'code' => $code,
            'description' => $this->faker->sentence,
        ];
    }
}