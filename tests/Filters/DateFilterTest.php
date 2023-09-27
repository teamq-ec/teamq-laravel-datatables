<?php

use Illuminate\Support\Arr;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\QueryBuilder\Enums\Comparators;
use TeamQ\QueryBuilder\Enums\JoinType;
use TeamQ\QueryBuilder\Filters\DateFilter;
use Tests\Mocks\Models\Author;
use Tests\Mocks\Models\Book;
use Tests\Mocks\Models\Chapter;
use Tests\Mocks\Models\Country;

beforeEach(function () {
    $this->request = new Illuminate\Http\Request();
    $this->request->setMethod(Request::METHOD_GET);

    $this->firstBook = Book::factory()
        ->for(
            Author::factory()
                ->for(Country::factory()->create(['name' => 'Belgium', 'code' => 'BE', 'created_at' => '2019-08-10']))
                ->create([
                    'email' => 'support@spatie.be',
                    'created_at' => '2019-08-10',
                ])
        )
        ->has(Chapter::factory(['title' => 'Models', 'created_at' => '2019-08-10']))
        ->has(Chapter::factory(['title' => 'Event Listeners', 'created_at' => '2019-08-10']))
        ->create([
            'isbn' => '758952123',
            'created_at' => '2019-08-10',
        ]);

    $this->secondBook = Book::factory()
        ->for(
            Author::factory()
                ->for(
                    Country::factory()->create(['name' => 'United States', 'code' => 'US', 'created_at' => '2019-08-20']
                    )
                )
                ->create([
                    'email' => 'taylor@laravel.com',
                    'created_at' => '2019-08-20',
                ])
        )
        ->has(Chapter::factory(['title' => 'Domain Layer', 'created_at' => '2019-08-20']))
        ->has(Chapter::factory(['title' => 'Event Communication', 'created_at' => '2019-08-20']))
        ->create([
            'isbn' => '5895421369',
            'created_at' => '2019-08-20',
        ]);
});

it('the query is formed correctly', function () {
    // filter[isbn][value] = 54213
    // filter[isbn][operator] = contains
    $this->request->query->add([
        'filter' => [
            'created_at' => [
                'value' => '2019-08-20',
                'operator' => Comparators\Number::Equal->value,
            ],
        ],
    ]);

    $query = str(Arr::query($this->request->query()))
        ->replace('%5B', '[')
        ->replace('%5D', ']')
        ->value();

    expect($query)
        ->toBe('filter[created_at][value]=2019-08-20&filter[created_at][operator]=1');
});

it('does not apply the filter when value is not an array of dates', function () {
    $this->request->query->add([
        'filter' => [
            'created_at' => [
                'value' => ['abc', 'fg'],
                'operator' => 5,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('created_at', new DateFilter()),
        ]);

    expect($queryBuilder->toRawSql())
        ->toBe(
            'select * from `books`'
        );
});

it('does not apply the filter if the value is not a date or null', function () {
    $this->request->query->add([
        'filter' => [
            'created_at' => [
                'value' => 'abc',
                'operator' => 5,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('created_at', new DateFilter()),
        ]);

    expect($queryBuilder->toRawSql())
        ->toBe(
            'select * from `books`'
        );
});

it('apply filter without sub levels (value, operator)', function () {
    $this->request->query->add([
        'filter' => [
            'created_at' => '2023-08-01',
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('created_at', new DateFilter()),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where date(`books`.`created_at`) = ?'
        );
});

it('apply filter without sub levels (operator)', function () {
    $this->request->query->add([
        'filter' => [
            'created_at' => [
                'value' => '2023-08-01',
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('created_at', new DateFilter()),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where date(`books`.`created_at`) = ?'
        );
});

it('apply filter without sub levels (value)', function () {
    $this->request->query->add([
        'filter' => [
            'created_at' => [
                'operator' => Comparators\Number::Filled->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('created_at', new DateFilter()),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where `books`.`created_at` is not null'
        );
});

it('apply filter with multi levels (value)', function () {
    $this->request->query->add([
        'filter' => [
            'created_at' => [
                'value' => [
                    '2020-08-01',
                    '2023-08-01',
                ],
                'operator' => Comparators\Number::In->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('created_at', new DateFilter()),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where `books`.`created_at` in (?, ?)'
        );
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
            AllowedFilter::custom('created_at', new DateFilter()),
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

it('apply filter on relationships of type "belongsTo"', function () {
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
            AllowedFilter::custom('author.created_at', new DateFilter()),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where exists (select * from `authors` where `books`.`author_id` = `authors`.`id` and date(`authors`.`created_at`) = ?)'
        );
});

it('apply filter on relationships of type "hasMany"', function () {
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
            AllowedFilter::custom('chapters.created_at', new DateFilter()),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where exists (select * from `chapters` where `books`.`id` = `chapters`.`book_id` and date(`chapters`.`created_at`) = ?)'
        );
});

it('apply filter on relationships using joins', function () {
    $this->request->query->add([
        'filter' => [
            'authors.created_at' => [
                'value' => '2019-08-10',
                'operator' => Comparators\Number::NotEqual->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::query()->joinRelationship('author'), $this->request)
        ->allowedFilters([
            AllowedFilter::custom('authors.created_at', new DateFilter(false)),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select `books`.* from `books` inner join `authors` on `books`.`author_id` = `authors`.`id` where date(`authors`.`created_at`) != ?'
        );
});

it('apply filter on relationships using dynamic power joins', function () {
    $this->request->query->add([
        'filter' => [
            'author.created_at' => [
                'value' => '2019-08-10',
                'operator' => Comparators\Number::NotEqual->value,
            ],
            'author.country.created_at' => [
                'value' => '2019-08-10',
                'operator' => Comparators\Number::NotEqual->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('author.created_at', new DateFilter(false, JoinType::Inner)),
            AllowedFilter::custom('author.country.created_at', new DateFilter(false, JoinType::Inner)),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select `books`.* from `books` inner join `authors` on `books`.`author_id` = `authors`.`id` inner join `countries` on `authors`.`country_id` = `countries`.`id` where date(`authors`.`created_at`) != ? and date(`countries`.`created_at`) != ?'
        );
});

it('filters using all number comparison operators with power joins', function ($value, $operator, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'author.created_at' => [
                'value' => $value,
                'operator' => $operator->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('author.created_at', new DateFilter(false, JoinType::Inner)),
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
