<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveLetterServiceRequest;
use App\Models\LetterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LetterServiceController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', LetterService::class);

        return view('admin.letter-services.index', ['services' => LetterService::withCount('applications')->orderBy('display_order')->get()]);
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

    private function data(SaveLetterServiceRequest $request): array
    {
        $validated = $request->validated();

        return [
            ...collect($validated)->only(['name', 'description', 'icon', 'processing_days', 'fee_information', 'pickup_instructions', 'display_order'])->all(),
            'slug' => $request->route('letterService')?->slug ?: $this->uniqueSlug($validated['name']),
            'code' => Str::upper($validated['code']),
            'requirements_json' => collect($validated['requirements'])->values()->map(fn (array $requirement, int $index) => [
                'key' => Str::slug($requirement['code']),
                'label' => trim(strip_tags($requirement['label'])),
                'description' => trim(strip_tags($requirement['description'] ?? '')),
                'required' => (bool) ($requirement['required'] ?? false),
                'display_order' => $index,
            ])->all(),
            'is_active' => $request->boolean('is_active'),
        ];
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
}
