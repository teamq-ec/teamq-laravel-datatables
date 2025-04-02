<?php

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\Datatables\QueryBuilder;
use Tests\Mocks\Models\Author;

beforeEach(function () {
    $this->request = new Illuminate\Http\Request;
    $this->request->setMethod(Request::METHOD_GET);
});

it('when the query parameter per_page is not present, laravel pagination is used', function () {
    $queryBuilder = QueryBuilder::for(Author::class, $this->request);

    expect($queryBuilder->paginate())
        ->perPage()->toBe(15)
        ->and($queryBuilder->result())
        ->perPage()->toBe(15);
});

it('when the query parameter per_page is not valid, laravel pagination is used', function () {
    $this->request->query->add(['perPage' => 50]);

    $queryBuilder = QueryBuilder::for(Author::class, $this->request);

    expect($queryBuilder->paginate())
        ->perPage()->toBe(15)
        ->and($queryBuilder->result())
        ->perPage()->toBe(15);
});

it('when the query parameter per_page is less than the min, laravel pagination is used', function () {
    $this->request->query->add(['per_page' => 0]);

    $queryBuilder = QueryBuilder::for(Author::class, $this->request);

    expect($queryBuilder->paginate())
        ->perPage()->toBe(15)
        ->and($queryBuilder->result())
        ->perPage()->toBe(15);
});

it('when the query parameter per_page is greater than the max, laravel pagination is used', function () {
    $this->request->query->add(['per_page' => 51]);

    $queryBuilder = QueryBuilder::for(Author::class, $this->request)
        ->setMaxPerPage(50);

    expect($queryBuilder->paginate())
        ->perPage()->toBe(15)
        ->and($queryBuilder->result())
        ->perPage()->toBe(15);
});

it('by default there is no limit for the parameter per_page', function () {
    $this->request->query->add(['per_page' => 1000]);

    $queryBuilder = QueryBuilder::for(Author::class, $this->request);

    expect($queryBuilder->paginate())
        ->perPage()->toBe(1000)
        ->and($queryBuilder->result())
        ->perPage()->toBe(1000);
});

it('the per_page value is used', function () {
    $this->request->query->add(['per_page' => 50]);

    $queryBuilder = QueryBuilder::for(Author::class, $this->request);

    expect($queryBuilder->paginate())
        ->perPage()->toBe(50)
        ->and($queryBuilder->result())
        ->perPage()->toBe(50);
});

it('this return a collection with all records when `all` is sent', function () {
    $this->request->query->add(['per_page' => 'all']);

    $queryBuilder = QueryBuilder::for(Author::class, $this->request);

    expect($queryBuilder->result())
        ->toBeInstanceOf(Collection::class);
});

it('this return a length aware pagination with paginated records when `all` is not sent', function () {
    $queryBuilder = QueryBuilder::for(Author::class, $this->request);

    expect($queryBuilder->result())
        ->toBeInstanceOf(LengthAwarePaginator::class);
});

it('returns the links with the URL parameters provided', function () {
    $this->request->query->add([
        'per_page' => '10',
        'filter' => [
            'name' => 'Luis Arce',
            'country_id' => 5,
        ],
        'include' => 'country',
        'sort' => '-created_at,order',
    ]);

    $queryBuilder = QueryBuilder::for(Author::class, $this->request)
        ->allowedFilters([
            AllowedFilter::exact('country_id'),
            AllowedFilter::partial('name'),
            AllowedFilter::partial('email'),
        ])
        ->allowedSorts([
            AllowedSort::field('order'),
            AllowedSort::field('created_at'),
        ])
        ->allowedIncludes([
            AllowedInclude::relationship('country'),
            AllowedInclude::relationship('books'),
        ]);

    expect($queryBuilder->result()->getUrlRange(1, 2))
        ->toBe([
            1 => 'http://localhost?per_page=10&filter%5Bname%5D=Luis%20Arce&filter%5Bcountry_id%5D=5&include=country&sort=-created_at%2Corder&page=1',
            2 => 'http://localhost?per_page=10&filter%5Bname%5D=Luis%20Arce&filter%5Bcountry_id%5D=5&include=country&sort=-created_at%2Corder&page=2',
        ]);
});
