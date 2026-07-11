<?php

namespace App\Enums;

enum Department: string
{
    case REFUND = 'refund';

    case COMMERCIAL = 'commercial';

    case AUDIT = 'audit';

    case FINANCE = 'finance';

    case TREASURY = 'treasury';

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