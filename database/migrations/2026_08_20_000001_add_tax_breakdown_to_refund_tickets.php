<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the Refund Officer's calculation fields to refund_tickets.
     * Kept per-ticket (not per-refund) since RefundTicket is already the
     * line item carrying fare_paid / refund_amount for a given ticket —
     * a refund can cover multiple tickets, each with its own fare and taxes.
     */
    public function up(): void
    {
        Schema::table('refund_tickets', function (Blueprint $table) {
            $table->decimal('nuc', 12, 2)->default(0)->after('fare_paid');
            $table->decimal('government_tax_ng', 12, 2)->default(0)->after('nuc');
            $table->decimal('security_tax_yq', 12, 2)->default(0)->after('government_tax_ng');
            $table->decimal('airport_tax_qt', 12, 2)->default(0)->after('security_tax_yq');
            $table->decimal('insurance', 12, 2)->default(0)->after('airport_tax_qt');

            $table->boolean('is_no_show')->default(false)->after('insurance');
            $table->decimal('no_show_fee', 12, 2)->default(0)->after('is_no_show');

            // Server-computed sum of the above, kept as a stored column so
            // it's queryable/sortable without recomputing on every read.
            $table->decimal('total_deduction', 12, 2)->default(0)->after('no_show_fee');
        });
    }

    public function down(): void
    {
        Schema::table('refund_tickets', function (Blueprint $table) {
            $table->dropColumn([
                'nuc', 'government_tax_ng', 'security_tax_yq', 'airport_tax_qt',
                'insurance', 'is_no_show', 'no_show_fee', 'total_deduction',
            ]);
        });
    }
};