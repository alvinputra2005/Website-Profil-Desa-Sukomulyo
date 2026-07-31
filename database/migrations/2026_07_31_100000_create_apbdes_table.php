<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apbdes', function (Blueprint $table) {
            $table->id();
            $table->year('year')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->boolean('is_partial_year')->default(false);
            $table->decimal('income_budget', 20, 2)->default(0);
            $table->decimal('income_realization', 20, 2)->default(0);
            $table->decimal('spending_budget', 20, 2)->default(0);
            $table->decimal('spending_realization', 20, 2)->default(0);
            $table->decimal('financing_receipt', 20, 2)->default(0);
            $table->decimal('financing_expenditure', 20, 2)->default(0);
            $table->json('revenue')->nullable();
            $table->json('spending')->nullable();
            $table->json('programs')->nullable();
            $table->longText('problems')->nullable();
            $table->longText('solutions')->nullable();
            $table->text('programs_note')->nullable();
            $table->text('quarters_note')->nullable();
            $table->longText('data_quality_notes')->nullable();
            $table->string('source_document')->nullable();
            $table->text('source_reference')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $this->importExistingLppdData();
    }

    public function down(): void
    {
        Schema::dropIfExists('apbdes');
    }

    private function importExistingLppdData(): void
    {
        $path = public_path('data/apbdes-sukomulyo-2019-2026.json');

        if (! is_file($path)) {
            return;
        }

        $payload = json_decode((string) file_get_contents($path), true);
        $years = is_array($payload['years'] ?? null) ? $payload['years'] : [];
        $sourceDocument = data_get($payload, 'source.primary_document', 'LPPD Desa Sukomulyo');
        $now = now();

        foreach ($years as $year => $detail) {
            if (! is_array($detail) || ! is_numeric($year)) {
                continue;
            }

            $summary = is_array($detail['summary'] ?? null) ? $detail['summary'] : [];
            $financing = is_array($detail['financing_summary'] ?? null) ? $detail['financing_summary'] : [];
            $sourceReference = collect($detail['source_reference'] ?? [])
                ->map(fn ($value, $key) => ucfirst(str_replace('_', ' ', (string) $key)).': '.$value)
                ->implode('; ');
            $isPublished = (int) $year <= 2025;

            DB::table('apbdes')->insert([
                'year' => (int) $year,
                'title' => 'APBDes Desa Sukomulyo Tahun '.$year,
                'description' => 'Rincian pendapatan, belanja, pembiayaan, realisasi, dan catatan APBDes Desa Sukomulyo.',
                'status' => $isPublished ? 'published' : 'draft',
                'is_partial_year' => (bool) ($summary['is_partial_year'] ?? false),
                'income_budget' => (float) ($summary['income'] ?? 0),
                'income_realization' => (float) ($summary['income_realization'] ?? 0),
                'spending_budget' => (float) ($summary['spending'] ?? 0),
                'spending_realization' => (float) ($summary['realization'] ?? 0),
                'financing_receipt' => (float) ($financing['receipt'] ?? 0),
                'financing_expenditure' => (float) ($financing['expenditure'] ?? 0),
                'revenue' => json_encode($detail['revenue'] ?? [], JSON_UNESCAPED_UNICODE),
                'spending' => json_encode($detail['spending'] ?? [], JSON_UNESCAPED_UNICODE),
                'programs' => json_encode($detail['programs'] ?? [], JSON_UNESCAPED_UNICODE),
                'programs_note' => $detail['programs_note'] ?? null,
                'quarters_note' => $detail['quarters_note'] ?? null,
                'data_quality_notes' => implode("\n", $detail['data_quality'] ?? []),
                'source_document' => $sourceDocument,
                'source_reference' => $sourceReference ?: null,
                'published_at' => $isPublished ? $now : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
