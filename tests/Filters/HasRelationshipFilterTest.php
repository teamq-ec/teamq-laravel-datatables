<?php

use Illuminate\Support\Arr;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\Datatables\Filters\HasRelationshipFilter;
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
                ->for(Country::factory()->create(['name' => 'United States', 'code' => 'US', 'order' => '10'])
                )
                ->create([
                    'email' => 'taylor@laravel.com',
                    'order' => '10',
                ])
        )
        ->create([
            'isbn' => 'US5895421369',
            'order' => '10',
        ]);
});

it('the query is formed correctly', function () {
    // filter[has_chapters] = 1
    $this->request->query->add([
        'filter' => [
            'has_chapters' => 1,
        ],
    ]);

    $query = str(Arr::query($this->request->query()))
        ->replace('%5B', '[')
        ->replace('%5D', ']')
        ->value();

    expect($query)
        ->toBe('filter[has_chapters]=1');
});

it('does not apply the filter if the value is invalid', function () {
    $this->request->query->add([
        'filter' => [
            'has_chapters' => 'yes',
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('has_chapters', new HasRelationshipFilter(), 'chapters'),
        ]);

    expect($queryBuilder->toRawSql())
        ->toBe(
            'select * from `books`'
        );
});

it('applies the "has relationship" filter when the provided value is "1"', function () {
    $this->request->query->add([
        'filter' => [
            'has_chapters' => '1',
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('has_chapters', new HasRelationshipFilter(), 'chapters'),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where exists (select * from `chapters` where `books`.`id` = `chapters`.`book_id`)'
        );
});

it('applies the "doesnt have relationship" filter when the provided value is "0"', function () {
    $this->request->query->add([
        'filter' => [
            'has_chapters' => '0',
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('has_chapters', new HasRelationshipFilter(), 'chapters'),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select * from `books` where not exists (select * from `chapters` where `books`.`id` = `chapters`.`book_id`)'
        );
});
