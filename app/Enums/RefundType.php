<?php

namespace App\Enums;

enum RefundType: string
{
    case INDIVIDUAL = 'INDIVIDUAL';
    case THIRD_PARTY = 'THIRD_PARTY';


public function label(): string
{
    return match ($this) {
        self::INDIVIDUAL => 'INDIVIDUAL',

        self::THIRD_PARTY => 'THIRD_PARTY',
    };
}

}