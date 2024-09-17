<?php

use Illuminate\Database\Query\Expression;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\Datatables\Enums\JoinType;
use TeamQ\Datatables\Filters\GlobalFilter;
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
                ->for(Country::factory()->create(['name' => 'Belgium', 'code' => 'BE']))
                ->create([
                    'name' => 'Spatie',
                    'email' => 'support@spatie.be',
                ])
        )
        ->has(Chapter::factory(['title' => 'Models', 'number' => 1]))
        ->has(Chapter::factory(['title' => 'Event Listeners', 'number' => 2]))
        ->create([
            'title' => 'Laravel Beyond Crud',
            'isbn' => '758952123',
            'pages' => 38,
        ]);

    $this->secondBook = Book::factory()
        ->for(
            Author::factory()
                ->for(Country::factory()->create(['name' => 'United States', 'code' => 'US']))
                ->create([
                    'name' => 'Taylor Otwell',
                    'email' => 'taylor@laravel.com',
                ])
        )
        ->has(Chapter::factory(['title' => 'Domain Layer', 'number' => 1]))
        ->has(Chapter::factory(['title' => 'Event Communication', 'number' => 2]))
        ->create([
            'title' => 'Domain Driven Design for Laravel',
            'isbn' => '5895421369',
            'pages' => 45,
        ]);
});

it('the query is formed correctly', function () {
    $this->request->query->add([
        'filter' => [
            'global' => '12345',
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            GlobalFilter::allowed([
                'author.name',
                'title',
                'isbn',
                'author.email',
                'author.country.name',
                'author.country.code',
                'chapters.title',
            ]),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where (exists (select * from `authors` where `books`.`author_id` = `authors`.`id` and (LOWER(`authors`.`name`) LIKE ? or LOWER(`authors`.`email`) LIKE ? or exists (select * from `countries` where `authors`.`country_id` = `countries`.`id` and (LOWER(`countries`.`name`) LIKE ? or LOWER(`countries`.`code`) LIKE ?)))) or exists (select * from `chapters` where `books`.`id` = `chapters`.`book_id` and (LOWER(`chapters`.`title`) LIKE ?)) or LOWER(`books`.`title`) LIKE ? or LOWER(`books`.`isbn`) LIKE ?)'
        );
});

it('does not apply the filter if the value is not a string', function () {
    $this->request->query->add([
        'filter' => [
            'global' => [
                'abc',
                'def',
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            GlobalFilter::allowed([
                'author.name',
                'title',
            ]),
        ]);

    expect($queryBuilder->toRawSql())
        ->toBe(
            'select * from `books`'
        );
});

it('apply filter on properties model', function ($value, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'global' => $value,
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            GlobalFilter::allowed([
                'title',
                'isbn',
            ]),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe($count)
        ->contains('title', $expected)->toBeTrue()
        ->and($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where (LOWER(`books`.`title`) LIKE ? or LOWER(`books`.`isbn`) LIKE ?)'
        );
})->with([
    // Value | Expected value | Expected count
    'global by title' => ['bEyoNd', 'Laravel Beyond Crud', 1],
    'global by isbn' => ['589542', 'Domain Driven Design for Laravel', 1],
]);

it('apply filter on query expression', function ($value, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'global' => $value,
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            GlobalFilter::allowed([
                new Expression("CONCAT(books.title, ' - ', books.isbn)"),
            ]),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe($count)
        ->contains('title', $expected)->toBeTrue()
        ->and($queryBuilder->toSql())
        ->toBe(
            "select * from `books` where (LOWER(CONCAT(books.title, ' - ', books.isbn)) LIKE ?)"
        );
})->with([
    // Value | Expected value | Expected count
    'first book concat expression' => ['crud - 7589', 'Laravel Beyond Crud', 1],
    'second book concat expression' => ['El - 5895421', 'Domain Driven Design for Laravel', 1],
]);

it('apply filter on relationships of type "belongsTo"', function ($value, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'global' => $value,
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            GlobalFilter::allowed(['author.name', 'author.email']),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe($count)
        ->contains('title', $expected)->toBeTrue()
        ->and($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where (exists (select * from `authors` where `books`.`author_id` = `authors`.`id` and (LOWER(`authors`.`name`) LIKE ? or LOWER(`authors`.`email`) LIKE ?)))'
        );
})->with([
    // Value | Expected value | Expected count
    'author.name' => ['sPatI', 'Laravel Beyond Crud', 1],
    'author.email' => ['ie.be', 'Laravel Beyond Crud', 1],
]);

it('apply filter on relationships of type "belongsTo" (deep)', function ($value, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'global' => $value,
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            GlobalFilter::allowed(['author.country.name', 'author.country.code']),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe($count)
        ->contains('title', $expected)->toBeTrue()
        ->and($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where (exists (select * from `authors` where `books`.`author_id` = `authors`.`id` and (exists (select * from `countries` where `authors`.`country_id` = `countries`.`id` and (LOWER(`countries`.`name`) LIKE ? or LOWER(`countries`.`code`) LIKE ?)))))'
        );
})->with([
    // Value | Expected value | Expected count
    'author.country.name' => ['elgium', 'Laravel Beyond Crud', 1],
    'author.country.code' => ['us', 'Domain Driven Design for Laravel', 1],
]);

it('apply filter on relationships of type "hasMany"', function ($value, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'global' => $value,
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::query()->with(['author']), $this->request)
        ->allowedFilters([
            GlobalFilter::allowed(['chapters.title', 'chapters.number']),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe($count)
        ->contains('title', $expected)->toBeTrue()
        ->and($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where (exists (select * from `chapters` where `books`.`id` = `chapters`.`book_id` and (LOWER(`chapters`.`title`) LIKE ? or LOWER(`chapters`.`number`) LIKE ?)))'
        );
})->with([
    // Value | Expected value | Expected count
    'chapters.title' => ['model', 'Laravel Beyond Crud', 1],
    'chapters.number' => ['2', 'Laravel Beyond Crud', 2],
]);

it('apply filter on properties and relationships using joins', function ($value, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'global' => $value,
        ],
    ]);

    $query = Book::query()
        ->join('authors', 'authors.id', 'books.author_id')
        ->join('countries', 'countries.id', 'authors.country_id');

    $queryBuilder = QueryBuilder::for($query, $this->request)
        ->allowedFilters([
            GlobalFilter::allowed(['title', 'isbn', 'countries.name', 'authors.name'], false),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe($count)
        ->contains('title', $expected)->toBeTrue()
        ->and($queryBuilder->toSql())
        ->toBe(
            'select * from `books` inner join `authors` on `authors`.`id` = `books`.`author_id` inner join `countries` on `countries`.`id` = `authors`.`country_id` where (LOWER(`books`.`title`) LIKE ? or LOWER(`books`.`isbn`) LIKE ? or LOWER(`countries`.`name`) LIKE ? or LOWER(`authors`.`name`) LIKE ?)'
        );
})->with('filters.global');

it('apply filter on properties and relationships using dynamic power joins', function ($value, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'global' => $value,
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            GlobalFilter::allowed(['title', 'isbn', 'author.name', 'author.country.name'], false, JoinType::Inner),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe($count)
        ->contains('title', $expected)->toBeTrue()
        ->and($queryBuilder->toSql())
        ->toBe(
            'select `books`.* from `books` inner join `authors` on `books`.`author_id` = `authors`.`id` inner join `countries` on `authors`.`country_id` = `countries`.`id` where (LOWER(`authors`.`name`) LIKE ? or LOWER(`countries`.`name`) LIKE ? or LOWER(`authors`.`name`) LIKE ? or LOWER(`countries`.`name`) LIKE ? or LOWER(`books`.`title`) LIKE ? or LOWER(`books`.`isbn`) LIKE ?)'
        );
})->with('filters.global');

it('filter by all properties', function () {
    $this->request->query->add([
        'filter' => [
            'global' => 'Belgium',
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            GlobalFilter::allowed([
                'author.name',
                'title',
                'isbn',
                'author.email',
                'author.country.name',
                'author.country.code',
                'chapters.title',
            ]),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe(1)
        ->contains('title', 'Laravel Beyond Crud')->toBeTrue()
        ->and($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where (exists (select * from `authors` where `books`.`author_id` = `authors`.`id` and (LOWER(`authors`.`name`) LIKE ? or LOWER(`authors`.`email`) LIKE ? or exists (select * from `countries` where `authors`.`country_id` = `countries`.`id` and (LOWER(`countries`.`name`) LIKE ? or LOWER(`countries`.`code`) LIKE ?)))) or exists (select * from `chapters` where `books`.`id` = `chapters`.`book_id` and (LOWER(`chapters`.`title`) LIKE ?)) or LOWER(`books`.`title`) LIKE ? or LOWER(`books`.`isbn`) LIKE ?)'
        );
});
