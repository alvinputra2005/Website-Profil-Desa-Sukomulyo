<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('residents', 'household_id')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->dropConstrainedForeignId('household_id');
            });
        }

        if (Schema::hasColumn('residents', 'household_relationship')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->dropIndex('residents_household_relationship_index');
            });
            Schema::table('residents', function (Blueprint $table) {
                $table->dropColumn('household_relationship');
            });
        }

        Schema::dropIfExists('households');
        Schema::dropIfExists('population_yearly_snapshots');
    }

    public function down(): void
    {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->string('household_number', 30)->unique();
            $table->foreignId('head_resident_id')->nullable()->unique()->constrained('residents')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('population_areas')->nullOnDelete();
            $table->string('address', 255)->nullable();
            $table->string('social_class', 50)->nullable();
            $table->boolean('is_dtks_registered')->default(false)->index();
            $table->string('dtks_reference', 30)->nullable();
            $table->date('registered_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->string('household_relationship', 50)->nullable()->index();
            $table->foreignId('household_id')->nullable()->constrained('households')->nullOnDelete();
        });

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
};
