<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('transactions')->where('status', 'PENDING')->update(['status' => 'pending']);
        DB::table('transactions')->where('status', 'FULFILLED')->update(['status' => 'fulfilled']);
        DB::table('transactions')->where('status', 'QUEUED_FOR_RETRY')->update(['status' => 'queued_for_retry']);
        DB::table('transactions')->where('status', 'PROCESSING')->update(['status' => 'processing']);
        DB::table('transactions')->where('status', 'NEEDS_ATTENTION')->update(['status' => 'needs_attention']);
        DB::table('transactions')->where('status', 'FAILED')->update(['status' => 'over_fulfillment_flagged']);

        DB::statement("ALTER TABLE transactions MODIFY status ENUM('pending', 'fulfilled', 'queued_for_retry', 'processing', 'needs_attention', 'over_fulfillment_flagged') NOT NULL DEFAULT 'pending'");
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('claimed_by', 64)->nullable()->after('provider_used');
        });
    }

    public function down(): void
    {
        DB::table('transactions')->where('status', 'pending')->update(['status' => 'PENDING']);
        DB::table('transactions')->where('status', 'fulfilled')->update(['status' => 'FULFILLED']);
        DB::table('transactions')->where('status', 'queued_for_retry')->update(['status' => 'QUEUED_FOR_RETRY']);
        DB::table('transactions')->where('status', 'processing')->update(['status' => 'PROCESSING']);
        DB::table('transactions')->where('status', 'needs_attention')->update(['status' => 'NEEDS_ATTENTION']);
        DB::table('transactions')->where('status', 'over_fulfillment_flagged')->update(['status' => 'FAILED']);

        DB::statement("ALTER TABLE transactions MODIFY status ENUM('PENDING', 'FULFILLED', 'QUEUED_FOR_RETRY', 'PROCESSING', 'NEEDS_ATTENTION', 'FAILED') NOT NULL DEFAULT 'PENDING'");
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('claimed_by');
        });
    }
};
