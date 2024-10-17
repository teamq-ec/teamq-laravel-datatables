<?php

use Illuminate\Support\Arr;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\Datatables\Enums\Comparators;
use TeamQ\Datatables\Enums\JoinType;
use TeamQ\Datatables\Filters\NumberFilter;
use Tests\Mocks\Models\Author;
use Tests\Mocks\Models\Book;
use Tests\Mocks\Models\Chapter;
use Tests\Mocks\Models\Country;

beforeEach(function () {
    $this->request = new Illuminate\Http\Request;
    $this->request->setMethod(Request::METHOD_GET);

    $this->firstBook = Book::factory()
        ->for(
            Author::factory()
                ->for(Country::factory()->create(['name' => 'Belgium', 'code' => 'BE', 'order' => '5']))
                ->create([
                    'email' => 'support@spatie.be',
                    'order' => '5',
                ])
        )
        ->has(Chapter::factory(['title' => 'Models', 'order' => '5']))
        ->has(Chapter::factory(['title' => 'Event Listeners', 'order' => '5']))
        ->create([
            'isbn' => '758952123',
            'order' => '5',
        ]);

    $this->secondBook = Book::factory()
        ->for(
            Author::factory()
                ->for(
                    Country::factory()->create(['name' => 'United States', 'code' => 'US', 'order' => '10']
                    )
                )
                ->create([
                    'email' => 'taylor@laravel.com',
                    'order' => '10',
                ])
        )
        ->has(Chapter::factory(['title' => 'Domain Layer', 'order' => '10']))
        ->has(Chapter::factory(['title' => 'Event Communication', 'order' => '10']))
        ->create([
            'isbn' => '5895421369',
            'order' => '10',
        ]);
});

it('the query is formed correctly', function () {
    // filter[isbn][value] = 54213
    // filter[isbn][operator] = contains
    $this->request->query->add([
        'filter' => [
            'order' => [
                'value' => 10,
                'operator' => Comparators\Number::Equal->value,
            ],
        ],
    ]);

    $query = str(Arr::query($this->request->query()))
        ->replace('%5B', '[')
        ->replace('%5D', ']')
        ->replace('%24', '$')
        ->value();

    expect($query)
        ->toBe('filter[order][value]=10&filter[order][operator]=$eq');
});

it('does not apply the filter when value is not an array of numbers', function () {
    $this->request->query->add([
        'filter' => [
            'order' => [
                'value' => ['abc', 'fg'],
                'operator' => 5,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('order', new NumberFilter),
        ]);

    expect($queryBuilder->toRawSql())
        ->toBe(
            'select * from `books`'
        );
});

it('does not apply the filter if the value is not a numeric or null', function () {
    $this->request->query->add([
        'filter' => [
            'order' => [
                'value' => 'abc',
                'operator' => 5,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('order', new NumberFilter),
        ]);

    expect($queryBuilder->toRawSql())
        ->toBe(
            'select * from `books`'
        );
});

it('apply filter without sub levels (value, operator)', function () {
    $this->request->query->add([
        'filter' => [
            'order' => 10,
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('order', new NumberFilter),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where `books`.`order` = ?'
        );
});

it('apply filter without sub levels (operator)', function () {
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

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where `books`.`order` = ?'
        );
});

it('apply filter without sub levels (value)', function () {
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

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where `books`.`order` is not null'
        );
});

it('apply filter with multi levels (value)', function () {
    $this->request->query->add([
        'filter' => [
            'order' => [
                'value' => [
                    5,
                    10,
                ],
                'operator' => Comparators\Number::In->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('order', new NumberFilter),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where `books`.`order` in (?, ?)'
        );
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

it('apply filter on relationships of type "belongsTo"', function () {
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

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where exists (select * from `authors` where `books`.`author_id` = `authors`.`id` and `authors`.`order` = ?)'
        );
});

it('apply filter on relationships of type "hasMany"', function () {
    $this->request->query->add([
        'filter' => [
            'chapters.order' => [
                'value' => '10',
                'operator' => Comparators\Number::Equal->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('chapters.order', new NumberFilter),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where exists (select * from `chapters` where `books`.`id` = `chapters`.`book_id` and `chapters`.`order` = ?)'
        );
});

it('apply filter on relationships using joins', function () {
    $this->request->query->add([
        'filter' => [
            'authors.order' => [
                'value' => '10',
                'operator' => Comparators\Number::NotEqual->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::query()->joinRelationship('author'), $this->request)
        ->allowedFilters([
            AllowedFilter::custom('authors.order', new NumberFilter(false)),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select `books`.* from `books` inner join `authors` on `books`.`author_id` = `authors`.`id` where `authors`.`order` != ?'
        );
});

it('apply filter on relationships using dynamic power joins', function () {
    $this->request->query->add([
        'filter' => [
            'author.order' => [
                'value' => '10',
                'operator' => Comparators\Number::NotEqual->value,
            ],
            'author.country.order' => [
                'value' => '10',
                'operator' => Comparators\Number::NotEqual->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('author.order', new NumberFilter(false, JoinType::Inner)),
            AllowedFilter::custom('author.country.order', new NumberFilter(false, JoinType::Inner)),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select `books`.* from `books` inner join `authors` on `books`.`author_id` = `authors`.`id` inner join `countries` on `authors`.`country_id` = `countries`.`id` where `authors`.`order` != ? and `countries`.`order` != ?'
        );
});

it('filters using all number comparison operators with power joins', function ($value, $operator, $expected, $count) {
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
            AllowedFilter::custom('author.order', new NumberFilter(false, JoinType::Inner)),
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
