<?php

namespace Tests\Mocks\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Mocks\Models\Chapter;

class ChapterFactory extends Factory
{
    protected $model = Chapter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->title(),
            'number' => fake()->randomDigitNotZero(),
        ];
    }
}
