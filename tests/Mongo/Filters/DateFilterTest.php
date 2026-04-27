<?php

use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\Datatables\Enums\Comparators;
use TeamQ\Datatables\Filters\Mongo\DateFilter;
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
                ->for(Country::factory()->create(['name' => 'Belgium', 'code' => 'BE', 'created_at' => '2019-08-10 12:00:00']))
                ->create([
                    'email' => 'support@spatie.be',
                    'created_at' => '2019-08-10 12:00:00',
                ])
        )
        ->has(Chapter::factory(['title' => 'Models', 'created_at' => '2019-08-10 12:00:00']))
        ->has(Chapter::factory(['title' => 'Event Listeners', 'created_at' => '2019-08-10 12:00:00']))
        ->create([
            'isbn' => '758952123',
            'created_at' => '2019-08-10 12:00:00',
        ]);

    $this->secondBook = Book::factory()
        ->for(
            Author::factory()
                ->for(Country::factory()->create(['name' => 'United States', 'code' => 'US', 'created_at' => '2019-08-20 12:00:00']))
                ->create([
                    'email' => 'taylor@laravel.com',
                    'created_at' => '2019-08-20 12:00:00',
                ])
        )
        ->has(Chapter::factory(['title' => 'Domain Layer', 'created_at' => '2019-08-20 12:00:00']))
        ->has(Chapter::factory(['title' => 'Event Communication', 'created_at' => '2019-08-20 12:00:00']))
        ->create([
            'isbn' => '5895421369',
            'created_at' => '2019-08-20 12:00:00',
        ]);
});

it('does not apply the filter when value is not an array of dates', function () {
    $this->request->query->add([
        'filter' => [
            'created_at' => [
                'value' => ['abc', 'fg'],
                'operator' => Comparators\Number::Between->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('created_at', new DateFilter),
        ]);

    expect($queryBuilder->get())->count()->toBe(2);
});

it('does not apply the filter if the value is not a date or null', function () {
    $this->request->query->add([
        'filter' => [
            'created_at' => [
                'value' => 'abc',
                'operator' => Comparators\Number::Equal->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('created_at', new DateFilter),
        ]);

    expect($queryBuilder->get())->count()->toBe(2);
});

it('applies filter without sub levels (value, operator)', function () {
    $this->request->query->add([
        'filter' => [
            'created_at' => '2019-08-10',
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('created_at', new DateFilter),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe(1)
        ->contains('isbn', '758952123')->toBeTrue();
});

it('applies filter without sub levels (operator)', function () {
    $this->request->query->add([
        'filter' => [
            'created_at' => [
                'value' => '2019-08-10',
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('created_at', new DateFilter),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe(1)
        ->contains('isbn', '758952123')->toBeTrue();
});

it('applies filter without sub levels (value)', function () {
    $this->request->query->add([
        'filter' => [
            'created_at' => [
                'operator' => Comparators\Number::Filled->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('created_at', new DateFilter),
        ]);

    expect($queryBuilder->get())->count()->toBe(2);
});

it('filters using all date comparison operators', function ($value, $operator, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'created_at' => [
                'value' => $value,
                'operator' => $operator->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('created_at', new DateFilter),
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
})->with('filters.date');

it('applies filter on relationships of type "belongsTo"', function () {
    $this->request->query->add([
        'filter' => [
            'author.created_at' => [
                'value' => '2019-08-10',
                'operator' => Comparators\Number::Equal->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('author.created_at', new DateFilter),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe(1)
        ->contains('isbn', '758952123')->toBeTrue();
});

it('applies filter on relationships of type "hasMany"', function () {
    $this->request->query->add([
        'filter' => [
            'chapters.created_at' => [
                'value' => '2019-08-10',
                'operator' => Comparators\Number::Equal->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('chapters.created_at', new DateFilter),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe(1)
        ->contains('isbn', '758952123')->toBeTrue();
});
