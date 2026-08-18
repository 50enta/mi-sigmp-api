<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locals', function (Blueprint $table) {
            $table->boolean('has_children')->default(false)->after('parent_id');
        });

        // Preserve the correct state for hierarchies created before this
        // denormalized column existed. Soft-deleted children do not count.
        $parentIds = DB::table('locals')
            ->whereNotNull('parent_id')
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('parent_id');

        if ($parentIds->isNotEmpty()) {
            DB::table('locals')
                ->whereIn('id', $parentIds)
                ->update(['has_children' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('locals', function (Blueprint $table) {
            $table->dropColumn('has_children');
        });
    }
};
