<?php

namespace Tests\Mocks\Enums;

enum AuthorTypeEnum: int
{
    case VIP = 1;
    case Public = 2;
    case Private = 3;
}
