<?php

namespace App\Enums;

use Mokhosh\FilamentKanban\Concerns\IsKanbanStatus;

enum SalesLeadStatus: string
{
    use IsKanbanStatus;

    case New = 'new';
    case Active = 'active';
    case Rejected = 'rejected';
    case Converted = 'converted';
}
