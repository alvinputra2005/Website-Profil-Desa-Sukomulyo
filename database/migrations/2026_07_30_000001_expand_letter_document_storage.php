<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('letter_document_requirements')) {
            Schema::create('letter_document_requirements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('letter_service_id')->constrained('letter_services')->cascadeOnDelete();
                $table->string('code', 50);
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->json('accepted_extensions')->nullable();
                $table->json('accepted_mime_types')->nullable();
                $table->unsignedInteger('max_size_kb')->default(5120);
                $table->boolean('is_required')->default(true);
                $table->unsignedInteger('display_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['letter_service_id', 'code']);
            });
        }
        if (Schema::hasTable('letter_application_documents')) {
            Schema::table('letter_application_documents', function (Blueprint $table) {
                $table->ulid('public_id')->nullable()->unique();
                $table->string('stored_extension', 20)->nullable();
                $table->unsignedBigInteger('size_bytes')->nullable();
                $table->string('upload_status', 30)->default('uploaded');
                $table->string('review_status', 30)->default('pending_review')->change();
                $table->text('admin_note')->nullable();
                $table->timestamp('uploaded_at')->nullable();
                $table->string('checksum_sha256', 64)->nullable();
                $table->string('etag', 255)->nullable();
            });
        }
    }
    public function down(): void {}
};
