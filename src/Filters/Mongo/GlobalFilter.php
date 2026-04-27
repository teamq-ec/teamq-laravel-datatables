<?php

namespace TeamQ\Datatables\Filters\Mongo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use MongoDB\BSON\Regex;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Filters\Filter;
use TeamQ\Datatables\Concerns\HasPropertyRelationship;

/**
 * MongoDB global filter for the `spatie/laravel-query-builder` package.
 *
 * Searches a single value across multiple model properties and relations
 * using case-insensitive BSON regex.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 *
 * @author Luis Arce
 */
class GlobalFilter implements Filter
{
    use HasPropertyRelationship;

    protected readonly array $fields;

    /**
     * @param  array<string>  $fields
     */
    public function __construct(array $fields, bool $addRelationConstraint = true)
    {
        $this->fields = $fields;
        $this->addRelationConstraint = $addRelationConstraint;
    }

    /**
     * Sets a shortcut to register the filter.
     */
    public static function allowed(
        array $fields,
        bool $addRelationConstraint = true,
        string $name = 'global',
    ): AllowedFilter {
        return AllowedFilter::custom($name, new static($fields, $addRelationConstraint));
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(Builder $query, $value, string $property): void
    {
        if (! $this->validate($value)) {
            return;
        }

        $regex = new Regex(preg_quote($value, '/'), 'i');

        $fields = collect($this->fields)
            ->filter(fn ($field) => is_string($field));

        // Everything is wrapped inside the same `where` condition to isolate
        // the rest of the `where` conditions that the query may have.
        $query->where(function (Builder $query) use ($regex, $fields) {
            // Extracts the properties grouped by the relationships, e.g.:
            // "authors" => [ "name", "email" ]
            $relations = $fields
                ->filter(fn ($field) => $this->isRelationProperty($query, $field))
                ->flip()
                ->undot();

            // Apply filter for relation properties.
            $relations->each(
                fn (array $properties, string $relation) => $this
                    ->setWhereRelations($query, $relation, $properties, $regex)
            );

            // Apply filter for model properties.
            $properties = $fields->filter(fn ($field) => ! $this->isRelationProperty($query, $field));
            $this->setWhereProperties($query, $properties, $regex);
        });
    }

    /**
     * Sets the where conditions for the relation properties.
     */
    protected function setWhereRelations(Builder $query, string $relation, array $properties, Regex $regex): void
    {
        $query->orWhereHas($relation, function (Builder $query) use ($properties, $regex) {
            $query->where(function (Builder $query) use ($properties, $regex) {
                collect($properties)
                    // Put arrays at the end.
                    ->sortBy(fn ($property) => is_array($property))
                    ->each(function (int|array $properties, string $name) use ($query, $regex) {
                        if (is_array($properties)) {
                            $this->setWhereRelations($query, $name, $properties, $regex);

                            return;
                        }

                        $query->orWhere($name, '=', $regex);
                    });
            });
        });
    }

    /**
     * Sets the where conditions for the model properties.
     */
    protected function setWhereProperties(Builder $query, Collection $properties, Regex $regex): void
    {
        $properties->each(function ($property) use ($query, $regex) {
            $query->orWhere($property, '=', $regex);
        });
    }

    /**
     * Validates the data in the payload.
     */
    protected function validate($value): bool
    {
        return is_string($value);
    }
}
