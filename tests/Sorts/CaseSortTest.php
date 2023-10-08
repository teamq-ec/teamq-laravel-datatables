<?php

use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\QueryBuilder\Sorts\CaseSort;
use Tests\Mocks\Enums\BookClassificationEnum;
use Tests\Mocks\Models\Book;

beforeEach(function () {
    $this->request = new Illuminate\Http\Request();
    $this->request->setMethod(Request::METHOD_GET);

    $this->firstBook = Book::factory()
        ->create([
            'title' => 'Laravel Beyond Crud',
            'isbn' => '758952123',
            'pages' => 38,
            'classification' => BookClassificationEnum::OverTwelveYearsOld,
        ]);

    $this->secondBook = Book::factory()
        ->create([
            'title' => 'Domain Driven Design for Laravel',
            'isbn' => '5895421369',
            'pages' => 45,
            'classification' => BookClassificationEnum::Adults,
        ]);
});

it('sorts the records in ascending order', function () {
    $this->request->query->add([
        'sort' => 'classification',
    ]);

    $cases = collect(BookClassificationEnum::cases())
        ->pluck('name', 'value')
        ->toArray();

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedSorts([
            AllowedSort::custom('classification', new CaseSort($cases)),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            "select * from `books` order by case when books.classification = 1 then 'SuitableForAll' when books.classification = 2 then 'Kids' when books.classification = 3 then 'OverTwelveYearsOld' when books.classification = 4 then 'Adults' end  asc"
        )
        ->and($queryBuilder->get())
        ->sequence(
            fn ($book) => $book->classification->toBe(BookClassificationEnum::Adults),
            fn ($book) => $book->classification->toBe(BookClassificationEnum::OverTwelveYearsOld),
        );
});

it('sorts the records in descending order', function () {
    $this->request->query->add([
        'sort' => '-classification',
    ]);

    $cases = collect(BookClassificationEnum::cases())
        ->pluck('name', 'value')
        ->toArray();

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedSorts([
            AllowedSort::custom('classification', new CaseSort($cases)),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            "select * from `books` order by case when books.classification = 1 then 'SuitableForAll' when books.classification = 2 then 'Kids' when books.classification = 3 then 'OverTwelveYearsOld' when books.classification = 4 then 'Adults' end  desc"
        )
        ->and($queryBuilder->get())
        ->sequence(
            fn ($book) => $book->classification->toBe(BookClassificationEnum::OverTwelveYearsOld),
            fn ($book) => $book->classification->toBe(BookClassificationEnum::Adults),
        );
});

it('sort records by relationship fields', function () {

})->skip('WIP');
