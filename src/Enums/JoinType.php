<?php

namespace TeamQ\QueryBuilder\Enums;

/**
 * Enum join type.
 *
 * @author Luis Arce
 */
enum JoinType: string
{
    case Inner = 'join';
    case Left = 'leftJoin';
    case Right = 'rightJoin';
}
