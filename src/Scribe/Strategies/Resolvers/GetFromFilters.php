<?php

namespace TeamQ\Datatables\Scribe\Strategies\Resolvers;

use BackedEnum;
use Error;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Filters\Filter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\Sorts\Sort;
use TeamQ\Datatables\Scribe\Strategies\Documentations\Documentation;

/**
 * Adds discovery resolver for the "filter" parameter of the query builder.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 * @link https://scribe.knuckles.wtf/laravel
 *
 * @author Luis Arce
 */
class GetFromFilters extends GetFromBase
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(Collection $properties, QueryBuilder $queryBuilder): Collection
    {
        $parameterName = $this->getParameterName();

        $model = $this->getFactoryModelFromQueryBuilder($queryBuilder);

        return $properties
            ->reduce(function (Collection $queryParams, AllowedFilter $allowedFilter) use ($parameterName, $model) {
                $name = sprintf('%s[%s]', $parameterName, $allowedFilter->getName());

                $allowedFilterReflectionClass = new ReflectionClass($allowedFilter);

                $filterClass = $allowedFilterReflectionClass
                    ->getProperty('filterClass')
                    ->getValue($allowedFilter);

                $value = $this->getExampleValue($model, $allowedFilter->getInternalName());

                return $queryParams->put(
                    $name,
                    array_merge([
                        'type' => 'string',
                        'required' => false,
                    ], $this->getDocumentation($filterClass, $value, $name), [
                        'name' => $name,
                    ])
                );
            }, new Collection);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ReflectionException
     */
    protected function getDocumentation(Filter|Sort|Collection|string $param, mixed $value = null, ?string $name = null): array
    {
        $documentations = config('datatables.documentation');

        $documentation = $documentations[is_string($param) ? $param : $param::class] ?? Documentation::class;

        $documentation = app($documentation);

        return $documentation($param, $value, $name);
    }

    /**
     * {@inheritDoc}
     */
    protected function getParameterName(): string
    {
        return config('query-builder.parameters.filter');
    }

    /**
     * Gets the data factory related to the model that is associated to the query builder class.
     */
    protected function getFactoryModelFromQueryBuilder(QueryBuilder $queryBuilder): Model
    {
        return $queryBuilder->getSubject()->getModel();
    }

    /**
     * It allows to generate a value according to the model being queried.
     */
    protected function getExampleValue(Model $model, string $property): mixed
    {
        try {
            $value = $model->getAttribute($property);

            if ($value instanceof BackedEnum) {
                $value = $value->value;
            }

            if (is_null($value) && isset($model->getCasts()[$property])) {
                $type = $model->getCasts()[$property];

                $value = match ($type) {
                    'int' => fake()->randomDigit(),
                    'string' => fake()->words(2),
                    'datetime' => fake()->dateTimeBetween('-5 years'),
                };
            }
        } catch (Exception|Error) {
            $value = null;
        }

        return $value;
    }
}
