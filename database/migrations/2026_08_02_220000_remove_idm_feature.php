<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $categoryIds = Schema::hasTable('statistic_categories')
            ? DB::table('statistic_categories')->where('slug', 'idm')->pluck('id')
            : collect();

        if (Schema::hasTable('statistic_datasets')) {
            DB::table('statistic_datasets')
                ->where(function ($query) use ($categoryIds): void {
                    $query->where('category', 'idm');

                    if ($categoryIds->isNotEmpty() && Schema::hasColumn('statistic_datasets', 'statistic_category_id')) {
                        $query->orWhereIn('statistic_category_id', $categoryIds);
                    }
                })
                ->delete();
        }

        if ($categoryIds->isNotEmpty()) {
            DB::table('statistic_categories')->whereIn('id', $categoryIds)->delete();
        }

        Schema::dropIfExists('idm_scores');
    }

    public function down(): void
    {
        // IDM has been permanently removed from the application.
    }
};
