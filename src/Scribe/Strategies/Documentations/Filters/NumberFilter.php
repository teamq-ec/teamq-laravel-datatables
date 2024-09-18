<?php

namespace TeamQ\Datatables\Scribe\Strategies\Documentations\Filters;

use Illuminate\Support\Collection;
use Spatie\QueryBuilder\Filters\Filter;
use Spatie\QueryBuilder\Sorts\Sort;
use TeamQ\Datatables\Enums\Comparators\Number;
use TeamQ\Datatables\Scribe\Strategies\Documentations\Documentation;

class NumberFilter extends Documentation
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(Filter|Sort|Collection|string $param, mixed $value = null, ?string $name = null): array
    {
        $operators = collect(Number::cases())
            ->map(fn (Number $operator) => "<li>{$operator->value} - {$operator->name}</li>")
            ->join('');

        return [
            'name' => $name,
            'type' => 'string',
            'required' => false,
            'description' => "<div>Number Filter. It belongs to a type of advanced filter, it allows filtering according to operators. Can receive different payload:
                    <ul>
                        <li><b>{$name}=20</b><br>It is evaluated by equals.</li>
                        <li><b>{$name}[value]=20</b><br>It is evaluated by equals.</li>
                        <li><b>{$name}[value]=20&{$name}[operator]=5</b><br>It is evaluated by operator 5 (LessThan).</li>
                        <li><b>{$name}[value][0]=20&{$name}[value][1]=20&{$name}[operator]=5</b><br>It is evaluated by operator 7 (Between). This operator will only take the first two 'values' however operators like 'In' will take the entire array of 'values'.</li>
                    </ul>
                    Valid comparison operators are:
                    <ul>
                    $operators
                    </ul>
            </div>",
        ];
    }
}
