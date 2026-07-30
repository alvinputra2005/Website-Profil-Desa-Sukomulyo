<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $importsExisted = Schema::hasTable('statistic_imports');

        if (! $importsExisted) {
            Schema::create('statistic_imports', function (Blueprint $table) {
                $table->id();
                $table->char('public_id', 26)->unique();
                $table->string('original_filename');
                $table->string('file_path');
                $table->char('file_sha256', 64)->index();
                $table->string('schema_name')->nullable();
                $table->string('schema_version', 20)->nullable();
                $table->string('status', 30)->default('uploaded');
                $table->unsignedInteger('total_datasets')->default(0);
                $table->unsignedInteger('total_rows')->default(0);
                $table->json('warnings_json')->nullable();
                $table->json('errors_json')->nullable();
                $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'created_at']);
            });
        } else {
            Schema::table('statistic_imports', function (Blueprint $table) {
                if (! Schema::hasColumn('statistic_imports', 'public_id')) {
                    $table->char('public_id', 26)->nullable()->unique();
                }
                if (! Schema::hasColumn('statistic_imports', 'file_sha256')) {
                    $table->char('file_sha256', 64)->nullable()->index();
                }
                if (! Schema::hasColumn('statistic_imports', 'schema_name')) {
                    $table->string('schema_name')->nullable();
                }
                if (! Schema::hasColumn('statistic_imports', 'schema_version')) {
                    $table->string('schema_version', 20)->nullable();
                }
                if (! Schema::hasColumn('statistic_imports', 'total_datasets')) {
                    $table->unsignedInteger('total_datasets')->default(0);
                }
                if (! Schema::hasColumn('statistic_imports', 'warnings_json')) {
                    $table->json('warnings_json')->nullable();
                }
                if (! Schema::hasColumn('statistic_imports', 'errors_json')) {
                    $table->json('errors_json')->nullable();
                }
                if (! Schema::hasColumn('statistic_imports', 'imported_by')) {
                    $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('statistic_imports', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable();
                }
                if (! Schema::hasIndex('statistic_imports', ['status', 'created_at'])) {
                    $table->index(['status', 'created_at']);
                }
            });
        }

        if (! Schema::hasTable('statistic_categories')) {
            Schema::create('statistic_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 120)->unique();
                $table->text('description')->nullable();
                $table->string('icon', 80)->nullable();
                $table->unsignedInteger('display_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['is_active', 'display_order']);
            });
        }

        Schema::table('statistic_datasets', function (Blueprint $table) {
            if (! Schema::hasColumn('statistic_datasets', 'statistic_import_id')) {
                $table->foreignId('statistic_import_id')->nullable()->constrained('statistic_imports')->nullOnDelete();
            }
            if (! Schema::hasColumn('statistic_datasets', 'statistic_category_id')) {
                $table->foreignId('statistic_category_id')->nullable()->constrained('statistic_categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('statistic_datasets', 'dataset_key')) {
                $table->string('dataset_key')->nullable()->unique();
            }
            if (! Schema::hasColumn('statistic_datasets', 'family')) {
                $table->string('family', 30)->nullable();
            }
            if (! Schema::hasColumn('statistic_datasets', 'table_number')) {
                $table->string('table_number', 30)->nullable();
            }
            if (! Schema::hasColumn('statistic_datasets', 'period')) {
                $table->string('period', 50)->nullable();
            }
            if (! Schema::hasColumn('statistic_datasets', 'short_title')) {
                $table->string('short_title')->nullable();
            }
            if (! Schema::hasColumn('statistic_datasets', 'region_type')) {
                $table->string('region_type', 100)->nullable();
            }
            if (! Schema::hasColumn('statistic_datasets', 'columns_json')) {
                $table->json('columns_json')->nullable();
            }
            if (! Schema::hasColumn('statistic_datasets', 'totals_json')) {
                $table->json('totals_json')->nullable();
            }
            if (! Schema::hasColumn('statistic_datasets', 'source_metadata_json')) {
                $table->json('source_metadata_json')->nullable();
            }
            if (! Schema::hasColumn('statistic_datasets', 'visualization_config_json')) {
                $table->json('visualization_config_json')->nullable();
            }
            if (! Schema::hasColumn('statistic_datasets', 'requires_manual_review')) {
                $table->boolean('requires_manual_review')->default(false);
            }
            if (! Schema::hasColumn('statistic_datasets', 'deleted_at')) {
                $table->softDeletes();
            }
            if (! Schema::hasIndex('statistic_datasets', 'stat_datasets_category_status_order_idx')) {
                $table->index(['statistic_category_id', 'status', 'display_order'], 'stat_datasets_category_status_order_idx');
            }
            if (! Schema::hasIndex('statistic_datasets', ['period', 'status'])) {
                $table->index(['period', 'status']);
            }
        });

        if (! Schema::hasTable('statistic_rows')) {
            Schema::create('statistic_rows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('statistic_dataset_id')->constrained('statistic_datasets')->cascadeOnDelete();
                $table->string('area_code', 100)->nullable();
                $table->string('area_name')->nullable();
                $table->json('values_json');
                $table->unsignedInteger('display_order')->default(0);
                $table->timestamps();
                $table->index(['statistic_dataset_id', 'display_order'], 'stat_rows_dataset_order_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('statistic_rows');

        Schema::table('statistic_datasets', function (Blueprint $table) {
            if (Schema::hasIndex('statistic_datasets', 'stat_datasets_category_status_order_idx')) {
                $table->dropIndex('stat_datasets_category_status_order_idx');
            }
            if (Schema::hasIndex('statistic_datasets', ['period', 'status'])) {
                $table->dropIndex(['period', 'status']);
            }
            if (Schema::hasColumn('statistic_datasets', 'dataset_key')) {
                $table->dropUnique(['dataset_key']);
            }
            if (Schema::hasColumn('statistic_datasets', 'statistic_category_id')) {
                $table->dropConstrainedForeignId('statistic_category_id');
            }
            if (Schema::hasColumn('statistic_datasets', 'statistic_import_id')) {
                $table->dropConstrainedForeignId('statistic_import_id');
            }
            $columns = array_values(array_filter([
                'dataset_key',
                'family',
                'table_number',
                'period',
                'short_title',
                'region_type',
                'columns_json',
                'totals_json',
                'source_metadata_json',
                'visualization_config_json',
                'requires_manual_review',
                'deleted_at',
            ], fn (string $column): bool => Schema::hasColumn('statistic_datasets', $column)));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::dropIfExists('statistic_categories');

        if (Schema::hasColumn('statistic_imports', 'disk')) {
            Schema::table('statistic_imports', function (Blueprint $table) {
                if (Schema::hasIndex('statistic_imports', ['status', 'created_at'])) {
                    $table->dropIndex(['status', 'created_at']);
                }
                if (Schema::hasColumn('statistic_imports', 'public_id')) {
                    $table->dropUnique(['public_id']);
                }
                if (Schema::hasColumn('statistic_imports', 'file_sha256')) {
                    $table->dropIndex(['file_sha256']);
                }
                $columns = array_values(array_filter([
                    'public_id', 'file_sha256', 'schema_name', 'schema_version', 'total_datasets',
                    'warnings_json', 'errors_json', 'imported_by', 'completed_at',
                ], fn (string $column): bool => Schema::hasColumn('statistic_imports', $column)));
                if (Schema::hasColumn('statistic_imports', 'imported_by')) {
                    $table->dropConstrainedForeignId('imported_by');
                    $columns = array_values(array_diff($columns, ['imported_by']));
                }
                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        } else {
            Schema::dropIfExists('statistic_imports');
        }
    }
};
