<?php

namespace App\Enums;

enum Department: string
{
    case REFUND = 'REFUND';

    case COMMERCIAL = 'COMMERCIAL';

    case AUDIT = 'AUDIT';

    case FINANCE = 'FINANCE';

    case TREASURY = 'TREASURY';

    public function label(): string
    {
        return match ($this) {

            self::REFUND => 'Refund',

            self::COMMERCIAL => 'Commercial',

            self::AUDIT => 'Audit',

            self::FINANCE => 'Finance',

            self::TREASURY => 'Treasury',
        };
    }

    public static function workflow(): array
    {
        return [
            self::REFUND,
            self::COMMERCIAL,
            self::AUDIT,
            self::FINANCE,
            self::TREASURY,
        ];
    }
}