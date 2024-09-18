<?php

namespace TeamQ\Datatables\Scribe\Strategies\Resolvers;

use Illuminate\Support\Collection;
use TeamQ\Datatables\QueryBuilder;

class GetFromQueryBuilder
{
    /**
     * Get documentation for teamq/laravel-spatie-filters.
     */
    public function __invoke(QueryBuilder $queryBuilder): Collection
    {
        return new Collection([
            config('query-builder.parameters.per_page') => [
                'name' => config('query-builder.parameters.per_page'),
                'type' => 'string',
                'required' => false,
                'description' => 'Allows you to limit the amount of results per page. The accepted values are:
                    <ul>
                        <li><b>integer</b> Must be a number greater than 0</li>
                        <li><b>all</b> Allows you to bring all the records.</li>
                    </ul>',
                'example' => 15,
            ],
        ]);
    }
}
