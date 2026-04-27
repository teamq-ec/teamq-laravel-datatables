<?php

namespace Tests\Mocks\Mongo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Mocks\Mongo\Models\Chapter;

class ChapterFactory extends Factory
{
    protected $model = Chapter::class;

    /**
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
