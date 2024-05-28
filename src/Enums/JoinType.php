<?php

namespace TeamQ\Datatables\Enums;

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
