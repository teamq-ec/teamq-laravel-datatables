<?php

use Spatie\QueryBuilder;
use TeamQ\Datatables\Scribe\Strategies\Documentations;
use TeamQ\Datatables;

return [
    'parameters' => [
        'per_page' => 'per_page',
    ],

    'per_page' => [
        // Represents the value to be sent by the user, to obtain all records, if using the result method.
        'all' => 'all',
    ],

    'documentation' => [
        QueryBuilder\Filters\FiltersExact::class => Documentations\Filters\ExactFilter::class,
        QueryBuilder\Filters\FiltersPartial::class => Documentations\Filters\PartialFilter::class,
        QueryBuilder\Filters\FiltersBeginsWithStrict::class => Documentations\Filters\BeginsWithStrictFilter::class,
        QueryBuilder\Filters\FiltersEndsWithStrict::class => Documentations\Filters\EndsWithStrictFilter::class,
        QueryBuilder\Filters\FiltersTrashed::class => Documentations\Filters\TrashedFilter::class,
        Datatables\Filters\GlobalFilter::class => Documentations\Filters\GlobalFilter::class,
        Datatables\Filters\DateFilter::class => Documentations\Filters\DateFilter::class,
        Datatables\Filters\NumberFilter::class => Documentations\Filters\NumberFilter::class,
        Datatables\Filters\TextFilter::class => Documentations\Filters\TextFilter::class,
    ],
];
