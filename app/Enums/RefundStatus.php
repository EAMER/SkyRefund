<?php

namespace App\Enums;

enum RefundStatus: string
{
    /*
    |--------------------------------------------------------------------------
    | Refund Officer
    |--------------------------------------------------------------------------
    */

    case NEW_REQUEST = 'NEW_REQUEST';

    /*
    |--------------------------------------------------------------------------
    | Commercial
    |--------------------------------------------------------------------------
    */

    case PENDING_COMMERCIAL = 'PENDING_COMMERCIAL';

    case RETURNED_BY_COMMERCIAL = 'RETURNED_BY_COMMERCIAL';

    /*
    |--------------------------------------------------------------------------
    | Audit
    |--------------------------------------------------------------------------
    */

    case PENDING_AUDIT = 'PENDING_AUDIT';

    case RETURNED_BY_AUDIT = 'RETURNED_BY_AUDIT';

    /*
    |--------------------------------------------------------------------------
    | Finance
    |--------------------------------------------------------------------------
    */

    case PENDING_FINANCE = 'PENDING_FINANCE';

    case RETURNED_BY_FINANCE = 'RETURNED_BY_FINANCE';

    /*
    |--------------------------------------------------------------------------
    | Treasury
    |--------------------------------------------------------------------------
    */

    case PENDING_TREASURY = 'PENDING_TREASURY';

    /*
    |--------------------------------------------------------------------------
    | Final States
    |--------------------------------------------------------------------------
    */

    case REFUND_COMPLETED = 'REFUND_COMPLETED';

    case REJECTED = 'REJECTED';

    case CANCELLED = 'CANCELLED';

    /*
    |--------------------------------------------------------------------------
    | Labels
    |--------------------------------------------------------------------------
    */

    public function label(): string
    {
        return match ($this) {

            self::NEW_REQUEST => 'New Request',

            self::PENDING_COMMERCIAL => 'Pending Commercial',

            self::RETURNED_BY_COMMERCIAL => 'Returned by Commercial',

            self::PENDING_AUDIT => 'Pending Audit',

            self::RETURNED_BY_AUDIT => 'Returned by Audit',

            self::PENDING_FINANCE => 'Pending Finance',

            self::RETURNED_BY_FINANCE => 'Returned by Finance',

            self::PENDING_TREASURY => 'Pending Treasury',

            self::REFUND_COMPLETED => 'Refund Completed',

            self::REJECTED => 'Rejected',

            self::CANCELLED => 'Cancelled',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow
    |--------------------------------------------------------------------------
    */

    public function next(): ?self
    {
        return match ($this) {

            self::NEW_REQUEST => self::PENDING_COMMERCIAL,

            self::PENDING_COMMERCIAL => self::PENDING_AUDIT,

            self::PENDING_AUDIT => self::PENDING_FINANCE,

            self::PENDING_FINANCE => self::PENDING_TREASURY,

            self::PENDING_TREASURY => self::REFUND_COMPLETED,

            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Returned Workflow
    |--------------------------------------------------------------------------
    */

    public function returnedTo(): ?self
    {
        return match ($this) {

            self::RETURNED_BY_COMMERCIAL => self::NEW_REQUEST,

            self::RETURNED_BY_AUDIT => self::PENDING_COMMERCIAL,

            self::RETURNED_BY_FINANCE => self::PENDING_AUDIT,

            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isReturned(): bool
    {
        return in_array($this, [

            self::RETURNED_BY_COMMERCIAL,

            self::RETURNED_BY_AUDIT,

            self::RETURNED_BY_FINANCE,
        ]);
    }

    public function isFinal(): bool
    {
        return in_array($this, [

            self::REFUND_COMPLETED,

            self::REJECTED,

            self::CANCELLED,
        ]);
    }

    public function isPending(): bool
    {
        return in_array($this, [

            self::NEW_REQUEST,

            self::PENDING_COMMERCIAL,

            self::PENDING_AUDIT,

            self::PENDING_FINANCE,

            self::PENDING_TREASURY,
        ]);
    }
}