<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->timestamp('manually_resolved_at')->nullable()->after('alert_channel');
            $table->text('resolution_note')->nullable()->after('manually_resolved_at');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['manually_resolved_at', 'resolution_note']);
        });
    }
};
