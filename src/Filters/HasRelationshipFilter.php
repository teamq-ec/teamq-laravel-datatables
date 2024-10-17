<?php

namespace TeamQ\Datatables\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class HasRelationshipFilter implements Filter
{
    /**
     * Filters by existing relationships with the related model.
     */
    public function __invoke(Builder $query, $value, string $property): void
    {
        if (! is_numeric($value)) {
            return;
        }

        if ((int) $value === 1) {
            $query->whereHas($property);
        }

        if ((int) $value === 0) {
            $query->whereDoesntHave($property);
        }
    }
}
