<?php

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
