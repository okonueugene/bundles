<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('checkout_request_id', 64)->nullable()->unique()->after('order_reference');
        });

        DB::statement("ALTER TABLE transactions MODIFY status ENUM(
            'pending', 'fulfilled', 'queued_for_retry', 'processing',
            'needs_attention', 'over_fulfillment_flagged', 'payment_failed'
        ) NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('checkout_request_id');
        });

        DB::statement("ALTER TABLE transactions MODIFY status ENUM(
            'pending', 'fulfilled', 'queued_for_retry', 'processing',
            'needs_attention', 'over_fulfillment_flagged'
        ) NOT NULL DEFAULT 'pending'");
    }
};
