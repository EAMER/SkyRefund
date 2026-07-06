<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMINISTRATOR = 'ADMINISTRATOR';
    case MANAGER = 'MANAGER';
    case OFFICER = 'OFFICER';
}