# Laravel Spatie Filters

[![Latest Version on Packagist](https://img.shields.io/packagist/v/teamq-ec/teamq-laravel-spatie-filters.svg?style=flat-square)](https://packagist.org/packages/teamq-ec/teamq-laravel-spatie-filters)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/teamq-ec/teamq-laravel-spatie-filters/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/teamq-ec/teamq-laravel-spatie-filters/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/teamq-ec/teamq-laravel-spatie-filters/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/teamq-ec/teamq-laravel-spatie-filters/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/teamq-ec/teamq-laravel-spatie-filters.svg?style=flat-square)](https://packagist.org/packages/teamq-ec/teamq-laravel-spatie-filters)

This is a collection of classes for filters and sorts, extending from the [spatie/laravel-query-builder](https://github.com/spatie/laravel-query-builder) package,
in addition to providing the possibility of applying these filters and sorting in related models using join through
from the [kirschbaum-development/eloquent-power-joins](https://github.com/kirschbaum-development/eloquent-power-joins) package.

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

### Pagination

This package uses Laravel pagination by default; However, it allows you to specify through parameters of
queries, the number of records to obtain, you can even obtain all the records.

```php
GET /books?per_page=30
```

It is recommended to use the `result` method instead of `paginate` or `get`. Since `result` encapsulates the logic of
both yes, it is requested to show all, below `result` will use `get` but if you want to see a number of records
below will use `paginate` with the amount provided.

```php
use Spatie\QueryBuilder\AllowedFilter;
use TeamQ\QueryBuilder\QueryBuilder;
use TeamQ\QueryBuilder\Filters\TextFilter;

$query = QueryBuilder::for(Book::class);

$query->result();
```

### Filters

| Filter | Class                                     | Operators |
|--------|-------------------------------------------|-----------|
| Text   | `TeamQ\QueryBuilder\Filters\TextFilter`   | Text      |
| Number | `TeamQ\QueryBuilder\Filters\NumberFilter` | Number    |
| Date   | `TeamQ\QueryBuilder\Filters\DateFilter`   | Number    |
| Global | `TeamQ\QueryBuilder\Filters\GlobalFilter` | -         |

<details>
    <summary>Text comparison operators</summary>

| Comparison operator | Key |
|---------------------|-----|
| Equal               | 1   |
| Not Equal           | 2   |
| Start With          | 3   |
| Not Start With      | 4   |
| End With            | 5   |
| Not End With        | 6   |
| Contains            | 7   |
| Not Contains        | 8   |
| In                  | 9   |
| Not In              | 10  |
| Filled              | 11  |
| Not Filled          | 12  |

</details>

<details>
    <summary>Number comparison operators</summary>

| Comparison operator   | Key |
|-----------------------|-----|
| Equal                 | 1   |
| Not Equal             | 2   |
| Greater Than          | 3   |
| Greater Than Or Equal | 4   |
| Less Than             | 5   |
| Less Than Or Equal    | 6   |
| Between               | 7   |
| Not Between           | 8   |
| In                    | 9   |
| Not In                | 10  |
| Filled                | 11  |
| Not Filled            | 12  |

</details>

You can use advanced filters that have support for multiple comparison operators.
The available comparison operators are located in `TeamQ\QueryBuilder\Enums\Comparators`

To use these advanced filters, just call them as custom filters:

```php
GET /books?filter[isbn][value]=54213&filter[isbn][operator]=5
```

```php
use Spatie\QueryBuilder\AllowedFilter;
use TeamQ\QueryBuilder\QueryBuilder;
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
use TeamQ\QueryBuilder\QueryBuilder;
use TeamQ\QueryBuilder\Filters\TextFilter;
use TeamQ\QueryBuilder\Enums\JoinType;

QueryBuilder::for(Book::class)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter(false, JoinType::Inner)),
        ]);
```

#### _Text Filter_

The following example uses the comparison operator `5`, which is equivalent to asking if there is a book where
isbn `End With` 54213

```php
GET /books?filter[isbn][value]=54213&filter[isbn][operator]=5
```

```sql
select *
from `books`
where lower(`isbn`) like '%54213'
```

```php
use Spatie\QueryBuilder\AllowedFilter;
use TeamQ\QueryBuilder\QueryBuilder;
use TeamQ\QueryBuilder\Filters\TextFilter;

QueryBuilder::for(Book::class)
        ->allowedFilters([
            AllowedFilter::custom('isbn', new TextFilter()),
        ]);
```

#### _Number Filter_

The following example uses the comparison operator `9`, which is equivalent to asking if there is a book where
id `In` 1, 5 or 9

For this example an array of values was used. Arraying values is supported by all types of operators (text and number).

```php
GET /books?filter[id][value][0]=1&filter[id][value][1]=5&filter[id][value][2]=9&filter[id][operator]=9
```

```sql
select *
from `books`
where id in (1, 5, 9)
```

```php
use Spatie\QueryBuilder\AllowedFilter;
use TeamQ\QueryBuilder\QueryBuilder;
use TeamQ\QueryBuilder\Filters\NumberFilter;

QueryBuilder::for(Book::class)
        ->allowedFilters([
            AllowedFilter::custom('id', new NumberFilter()),
        ]);
```

#### _Date Filter_

The following example uses the comparison operator `8`, which is equivalent to asking if there is a book where
created at `Not Between` 2019-08-01 and 2019-08-10

For this example an array of values was used. Arraying values is supported by all types of operators (text and number).

```php
GET /books?filter[created_at][value][0]=2019-08-01&filter[created_at][value][1]=2019-08-10&filter[id][operator]=8
```

```sql
select *
from `books`
where created_at not between '2019-08-01' and '2019-08-10'
```

```php
use Spatie\QueryBuilder\AllowedFilter;
use TeamQ\QueryBuilder\QueryBuilder;
use TeamQ\QueryBuilder\Filters\DateFilter;

QueryBuilder::for(Book::class)
        ->allowedFilters([
            AllowedFilter::custom('created_at', new DateFilter()),
        ]);
```

#### _Global Filter_

The global filter implements general search functionality for the model and its relationships.

This is not a global search engine between entities!!!
For that you can use the package [spatie/laravel-searchable](https://github.com/spatie/laravel-searchable)

To use this filter, you must pass the model fields to be filtered or their relationships.

```php
use Spatie\QueryBuilder\AllowedFilter;
use TeamQ\QueryBuilder\QueryBuilder;
use TeamQ\QueryBuilder\Filters\GlobalFilter;

QueryBuilder::for(Book::class)
        ->allowedFilters([
            AllowedFilter::custom('search', new GlobalFilter([
                'author.name',
                'title',
            ])),
        ]);
```

---

### Sorts

| Sort     | Class                                   |
|----------|-----------------------------------------|
| Relation | `TeamQ\QueryBuilder\Sorts\RelationSort` |
| Case     | `TeamQ\QueryBuilder\Sorts\CaseSort`     |

#### _RelationSort_

To sort by fields of related tables you must use `join`, there is no easy way to do it from eloquent,
so you can use `RelationSort`, this class receives the type of `join` as a parameter.

```php
use Spatie\QueryBuilder\AllowedFilter;
use TeamQ\QueryBuilder\QueryBuilder;
use TeamQ\QueryBuilder\Sorts\RelationSort;

QueryBuilder::for(Book::class)
        ->allowedSorts([
            AllowedSort::custom('author.name', new RelationSort(JoinType::Inner)),
        ])
```

#### _CaseSort_

If you use enums or states, where each enum or state is represented by a number, you may want to sort by name
of that enum or state and not by the number, then you can use `CaseSort`.

You must pass an array `[$key => $value]`, which will be used to generate the sort.

As a second parameter you can specify the type of `Join` that you want to use, if the ordering is by the field of a
related model. By default, it is `Inner Join`.

```php
use Spatie\QueryBuilder\AllowedFilter;
use TeamQ\QueryBuilder\QueryBuilder;
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
