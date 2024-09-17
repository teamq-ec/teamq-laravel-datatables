<?php

namespace TeamQ\Datatables\Scribe\Strategies\Documentations\Filters;

use Illuminate\Support\Collection;
use Spatie\QueryBuilder\Filters\Filter;
use Spatie\QueryBuilder\Sorts\Sort;
use TeamQ\Datatables\Scribe\Strategies\Documentations\Documentation;

class EndsWithStrictFilter extends Documentation
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(Filter|Sort|Collection|string $param, mixed $value = null, ?string $name = null): array
    {
        return [
            'name' => $name,
            'type' => 'string',
            'required' => false,
            'description' => 'End Partial Filter. It is evaluated as: The field ends with this value.',
            // 'example' => str($value)->substr(0, 3) ?: '@gmail.com',
        ];
    }
}
