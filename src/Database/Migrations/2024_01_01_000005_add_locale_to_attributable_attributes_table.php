<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributable_attributes', function (Blueprint $table) {
            $table->string('locale', 10)->nullable()->after('attribute_id');
            
            // Update unique constraint to include locale
            $table->dropUnique('unique_attributable_attribute');
            $table->unique(['attributable_type', 'attributable_id', 'attribute_id', 'locale'], 'unique_attributable_attribute_locale');
            
            // Add index for locale
            $table->index('locale', 'idx_locale');
        });
    }

    public function down(): void
    {
        Schema::table('attributable_attributes', function (Blueprint $table) {
            $table->dropIndex('idx_locale');
            $table->dropUnique('unique_attributable_attribute_locale');
        });

        // After dropping the locale-aware unique index, multiple rows may share the same
        // (attributable_type, attributable_id, attribute_id). Remove duplicates before
        // restoring the legacy unique index (required for SQLite / RefreshDatabase rollbacks).
        $keepIds = DB::table('attributable_attributes')
            ->selectRaw('MIN(id) as id')
            ->groupBy('attributable_type', 'attributable_id', 'attribute_id')
            ->pluck('id');

        if ($keepIds->isNotEmpty()) {
            DB::table('attributable_attributes')->whereNotIn('id', $keepIds->all())->delete();
        }

        Schema::table('attributable_attributes', function (Blueprint $table) {
            $table->unique(['attributable_type', 'attributable_id', 'attribute_id'], 'unique_attributable_attribute');
            $table->dropColumn('locale');
        });
    }
};

