<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Any department can select who to query on a particular request" —
     * a side conversation on a refund, deliberately kept out of
     * RefundStatus/current_department since it isn't a workflow transition
     * and shouldn't change who currently owns the refund.
     */
    public function up(): void
    {
        Schema::create('refund_queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raised_by_user_id')->constrained('users');
            $table->foreignId('directed_to_user_id')->constrained('users');
            $table->text('message');
            $table->text('response')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['refund_id', 'resolved_at']);
            $table->index('directed_to_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_queries');
    }
};