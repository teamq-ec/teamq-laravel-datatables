<?php

namespace TeamQ\Datatables\Enums\Comparators;

/**
 * Number comparison operators.
 *
 * @author Luis Arce
 */
enum Number: string
{
    case Equal = '$eq';
    case NotEqual = '$notEq';
    case GreaterThan = '$gt';
    case GreaterThanOrEqual = '$gte';
    case LessThan = '$lt';
    case LessThanOrEqual = '$lte';
    case Between = '$between';
    case NotBetween = '$notBetween';
    case In = '$in';
    case NotIn = '$notIn';
    case Filled = '$filled';
    case NotFilled = '$notFilled';
}
