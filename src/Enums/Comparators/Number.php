<?php

namespace TeamQ\Datatables\Enums\Comparators;

/**
 * Number comparison operators.
 *
 * @author Luis Arce
 */
enum Number: int
{
    case Equal = 1;
    case NotEqual = 2;
    case GreaterThan = 3;
    case GreaterThanOrEqual = 4;
    case LessThan = 5;
    case LessThanOrEqual = 6;
    case Between = 7;
    case NotBetween = 8;
    case In = 9;
    case NotIn = 10;
    case Filled = 11;
    case NotFilled = 12;
}
