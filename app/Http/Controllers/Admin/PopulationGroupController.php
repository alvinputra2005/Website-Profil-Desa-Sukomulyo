<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SavePopulationGroupRequest;
use App\Models\PopulationGroup;
use App\Models\PopulationGroupMember;
use App\Models\Resident;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PopulationGroupController extends PopulationController
{
    public function __construct(private ActivityLogger $logger) {}

    public function index(Request $request): View
    {
        $query = PopulationGroup::with('chairperson')->withCount('memberships');
        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($builder) => $builder->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        return view('admin.population.groups.index', [
            'groups' => $query->orderBy('name')->paginate(20)->withQueryString(),
            'categories' => PopulationGroup::distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function create(): View
    {
        return view('admin.population.groups.form', $this->formData(new PopulationGroup));
    }

    public function store(SavePopulationGroupRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $group = DB::transaction(function () use ($data) {
            $group = PopulationGroup::create($this->groupData($data));
            $this->syncChairperson($group, null);
            $this->logger->log('created', 'kelompok', $group);

            return $group;
        });

        return redirect()->route('admin.population.groups.show', $group)->with('success', 'Kelompok berhasil ditambahkan.');
    }

    public function show(PopulationGroup $group): View
    {
        $group->load(['chairperson', 'memberships.resident']);

        return view('admin.population.groups.show', compact('group'));
    }

    public function edit(PopulationGroup $group): View
    {
        return view('admin.population.groups.form', $this->formData($group));
    }

    public function update(SavePopulationGroupRequest $request, PopulationGroup $group): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($data, $group) {
            $oldChairperson = $group->chairperson_id;
            $group->update($this->groupData($data));
            $this->syncChairperson($group, $oldChairperson);
            $this->logger->log('updated', 'kelompok', $group);
        });

        return back()->with('success', 'Kelompok berhasil diperbarui.');
    }

    public function destroy(PopulationGroup $group): RedirectResponse
    {
        $this->logger->log('archived', 'kelompok', $group);
        $group->delete();

        return redirect()->route('admin.population.groups.index')->with('success', 'Kelompok diarsipkan.');
    }

    private function groupData(array $data): array
    {
        return collect($data)->only(['code', 'name', 'category', 'establishment_decree', 'chairperson_id', 'description'])
            ->merge(['is_active' => (bool) ($data['is_active'] ?? false)])
            ->all();
    }

    private function syncChairperson(PopulationGroup $group, ?int $oldChairperson): void
    {
        if ($oldChairperson && $oldChairperson !== $group->chairperson_id) {
            PopulationGroupMember::where('group_id', $group->id)->where('resident_id', $oldChairperson)->where('position', 'Ketua')->update(['position' => 'Anggota']);
        }

        PopulationGroupMember::updateOrCreate(
            ['group_id' => $group->id, 'resident_id' => $group->chairperson_id],
            ['position' => 'Ketua']
        );
    }

    private function formData(PopulationGroup $group): array
    {
        return [
            'group' => $group,
            'residents' => Resident::where('status', 'active')->orderBy('name')->get(),
            'categories' => PopulationGroup::distinct()->orderBy('category')->pluck('category'),
        ];
    }
}
