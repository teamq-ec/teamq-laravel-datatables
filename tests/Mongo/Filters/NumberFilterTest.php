<?php

use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\Datatables\Enums\Comparators;
use TeamQ\Datatables\Filters\Mongo\NumberFilter;
use Tests\Mocks\Mongo\Models\Author;
use Tests\Mocks\Mongo\Models\Book;
use Tests\Mocks\Mongo\Models\Chapter;
use Tests\Mocks\Mongo\Models\Country;

beforeEach(function () {
    $this->request = new Illuminate\Http\Request;
    $this->request->setMethod(Request::METHOD_GET);

    $this->firstBook = Book::factory()
        ->for(
            Author::factory()
                ->for(Country::factory()->create(['name' => 'Belgium', 'code' => 'BE', 'order' => 5]))
                ->create([
                    'email' => 'support@spatie.be',
                    'order' => 5,
                ])
        )
        ->has(Chapter::factory(['title' => 'Models', 'order' => 5]))
        ->has(Chapter::factory(['title' => 'Event Listeners', 'order' => 5]))
        ->create([
            'isbn' => '758952123',
            'order' => 5,
        ]);

    $this->secondBook = Book::factory()
        ->for(
            Author::factory()
                ->for(Country::factory()->create(['name' => 'United States', 'code' => 'US', 'order' => 10]))
                ->create([
                    'email' => 'taylor@laravel.com',
                    'order' => 10,
                ])
        )
        ->has(Chapter::factory(['title' => 'Domain Layer', 'order' => 10]))
        ->has(Chapter::factory(['title' => 'Event Communication', 'order' => 10]))
        ->create([
            'isbn' => '5895421369',
            'order' => 10,
        ]);
});

it('does not apply the filter when value is not an array of numbers', function () {
    $this->request->query->add([
        'filter' => [
            'order' => [
                'value' => ['abc', 'fg'],
                'operator' => Comparators\Number::Equal->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('order', new NumberFilter),
        ]);

    expect($queryBuilder->get())->count()->toBe(2);
});

it('does not apply the filter if the value is not numeric or null', function () {
    $this->request->query->add([
        'filter' => [
            'order' => [
                'value' => 'abc',
                'operator' => Comparators\Number::Equal->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('order', new NumberFilter),
        ]);

    expect($queryBuilder->get())->count()->toBe(2);
});

it('applies filter without sub levels (value, operator)', function () {
    $this->request->query->add([
        'filter' => [
            'order' => 10,
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('order', new NumberFilter),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe(1)
        ->contains('isbn', '5895421369')->toBeTrue();
});

it('applies filter without sub levels (operator)', function () {
    $this->request->query->add([
        'filter' => [
            'order' => [
                'value' => 10,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('order', new NumberFilter),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe(1)
        ->contains('isbn', '5895421369')->toBeTrue();
});

it('applies filter without sub levels (value)', function () {
    $this->request->query->add([
        'filter' => [
            'order' => [
                'operator' => Comparators\Number::Filled->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('order', new NumberFilter),
        ]);

    expect($queryBuilder->get())->count()->toBe(2);
});

it('applies filter with multi levels (value)', function () {
    $this->request->query->add([
        'filter' => [
            'order' => [
                'value' => [5, 10],
                'operator' => Comparators\Number::In->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('order', new NumberFilter),
        ]);

    expect($queryBuilder->get())->count()->toBe(2);
});

it('filters using all number comparison operators', function ($value, $operator, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'order' => [
                'value' => $value,
                'operator' => $operator->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('order', new NumberFilter),
        ]);

    $result = $queryBuilder->get();

    expect($result)->count()->toBe($count);

    $negative = [
        Comparators\Number::NotEqual,
        Comparators\Number::NotFilled,
        Comparators\Number::NotIn,
        Comparators\Number::NotBetween,
    ];

    if (in_array($operator, $negative, true)) {
        expect($result)->contains('isbn', $expected)->toBeFalse();
    } else {
        expect($result)->contains('isbn', $expected)->toBeTrue();
    }
})->with('filters.number');

it('applies filter on relationships of type "belongsTo"', function () {
    $this->request->query->add([
        'filter' => [
            'author.order' => [
                'value' => 5,
                'operator' => Comparators\Number::Equal->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('author.order', new NumberFilter),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe(1)
        ->contains('isbn', '758952123')->toBeTrue();
});

it('applies filter on relationships of type "hasMany"', function () {
    $this->request->query->add([
        'filter' => [
            'chapters.order' => [
                'value' => 10,
                'operator' => Comparators\Number::Equal->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('chapters.order', new NumberFilter),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe(1)
        ->contains('isbn', '5895421369')->toBeTrue();
});

it('filters using all number comparison operators across a relationship', function ($value, $operator, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'author.order' => [
                'value' => $value,
                'operator' => $operator->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('author.order', new NumberFilter),
        ]);

    expect($queryBuilder->get())->count()->toBe($count);
})->with('filters.number');
