<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundTicketAmountLog extends Model
{
    protected $fillable = [
        'refund_ticket_id',
        'changed_by',
        'old_amount',
        'new_amount',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'old_amount' => 'decimal:2',
            'new_amount' => 'decimal:2',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(RefundTicket::class, 'refund_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException('Refund ticket amount logs cannot be modified.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Refund ticket amount logs cannot be deleted.');
        });
    }
}