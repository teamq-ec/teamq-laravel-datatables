<?php

namespace TeamQ\QueryBuilder\Sorts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Spatie\QueryBuilder\Sorts\Sort;

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
    /**
     * @param  array<string|int, string|int>  $cases
     */
    public function __construct(protected array $cases)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        $column = $query->qualifyColumn($property);

        $sql = 'case ';
        foreach ($this->cases as $key => $value) {
            $sql .= "when {$column} = {$key} then '{$value}' ";
        }
        $sql .= 'end ';

        $query->orderBy(new Expression($sql), $descending ? 'desc' : 'asc');
    }
}
