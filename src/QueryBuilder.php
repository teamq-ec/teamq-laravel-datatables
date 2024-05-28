<?php

namespace TeamQ\Datatables;

use Spatie\QueryBuilder\QueryBuilder as BaseQueryBuilder;
use TeamQ\Datatables\Concerns\PerPageQuery;

/**
 * Overwrite the query builder base class and add support for the perPage parameter
 *
 * @link https://spatie.be/docs/laravel-query-builder
 *
 * @author Luis Arce
 */
class QueryBuilder extends BaseQueryBuilder
{
    use PerPageQuery;
}
