<?php

use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\Datatables\Enums\JoinType;
use TeamQ\Datatables\Sorts\RelationSort;
use Tests\Mocks\Models\Country;
use Tests\Mocks\Models\Flight;

beforeEach(function () {
    $this->request = new Illuminate\Http\Request();
    $this->request->setMethod(Request::METHOD_GET);

    $belgium = Country::factory()->create(['name' => 'Belgium', 'code' => 'BE']);
    $ecuador = Country::factory()->create(['name' => 'Ecuador', 'code' => 'EC']);

    $this->firstFlight = Flight::factory()
        ->for($belgium, 'departure')
        ->for($ecuador, 'arrival')
        ->create([
            'code' => '7485',
        ]);

    $this->secondFlight = Flight::factory()
        ->for($ecuador, 'departure')
        ->for($belgium, 'arrival')
        ->create([
            'code' => '8596',
        ]);
});

it('apply relationships using aliases that point to the same table', function () {
    $this->request->query->add([
        'sort' => 'departure.code',
    ]);

    $queryBuilder = QueryBuilder::for(Flight::class, $this->request)
        ->allowedSorts([
            AllowedSort::custom('departure.code', new RelationSort(JoinType::Inner, joinAliases: 'departure')),
            AllowedSort::custom('arrival.code', new RelationSort(JoinType::Inner, joinAliases: 'arrival')),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select `flights`.* from `flights` inner join `countries` as `departure` on `flights`.`departure_id` = `departure`.`id` order by `departure`.`code` asc'
        )
        ->and($queryBuilder->get())
        ->sequence(
            fn ($flight) => $flight->code->toBe('7485'),
            fn ($flight) => $flight->code->toBe('8596'),
        );
});
