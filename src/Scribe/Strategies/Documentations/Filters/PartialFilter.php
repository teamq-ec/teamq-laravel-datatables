<?php

namespace TeamQ\Datatables\Scribe\Strategies\Documentations\Filters;

use Illuminate\Support\Collection;
use Spatie\QueryBuilder\Filters\Filter;
use Spatie\QueryBuilder\Sorts\Sort;
use TeamQ\Datatables\Scribe\Strategies\Documentations\Documentation;

class PartialFilter extends Documentation
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
            'description' => 'Partial Filter. It is evaluated as: The field contains this value.',
            // 'example' => str($value)->substr(2, 5) ?: str()->words(2)->lower(),
        ];
    }
}
