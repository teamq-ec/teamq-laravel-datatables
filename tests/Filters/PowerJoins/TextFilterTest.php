<?php

use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use TeamQ\Datatables\Enums\Comparators;
use TeamQ\Datatables\Enums\JoinType;
use TeamQ\Datatables\Filters\TextFilter;
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
        'filter' => [
            'departure.name' => [
                'value' => 'gium',
                'operator' => Comparators\Text::EndWith->value,
            ],
            'arrival.name' => [
                'value' => 'cuado',
                'operator' => Comparators\Text::Contains->value,
            ],
        ],
    ]);

    $queryBuilder = QueryBuilder::for(Flight::class, $this->request)
        ->allowedFilters([
            AllowedFilter::custom('departure.name', new TextFilter(false, JoinType::Inner, 'departure')),
            AllowedFilter::custom('arrival.name', new TextFilter(false, JoinType::Inner, 'arrival')),
        ]);

    expect($queryBuilder->toSql())
        ->toBe(
            'select `flights`.* from `flights` inner join `countries` as `departure` on `flights`.`departure_id` = `departure`.`id` inner join `countries` as `arrival` on `flights`.`arrival_id` = `arrival`.`id` where lower(`departure`.`name`) like ? and lower(`arrival`.`name`) like ?'
        )
        ->and($queryBuilder->get())
        ->toHaveCount(1)
        ->sequence(
            fn ($flight) => $flight->code->toBe('7485')
        );
});
