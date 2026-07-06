<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refund_tickets', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Primary Key
            |--------------------------------------------------------------------------
            */

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('refund_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Booking Information
            |--------------------------------------------------------------------------
            */

            $table->string('booking_reference', 20);

            $table->string('ticket_number', 30);

            $table->string('passenger_name');

            /*
            |--------------------------------------------------------------------------
            | Flight Information
            |--------------------------------------------------------------------------
            */

            $table->string('flight_number', 20);

            $table->string('airline_code', 10);

            $table->string('origin_airport', 10);

            $table->string('destination_airport', 10);

            $table->dateTime('departure_datetime');

            $table->dateTime('arrival_datetime')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Ticket Details
            |--------------------------------------------------------------------------
            */

            $table->string('cabin_class', 30)->nullable();

            $table->decimal('fare_paid', 12, 2)->nullable();

            $table->decimal('refund_amount', 12, 2)->nullable();

            $table->string('currency', 5)->default('NGN');

            $table->string('ticket_status', 30)->default('ACTIVE');

            $table->text('remarks')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Soft Deletes
            |--------------------------------------------------------------------------
            */

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('refund_id');

            $table->index('booking_reference');

            $table->index('ticket_number');

            $table->index('flight_number');

            $table->index('departure_datetime');

            $table->index([
                'origin_airport',
                'destination_airport'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_tickets');
    }
};