<?php

namespace Tests\Mocks\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Mocks\Enums\BookClassificationEnum;
use Tests\Mocks\Models\Author;
use Tests\Mocks\Models\Book;

class BookFactory extends Factory
{
    protected $model = Book::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => Author::factory(),
            'title' => fake()->title(),
            'isbn' => fake()->unique()->isbn10(),
            'classification' => fake()->randomElement(BookClassificationEnum::cases()),
            'pages' => fake()->randomDigitNotZero(),
        ];
    }
}
