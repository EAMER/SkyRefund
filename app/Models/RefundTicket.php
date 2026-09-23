<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundTicket extends Model
{
    use HasFactory, SoftDeletes;



    protected $fillable = [

        'refund_id',

        'booking_reference',

        'ticket_number',

        'passenger_name',

        'flight_number',

        'airline_code',

        'origin_airport',

        'destination_airport',

        'departure_datetime',

        'arrival_datetime',

        'cabin_class',

        'fare_paid',

        'refund_amount',

        'currency',

        'ticket_status',

        'remarks',

        'nuc',

        'government_tax_ng',

        'security_tax_yq',

        'airport_tax_qt',

        'insurance',

        'is_no_show',

        'no_show_fee',

        'total_deduction',

    ];



    protected function casts(): array
    {
        return [

            'departure_datetime' =>
                'datetime',

            'arrival_datetime' =>
                'datetime',


            'fare_paid' =>
                'decimal:2',


            'refund_amount' =>
                'decimal:2',


            'ticket_status' =>
                TicketStatus::class,


            'nuc' =>
                'decimal:2',

            'government_tax_ng' =>
                'decimal:2',

            'security_tax_yq' =>
                'decimal:2',

            'airport_tax_qt' =>
                'decimal:2',

            'insurance' =>
                'decimal:2',

            'is_no_show' =>
                'boolean',

            'no_show_fee' =>
                'decimal:2',

            'total_deduction' =>
                'decimal:2',

        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


    public function refund(): BelongsTo
    {
        return $this->belongsTo(
            Refund::class
        );
    }

    public function amountLogs(): HasMany
{
    return $this->hasMany(RefundTicketAmountLog::class);
}


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */


    public function isRefundable(): bool
    {
        return in_array(
            $this->ticket_status,
            [
                TicketStatus::CONFIRMED,
                TicketStatus::USED,
            ],
            true
        );
    }



    public function route(): string
    {
        return "{$this->origin_airport} → {$this->destination_airport}";
    }


    /**
     * Recomputes total_deduction and refund_amount from the entered tax/fee
     * fields. Call this before saving whenever the officer's calculation
     * inputs change (nuc, taxes, insurance, no-show fee) — never trust a
     * client-supplied total_deduction or refund_amount directly.
     */
    public function recalculateDeduction(): void
    {
        $this->total_deduction = (float) $this->nuc
            + (float) $this->government_tax_ng
            + (float) $this->security_tax_yq
            + (float) $this->airport_tax_qt
            + (float) $this->insurance
            + ($this->is_no_show ? (float) $this->no_show_fee : 0);

        $this->refund_amount = max(0, (float) $this->fare_paid - $this->total_deduction);
    }




    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */


    public function scopePending(
        Builder $query
    ): Builder {

        return $query->where(
            'ticket_status',
            TicketStatus::PENDING
        );
    }



    public function scopeForRefund(
        Builder $query,
        int $refundId
    ): Builder {

        return $query->where(
            'refund_id',
            $refundId
        );
    }
}