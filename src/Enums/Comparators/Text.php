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
    case StartWith = '$like:start';
    case NotStartWith = '$notLike:start';
    case EndWith = '$like:end';
    case NotEndWith = '$notLike:end';
    case Contains = '$like';
    case NotContains = '$notLike';
    case In = '$in';
    case NotIn = '$notIn';
    case Filled = '$filled';
    case NotFilled = '$notFilled';
}
