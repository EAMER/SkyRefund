<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refund_attachments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('refund_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('type',[
                'signature',
                'passenger_id',
                'account_holder_id',
                'authorization_letter',
                'other'
            ]);

            $table->string('original_name');

            $table->string('stored_name');

            $table->string('path');

            $table->string('mime_type');

            $table->unsignedBigInteger('size');

            $table->timestamps();

            $table->index([
                'refund_id',
                'type'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_attachments');
    }
};