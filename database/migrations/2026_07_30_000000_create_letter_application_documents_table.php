<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('letter_application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_application_id')->constrained()->cascadeOnDelete();
            $table->string('requirement_key', 100);
            $table->string('label', 180);
            $table->string('disk', 40);
            $table->string('path', 500);
            $table->string('original_name', 255);
            $table->string('mime_type', 120);
            $table->unsignedInteger('file_size');
            $table->string('review_status', 30)->default('pending');
            $table->text('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['letter_application_id', 'requirement_key']);
        });
    }

    public function down(): void { Schema::dropIfExists('letter_application_documents'); }
};
