<?php

namespace TeamQ\Datatables\Scribe\Strategies\Documentations\Filters;

use Illuminate\Support\Collection;
use Spatie\QueryBuilder\Filters\Filter;
use Spatie\QueryBuilder\Sorts\Sort;
use TeamQ\Datatables\Scribe\Strategies\Documentations\Documentation;

class TrashedFilter extends Documentation
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
            'description' => 'Trashed Filter. For soft deleted records. The accepted values are:
                    <ul>
                        <li><b>with:</b> With deleted records.</li>
                        <li><b>only:</b> Only deleted records.</li>
                        <li><b>without:</b> Without deleted records.</li>
                    </ul>',
            'example' => 'only',
        ];
    }
}
