<?php

namespace App\Enums;

enum Priority:string
{
    case LOW = 'LOW';

    case MEDIUM = 'MEDIUM';

    case HIGH = 'HIGH';

    case URGENT = 'URGENT';

    public function color(): string
    {
        return match ($this) {

            self::LOW => 'gray',

            self::MEDIUM => 'blue',

            self::HIGH => 'orange',

            self::URGENT => 'red',
        };
    }
}