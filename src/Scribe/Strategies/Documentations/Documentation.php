<?php

namespace TeamQ\Datatables\Scribe\Strategies\Documentations;

use Illuminate\Support\Collection;
use Knuckles\Scribe\Attributes\QueryParam;
use ReflectionClass;
use ReflectionException;
use Spatie\QueryBuilder\Filters\Filter;
use Spatie\QueryBuilder\Sorts\Sort;

class Documentation
{
    /**
     * Gets the documentation for the query params of the Spatie QueryBuilder package.
     *
     *
     * @return array|string[]
     *
     * @throws ReflectionException
     */
    public function __invoke(Filter|Sort|Collection|string $param, mixed $value = null, ?string $name = null): array
    {
        $reflectionClass = new ReflectionClass($param);

        $documentation = $reflectionClass->getAttributes(QueryParam::class);

        if (! empty($documentation)) {
            $documentation = $documentation[0]->newInstance();

            return $documentation->toArray();
        }

        if (method_exists($param, 'docs')) {
            return $param::docs()->toArray();
        }

        return [];
    }
}
