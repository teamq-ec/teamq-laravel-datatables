<?php

namespace TeamQ\QueryBuilder\Filters;

use TeamQ\QueryBuilder\Enums\Comparators;

/**
 * Adds custom filter support for text with comparison operators.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 *
 * @author Luis Arce
 */
class TextFilter extends Filter
{
    protected const ARRAY_OPERATORS = [
        Comparators\Text::In,
        Comparators\Text::NotIn,
    ];

    /**
     * Get the mapped data from the payload.
     */
    protected function getPayloadData(mixed $payload): ?array
    {
        if (is_string($payload)) {
            return [
                $payload,
                Comparators\Text::Contains,
            ];
        }

        if (is_array($payload)) {
            $value = $payload['value'] ?? null;
            $operator = $payload['operator'] ?? null;

            return [
                $value,
                Comparators\Text::tryFrom($operator) ?: Comparators\Text::Contains,
            ];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function handle($query, $value, $property, $operator, $boolean = 'and'): void
    {
        $field = $query->getQuery()->getGrammar()->wrap($property);

        match ($operator) {
            Comparators\Text::Equal => $query
                ->whereRaw("lower({$field}) like ?", [$value], $boolean),

            Comparators\Text::NotEqual => $query
                ->whereRaw("lower({$field}) not like ?", [$value], $boolean),

            Comparators\Text::StartWith => $query
                ->whereRaw("lower({$field}) like ?", ["$value%"], $boolean),

            Comparators\Text::NotStartWith => $query
                ->whereRaw("lower({$field}) not like ?", ["$value%"], $boolean),

            Comparators\Text::EndWith => $query
                ->whereRaw("lower({$field}) like ?", ["%$value"], $boolean),

            Comparators\Text::NotEndWith => $query
                ->whereRaw("lower({$field}) not like ?", ["%$value"], $boolean),

            Comparators\Text::NotContains => $query
                ->whereRaw("lower({$field}) not like ?", ["%$value%"], $boolean),

            Comparators\Text::In => $query
                ->whereIn($property, $value, $boolean),

            Comparators\Text::NotIn => $query
                ->whereNotIn($property, $value, $boolean),

            Comparators\Text::Filled => $query
                ->whereNotNull($property, $boolean),

            Comparators\Text::NotFilled => $query
                ->whereNull($property, $boolean),

            default => $query
                ->whereRaw("lower({$field}) like ?", ["%$value%"], $boolean),
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function validate(mixed $value, mixed $operator): mixed
    {
        if (is_array($value)) {
            $value = array_filter($value, 'strlen');

            return count($value) > 0 ? $value : false;
        }

        if (is_string($value) || is_null($value)) {
            return is_string($value)
                ? mb_strtolower($value, 'UTF8')
                : $value;
        }

        return false;
    }
}
