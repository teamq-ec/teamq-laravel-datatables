<?php

use TeamQ\Datatables\Enums\Comparators;

dataset('per-page-functions', [
    'paginate',
    'result',
]);

dataset('filters.global', [
    // Value | Expected | Count
    'countries.name' => [
        'Belg', // Belgium
        'Laravel Beyond Crud',
        1,
    ],
    'authors.name' => [
        'Taylor', // Taylor Otwell
        'Domain Driven Design for Laravel',
        1,
    ],
    'title' => [
        'beyond C',
        'Laravel Beyond Crud',
        1,
    ],
    'isbn' => [
        '58954', // 5895421369
        'Domain Driven Design for Laravel',
        1,
    ],
]);

dataset('filters.text:isbn', [
    // Value | Operator | Expected | Count
    Comparators\Text::Equal->name => [
        'BE758952123',
        Comparators\Text::Equal,
        'BE758952123',
        1,
    ],
    Comparators\Text::NotEqual->name => [
        'BE758952123',
        Comparators\Text::NotEqual,
        'BE758952123',
        1,
    ],
    Comparators\Text::StartWith->name => [
        'BE7589',
        Comparators\Text::StartWith,
        'BE758952123',
        1,
    ],
    Comparators\Text::NotStartWith->name => [
        'BE7589',
        Comparators\Text::NotStartWith,
        'BE758952123',
        1,
    ],
    Comparators\Text::EndWith->name => [
        '2123',
        Comparators\Text::EndWith,
        'BE758952123',
        1,
    ],
    Comparators\Text::NotEndWith->name => [
        '2123',
        Comparators\Text::NotEndWith,
        'BE758952123',
        1,
    ],
    Comparators\Text::Contains->name => [
        '8952',
        Comparators\Text::Contains,
        'BE758952123',
        1,
    ],
    Comparators\Text::NotContains->name => [
        '8952',
        Comparators\Text::NotContains,
        'BE758952123',
        1,
    ],
    Comparators\Text::Filled->name => [
        null,
        Comparators\Text::Filled,
        'BE758952123',
        2,
    ],
    Comparators\Text::NotFilled->name => [
        null,
        Comparators\Text::NotFilled,
        'BE758952123',
        0,
    ],
    Comparators\Text::In->name => [
        ['BE758952123', 'us5895421369'],
        Comparators\Text::In,
        'BE758952123',
        2,
    ],
    Comparators\Text::NotIn->name => [
        ['be758952123', 'us5895421369'],
        Comparators\Text::NotIn,
        'BE758952123',
        0,
    ],
]);

dataset('filters.text:author.email', [
    // Value | Operator | Expected | Count
    Comparators\Text::Equal->name => [
        'support@spatie.be',
        Comparators\Text::Equal,
        'support@spatie.be',
        1,
    ],
    Comparators\Text::NotEqual->name => [
        'support@spatie.be',
        Comparators\Text::NotEqual,
        'support@spatie.be',
        1,
    ],
    Comparators\Text::StartWith->name => [
        'support@',
        Comparators\Text::StartWith,
        'support@spatie.be',
        1,
    ],
    Comparators\Text::NotStartWith->name => [
        'support@',
        Comparators\Text::NotStartWith,
        'support@spatie.be',
        1,
    ],
    Comparators\Text::EndWith->name => [
        '@spatie.be',
        Comparators\Text::EndWith,
        'support@spatie.be',
        1,
    ],
    Comparators\Text::NotEndWith->name => [
        '@spatie.be',
        Comparators\Text::NotEndWith,
        'support@spatie.be',
        1,
    ],
    Comparators\Text::Contains->name => [
        'spatie',
        Comparators\Text::Contains,
        'support@spatie.be',
        1,
    ],
    Comparators\Text::NotContains->name => [
        'spatie',
        Comparators\Text::NotContains,
        'support@spatie.be',
        1,
    ],
    Comparators\Text::Filled->name => [
        null,
        Comparators\Text::Filled,
        'support@spatie.be',
        2,
    ],
    Comparators\Text::NotFilled->name => [
        null,
        Comparators\Text::NotFilled,
        'support@spatie.be',
        0,
    ],
    Comparators\Text::In->name => [
        ['SUPPORT@SPATIE.BE', 'TAYLOR@LARAVEL.COM'],
        Comparators\Text::In,
        'support@spatie.be',
        2,
    ],
    Comparators\Text::NotIn->name => [
        ['SUPPORT@SPATIE.BE', 'TAYLOR@LARAVEL.COM'],
        Comparators\Text::NotIn,
        'support@spatie.be',
        0,
    ],
]);

dataset('filters.number', [
    // Value | Operator | Expected | Count
    Comparators\Number::Equal->name => [
        '5',
        Comparators\Number::Equal,
        '758952123',
        1,
    ],
    Comparators\Number::NotEqual->name => [
        '5',
        Comparators\Number::NotEqual,
        '758952123',
        1,
    ],
    Comparators\Number::GreaterThan->name => [
        '5',
        Comparators\Number::GreaterThan,
        '5895421369',
        1,
    ],
    Comparators\Number::GreaterThanOrEqual->name => [
        '5',
        Comparators\Number::GreaterThanOrEqual,
        '758952123',
        2,
    ],
    Comparators\Number::LessThan->name => [
        '10',
        Comparators\Number::LessThan,
        '758952123',
        1,
    ],
    Comparators\Number::LessThanOrEqual->name => [
        '10',
        Comparators\Number::LessThanOrEqual,
        '5895421369',
        2,
    ],
    Comparators\Number::Between->name => [
        [0, 5],
        Comparators\Number::Between,
        '758952123',
        1,
    ],
    Comparators\Number::NotBetween->name => [
        [5, 10],
        Comparators\Number::NotBetween,
        '758952123',
        0,
    ],
    Comparators\Number::In->name => [
        [5, 10],
        Comparators\Number::In,
        '758952123',
        2,
    ],
    Comparators\Number::NotIn->name => [
        [5, 10],
        Comparators\Number::NotIn,
        '758952123',
        0,
    ],
    Comparators\Number::Filled->name => [
        null,
        Comparators\Number::Filled,
        '758952123',
        2,
    ],
    Comparators\Number::NotFilled->name => [
        null,
        Comparators\Number::NotFilled,
        '758952123',
        0,
    ],
]);

dataset('filters.date', [
    // Value | Operator | Expected | Count
    Comparators\Number::Equal->name => [
        '2019-08-10',
        Comparators\Number::Equal,
        '758952123',
        1,
    ],
    Comparators\Number::NotEqual->name => [
        '2019-08-10',
        Comparators\Number::NotEqual,
        '758952123',
        1,
    ],
    Comparators\Number::GreaterThan->name => [
        '2019-08-10',
        Comparators\Number::GreaterThan,
        '5895421369',
        1,
    ],
    Comparators\Number::GreaterThanOrEqual->name => [
        '2019-08-10',
        Comparators\Number::GreaterThanOrEqual,
        '758952123',
        2,
    ],
    Comparators\Number::LessThan->name => [
        '2019-08-20',
        Comparators\Number::LessThan,
        '758952123',
        1,
    ],
    Comparators\Number::LessThanOrEqual->name => [
        '2019-08-20',
        Comparators\Number::LessThanOrEqual,
        '5895421369',
        2,
    ],
    Comparators\Number::Between->name => [
        ['2019-08-01', '2019-08-10'],
        Comparators\Number::Between,
        '758952123',
        1,
    ],
    Comparators\Number::NotBetween->name => [
        ['2019-08-10', '2019-08-20'],
        Comparators\Number::NotBetween,
        '758952123',
        0,
    ],
    Comparators\Number::In->name => [
        ['2019-08-10', '2019-08-20'],
        Comparators\Number::In,
        '758952123',
        2,
    ],
    Comparators\Number::NotIn->name => [
        ['2019-08-10', '2019-08-20'],
        Comparators\Number::NotIn,
        '758952123',
        0,
    ],
    Comparators\Number::Filled->name => [
        null,
        Comparators\Number::Filled,
        '758952123',
        2,
    ],
    Comparators\Number::NotFilled->name => [
        null,
        Comparators\Number::NotFilled,
        '758952123',
        0,
    ],
]);
