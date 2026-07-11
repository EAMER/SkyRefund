<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->foreignId('airline_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            $table->enum('department', [
                'REFUND',
                'COMMERCIAL',
                'AUDIT',
                'FINANCE',
                'TREASURY',
            ])->default('REFUND');

            $table->enum('role', [
                'SUPER_ADMIN',
                'REFUND_OFFICER',
                'COMMERCIAL',
                'AUDIT',
                'FINANCE',
                'TREASURY',
            ])->default('REFUND_OFFICER');

            $table->boolean('active')
                ->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign(['airline_id']);

            $table->dropColumn([
                'airline_id',
                'department',
                'role',
                'active',
            ]);
        });
    }
};