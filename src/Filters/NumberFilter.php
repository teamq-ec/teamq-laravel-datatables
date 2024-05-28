<?php

namespace TeamQ\Datatables\Filters;

use TeamQ\Datatables\Enums\Comparators;

/**
 * Adds custom filter support for numbers with comparison operators.
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

            Comparators\Number::NotBetween => $query
                ->whereNotBetween($property, $value, $boolean),

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
     */
    protected function validate(mixed $value, mixed $operator): mixed
    {
        if (is_array($value)) {
            $value = array_filter($value, 'is_numeric');

            return count($value) > 0 ? $value : false;
        }

        if (is_numeric($value) || is_null($value)) {
            return $value;
        }

        return false;
    }
}
