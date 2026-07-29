<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_services', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->string('code', 20)->unique();
            $table->text('description');
            $table->json('requirements_json');
            $table->json('form_schema_json')->nullable();
            $table->unsignedTinyInteger('processing_days')->default(3);
            $table->string('fee_information', 100)->default('Gratis');
            $table->text('pickup_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_active', 'display_order']);
        });

        Schema::create('letter_applications', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->string('application_number', 40)->unique();
            $table->char('tracking_token_hash', 64)->unique();
            $table->string('tracking_pin_hash');
            $table->timestamp('tracking_expires_at')->nullable();
            $table->foreignId('letter_service_id')->constrained('letter_services')->restrictOnDelete();
            $table->foreignId('resident_id')->nullable()->constrained('residents')->nullOnDelete();
            $table->json('service_snapshot_json');
            $table->string('applicant_name', 150);
            $table->text('applicant_nik');
            $table->char('applicant_nik_hash', 64)->index();
            $table->text('applicant_phone');
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->char('sex', 1)->nullable();
            $table->text('address');
            $table->string('hamlet', 100)->nullable();
            $table->string('rt', 3)->nullable();
            $table->string('rw', 3)->nullable();
            $table->text('purpose');
            $table->json('form_data_json')->nullable();
            $table->string('status', 40)->index();
            $table->text('public_note')->nullable();
            $table->text('internal_note')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('whatsapp_confirmation_opened_at')->nullable();
            $table->timestamp('whatsapp_admin_opened_at')->nullable();
            $table->foreignId('last_whatsapp_opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'submitted_at']);
            $table->index(['letter_service_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });

        Schema::create('letter_application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_application_id')->constrained('letter_applications')->cascadeOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->text('public_note')->nullable();
            $table->text('internal_note')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['letter_application_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_application_status_histories');
        Schema::dropIfExists('letter_applications');
        Schema::dropIfExists('letter_services');
    }
};
