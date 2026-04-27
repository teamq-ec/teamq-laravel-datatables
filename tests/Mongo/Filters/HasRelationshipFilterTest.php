<?php

use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\Datatables\Filters\Mongo\HasRelationshipFilter;
use Tests\Mocks\Mongo\Models\Author;
use Tests\Mocks\Mongo\Models\Book;
use Tests\Mocks\Mongo\Models\Chapter;
use Tests\Mocks\Mongo\Models\Country;

beforeEach(function () {
    $this->request = new Illuminate\Http\Request;
    $this->request->setMethod(Request::METHOD_GET);

    // Book with chapters.
    $this->firstBook = Book::factory()
        ->for(
            Author::factory()
                ->for(Country::factory()->create(['name' => 'Belgium', 'code' => 'BE']))
                ->create(['email' => 'support@spatie.be'])
        )
        ->has(Chapter::factory(['title' => 'Models']))
        ->has(Chapter::factory(['title' => 'Event Listeners']))
        ->create(['isbn' => 'BE758952123']);

    // Book without chapters.
    $this->secondBook = Book::factory()
        ->for(
            Author::factory()
                ->for(Country::factory()->create(['name' => 'United States', 'code' => 'US']))
                ->create(['email' => 'taylor@laravel.com'])
        )
        ->create(['isbn' => 'US5895421369']);
});

it('does not apply the filter if the value is invalid', function () {
    $this->request->query->add([
        'filter' => [
            'has_chapters' => 'yes',
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('has_chapters', new HasRelationshipFilter, 'chapters'),
        ]);

    expect($queryBuilder->get())->count()->toBe(2);
});

it('applies the "has relationship" filter when the provided value is "1"', function () {
    $this->request->query->add([
        'filter' => [
            'has_chapters' => '1',
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('has_chapters', new HasRelationshipFilter, 'chapters'),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe(1)
        ->contains('isbn', 'BE758952123')->toBeTrue();
});

it('applies the "doesnt have relationship" filter when the provided value is "0"', function () {
    $this->request->query->add([
        'filter' => [
            'has_chapters' => '0',
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('has_chapters', new HasRelationshipFilter, 'chapters'),
        ]);

    expect($queryBuilder->get())
        ->count()->toBe(1)
        ->contains('isbn', 'US5895421369')->toBeTrue();
});
