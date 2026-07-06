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
        Schema::create('refunds', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Primary Key
            |--------------------------------------------------------------------------
            */

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->string('reference', 50)->unique();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('airline_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Passenger Information
            |--------------------------------------------------------------------------
            */

            $table->string('first_name');
            $table->string('last_name');

            $table->string('email');
            $table->string('phone', 30);

            $table->text('address')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Refund Information
            |--------------------------------------------------------------------------
            */

            $table->string('refund_reason', 50);

            $table->string('refund_type', 30);

            $table->longText('passenger_explanation');

            /*
            |--------------------------------------------------------------------------
            | Banking Details
            |--------------------------------------------------------------------------
            */

            $table->string('bank_name');

            $table->string('account_name');

            $table->string('account_number', 30);

            $table->string('account_type', 30);

            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

            $table->string('current_department', 40)
                ->default('REFUND');

            $table->string('current_status', 60)
                ->default('NEW_REQUEST');

            $table->string('priority', 20)
                ->default('MEDIUM');

            /*
            |--------------------------------------------------------------------------
            | Artificial Intelligence
            |--------------------------------------------------------------------------
            */

            $table->boolean('ai_flagged')
                ->default(false);

            $table->decimal('ai_score', 5, 2)
                ->nullable();

            $table->timestamp('ai_processed_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Administration
            |--------------------------------------------------------------------------
            */

            $table->boolean('consent')
                ->default(false);

            $table->text('admin_notes')
                ->nullable();

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

            $table->index('reference');
            $table->index('email');

            $table->index('current_status');
            $table->index('current_department');

            $table->index('airline_id');
            $table->index('created_by');
            $table->index('assigned_to');

            $table->index(['airline_id', 'current_status']);
            $table->index(['airline_id', 'current_department']);

            $table->index('created_at');
            $table->index('ai_flagged');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};