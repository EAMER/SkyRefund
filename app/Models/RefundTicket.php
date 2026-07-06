<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    ];

    protected function casts(): array
    {
        return [

            'departure_datetime' => 'datetime',

            'arrival_datetime' => 'datetime',

            'fare_paid' => 'decimal:2',

            'refund_amount' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }
}