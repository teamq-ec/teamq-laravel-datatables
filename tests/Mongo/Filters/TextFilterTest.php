<?php

use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\Datatables\Enums\Comparators;
use TeamQ\Datatables\Filters\Mongo\TextFilter;
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
            'isbn' => 'BE758952123',
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
            'isbn' => 'US5895421369',
            'order' => 10,
        ]);
});

it('does not apply the filter if the value is an array of empty strings', function () {
    $this->request->query->add([
        'filter' => [
            'isbn' => [
                'value' => ['', ''],
                'operator' => Comparators\Text::Contains->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter),
        ]);

    expect($queryBuilder->get())->count()->toBe(2);
});

it('does not apply the filter if the value is not a string or null', function () {
    $this->request->query->add([
        'filter' => [
            'isbn' => [
                'value' => true,
                'operator' => Comparators\Text::Contains->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter),
        ]);

    expect($queryBuilder->get())->count()->toBe(2);
});

it('applies filter without sub levels (value, operator)', function () {
    $this->request->query->add([
        'filter' => [
            'isbn' => '54213',
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe(1)
        ->contains('isbn', 'US5895421369')->toBeTrue();
});

it('applies filter without sub levels (operator)', function () {
    $this->request->query->add([
        'filter' => [
            'isbn' => [
                'value' => '54213',
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe(1)
        ->contains('isbn', 'US5895421369')->toBeTrue();
});

it('applies filter without sub levels (value)', function () {
    $this->request->query->add([
        'filter' => [
            'isbn' => [
                'operator' => Comparators\Text::Filled->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter),
        ]);

    expect($queryBuilder->get())->count()->toBe(2);
});

it('applies filter with multi levels (value)', function () {
    $this->request->query->add([
        'filter' => [
            'isbn' => [
                'value' => ['54213', '7589'],
                'operator' => Comparators\Text::Contains->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter),
        ]);

    expect($queryBuilder->get())->count()->toBe(2);
});

it('filters using all text comparison operators', function ($value, $operator, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'isbn' => [
                'value' => $value,
                'operator' => $operator->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter),
        ]);

    $result = $queryBuilder->get();

    expect($result)->count()->toBe($count);

    $negative = [
        Comparators\Text::NotEqual,
        Comparators\Text::NotStartWith,
        Comparators\Text::NotEndWith,
        Comparators\Text::NotContains,
        Comparators\Text::NotFilled,
        Comparators\Text::NotIn,
    ];

    if (in_array($operator, $negative, true)) {
        expect($result)->contains('isbn', $expected)->toBeFalse();
    } else {
        expect($result)->contains('isbn', $expected)->toBeTrue();
    }
})->with('filters.text:isbn');

it('applies filter on relationships of type "belongsTo"', function () {
    $this->request->query->add([
        'filter' => [
            'author.email' => [
                'value' => '@spatie.be',
                'operator' => Comparators\Text::EndWith->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('author.email', new TextFilter),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe(1)
        ->contains('isbn', 'BE758952123')->toBeTrue();
});

it('applies filter on relationships of type "hasMany"', function () {
    $this->request->query->add([
        'filter' => [
            'chapters.title' => [
                'value' => 'Event',
                'operator' => Comparators\Text::Contains->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('chapters.title', new TextFilter),
        ]);

    expect($queryBuilder->get())->count()->toBe(2);
});

it('filters using all text comparison operators across a relationship', function ($value, $operator, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'author.email' => [
                'value' => $value,
                'operator' => $operator->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('author.email', new TextFilter),
        ]);

    expect($queryBuilder->get())->count()->toBe($count);
})->with('filters.text:author.email');
