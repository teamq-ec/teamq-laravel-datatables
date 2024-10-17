<?php

namespace TeamQ\Datatables\Enums\Comparators;

/**
 * Text comparison operators.
 *
 * @author Luis Arce
 */
enum Text: string
{
    case Equal = '$eq';
    case NotEqual = '$notEq';
    case StartWith = '$startWith';
    case NotStartWith = '$notStartWith';
    case EndWith = '$endWith';
    case NotEndWith = '$notEndWith';
    case Contains = '$contains';
    case NotContains = '$notContains';
    case In = '$in';
    case NotIn = '$notIn';
    case Filled = '$filled';
    case NotFilled = '$notFilled';
}
