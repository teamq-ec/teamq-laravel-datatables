<?php

namespace TeamQ\QueryBuilder\Sorts;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Sorts\Sort;
use TeamQ\QueryBuilder\Concerns\HasPropertyRelationship;
use TeamQ\QueryBuilder\Enums\AggregationType;
use TeamQ\QueryBuilder\Enums\JoinType;

/**
 * Adds the possibility to sort by related fields using the kirschbaum-development/eloquent-power-joins.
 *
 * @link https://github.com/kirschbaum-development/eloquent-power-joins
 *
 * @author Luis Arce
 */
class RelationSort implements Sort
{
    use HasPropertyRelationship;

    public function __construct(
        private readonly JoinType $joinType = JoinType::Inner,
        private readonly ?AggregationType $aggregationType = null,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        if (! $this->isRelationProperty($query, $property)) {
            return;
        }

        $query
            ->orderByPowerJoins(
                $property,
                $descending ? 'desc' : 'asc',
                $this->aggregationType?->value,
                $this->joinType->value
            );
    }
}
