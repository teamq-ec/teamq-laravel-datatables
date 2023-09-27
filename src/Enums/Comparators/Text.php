<?php

namespace TeamQ\QueryBuilder\Enums\Comparators;

/**
 * Text comparison operators.
 *
 * @author Luis Arce
 */
enum Text: int
{
    case Equal = 1;
    case NotEqual = 2;
    case StartWith = 3;
    case NotStartWith = 4;
    case EndWith = 5;
    case NotEndWith = 6;
    case Contains = 7;
    case NotContains = 8;
    case In = 9;
    case NotIn = 10;
    case Filled = 11;
    case NotFilled = 12;
}
