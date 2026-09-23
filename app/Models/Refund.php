<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\Department;
use App\Enums\Priority;
use App\Enums\RefundReason;
use App\Enums\RefundStatus;
use App\Enums\RefundType;
use App\Enums\RouteType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Refund extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | System Fields
        |--------------------------------------------------------------------------
        */

        'reference',
        'airline_id',
        'created_by',
        'assigned_to',

        /*
        |--------------------------------------------------------------------------
        | Passenger Information
        |--------------------------------------------------------------------------
        */

        'first_name',
        'last_name',
        'email',
        'phone',
        'address',

        /*
        |--------------------------------------------------------------------------
        | Refund Information
        |--------------------------------------------------------------------------
        */

        'refund_reason',
        'refund_type',
        'route_type',
        'passenger_explanation',

        /*
        |--------------------------------------------------------------------------
        | Bank Information
        |--------------------------------------------------------------------------
        */

        'bank_name',
        'account_name',
        'account_number',
        'account_type',

        /*
        |--------------------------------------------------------------------------
        | Workflow
        |--------------------------------------------------------------------------
        */

        'current_status',
        'current_department',
        'priority',

        /*
        |--------------------------------------------------------------------------
        | AI
        |--------------------------------------------------------------------------
        */

        'ai_flagged',
        'ai_score',
        'ai_processed_at',

        /*
        |--------------------------------------------------------------------------
        | Other
        |--------------------------------------------------------------------------
        */

        'consent',
        'admin_notes',
        'payment_reference',
        'paid_at',
    ];

    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [

            'refund_reason' => RefundReason::class,

            'refund_type' => RefundType::class,

            'route_type' => RouteType::class,

            'account_type' => AccountType::class,

            'current_department' => Department::class,

            'current_status' => RefundStatus::class,

            'priority' => Priority::class,

            'ai_flagged' => 'boolean',

            'ai_score' => 'decimal:2',

            'ai_processed_at' => 'datetime',

            'consent' => 'boolean',

            'paid_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(
            RefundTicket::class
        );
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(
            RefundAttachment::class
        );
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(
            AIAnalysis::class
        );
    }

    public function queries(): HasMany
    {
        return $this->hasMany(
            RefundQuery::class
        );
    }
    public function statusLogs(): HasMany
    {
        return $this->hasMany(
            RefundStatusLog::class
        )->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForAirline(
        Builder $query,
        int $airlineId
    ): Builder {

        return $query->where(
            'airline_id',
            $airlineId
        );
    }

    public function scopeAssignedTo(
        Builder $query,
        int $userId
    ): Builder {

        return $query->where(
            'assigned_to',
            $userId
        );
    }

    public function scopePending(
        Builder $query
    ): Builder {

        return $query->whereIn(
            'current_status',
            [
                RefundStatus::NEW_REQUEST,
                RefundStatus::PENDING_COMMERCIAL,
                RefundStatus::PENDING_AUDIT,
                RefundStatus::PENDING_FINANCE,
                RefundStatus::PENDING_TREASURY,
            ]
        );
    }

    public function scopeHighPriority(
        Builder $query
    ): Builder {

        return $query->where(
            'priority',
            Priority::HIGH
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getPassengerNameAttribute(): string
    {
        return trim(
            "{$this->first_name} {$this->last_name}"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isCompleted(): bool
    {
        return $this->current_status === RefundStatus::REFUND_COMPLETED;
    }

    public function isRejected(): bool
    {
        return $this->current_status === RefundStatus::REJECTED;
    }

    public function isCancelled(): bool
    {
        return $this->current_status === RefundStatus::CANCELLED;
    }

    public function isReturned(): bool
    {
        return $this->current_status?->isReturned() ?? false;
    }

    public function isPending(): bool
    {
        return $this->current_status?->isPending() ?? false;
    }

    public function canMoveTo(
        RefundStatus $status
    ): bool {

        return $this->current_status
            ?->canTransitionTo($status) ?? false;
    }
}