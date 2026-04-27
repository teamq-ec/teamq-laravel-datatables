<?php

use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\Datatables\Filters\Mongo\GlobalFilter;
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

it('does not apply the filter if the value is not a string', function () {
    $this->request->query->add([
        'filter' => [
            'global' => ['abc', 'def'],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            GlobalFilter::allowed([
                'author.name',
                'title',
            ]),
        ]);

    expect($queryBuilder->get())->count()->toBe(2);
});

it('applies filter on properties model', function ($value, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'global' => $value,
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            GlobalFilter::allowed(['title', 'isbn']),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe($count)
        ->contains('title', $expected)->toBeTrue();
})->with([
    // Value | Expected value | Expected count
    'global by title' => ['bEyoNd', 'Laravel Beyond Crud', 1],
    'global by isbn' => ['589542', 'Domain Driven Design for Laravel', 1],
]);

it('applies filter on relationships of type "belongsTo"', function ($value, $expected, $count) {
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
        ->contains('title', $expected)->toBeTrue();
})->with([
    // Value | Expected value | Expected count
    'author.name' => ['sPatI', 'Laravel Beyond Crud', 1],
    'author.email' => ['ie.be', 'Laravel Beyond Crud', 1],
]);

it('applies filter on relationships of type "belongsTo" (deep)', function ($value, $expected, $count) {
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
        ->contains('title', $expected)->toBeTrue();
})->with([
    // Value | Expected value | Expected count
    'author.country.name' => ['elgium', 'Laravel Beyond Crud', 1],
    'author.country.code' => ['us', 'Domain Driven Design for Laravel', 1],
]);

it('applies filter on relationships of type "hasMany"', function ($value, $expected, $count) {
    $this->request->query->add([
        'filter' => [
            'global' => $value,
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            GlobalFilter::allowed(['chapters.title']),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe($count)
        ->contains('title', $expected)->toBeTrue();
})->with([
    // Value | Expected value | Expected count
    'chapters.title' => ['model', 'Laravel Beyond Crud', 1],
]);

it('filters by all properties and relationships at once', function () {
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
        ->contains('title', 'Laravel Beyond Crud')->toBeTrue();
});
