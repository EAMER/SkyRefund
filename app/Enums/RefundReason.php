<?php

namespace App\Enums;

enum RefundReason: string
{
    case FLIGHT_CANCELLATION = 'FLIGHT_CANCELLATION';

    case FLIGHT_DELAY = 'FLIGHT_DELAY';

    case SCHEDULE_CHANGE = 'SCHEDULE_CHANGE';

    case OVERBOOKING = 'OVERBOOKING';

    case DUPLICATE_BOOKING = 'DUPLICATE_BOOKING';

    case MEDICAL = 'MEDICAL';

    case VISA_DENIAL = 'VISA_DENIAL';

    case PERSONAL = 'PERSONAL';

    case OTHER = 'OTHER';

    public function label(): string
    {
        return match ($this) {

            self::FLIGHT_CANCELLATION => 'Flight Cancellation',

            self::FLIGHT_DELAY => 'Flight Delay',

            self::SCHEDULE_CHANGE => 'Schedule Change',

            self::OVERBOOKING => 'Overbooking',

            self::DUPLICATE_BOOKING => 'Duplicate Booking',

            self::MEDICAL => 'Medical',

            self::VISA_DENIAL => 'Visa Denial',

            self::PERSONAL => 'Personal',

            self::OTHER => 'Other',
        };
    }
}