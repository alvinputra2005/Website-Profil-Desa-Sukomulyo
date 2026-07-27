<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('village_comments', function (Blueprint $table) {
            $table->string('page_key', 40)->default('identitas')->after('id');
            $table->index(['page_key', 'is_visible', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('village_comments', function (Blueprint $table) {
            $table->dropIndex(['page_key', 'is_visible', 'created_at']);
            $table->dropColumn('page_key');
        });
    }
};
