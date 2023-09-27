<?php

namespace Tests\Mocks\Enums;

enum BookClassificationEnum: int
{
    case SuitableForAll = 1;
    case Kids = 2;
    case OverTwelveYearsOld = 3;
    case Adults = 4;
}
