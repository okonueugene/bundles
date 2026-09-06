<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('mpesa_receipt_number', 32)->unique();
            $table->string('phone_number', 15);
            $table->decimal('amount', 10, 2);
            $table->string('package_code', 64)->nullable();
            $table->enum('status', [
                'PENDING',
                'FULFILLED',
                'QUEUED_FOR_RETRY',
                'PROCESSING',
                'NEEDS_ATTENTION',
                'FAILED'
            ])->default('PENDING');
            $table->string('provider_used', 32)->nullable();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
