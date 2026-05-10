<?php

namespace App\Enums;

enum CadreStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case TRANSFERRED = 'transferred';
    case GRADUATED = 'graduated';
}
