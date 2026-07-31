<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStatisticCategoryRequest;
use App\Models\StatisticCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StatisticCategoryController extends Controller
{
    public function __construct(private ActivityLogger $logger) {}

    public function create(): View
    {
        $this->authorize('manage-data');

        return view('admin.statistics.category-create');
    }

    public function store(StoreStatisticCategoryRequest $request): RedirectResponse
    {
        $this->authorize('manage-data');
        $validated = $request->validated();
        $name = trim($validated['name']);
        $category = StatisticCategory::query()->create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'description' => $validated['description'] ?? null,
            'icon' => filled($validated['icon'] ?? null) ? trim($validated['icon']) : 'fa-table',
            'display_order' => ((int) StatisticCategory::query()->max('display_order')) + 1,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        $this->logger->log('created', 'statistics_category', $category, null, $category->toArray());

        return redirect()->route('admin.statistics.categories.show', $category->slug)
            ->with('success', 'Kategori dataset berhasil dibuat.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'kategori-statistik';
        $slug = $base;
        $number = 2;
        while (StatisticCategory::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$number++;
        }

        return $slug;
    }
}
