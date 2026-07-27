<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('village_comments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('address', 300);
            $table->string('phone', 25);
            $table->text('comment');
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamps();
            $table->index(['is_visible', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('village_comments');
    }
};
