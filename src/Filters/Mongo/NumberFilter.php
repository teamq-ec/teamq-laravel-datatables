<?php

namespace TeamQ\Datatables\Filters\Mongo;

use Illuminate\Database\Eloquent\Builder;
use TeamQ\Datatables\Enums\Comparators;

/**
 * MongoDB number filter with comparison operators.
 *
 * Maps directly to MongoDB-supported Eloquent operators without
 * SQL column qualification.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 *
 * @author Luis Arce
 */
class NumberFilter extends Filter
{
    /**
     * Operators that receive values as arrays or should be filtered as an array.
     * Ex: In, NotIn, Between, etc.
     */
    protected const ARRAY_OPERATORS = [
        Comparators\Number::Between,
        Comparators\Number::NotBetween,
        Comparators\Number::In,
        Comparators\Number::NotIn,
    ];

    /**
     * Get the mapped data from the payload.
     */
    protected function getPayloadData(mixed $payload): ?array
    {
        if (is_numeric($payload)) {
            return [
                $payload,
                Comparators\Number::Equal,
            ];
        }

        if (is_array($payload)) {
            $value = $payload['value'] ?? null;
            $operator = $payload['operator'] ?? null;

            return [
                $value,
                Comparators\Number::tryFrom($operator) ?: Comparators\Number::Equal,
            ];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function handle($query, $value, $property, $operator, $boolean = 'and'): void
    {
        match ($operator) {
            Comparators\Number::NotEqual => $query
                ->where($property, '!=', $value, $boolean),

            Comparators\Number::GreaterThan => $query
                ->where($property, '>', $value, $boolean),

            Comparators\Number::GreaterThanOrEqual => $query
                ->where($property, '>=', $value, $boolean),

            Comparators\Number::LessThan => $query
                ->where($property, '<', $value, $boolean),

            Comparators\Number::LessThanOrEqual => $query
                ->where($property, '<=', $value, $boolean),

            Comparators\Number::Between => $query
                ->whereBetween($property, $value, $boolean),

            // mongodb/laravel-mongodb compiles whereNotBetween with $lte/$gte
            // (inclusive of the bounds), which contradicts SQL's NOT BETWEEN
            // semantics. Express it explicitly with strict comparisons.
            Comparators\Number::NotBetween => $query
                ->where(function (Builder $query) use ($property, $value) {
                    $query
                        ->where($property, '<', $value[0])
                        ->orWhere($property, '>', $value[1]);
                }, null, null, $boolean),

            Comparators\Number::In => $query
                ->whereIn($property, $value, $boolean),

            Comparators\Number::NotIn => $query
                ->whereNotIn($property, $value, $boolean),

            Comparators\Number::Filled => $query
                ->whereNotNull($property, $boolean),

            Comparators\Number::NotFilled => $query
                ->whereNull($property, $boolean),

            default => $query
                ->where($property, '=', $value, $boolean),
        };
    }

    /**
     * {@inheritdoc}
     *
     * MongoDB does strict type matching, so numeric string payloads must
     * be cast to int/float before reaching the query builder.
     */
    protected function validate(mixed $value, mixed $operator): mixed
    {
        if (is_array($value)) {
            $value = array_filter($value, 'is_numeric');

            if (count($value) === 0) {
                return false;
            }

            return array_map(static fn ($item) => $item + 0, $value);
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        if (is_null($value)) {
            return $value;
        }

        return false;
    }
}
