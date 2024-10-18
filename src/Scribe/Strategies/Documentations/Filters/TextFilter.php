<?php

namespace TeamQ\Datatables\Scribe\Strategies\Documentations\Filters;

use Illuminate\Support\Collection;
use Spatie\QueryBuilder\Filters\Filter;
use Spatie\QueryBuilder\Sorts\Sort;
use TeamQ\Datatables\Enums\Comparators\Text;
use TeamQ\Datatables\Scribe\Strategies\Documentations\Documentation;

class TextFilter extends Documentation
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(Filter|Sort|Collection|string $param, mixed $value = null, ?string $name = null): array
    {
        $operators = collect(Text::cases())
            ->map(fn (Text $operator) => "<li>{$operator->value} - {$operator->name}</li>")
            ->join('');

        return [
            'name' => $name,
            'type' => 'string',
            'required' => false,
            'description' => "<div>Text Filter. It belongs to a type of advanced filter, it allows filtering according to operators. Can receive different payload:
                    <ul>
                        <li><b>{$name}=Luis</b> It is evaluated by equals.</li>
                        <li><b>{$name}[value]=Luis</b> It is evaluated by equals.</li>
                        <li><b>{$name}[value]=Luis&{$name}[operator]=\$endWith</b> It is evaluated by operator EndWith.</li>
                    </ul>
                    Valid comparison operators are:
                    <ul>
                    $operators
                    </ul>
            </div>",
        ];
    }
}
