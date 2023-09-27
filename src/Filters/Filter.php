<?php

namespace TeamQ\QueryBuilder\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter as IFilter;
use TeamQ\QueryBuilder\Concerns\HasPropertyRelationship;
use TeamQ\QueryBuilder\Enums\JoinType;

/**
 * Filter Base Class.
 *
 * @author Luis Arce
 */
abstract class Filter implements IFilter
{
    use HasPropertyRelationship;

    protected ?JoinType $joinType = null;

    protected const ARRAY_OPERATORS = [];

    /**
     * Get the mapped data from the payload.
     */
    abstract protected function getPayloadData(mixed $payload): ?array;

    /**
     * It is in charge of applying the query according to the given parameters.
     */
    abstract protected function handle(
        Builder $query,
        mixed $value,
        string $property,
        mixed $operator,
        string $boolean = 'and'
    ): void;

    /**
     * Filter constructor.
     */
    public function __construct(bool $addRelationConstraint = true, JoinType $joinType = null)
    {
        $this->addRelationConstraint = $addRelationConstraint;
        $this->joinType = $joinType;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(Builder $query, $value, string $property): void
    {
        if ($this->addRelationConstraint && $this->isRelationProperty($query, $property)) {
            $this->withRelationConstraint($query, $value, $property);

            return;
        }

        if (! $this->addRelationConstraint && $this->joinType && $this->isRelationProperty($query, $property)) {
            $this->withJoinConstraint($query, $value, $property);

            return;
        }

        [$value, $operator] = $this->getPayloadData($value);

        $property = $query->qualifyColumn($property);

        if (isset($value) || isset($operator)) {
            $value = $this->validate($value, $operator);

            if ($value !== false) {
                if (is_array($value)) {
                    if (in_array($operator, static::ARRAY_OPERATORS, true)) {
                        $this->handle($query, $value, $property, $operator);
                    } else {
                        $query->where(function (Builder $query) use ($value, $property, $operator) {
                            foreach ($value as $item) {
                                $this->handle($query, $item, $property, $operator, 'or');
                            }
                        });
                    }

                    return;
                }

                $this->handle($query, $value, $property, $operator);
            }
        }
    }

    /**
     * Applies the filter within an Eloquent relationship.
     */
    protected function withRelationConstraint(Builder $query, $value, string $property): void
    {
        [$relationName, $property] = $this->getConstraintParts($property);

        $query->whereHas($relationName, function (Builder $query) use ($value, $property) {
            $this->relationConstraints[] = $property = $query->qualifyColumn($property);

            $this->__invoke($query, $value, $property);
        });
    }

    /**
     * Applies the filter within an PowerJoin relationship.
     */
    protected function withJoinConstraint(Builder $query, $value, string $property): void
    {
        [$relationName, $property] = $this->getConstraintParts($property);

        $relation = $query;

        foreach (explode('.', $relationName) as $partial) {
            $relation = $relation->getRelation($partial);
        }

        $query->joinRelationship($relationName, joinType: $this->joinType->value);

        $this->relationConstraints[] = $property = $relation->qualifyColumn($property);

        $this->__invoke($query, $value, $property);
    }

    /**
     * Validates the data in the payload and returns the data.
     *
     * @return mixed false if the data is invalid
     */
    protected function validate(mixed $value, mixed $operator): mixed
    {
        return $value;
    }
}
