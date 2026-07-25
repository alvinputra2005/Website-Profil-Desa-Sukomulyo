<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('population_areas', function (Blueprint $table) {
            $table->id();
            $table->string('hamlet', 100);
            $table->string('rw', 3)->default('');
            $table->string('rt', 3)->default('');
            $table->timestamps();
            $table->unique(['hamlet', 'rw', 'rt']);
        });

        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->char('nik', 16)->unique();
            $table->string('name', 100)->index();
            $table->string('sex', 1)->index();
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable()->index();
            $table->string('religion', 30)->nullable()->index();
            $table->string('marital_status', 30)->nullable()->index();
            $table->string('citizenship', 10)->default('WNI')->index();
            $table->string('education', 100)->nullable()->index();
            $table->string('occupation', 100)->nullable()->index();
            $table->string('blood_type', 3)->nullable();
            $table->char('father_nik', 16)->nullable();
            $table->string('father_name', 100)->nullable();
            $table->char('mother_nik', 16)->nullable();
            $table->string('mother_name', 100)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('current_address', 255)->nullable();
            $table->string('previous_address', 255)->nullable();
            $table->foreignId('area_id')->nullable()->constrained('population_areas')->nullOnDelete();
            $table->string('family_relationship', 50)->nullable()->index();
            $table->string('household_relationship', 50)->nullable()->index();
            $table->string('resident_status', 30)->default('permanent')->index();
            $table->string('status', 30)->default('active')->index();
            $table->date('registered_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'sex']);
            $table->index(['area_id', 'status']);
        });

        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->char('family_card_number', 16)->unique();
            $table->foreignId('head_resident_id')->nullable()->unique()->constrained('residents')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('population_areas')->nullOnDelete();
            $table->string('address', 255)->nullable();
            $table->string('social_class', 50)->nullable();
            $table->date('registered_at')->nullable();
            $table->date('issued_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

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
            $table->foreignId('family_id')->nullable()->after('area_id')->constrained('families')->nullOnDelete();
            $table->foreignId('household_id')->nullable()->after('family_id')->constrained('households')->nullOnDelete();
        });

        Schema::create('population_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100)->index();
            $table->string('category', 100)->index();
            $table->string('establishment_decree', 100)->nullable();
            $table->foreignId('chairperson_id')->nullable()->constrained('residents')->nullOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('population_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('population_groups')->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained('residents')->restrictOnDelete();
            $table->string('member_number', 30)->nullable();
            $table->string('position', 50)->default('Anggota');
            $table->string('appointment_decree', 100)->nullable();
            $table->date('appointment_date')->nullable();
            $table->string('dismissal_decree', 100)->nullable();
            $table->date('dismissal_date')->nullable();
            $table->string('period', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['group_id', 'resident_id']);
            $table->unique(['group_id', 'member_number']);
        });

        Schema::create('resident_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('residents')->restrictOnDelete();
            $table->string('event_type', 30)->index();
            $table->date('event_date')->index();
            $table->date('reported_at')->nullable();
            $table->string('resident_name', 100);
            $table->char('nik', 16);
            $table->string('sex', 1);
            $table->char('family_card_number', 16)->nullable();
            $table->string('origin_address', 255)->nullable();
            $table->string('destination_address', 255)->nullable();
            $table->string('cause', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['event_type', 'event_date', 'sex']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resident_events');
        Schema::dropIfExists('population_group_members');
        Schema::dropIfExists('population_groups');

        Schema::table('residents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('household_id');
            $table->dropConstrainedForeignId('family_id');
        });

        Schema::dropIfExists('households');
        Schema::dropIfExists('families');
        Schema::dropIfExists('residents');
        Schema::dropIfExists('population_areas');
    }
};
