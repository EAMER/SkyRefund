<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class RefundQuery extends Model
{
    protected $fillable = [
        'refund_id',
        'raised_by_user_id',
        'directed_to_user_id',
        'message',
        'response',
        'resolved_by_user_id',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }


    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by_user_id');
    }

    public function directedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'directed_to_user_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }


    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeDirectedTo(Builder $query, int $userId): Builder
    {
        return $query->where('directed_to_user_id', $userId);
    }


    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }
}