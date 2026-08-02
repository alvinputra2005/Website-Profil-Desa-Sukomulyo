<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('letter_applications')) {
            return;
        }

        Schema::table('letter_applications', function (Blueprint $table): void {
            $table->timestamp('submitted_at')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('letter_applications')) {
            return;
        }

        DB::table('letter_applications')
            ->whereNull('submitted_at')
            ->update(['submitted_at' => DB::raw('created_at')]);

        Schema::table('letter_applications', function (Blueprint $table): void {
            $table->timestamp('submitted_at')->useCurrent()->nullable(false)->change();
        });
    }
};
