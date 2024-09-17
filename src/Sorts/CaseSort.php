<?php

namespace TeamQ\Datatables\Sorts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Spatie\QueryBuilder\Sorts\Sort;
use TeamQ\Datatables\Concerns\HasPropertyRelationship;
use TeamQ\Datatables\Enums\AggregationType;
use TeamQ\Datatables\Enums\JoinType;

/**
 * Add support for sorting using select case, for cases such as Enums, States, Constants,
 * as long as a key => value array is provided.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 *
 * @author Luis Arce
 */
class CaseSort implements Sort
{
    use HasPropertyRelationship;

    public function __construct(
        protected array $cases,
        private readonly JoinType $joinType = JoinType::Inner,
        private readonly ?AggregationType $aggregationType = null,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        if ($this->isRelationProperty($query, $property)) {
            [$relationName, $column] = $this->getConstraintParts($property);

            $relation = $query;

            foreach (explode('.', $relationName) as $partial) {
                $relation = $relation->getRelation($partial);
            }

            $expression = $this->getQueryExpression($relation->qualifyColumn($column));

            $query
                ->orderByPowerJoins(
                    [$relationName, new Expression($expression)],
                    $descending ? 'desc' : 'asc',
                    $this->aggregationType?->value,
                    $this->joinType->value
                );

            return;
        }

        $expression = $this->getQueryExpression($query->qualifyColumn($property));

        $query->orderBy(new Expression($expression), $descending ? 'desc' : 'asc');
    }

    /**
     * Generates the SQL expression that will be used to sort.
     */
    protected function getQueryExpression(string $column): string
    {
        $sql = 'case ';
        foreach ($this->cases as $key => $value) {
            $sql .= "when {$column} = {$key} then '{$value}' ";
        }
        $sql .= 'end ';

        return $sql;
    }
}
