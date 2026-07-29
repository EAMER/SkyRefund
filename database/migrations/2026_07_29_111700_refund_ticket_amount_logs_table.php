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
        Schema::create('refund_ticket_amount_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('refund_ticket_id')->constrained()->cascadeOnDelete();
    $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
    $table->decimal('old_amount', 10, 2);
    $table->decimal('new_amount', 10, 2);
    $table->string('reason');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_ticket_amount_logs');
    }
};
