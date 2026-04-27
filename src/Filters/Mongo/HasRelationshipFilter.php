<?php

namespace TeamQ\Datatables\Filters\Mongo;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * MongoDB relationship existence filter.
 *
 * `mongodb/laravel-mongodb` natively supports `whereHas` / `whereDoesntHave`,
 * so the implementation is identical in shape to the SQL counterpart and
 * lives in the Mongo namespace only for API consistency.
 *
 * @author Luis Arce
 */
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
