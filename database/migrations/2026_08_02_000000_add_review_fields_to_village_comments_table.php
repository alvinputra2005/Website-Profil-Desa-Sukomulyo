<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('village_comments', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('is_visible')->index();
            $table->timestamp('reviewed_at')->nullable()->after('status');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable()->after('reviewed_by');
        });

        DB::table('village_comments')
            ->where('is_visible', true)
            ->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('village_comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['status', 'reviewed_at', 'review_note']);
        });
    }
};
