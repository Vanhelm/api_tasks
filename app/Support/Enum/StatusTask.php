<?php

namespace App\Support\Enum;

enum StatusTask: string
{
    case PLANNED  = 'planned';
    case PROGRESS = 'in_progress';
    case DONE     = 'done';
}
