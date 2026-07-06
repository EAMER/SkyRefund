<?php

namespace App\Enums;

enum CabinClass:string
{
    case ECONOMY = 'ECONOMY';

    case PREMIUM_ECONOMY = 'PREMIUM_ECONOMY';

    case BUSINESS = 'BUSINESS';

    case FIRST = 'FIRST';
}