<?php

namespace TeamQ\Datatables\Enums\Comparators;

enum Condition: string
{
    case And = '$and';
    case Or = '$or';
}
