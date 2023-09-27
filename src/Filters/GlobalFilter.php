<?php

namespace TeamQ\QueryBuilder\Filters;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Filters\Filter;
use TeamQ\QueryBuilder\Concerns\HasPropertyRelationship;
use TeamQ\QueryBuilder\Enums\JoinType;

/**
 * Adds global filter support through the `spatie/laravel-query-builder` package.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 *
 * @author Luis Arce
 */
class GlobalFilter implements Filter
{
    use HasPropertyRelationship;

    protected readonly array $fields;

    protected ?JoinType $joinType = null;

    /**
     * @param  array<string|Expression>  $fields
     */
    public function __construct(array $fields, bool $addRelationConstraint = true, JoinType $joinType = null)
    {
        $this->fields = $fields;
        $this->addRelationConstraint = $addRelationConstraint;
        $this->joinType = $joinType;
    }

    /**
     * Sets a shortcut to register the filter.
     */
    public static function allowed(
        array $fields,
        bool $addRelationConstraint = true,
        JoinType $joinType = null,
        string $name = 'global',
    ): AllowedFilter {
        return AllowedFilter::custom($name, new static($fields, $addRelationConstraint, $joinType));
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(Builder $query, $value, string $property): void
    {
        // Validate the payload value
        if (! $this->validate($value)) {
            return;
        }

        // Converts the payload value to lowercase to be filtered
        $value = mb_strtolower($value, 'UTF8');

        // Get valid fields.
        $fields = collect($this->fields)
            ->filter(fn ($field) => is_string($field) || $field instanceof Expression);

        // Apply join relationships for filters
        if (! $this->addRelationConstraint && $this->joinType) {
            $fields
                ->filter(fn ($field) => $this->isRelationProperty($query, $field))
                ->each(fn ($field) => $this->setJoinsRelationship($query, $field));
        }

        // Everything is wrapped inside the same `where` condition to isolate
        // the rest of the `where` conditions that the query may have.
        $query->where(function (Builder $query) use ($value, $fields) {
            if ($this->addRelationConstraint) {
                // Extracts the properties grouped by the relationships, e.g.:
                // "authors" => [ "name", "email" ]
                $relations = $fields
                    ->filter(fn ($field) => $this->isRelationProperty($query, $field))
                    ->flip()
                    ->undot();

                // Apply filter for relation property.
                $relations->each(
                    fn (array $properties, string $relation) => $this
                        ->setWhereRelations($query, $relation, $properties, $value)
                );

                // Apply filter for model properties.
                $properties = $fields->filter(fn ($field) => ! $this->isRelationProperty($query, $field));
                $this->setWhereProperties($query, $properties, $value);

                return;
            }

            if ($this->joinType) {
                // Apply filter with join for relation property.
                $fields
                    ->filter(fn ($field) => $this->isRelationProperty($query, $field))
                    ->each(fn (string $property) => $this->setWhereJoins($query, $value));
            }

            // Apply filter for model properties.
            $properties = $fields
                ->filter(fn ($field) => ! $this->isRelationProperty($query, $field));

            $this->setWhereProperties($query, $properties, $value);
        });
    }

    /**
     * Sets the where conditions for the relation properties by joins.
     */
    protected function setJoinsRelationship(Builder $query, string $property): void
    {
        [$relationName, $property] = $this->getConstraintParts($property);

        $relation = $query;

        foreach (explode('.', $relationName) as $partial) {
            $relation = $relation->getRelation($partial);
        }

        $query->joinRelationship($relationName, joinType: $this->joinType->value);

        $this->relationConstraints[] = $relation->qualifyColumn($property);
    }

    /**
     * Sets the where conditions for the relation properties by joins.
     */
    protected function setWhereJoins(Builder $query, mixed $value): void
    {
        foreach ($this->relationConstraints as $property) {
            [$sql, $bindings] = $this->getWhereRawParameters($query->getGrammar()->wrap($property), $value);

            $query->orWhereRaw($sql, $bindings);
        }
    }

    /**
     * Sets the where conditions for the relation properties.
     */
    protected function setWhereRelations(Builder $query, string $relation, array $properties, mixed $value): void
    {
        $query->orWhereHas($relation, function (Builder $query) use ($properties, $value) {
            $query->where(function (Builder $query) use ($properties, $value) {
                collect($properties)
                    // Put arrays at the end.
                    ->sortBy(fn ($property) => is_array($property))
                    ->each(function (int|array $properties, string $name) use ($query, $value) {
                        if (is_array($properties)) {
                            $this->setWhereRelations($query, $name, $properties, $value);

                            return;
                        }

                        $property = $query->getGrammar()->wrap($query->qualifyColumn($name));

                        [$sql, $bindings] = $this->getWhereRawParameters($property, $value);

                        $query->orWhereRaw($sql, $bindings);
                    });
            });
        });
    }

    /**
     * Sets the where conditions for the model properties.
     */
    protected function setWhereProperties(Builder $query, Collection $properties, mixed $value): void
    {
        $properties->each(function ($property) use ($query, $value) {
            $property = $query->getGrammar()->isExpression($property)
                ? $property
                : $query->qualifyColumn($property);

            $property = $query->getGrammar()->wrap($property);

            [$sql, $bindings] = $this->getWhereRawParameters($property, $value);

            $query->orWhereRaw($sql, $bindings);
        });
    }

    /**
     * Gets the parameters, property and value for the where condition in raw format.
     */
    protected function getWhereRawParameters(string $property, mixed $value): array
    {
        return [
            "LOWER({$property}) LIKE ?",
            ["%{$value}%"],
        ];
    }

    /**
     * Validates the data in the payload.
     *
     * @return mixed false if the data is invalid
     */
    protected function validate($value): bool
    {
        return is_string($value);
    }
}
