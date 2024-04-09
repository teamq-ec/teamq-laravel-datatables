<?php

namespace Tests\Mocks\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Mocks\Models\Country;
use Tests\Mocks\Models\Flight;

class FlightFactory extends Factory
{
    protected $model = Flight::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'departure_id' => Country::factory(),
            'arrival_id' => Country::factory(),
            'code' => fake()->colorName(),
        ];
    }
}
