<?php

namespace App\Enums;

enum TicketStatus: string
{
    case PENDING = 'PENDING';

    case CONFIRMED = 'CONFIRMED';

    case CANCELLED = 'CANCELLED';

    case USED = 'USED';

    case REFUNDED = 'REFUNDED';


    public function label(): string
    {
        return match ($this) {

            self::PENDING =>
                'Pending',

            self::CONFIRMED =>
                'Confirmed',

            self::CANCELLED =>
                'Cancelled',

            self::USED =>
                'Used',

            self::REFUNDED =>
                'Refunded',

        };
    }
}