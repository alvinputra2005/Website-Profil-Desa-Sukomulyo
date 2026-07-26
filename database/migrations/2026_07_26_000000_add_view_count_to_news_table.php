<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table): void {
            $table->unsignedBigInteger('view_count')->default(0)->after('published_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table): void {
            $table->dropIndex(['view_count']);
            $table->dropColumn('view_count');
        });
    }
};
