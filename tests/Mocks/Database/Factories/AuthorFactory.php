<?php

namespace Tests\Mocks\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Mocks\Models\Author;
use Tests\Mocks\Models\Country;

class AuthorFactory extends Factory
{
    protected $model = Author::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
