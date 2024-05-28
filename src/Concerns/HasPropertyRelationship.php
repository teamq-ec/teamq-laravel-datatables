<?php

namespace TeamQ\Datatables\Concerns;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Adds validations to determine if the given property is within a relation.
 *
 * @author Luis Arce
 */
trait HasPropertyRelationship
{
    protected array $relationConstraints = [];

    protected bool $addRelationConstraint = true;

    /**
     * Verify if it is a property of a relation.
     */
    protected function isRelationProperty(Builder $query, string|Expression $property): bool
    {
        if (! is_string($property) || ! Str::contains($property, '.')) {
            return false;
        }

        if (in_array($property, $this->relationConstraints, true)) {
            return false;
        }

        $firstRelationship = explode('.', $property)[0];

        if (! method_exists($query->getModel(), $firstRelationship)) {
            return false;
        }

        return is_a($query->getModel()->{$firstRelationship}(), Relation::class);
    }

    /**
     * Get the constraint parts [$relation, $property] of the given property.
     */
    protected function getConstraintParts(string $property): array
    {
        return collect(explode('.', $property))
            ->pipe(function (Collection $parts) {
                return [
                    $parts->except(count($parts) - 1)->implode('.'),
                    $parts->last(),
                ];
            });
    }
}
