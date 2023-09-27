<?php

namespace TeamQ\QueryBuilder;

use Spatie\QueryBuilder\QueryBuilder as BaseQueryBuilder;
use TeamQ\QueryBuilder\Concerns\PerPageQuery;

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
