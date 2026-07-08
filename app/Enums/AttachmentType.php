<?php

namespace App\Enums;

enum AttachmentType: string
{
    case SIGNATURE = 'signature';

    case PASSENGER_ID = 'passenger_id';

    case ACCOUNT_HOLDER_ID = 'account_holder_id';

    case AUTHORIZATION_LETTER = 'authorization_letter';

    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {

            self::SIGNATURE => 'Signature',

            self::PASSENGER_ID => 'Passenger Identification',

            self::ACCOUNT_HOLDER_ID => 'Account Holder Identification',

            self::AUTHORIZATION_LETTER => 'Authorization Letter',

            self::OTHER => 'Other Document',

        };
    }
}