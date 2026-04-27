<?php

namespace TeamQ\Datatables\Filters\Mongo;

use Illuminate\Database\Eloquent\Builder;
use MongoDB\BSON\Regex;
use TeamQ\Datatables\Enums\Comparators;

/**
 * MongoDB text filter with comparison operators.
 *
 * Case-insensitive matching is delegated to BSON regex with the `i` flag,
 * mirroring the SQL filter's `LOWER(...) LIKE ?` semantics without
 * relying on raw SQL.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 *
 * @author Luis Arce
 */
class TextFilter extends Filter
{
    /**
     * Operators that receive values as arrays or should be filtered as an array.
     * Ex: In, NotIn, Between, etc.
     */
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
        $quoted = is_string($value) ? preg_quote($value, '/') : null;

        match ($operator) {
            Comparators\Text::Equal => $query
                ->where($property, 'regex', new Regex('^'.$quoted.'$', 'i'), $boolean),

            Comparators\Text::NotEqual => $query
                ->where($property, 'not regex', new Regex('^'.$quoted.'$', 'i'), $boolean),

            Comparators\Text::StartWith => $query
                ->where($property, 'regex', new Regex('^'.$quoted, 'i'), $boolean),

            Comparators\Text::NotStartWith => $query
                ->where($property, 'not regex', new Regex('^'.$quoted, 'i'), $boolean),

            Comparators\Text::EndWith => $query
                ->where($property, 'regex', new Regex($quoted.'$', 'i'), $boolean),

            Comparators\Text::NotEndWith => $query
                ->where($property, 'not regex', new Regex($quoted.'$', 'i'), $boolean),

            Comparators\Text::NotContains => $query
                ->where($property, 'not regex', new Regex($quoted, 'i'), $boolean),

            Comparators\Text::In => $query
                ->where(function (Builder $query) use ($property, $value) {
                    foreach ($value as $item) {
                        $query->orWhere(
                            $property,
                            'regex',
                            new Regex('^'.preg_quote($item, '/').'$', 'i')
                        );
                    }
                }, null, null, $boolean),

            Comparators\Text::NotIn => $query
                ->where(function (Builder $query) use ($property, $value) {
                    foreach ($value as $item) {
                        $query->where(
                            $property,
                            'not regex',
                            new Regex('^'.preg_quote($item, '/').'$', 'i')
                        );
                    }
                }, null, null, $boolean),

            Comparators\Text::Filled => $query
                ->whereNotNull($property, $boolean),

            Comparators\Text::NotFilled => $query
                ->whereNull($property, $boolean),

            default => $query
                ->where($property, 'regex', new Regex($quoted, 'i'), $boolean),
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
            return $value;
        }

        return false;
    }
}
