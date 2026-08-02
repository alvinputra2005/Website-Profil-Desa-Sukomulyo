<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SavePopulationGroupMemberRequest;
use App\Models\PopulationGroup;
use App\Models\PopulationGroupMember;
use App\Models\Resident;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PopulationGroupMemberController extends PopulationController
{
    public function __construct(private ActivityLogger $logger) {}

    public function create(PopulationGroup $group): View
    {
        $this->authorize('view', $group);
        $this->authorize('create', PopulationGroupMember::class);
        return view('admin.population.groups.member-form', [
            'group' => $group,
            'membership' => new PopulationGroupMember,
            'residents' => Resident::where('status', 'active')
                ->whereDoesntHave('groupMemberships', fn ($query) => $query->where('group_id', $group->id))
                ->orderBy('name')->get(),
        ]);
    }

    public function store(SavePopulationGroupMemberRequest $request, PopulationGroup $group): RedirectResponse
    {
        $this->authorize('update', $group);
        $this->authorize('create', PopulationGroupMember::class);
        $data = $request->validated();
        $membership = $group->memberships()->create($data);
        $this->logger->log('created', 'anggota_kelompok', $membership);

        return redirect()->route('admin.population.groups.show', $group)->with('success', 'Anggota kelompok berhasil ditambahkan.');
    }

    public function edit(PopulationGroup $group, PopulationGroupMember $membership): View
    {
        abort_unless($membership->group_id === $group->id, 404);
        $this->authorize('view', $group);
        $this->authorize('update', $membership);

        return view('admin.population.groups.member-form', [
            'group' => $group,
            'membership' => $membership,
            'residents' => Resident::where('status', 'active')
                ->where(fn ($query) => $query->whereKey($membership->resident_id)
                    ->orWhereDoesntHave('groupMemberships', fn ($members) => $members->where('group_id', $group->id)))
                ->orderBy('name')->get(),
        ]);
    }

    public function update(SavePopulationGroupMemberRequest $request, PopulationGroup $group, PopulationGroupMember $membership): RedirectResponse
    {
        abort_unless($membership->group_id === $group->id, 404);
        $this->authorize('update', $group);
        $this->authorize('update', $membership);
        $membership->update($request->validated());
        if ($membership->position === 'Ketua') {
            $group->update(['chairperson_id' => $membership->resident_id]);
            $group->memberships()->where('id', '!=', $membership->id)->where('position', 'Ketua')->update(['position' => 'Anggota']);
        }
        $this->logger->log('updated', 'anggota_kelompok', $membership);

        return redirect()->route('admin.population.groups.show', $group)->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(PopulationGroup $group, PopulationGroupMember $membership): RedirectResponse
    {
        abort_unless($membership->group_id === $group->id, 404);
        $this->authorize('update', $group);
        $this->authorize('delete', $membership);
        if ($membership->resident_id === $group->chairperson_id) {
            return back()->withErrors(['membership' => 'Ketua kelompok tidak dapat dihapus. Pilih ketua baru terlebih dahulu.']);
        }
        $this->logger->log('deleted', 'anggota_kelompok', $membership);
        $membership->delete();

        return back()->with('success', 'Anggota kelompok berhasil dihapus.');
    }

    public function bulkDestroy(Request $request, PopulationGroup $group): RedirectResponse
    {
        $this->authorize('update', $group);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', Rule::exists('population_group_members', 'id')->where('group_id', $group->id)],
        ]);

        $memberships = PopulationGroupMember::where('group_id', $group->id)->whereKey($data['ids'])->get();
        if ($memberships->contains(fn (PopulationGroupMember $membership): bool => $membership->resident_id === $group->chairperson_id)) {
            throw ValidationException::withMessages(['ids' => 'Ketua kelompok tidak dapat dihapus. Pilih ketua baru terlebih dahulu.']);
        }

        DB::transaction(function () use ($memberships): void {
            $memberships->each(function (PopulationGroupMember $membership): void {
                $this->authorize('delete', $membership);
                $this->logger->log('deleted', 'anggota_kelompok', $membership);
                $membership->delete();
            });
        });

        return back()->with('success', $memberships->count().' anggota kelompok berhasil dihapus.');
    }

}
