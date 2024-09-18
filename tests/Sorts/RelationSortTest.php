<?php

use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\Datatables\Enums\AggregationType;
use TeamQ\Datatables\Enums\JoinType;
use TeamQ\Datatables\Sorts\RelationSort;
use Tests\Mocks\Enums\BookClassificationEnum;
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
        ->has(Chapter::factory(['title' => 'Models', 'number' => 3]))
        ->has(Chapter::factory(['title' => 'Event Listeners', 'number' => 4]))
        ->create([
            'title' => 'Laravel Beyond Crud',
            'isbn' => '758952123',
            'pages' => 38,
            'classification' => BookClassificationEnum::OverTwelveYearsOld,
            'order' => 5,
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
            'classification' => BookClassificationEnum::Adults,
            'order' => 3,
        ]);
});

it('sort the records in ascending order using the "BelongTo" relationship', function () {
    $this->request->query->add([
        'sort' => 'author.name',
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedSorts([
            AllowedSort::custom('author.name', new RelationSort(JoinType::Inner)),
        ]);

    expect($queryBuilder->get())
        ->sequence(
            fn ($book) => $book->author->name->toBe('Spatie'),
            fn ($book) => $book->author->name->toBe('Taylor Otwell'),
        );
});

it('sort the records in descending order using the "BelongTo" relationship', function () {
    $this->request->query->add([
        'sort' => '-author.name',
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedSorts([
            AllowedSort::custom('author.name', new RelationSort(JoinType::Inner)),
        ]);

    expect($queryBuilder->get())
        ->sequence(
            fn ($book) => $book->author->name->toBe('Taylor Otwell'),
            fn ($book) => $book->author->name->toBe('Spatie'),
        );
});

it('sort the records in ascending order using the "BelongTo" relationship (deep level)', function () {
    $this->request->query->add([
        'sort' => 'author.country.name',
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedSorts([
            AllowedSort::custom('author.country.name', new RelationSort(JoinType::Inner)),
        ]);

    expect($queryBuilder->get())
        ->sequence(
            fn ($book) => $book->author->country->name->toBe('Belgium'),
            fn ($book) => $book->author->country->name->toBe('United States'),
        );
});

it('sort the records in descending order using the "BelongTo" relationship (deep level)', function () {
    $this->request->query->add([
        'sort' => '-author.country.name',
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedSorts([
            AllowedSort::custom('author.country.name', new RelationSort(JoinType::Inner)),
        ]);

    expect($queryBuilder->get())
        ->sequence(
            fn ($book) => $book->author->country->name->toBe('United States'),
            fn ($book) => $book->author->country->name->toBe('Belgium'),
        );
});

it('sort the records in ascending order using the "HasMany" relationship with aggregations', function () {
    $this->request->query->add([
        'sort' => 'chapters.number',
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedSorts([
            AllowedSort::custom('chapters.number', new RelationSort(JoinType::Left, AggregationType::Sum)),
        ]);

    expect($queryBuilder->distinct()->get())
        ->sequence(
            fn ($book) => $book->title->toBe('Domain Driven Design for Laravel'),
            fn ($book) => $book->title->toBe('Laravel Beyond Crud'),
        );
});

it('sort the records in descending order using the "HasMany" relationship with aggregations', function () {
    $this->request->query->add([
        'sort' => '-chapters.number',
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedSorts([
            AllowedSort::custom('chapters.number', new RelationSort(JoinType::Left, AggregationType::Sum)),
        ]);

    expect($queryBuilder->get())
        ->sequence(
            fn ($book) => $book->title->toBe('Laravel Beyond Crud'),
            fn ($book) => $book->title->toBe('Domain Driven Design for Laravel'),
        );
});

it('sort the records in descending order using the "HasMany" relationship with aliases', function () {
    $this->request->query->add([
        'sort' => '-chapters.number',
    ]);

    $queryBuilder = QueryBuilder::for(Book::class, $this->request)
        ->allowedSorts([
            AllowedSort::custom('chapters.number',
                new RelationSort(JoinType::Left, AggregationType::Sum, 'chapters_alias')
            ),
        ]);

    expect($queryBuilder->toSql())
        ->toBe('select `books`.*, sum(chapters_alias.number) as chapters_alias_number_sum from `books` left join `chapters` as `chapters_alias` on `chapters_alias`.`book_id` = `books`.`id` group by `books`.`id` order by chapters_alias_number_sum desc')
        ->and($queryBuilder->get())
        ->sequence(
            fn ($book) => $book->title->toBe('Laravel Beyond Crud'),
            fn ($book) => $book->title->toBe('Domain Driven Design for Laravel'),
        );
});

it('sort the records in descending order using the multiple relationship aliases', function () {
    $this->request->query->add([
        'sort' => 'authors.books.order',
    ]);

    $queryBuilder = QueryBuilder::for(Country::class, $this->request)
        ->allowedSorts([
            AllowedSort::custom('authors.books.order',
                new RelationSort(JoinType::Inner, joinAliases: [
                    'authors' => fn ($join) => $join->as('authors_alias'),
                    'books' => fn ($join) => $join->as('books_alias'),
                ])
            ),
        ]);

    expect($queryBuilder->toSql())
        ->toBe('select `countries`.* from `countries` inner join `authors` as `authors_alias` on `authors_alias`.`country_id` = `countries`.`id` inner join `books` as `books_alias` on `books_alias`.`author_id` = `authors_alias`.`id` order by `books_alias`.`order` asc')
        ->and($queryBuilder->get())
        ->sequence(
            fn ($country) => $country->code->toBe('US'),
            fn ($country) => $country->code->toBe('BE'),
        );
});
