<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundStatusLog extends Model
{
    protected $fillable = [
        'refund_id',
        'changed_by',
        'old_status',
        'new_status',
        'note',
    ];


    /**
     * Status values casting.
     */
    protected function casts(): array
    {
        return [
            'old_status' => RefundStatus::class,
            'new_status' => RefundStatus::class,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


    /**
     * Refund this log belongs to.
     */
    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }


    /**
     * User who changed the status.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }


    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */


    /**
     * Logs for a specific refund.
     */
    public function scopeForRefund(
        Builder $query,
        int $refundId
    ): Builder {

        return $query->where(
            'refund_id',
            $refundId
        );
    }


    /**
     * Latest changes first.
     */
    public function scopeLatestChanges(
        Builder $query
    ): Builder {

        return $query->latest();
    }


    /*
    |--------------------------------------------------------------------------
    | Model Protection
    |--------------------------------------------------------------------------
    */


    /**
     * Prevent modification of audit history.
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException(
                'Refund status logs cannot be modified.'
            );
        });


        static::deleting(function () {
            throw new \RuntimeException(
                'Refund status logs cannot be deleted.'
            );
        });
    }
}