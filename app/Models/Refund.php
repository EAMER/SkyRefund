<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\Department;
use App\Enums\Priority;
use App\Models\RefundTicket;
use App\Enums\RefundReason;
use App\Enums\RefundType;
use App\Enums\RouteType;
use App\Enums\RefundStatus;
use App\Models\RefundStatusLog;
use App\Models\RefundAttachment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Refund extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'reference',

        'airline_id',
        'created_by',
        'assigned_to',

        'first_name',
        'last_name',
        'email',
        'phone',
        'address',

        'refund_reason',
        'refund_type',
        'passenger_explanation',

        'bank_name',
        'account_name',
        'account_number',
        'account_type',

        'current_department',
        'current_status',
        'priority',

        'ai_flagged',
        'ai_score',
        'ai_processed_at',

        'consent',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'refund_reason' => RefundReason::class,
            'refund_type' => RefundType::class,
            'account_type' => AccountType::class,
            'current_department' => Department::class,
            'current_status' => RefundStatus::class,
            'priority' => Priority::class,

            'ai_flagged' => 'boolean',
            'ai_score' => 'decimal:2',
            'ai_processed_at' => 'datetime',

            'consent' => 'boolean',
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
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(RefundTicket::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(RefundAttachment::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(AIAnalysis::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(RefundStatusLog::class);
    }
}