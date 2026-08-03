<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveLetterServiceRequest;
use App\Models\LetterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LetterServiceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', LetterService::class);

        $status = $request->query('status');

        return view('admin.letter-services.index', [
            'services' => LetterService::withCount('applications')
                ->when($status === 'active', fn ($query) => $query->where('is_active', true))
                ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
                ->orderBy('display_order')
                ->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', LetterService::class);

        return view('admin.letter-services.form', ['letterService' => new LetterService]);
    }

    public function store(SaveLetterServiceRequest $request): RedirectResponse
    {
        $data = $this->data($request);
        $data['created_by'] = $request->user()->id;
        LetterService::create($data);

        return redirect()->route('admin.letter-services.index')->with('success', 'Layanan surat dibuat.');
    }

    public function edit(LetterService $letterService): View
    {
        $this->authorize('update', $letterService);

        return view('admin.letter-services.form', compact('letterService'));
    }

    public function update(SaveLetterServiceRequest $request, LetterService $letterService): RedirectResponse
    {
        $letterService->update([...$this->data($request), 'updated_by' => $request->user()->id]);

        return redirect()->route('admin.letter-services.index')->with('success', 'Layanan surat diperbarui.');
    }

    public function destroy(LetterService $letterService): RedirectResponse
    {
        $this->authorize('delete', $letterService);
        abort_if($letterService->applications()->exists(), 422, 'Layanan yang sudah memiliki permohonan tidak dapat dihapus.');
        $letterService->delete();

        return back()->with('success', 'Layanan surat dihapus.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', LetterService::class);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:letter_services,id'],
        ]);

        $services = LetterService::whereKey($data['ids'])->withCount('applications')->get();
        if ($services->contains(fn (LetterService $service): bool => $service->applications_count > 0)) {
            throw ValidationException::withMessages(['ids' => 'Ada layanan terpilih yang sudah memiliki permohonan dan tidak dapat dihapus.']);
        }

        DB::transaction(function () use ($services): void {
            $services->each(function (LetterService $service): void {
                $this->authorize('delete', $service);
                $service->delete();
            });
        });

        return back()->with('success', $services->count().' layanan surat dihapus.');
    }

    private function data(SaveLetterServiceRequest $request): array
    {
        $validated = $request->validated();
        $requirements = collect($validated['requirements'])->values()->map(function (array $requirement, int $index): array {
            $conditionField = trim((string) ($requirement['condition_field'] ?? ''));
            $conditionValues = $this->conditionValues((string) ($requirement['condition_values'] ?? ''));
            $normalized = [
                'key' => Str::slug($requirement['code']),
                'label' => trim(strip_tags($requirement['label'])),
                'description' => trim(strip_tags($requirement['description'] ?? '')),
                'required' => $conditionField === '' && (bool) ($requirement['required'] ?? false),
                'display_order' => $index,
            ];

            if ($conditionField !== '' && $conditionValues !== []) {
                $normalized['required_when'] = [
                    'field' => $conditionField,
                    'values' => $conditionValues,
                ];
            }

            return $normalized;
        })->all();

        $data = [
            ...collect($validated)->only(['name', 'description', 'icon', 'processing_days', 'fee_information', 'pickup_instructions', 'display_order'])->all(),
            'slug' => $request->route('letterService')?->slug ?: $this->uniqueSlug($validated['name']),
            'code' => Str::upper($validated['code']),
            'requirements_json' => $requirements,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->boolean('form_fields_present')) {
            $data['form_schema_json'] = collect($validated['form_fields'] ?? [])->values()->map(function (array $field): array {
                $normalized = [
                    'key' => $field['key'],
                    'label' => trim(strip_tags($field['label'])),
                    'type' => $field['type'],
                    'required' => (bool) ($field['required'] ?? false),
                ];

                $supportsLimits = in_array($field['type'], ['text', 'textarea', 'number'], true);
                if ($supportsLimits && isset($field['min']) && $field['min'] !== null && $field['min'] !== '') {
                    $normalized['min'] = (int) $field['min'];
                }
                if ($supportsLimits && isset($field['max']) && $field['max'] !== null && $field['max'] !== '') {
                    $normalized['max'] = (int) $field['max'];
                }
                if (in_array($field['type'], ['select', 'radio'], true)) {
                    $normalized['options'] = $this->formOptions((string) ($field['options'] ?? ''));
                }

                return $normalized;
            })->all();
        }

        return $data;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'jenis-surat';
        $slug = $base;
        $suffix = 2;

        while (LetterService::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function conditionValues(string $raw): array
    {
        return collect(explode(',', $raw))->map(fn (string $value): string => trim($value))->filter()->unique()->values()->all();
    }

    private function formOptions(string $raw): array
    {
        return collect(preg_split('/\r\n|\r|\n/', trim($raw)) ?: [])
            ->filter(fn (string $line): bool => trim($line) !== '')
            ->mapWithKeys(function (string $line): array {
                [$value, $label] = array_map('trim', explode('=', $line, 2));

                return [$value => trim(strip_tags($label))];
            })
            ->all();
    }
}
