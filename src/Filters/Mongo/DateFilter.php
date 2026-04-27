<?php

namespace TeamQ\Datatables\Filters\Mongo;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use TeamQ\Datatables\Enums\Comparators;

/**
 * MongoDB date filter with comparison operators.
 *
 * Replaces the SQL `whereDate(...)` semantics with day-boundary ranges
 * (start of day / end of day) since MongoDB does not provide a native
 * date-truncation operator outside the aggregation pipeline.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 *
 * @author Luis Arce
 */
class DateFilter extends Filter
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
        if (is_string($payload) && strtotime($payload) !== false) {
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
            Comparators\Number::Equal => $query
                ->whereBetween($property, $this->dayRange($value), $boolean),

            // mongodb/laravel-mongodb compiles whereNotBetween with $lte/$gte
            // (inclusive of the bounds), which differs from SQL's NOT BETWEEN.
            // Express it explicitly with strict comparisons.
            Comparators\Number::NotEqual => $query
                ->where(function (Builder $query) use ($property, $value) {
                    [$start, $end] = $this->dayRange($value);
                    $query
                        ->where($property, '<', $start)
                        ->orWhere($property, '>', $end);
                }, null, null, $boolean),

            Comparators\Number::GreaterThan => $query
                ->where($property, '>', CarbonImmutable::parse($value)->endOfDay(), $boolean),

            Comparators\Number::GreaterThanOrEqual => $query
                ->where($property, '>=', CarbonImmutable::parse($value)->startOfDay(), $boolean),

            Comparators\Number::LessThan => $query
                ->where($property, '<', CarbonImmutable::parse($value)->startOfDay(), $boolean),

            Comparators\Number::LessThanOrEqual => $query
                ->where($property, '<=', CarbonImmutable::parse($value)->endOfDay(), $boolean),

            Comparators\Number::Between => $query
                ->whereBetween(
                    $property,
                    [
                        CarbonImmutable::parse($value[0])->startOfDay(),
                        CarbonImmutable::parse($value[1])->endOfDay(),
                    ],
                    $boolean
                ),

            Comparators\Number::NotBetween => $query
                ->where(function (Builder $query) use ($property, $value) {
                    $query
                        ->where($property, '<', CarbonImmutable::parse($value[0])->startOfDay())
                        ->orWhere($property, '>', CarbonImmutable::parse($value[1])->endOfDay());
                }, null, null, $boolean),

            Comparators\Number::In => $query
                ->where(function (Builder $query) use ($property, $value) {
                    foreach ($value as $date) {
                        $query->orWhereBetween($property, $this->dayRange($date));
                    }
                }, null, null, $boolean),

            Comparators\Number::NotIn => $query
                ->where(function (Builder $query) use ($property, $value) {
                    foreach ($value as $date) {
                        $query->whereNotBetween($property, $this->dayRange($date));
                    }
                }, null, null, $boolean),

            Comparators\Number::Filled => $query
                ->whereNotNull($property, $boolean),

            Comparators\Number::NotFilled => $query
                ->whereNull($property, $boolean),

            default => $query
                ->whereBetween($property, $this->dayRange($value), $boolean),
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function validate(mixed $value, mixed $operator): mixed
    {
        if (is_array($value)) {
            $value = array_filter($value, 'strtotime');

            return count($value) > 0 ? $value : false;
        }

        if (is_null($value) || strtotime($value) !== false) {
            return $value;
        }

        return false;
    }

    /**
     * Compute the [startOfDay, endOfDay] range for a date string.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function dayRange(string $value): array
    {
        $date = CarbonImmutable::parse($value);

        return [$date->startOfDay(), $date->endOfDay()];
    }
}
