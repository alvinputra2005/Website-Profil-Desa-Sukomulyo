<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('population_yearly_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();
            $table->unsignedBigInteger('male_count')->default(0);
            $table->unsignedBigInteger('female_count')->default(0);
            $table->date('reference_date')->nullable();
            $table->string('source', 150)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('population_yearly_snapshots');
    }
};
