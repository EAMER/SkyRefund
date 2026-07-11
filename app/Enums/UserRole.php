<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';

    case REFUND_OFFICER = 'REFUND_OFFICER';

    case COMMERCIAL = 'COMMERCIAL';

    case AUDIT = 'AUDIT';

    case FINANCE = 'FINANCE';

    case TREASURY = 'TREASURY';

    /**
     * Human readable name.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::REFUND_OFFICER => 'Refund Officer',
            self::COMMERCIAL => 'Commercial',
            self::AUDIT => 'Audit',
            self::FINANCE => 'Finance',
            self::TREASURY => 'Treasury',
        };
    }

    /**
     * Can manage the entire system?
     */
    public function isAdmin(): bool
    {
        return $this === self::SUPER_ADMIN;
    }

    /**
     * Can assign refund requests?
     */
    public function canAssignRefunds(): bool
    {
        return in_array($this, [
            self::SUPER_ADMIN,
            self::REFUND_OFFICER,
        ]);
    }

    /**
     * Can approve workflow?
     */
    public function canApprove(): bool
    {
        return in_array($this, [
            self::COMMERCIAL,
            self::AUDIT,
            self::FINANCE,
            self::TREASURY,
            self::SUPER_ADMIN,
        ]);
    }

    /**
     * Can reject requests?
     */
    public function canReject(): bool
    {
        return in_array($this, [
            self::COMMERCIAL,
            self::AUDIT,
            self::FINANCE,
            self::TREASURY,
            self::SUPER_ADMIN,
        ]);
    }
}