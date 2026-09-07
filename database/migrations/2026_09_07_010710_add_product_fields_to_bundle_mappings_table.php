<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $hasSlug = Schema::hasColumn('bundle_mappings', 'slug');
        $hasType = Schema::hasColumn('bundle_mappings', 'type');
        $hasValidity = Schema::hasColumn('bundle_mappings', 'validity');

        Schema::table('bundle_mappings', function (Blueprint $table) use ($hasSlug, $hasType, $hasValidity) {
            if (! $hasSlug) {
                $table->string('slug', 64)->nullable()->after('network');
            }

            if (! $hasType) {
                $table->string('type', 16)->default('data')->after('amount');
            }

            if (! $hasValidity) {
                $table->string('validity', 64)->nullable()->after('description');
            }
        });

        foreach (DB::table('bundle_mappings')->whereNull('slug')->orderBy('id')->get() as $mapping) {
            DB::table('bundle_mappings')->where('id', $mapping->id)->update([
                'slug' => Str::slug($mapping->description.'-'.$mapping->amount).'-'.$mapping->id,
            ]);
        }

        if (! $hasSlug) {
            Schema::table('bundle_mappings', function (Blueprint $table) {
                $table->string('slug', 64)->nullable(false)->unique()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('bundle_mappings', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['slug', 'type', 'validity'],
                fn (string $column) => Schema::hasColumn('bundle_mappings', $column)
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
