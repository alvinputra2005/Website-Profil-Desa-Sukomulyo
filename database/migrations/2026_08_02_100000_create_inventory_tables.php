<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('category', 32)->index();
            $table->string('name');
            $table->string('item_code', 64);
            $table->string('register_number', 64);
            $table->unsignedSmallInteger('acquisition_year')->index();
            $table->string('origin', 64)->index();
            $table->decimal('value', 18, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('condition', 32)->default('Baik');
            $table->string('status', 32)->default('Aktif')->index();
            $table->json('details')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['category', 'item_code', 'register_number'], 'inventory_code_register_unique');
        });

        Schema::create('inventory_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->string('asset_status', 32);
            $table->string('mutation_type', 64);
            $table->date('mutation_date')->index();
            $table->decimal('sale_price', 18, 2)->nullable();
            $table->string('recipient')->nullable();
            $table->text('notes');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_mutations');
        Schema::dropIfExists('inventory_items');
    }
};
