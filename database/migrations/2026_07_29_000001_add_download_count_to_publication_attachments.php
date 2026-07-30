<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publication_attachments', function (Blueprint $table): void {
            $table->unsignedBigInteger('download_count')->default(0)->after('display_order');
        });
    }

    public function down(): void
    {
        Schema::table('publication_attachments', function (Blueprint $table): void {
            $table->dropColumn('download_count');
        });
    }
};
