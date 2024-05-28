<?php

namespace TeamQ\Datatables\Enums;

/**
 * Enum aggregation type.
 *
 * @author Luis Arce
 */
enum AggregationType: string
{
    case Count = 'count';
    case Sum = 'sum';
    case Avg = 'avg';
    case Min = 'min';
    case Max = 'max';
}
