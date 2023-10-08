# Laravel Spatie Filters

[![Latest Version on Packagist](https://img.shields.io/packagist/v/teamq-ec/teamq-laravel-spatie-filters.svg?style=flat-square)](https://packagist.org/packages/teamq-ec/teamq-laravel-spatie-filters)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/teamq-ec/teamq-laravel-spatie-filters/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/teamq-ec/teamq-laravel-spatie-filters/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/teamq-ec/teamq-laravel-spatie-filters/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/teamq-ec/teamq-laravel-spatie-filters/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/teamq-ec/teamq-laravel-spatie-filters.svg?style=flat-square)](https://packagist.org/packages/teamq-ec/teamq-laravel-spatie-filters)

This is a collection of classes for filters and sorts, extending from the `spatie/laravel-query-builder` package,
in addition to providing the possibility of applying these filters and sorting in related models using join through
from the `kirschbaum-development/eloquent-power-joins` package.

## Installation

You can install the package via composer:

```bash
composer require teamq/laravel-spatie-filters
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-spatie-filters-config"
```

This is the contents of the published config file:

```php
return [
    'per_page' => [
        // Represents the value to be sent by the user, to obtain all records, if using the result method.
        'all' => 'all',
    ],
];
```

## Usage

### Filters

You can use advanced filters that have support for multiple comparison operators.
The available comparison operators are located in `TeamQ\QueryBuilder\Enums\Comparators`

To use these advanced filters, just call them as custom filters:

```php
GET /books?filter[isbn][value]=54213&filter[isbn][operator]=5
```

```php
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use TeamQ\QueryBuilder\Filters\TextFilter;

QueryBuilder::for(Book::class)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter()),
        ]);
```

If you want to handle relationships using join, you must pass `false` as the first parameter to the filter
and pass the type of `join` to use.

```php
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use TeamQ\QueryBuilder\Filters\TextFilter;
use TeamQ\QueryBuilder\Enums\JoinType;

QueryBuilder::for(Book::class)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter(false, JoinType::Inner)),
        ]);
```

##### Available filters:

| Filter | Class                                     |
|--------|-------------------------------------------|
| Text   | `TeamQ\QueryBuilder\Filters\TextFilter`   |
| Number | `TeamQ\QueryBuilder\Filters\NumberFilter` |
| Date   | `TeamQ\QueryBuilder\Filters\DateFilter`   |
| Global | `TeamQ\QueryBuilder\Filters\GlobalFilter` |

#### Global Filter

The global filter implements general search functionality for the model and its relationships.

This is not a global search engine between entities!!!
For that you can use the package [spatie/laravel-searchable](https://github.com/spatie/laravel-searchable)

To use this filter, you must pass the model fields to be filtered or their relationships.

```php
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use TeamQ\QueryBuilder\Filters\GlobalFilter;

QueryBuilder::for(Book::class)
        ->allowedFilters([
            AllowedFilter::custom('search', new GlobalFilter([
                'author.name',
                'title',
            ])),
        ]);
```

### Sorts

#### RelationSort

To sort by fields of related tables you must use `join`, there is no easy way to do it from eloquent,
so you can use `TeamQ\QueryBuilder\Sorts\RelationSort`, this class receives the type of `join` as a parameter.

```php
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use TeamQ\QueryBuilder\Sorts\RelationSort;

QueryBuilder::for(Book::class)
        ->allowedSorts([
            AllowedSort::custom('author.name', new RelationSort(JoinType::Inner)),
        ])
```

#### CaseSort

If you use enums or states, where each enum or state is represented by a number, you may want to sort by name
of that enum or state and not by the number, then you can use `TeamQ\QueryBuilder\Sorts\CaseSort`.

You must pass an array `[$key => $value]`, which will be used to generate the sort.

```php
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use TeamQ\QueryBuilder\Sorts\CaseSort;

QueryBuilder::for(Book::class)
        ->allowedSorts([
            AllowedSort::custom('state', new CaseSort([
                1 => 'Active',
                2 => 'Rejected',
                3 => 'Deleted',
                4 => 'Completed',
            ])),
        ]);
```

## Testing

Can use docker-compose to run

```bash
docker-compose exec app composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Luis Arce](https://github.com/luilliarcec)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
