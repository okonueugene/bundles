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
                'pending',
                'fulfilled',
                'queued_for_retry',
                'processing',
                'needs_attention',
                'over_fulfillment_flagged',
            ])->default('pending');
            $table->string('provider_used', 32)->nullable();
            $table->string('claimed_by', 64)->nullable();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->unsignedInteger('background_attempt_count')->default(0);
            $table->timestamp('alert_sent_at')->nullable();
            $table->string('alert_channel', 16)->nullable();
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
