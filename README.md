# Laravel Spatie Filters

[![Latest Version on Packagist](https://img.shields.io/packagist/v/teamq-ec/teamq-laravel-spatie-filters.svg?style=flat-square)](https://packagist.org/packages/teamq-ec/teamq-laravel-spatie-filters)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/teamq-ec/teamq-laravel-spatie-filters/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/teamq-ec/teamq-laravel-spatie-filters/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/teamq-ec/teamq-laravel-spatie-filters/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/teamq-ec/teamq-laravel-spatie-filters/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/teamq-ec/teamq-laravel-spatie-filters.svg?style=flat-square)](https://packagist.org/packages/teamq-ec/teamq-laravel-spatie-filters)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

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

WIP

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
