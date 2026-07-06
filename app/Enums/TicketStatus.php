<?php

namespace App\Enums;

enum TicketStatus:string
{
    case ACTIVE = 'ACTIVE';

    case FLOWN = 'FLOWN';

    case CANCELLED = 'CANCELLED';

    case NO_SHOW = 'NO_SHOW';

    case REFUNDED = 'REFUNDED';

    case EXPIRED = 'EXPIRED';
}