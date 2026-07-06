<?php

namespace App\Enums;

enum AccountType: string
{
    case SAVINGS = 'SAVINGS';

    case CURRENT = 'CURRENT';

    case DOMICILIARY = 'DOMICILIARY';

    public function label(): string
    {
        return match ($this) {

            self::SAVINGS => 'Savings',

            self::CURRENT => 'Current',

            self::DOMICILIARY => 'Domiciliary',
        };
    }
}