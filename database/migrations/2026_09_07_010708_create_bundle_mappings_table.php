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
        Schema::create('bundle_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('network', 32)->default('safaricom');
            $table->decimal('amount', 10, 2);
            $table->string('package_code', 64);
            $table->string('fallback_package_code', 64)->nullable();
            $table->time('available_from')->nullable();
            $table->time('available_until')->nullable();
            $table->string('description', 255);
            $table->timestamps();

            $table->unique(['network', 'amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundle_mappings');
    }
};
