<?php

namespace TeamQ\Datatables\Scribe\Strategies;

use Illuminate\Support\Collection;
use Knuckles\Camel\Extraction\ExtractedEndpointData;
use Knuckles\Scribe\Extracting\ParamHelpers;
use Knuckles\Scribe\Extracting\Strategies\Strategy;
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionUnionType;
use Spatie\QueryBuilder\QueryBuilder as SpatieQueryBuilder;
use TeamQ\Datatables\QueryBuilder;
use TeamQ\Datatables\Scribe\Strategies\Resolvers\GetFromFields;
use TeamQ\Datatables\Scribe\Strategies\Resolvers\GetFromFilters;
use TeamQ\Datatables\Scribe\Strategies\Resolvers\GetFromIncludes;
use TeamQ\Datatables\Scribe\Strategies\Resolvers\GetFromQueryBuilder;
use TeamQ\Datatables\Scribe\Strategies\Resolvers\GetFromSorts;

/**
 * Adds the possibility of documenting the API from a class that extends `Spatie\QueryBuilder\QueryBuilder`
 * by automatically discovering its filters, sorting, includes and fields that are defined.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 * @link https://scribe.knuckles.wtf/laravel
 *
 * @author Luis Arce
 */
class GetFromSpatieQueryBuilder extends Strategy
{
    /**
     * Trait containing some helper methods for dealing with "parameters",
     * such as generating examples and casting values to types.
     * Useful if your strategy extracts information about parameters or generates examples.
     */
    use ParamHelpers;

    /**
     * Define QueryBuilder properties.
     *
     * @var array|string[]
     */
    private array $properties = [
        'allowedFilters' => GetFromFilters::class,
        'allowedSorts' => GetFromSorts::class,
        'allowedFields' => GetFromFields::class,
        'allowedIncludes' => GetFromIncludes::class,
    ];

    /**
     * @link https://scribe.knuckles.wtf/laravel/advanced/plugins
     *
     * @param  ExtractedEndpointData  $endpointData  The endpoint we are currently processing.
     *                                               Contains details about httpMethods, controller, method, route, url, etc, as well as already extracted data.
     * @param  array  $routeRules  Array of rules for the ruleset which this route belongs to.
     *
     * See the documentation linked above for more details about writing custom strategies.
     *
     * @throws ReflectionException
     */
    public function __invoke(ExtractedEndpointData $endpointData, array $settings = []): ?array
    {
        if (! $queryBuilderReflectionClass = $this->getQueryBuilderReflectionClass($endpointData->method)) {
            return [];
        }

        // Get the name of the QueryBuilder class associated to the Controller.
        $className = $queryBuilderReflectionClass->getName();

        // Create an instance of that query builder class.
        $queryBuilder = app($className);

        $documentation = new Collection;

        if ($queryBuilder instanceof QueryBuilder) {
            $documentation = $documentation->merge(app(GetFromQueryBuilder::class)->__invoke($queryBuilder));
        }

        foreach ($this->properties as $property => $resolver) {
            // Get properties used in the QueryBuilder.
            $properties = $queryBuilderReflectionClass
                ->getProperty($property)
                ->getValue($queryBuilder);

            if (! $properties instanceof Collection) {
                continue;
            }

            $documentation = $documentation->merge(app($resolver)->__invoke($properties, $queryBuilder));
        }

        return $documentation->toArray();
    }

    /**
     * Gets the reflection class from Spatie's Query Builder class.
     */
    protected function getQueryBuilderReflectionClass(ReflectionFunctionAbstract $method): ?ReflectionClass
    {
        foreach ($method->getParameters() as $argument) {
            $argType = $argument->getType();

            if ($argType === null || $argType instanceof ReflectionUnionType) {
                continue;
            }

            $argumentClassName = $argType->getName();

            try {
                $argumentClass = new ReflectionClass($argumentClassName);
            } catch (ReflectionException $e) {
                continue;
            }

            if (
                class_exists(SpatieQueryBuilder::class) &&
                $argumentClass->isSubclassOf(SpatieQueryBuilder::class)
            ) {
                return $argumentClass;
            }
        }

        return null;
    }
}
