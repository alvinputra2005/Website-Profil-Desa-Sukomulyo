<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $t) {
            $t->id();
            $t->string('original_name');
            $t->string('stored_name');
            $t->string('disk', 50)->default('public');
            $t->string('storage_path', 500);
            $t->string('mime_type', 100)->index();
            $t->string('extension', 20);
            $t->unsignedBigInteger('file_size');
            $t->unsignedInteger('width')->nullable();
            $t->unsignedInteger('height')->nullable();
            $t->string('alt_text')->nullable();
            $t->text('caption')->nullable();
            $t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['disk', 'storage_path']);
            $t->index('original_name');
        });
        Schema::create('village_identities', function (Blueprint $t) {
            $t->id();
            $t->string('site_name')->default('Desa Sukomulyo');
            $t->string('tagline')->nullable();
            $t->string('village_code', 20)->nullable();
            $t->string('village_bps_code', 20)->nullable();
            $t->string('postal_code', 5)->nullable();
            $t->text('address')->nullable();
            $t->string('email')->nullable();
            $t->string('phone', 30)->nullable();
            $t->string('mobile', 30)->nullable();
            $t->string('website')->nullable();
            $t->string('district_name', 100)->nullable();
            $t->string('district_code', 20)->nullable();
            $t->string('district_head_name')->nullable();
            $t->string('district_head_nip', 30)->nullable();
            $t->string('regency_name', 100)->nullable();
            $t->string('regency_code', 20)->nullable();
            $t->string('province_name', 100)->nullable();
            $t->string('province_code', 20)->nullable();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
        Schema::create('village_profile_sections', function (Blueprint $t) {
            $t->id();
            $t->string('section_key', 50)->unique();
            $t->string('title');
            $t->longText('content');
            $t->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $t->string('status', 20)->default('draft');
            $t->unsignedInteger('display_order')->default(0);
            $t->string('seo_title')->nullable();
            $t->string('seo_description', 320)->nullable();
            $t->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $t->timestamps();
            $t->index(['status', 'display_order']);
        });
        Schema::create('officials', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('position');
            $t->foreignId('photo_id')->nullable()->constrained('media')->nullOnDelete();
            $t->text('biography')->nullable();
            $t->unsignedInteger('display_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['is_active', 'display_order']);
        });
        Schema::create('news_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name', 100)->unique();
            $t->string('slug', 160)->unique();
            $t->text('description')->nullable();
            $t->timestamps();
        });
        Schema::create('news', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained('news_categories')->restrictOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('excerpt')->nullable();
            $t->longText('content');
            $t->foreignId('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $t->string('status', 20)->default('draft');
            $t->timestamp('published_at')->nullable()->index();
            $t->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $t->string('seo_title')->nullable();
            $t->string('seo_description', 320)->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['status', 'published_at']);
        });
        Schema::create('publications', function (Blueprint $t) {
            $t->id();
            $t->string('type', 50)->index();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('excerpt')->nullable();
            $t->longText('content');
            $t->foreignId('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->string('status', 20)->default('draft');
            $t->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $t->string('seo_title')->nullable();
            $t->string('seo_description', 320)->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['status', 'published_at']);
            $t->index(['type', 'status', 'published_at']);
        });
        Schema::create('publication_attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('publication_id')->constrained()->cascadeOnDelete();
            $t->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $t->string('title')->nullable();
            $t->unsignedInteger('display_order')->default(0);
            $t->timestamps();
            $t->unique(['publication_id', 'media_id']);
            $t->index(['publication_id', 'display_order']);
        });
        Schema::create('statistic_datasets', function (Blueprint $t) {
            $t->id();
            $t->string('category', 50);
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->year('year');
            $t->string('unit', 50);
            $t->string('visualization_type', 30);
            $t->string('source')->nullable();
            $t->string('status', 20)->default('draft');
            $t->unsignedInteger('display_order')->default(0);
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->string('seo_title')->nullable();
            $t->string('seo_description', 320)->nullable();
            $t->timestamps();
            $t->index(['category', 'year']);
            $t->index(['status', 'display_order']);
        });
        Schema::create('statistic_values', function (Blueprint $t) {
            $t->id();
            $t->foreignId('dataset_id')->constrained('statistic_datasets')->cascadeOnDelete();
            $t->string('label');
            $t->decimal('value', 20, 4);
            $t->decimal('secondary_value', 20, 4)->nullable();
            $t->unsignedInteger('display_order')->default(0);
            $t->json('metadata_json')->nullable();
            $t->timestamps();
            $t->unique(['dataset_id', 'label']);
            $t->index(['dataset_id', 'display_order']);
        });
        Schema::create('idm_scores', function (Blueprint $t) {
            $t->id();
            $t->year('year')->unique();
            $t->decimal('idm_score', 8, 4);
            $t->decimal('iks_score', 8, 4);
            $t->decimal('ike_score', 8, 4);
            $t->decimal('ikl_score', 8, 4);
            $t->string('status_label', 100);
            $t->string('source')->nullable();
            $t->timestamps();
        });
        Schema::create('map_layers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('geometry_type', 30);
            $t->json('style_json')->nullable();
            $t->unsignedInteger('display_order')->default(0);
            $t->boolean('is_visible')->default(true);
            $t->timestamps();
            $t->index(['is_visible', 'display_order']);
        });
        Schema::create('map_features', function (Blueprint $t) {
            $t->id();
            $t->foreignId('layer_id')->constrained('map_layers')->cascadeOnDelete();
            $t->string('name');
            $t->text('description')->nullable();
            $t->json('geometry_json');
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->foreignId('photo_id')->nullable()->constrained('media')->nullOnDelete();
            $t->json('properties_json')->nullable();
            $t->boolean('is_visible')->default(true);
            $t->timestamps();
            $t->index(['layer_id', 'is_visible']);
            $t->index(['latitude', 'longitude']);
        });
        Schema::create('galleries', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->date('event_date')->nullable();
            $t->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $t->string('status', 20)->default('draft');
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->string('seo_title')->nullable();
            $t->string('seo_description', 320)->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['status', 'event_date']);
        });
        Schema::create('gallery_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gallery_id')->constrained()->cascadeOnDelete();
            $t->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $t->text('caption')->nullable();
            $t->unsignedInteger('display_order')->default(0);
            $t->timestamps();
            $t->unique(['gallery_id', 'media_id']);
            $t->index(['gallery_id', 'display_order']);
        });
        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $t->string('action', 100)->index();
            $t->string('module', 100);
            $t->string('record_type')->nullable();
            $t->unsignedBigInteger('record_id')->nullable();
            $t->text('description')->nullable();
            $t->json('old_values_json')->nullable();
            $t->json('new_values_json')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['module', 'created_at']);
            $t->index(['record_type', 'record_id']);
            $t->index('created_at');
        });
        Schema::table('contact_messages', function (Blueprint $t) {
            $t->index('created_at');
            $t->index('email');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', fn (Blueprint $t) => $t->dropIndex(['created_at']));
        foreach (['activity_logs', 'gallery_items', 'galleries', 'map_features', 'map_layers', 'idm_scores', 'statistic_values', 'statistic_datasets', 'publication_attachments', 'publications', 'news', 'news_categories', 'officials', 'village_profile_sections', 'village_identities', 'media'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
