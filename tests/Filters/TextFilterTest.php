<?php

use Illuminate\Support\Arr;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\Datatables\Enums\Comparators;
use TeamQ\Datatables\Enums\JoinType;
use TeamQ\Datatables\Filters\TextFilter;
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
                ->for(Country::factory()->create(['name' => 'Belgium', 'code' => 'BE', 'order' => '5']))
                ->create([
                    'email' => 'support@spatie.be',
                    'order' => '5',
                ])
        )
        ->has(Chapter::factory(['title' => 'Models', 'order' => '5']))
        ->has(Chapter::factory(['title' => 'Event Listeners', 'order' => '5']))
        ->create([
            'isbn' => 'BE758952123',
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
            'isbn' => 'US5895421369',
            'order' => '10',
        ]);
});

it('the query is formed correctly', function () {
    // filter[isbn][value] = 54213
    // filter[isbn][operator] = contains
    $this->request->query->add([
        'filter' => [
            'isbn' => [
                'value' => '54213',
                'operator' => 5,
            ],
        ],
    ]);

    $query = str(Arr::query($this->request->query()))
        ->replace('%5B', '[')
        ->replace('%5D', ']')
        ->value();

    expect($query)
        ->toBe('filter[isbn][value]=54213&filter[isbn][operator]=5');
});

it('does not apply the filter if the value is an array of empty strings', function () {
    $this->request->query->add([
        'filter' => [
            'isbn' => [
                'value' => ['', ''],
                'operator' => 5,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter()),
        ]);

    expect($queryBuilder->toRawSql())
        ->toBe(
            'select * from `books`'
        );
});

it('does not apply the filter if the value is not a string or null', function () {
    $this->request->query->add([
        'filter' => [
            'isbn' => [
                'value' => true,
                'operator' => 5,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter()),
        ]);

    expect($queryBuilder->toRawSql())
        ->toBe(
            'select * from `books`'
        );
});

it('apply filter without sub levels (value, operator)', function () {
    $this->request->query->add([
        'filter' => [
            'isbn' => '54213',
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter()),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where lower(`books`.`isbn`) like ?'
        );
});

it('apply filter without sub levels (operator)', function () {
    $this->request->query->add([
        'filter' => [
            'isbn' => [
                'value' => '54213',
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter()),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where lower(`books`.`isbn`) like ?'
        );
});

it('apply filter without sub levels (value)', function () {
    $this->request->query->add([
        'filter' => [
            'isbn' => [
                'operator' => Comparators\Text::Filled->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter()),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where `books`.`isbn` is not null'
        );
});

it('apply filter with multi levels (value)', function () {
    $this->request->query->add([
        'filter' => [
            'isbn' => [
                'value' => [
                    '54213',
                    '7589',
                ],
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter()),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where (lower(`books`.`isbn`) like ? or lower(`books`.`isbn`) like ?)'
        );
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
            AllowedFilter::custom('isbn', new TextFilter()),
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

it('apply filter on relationships of type "belongsTo"', function () {
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
            AllowedFilter::custom('author.email', new TextFilter()),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where exists (select * from `authors` where `books`.`author_id` = `authors`.`id` and lower(`authors`.`email`) like ?)'
        );
});

it('apply filter on relationships of type "hasMany"', function () {
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
            AllowedFilter::custom('chapters.title', new TextFilter()),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where exists (select * from `chapters` where `books`.`id` = `chapters`.`book_id` and lower(`chapters`.`title`) like ?)'
        );
});

it('apply filter on relationships using joins', function () {
    $this->request->query->add([
        'filter' => [
            'authors.email' => [
                'value' => '@spatie.be',
                'operator' => Comparators\Text::EndWith->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::query()->joinRelationship('author'), $this->request)
        ->allowedFilters([
            AllowedFilter::custom('authors.email', new TextFilter(false)),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select `books`.* from `books` inner join `authors` on `books`.`author_id` = `authors`.`id` where lower(`authors`.`email`) like ?'
        );
});

it('apply filter on relationships using dynamic power joins', function () {
    $this->request->query->add([
        'filter' => [
            'author.email' => [
                'value' => '@spatie.be',
                'operator' => Comparators\Text::EndWith->value,
            ],
            'author.country.name' => [
                'value' => 'Belgium',
                'operator' => Comparators\Text::Contains->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('author.email', new TextFilter(false, JoinType::Inner)),
            AllowedFilter::custom('author.country.name', new TextFilter(false, JoinType::Inner)),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select `books`.* from `books` inner join `authors` on `books`.`author_id` = `authors`.`id` inner join `countries` on `authors`.`country_id` = `countries`.`id` where lower(`authors`.`email`) like ? and lower(`countries`.`name`) like ?'
        );
});

it('filters using all text comparison operators with power joins', function ($value, $operator, $expected, $count) {
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
            AllowedFilter::custom('author.email', new TextFilter(false, JoinType::Inner)),
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
        expect($result)->contains('author.email', $expected)->toBeFalse();
    } else {
        expect($result)->contains('author.email', $expected)->toBeTrue();
    }
})->with('filters.text:author.email');
