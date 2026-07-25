<?php

namespace App\Http\Controllers\Admin;

use App\Models\PopulationGroup;
use App\Models\PopulationGroupMember;
use App\Models\Resident;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PopulationGroupMemberController extends PopulationController
{
    public function __construct(private ActivityLogger $logger) {}

    public function create(PopulationGroup $group): View
    {
        return view('admin.population.groups.member-form', [
            'group' => $group,
            'membership' => new PopulationGroupMember,
            'residents' => Resident::where('status', 'active')
                ->whereDoesntHave('groupMemberships', fn ($query) => $query->where('group_id', $group->id))
                ->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, PopulationGroup $group): RedirectResponse
    {
        $data = $this->validated($request, $group);
        $membership = $group->memberships()->create($data);
        $this->logger->log('created', 'anggota_kelompok', $membership);

        return redirect()->route('admin.population.groups.show', $group)->with('success', 'Anggota kelompok berhasil ditambahkan.');
    }

    public function edit(PopulationGroup $group, PopulationGroupMember $membership): View
    {
        abort_unless($membership->group_id === $group->id, 404);

        return view('admin.population.groups.member-form', [
            'group' => $group,
            'membership' => $membership,
            'residents' => Resident::where('status', 'active')
                ->where(fn ($query) => $query->whereKey($membership->resident_id)
                    ->orWhereDoesntHave('groupMemberships', fn ($members) => $members->where('group_id', $group->id)))
                ->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, PopulationGroup $group, PopulationGroupMember $membership): RedirectResponse
    {
        abort_unless($membership->group_id === $group->id, 404);
        $membership->update($this->validated($request, $group, $membership));
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
        if ($membership->resident_id === $group->chairperson_id) {
            return back()->withErrors(['membership' => 'Ketua kelompok tidak dapat dihapus. Pilih ketua baru terlebih dahulu.']);
        }
        $this->logger->log('deleted', 'anggota_kelompok', $membership);
        $membership->delete();

        return back()->with('success', 'Anggota kelompok berhasil dihapus.');
    }

    private function validated(Request $request, PopulationGroup $group, ?PopulationGroupMember $membership = null): array
    {
        return $request->validate([
            'resident_id' => [
                'required',
                'exists:residents,id',
                Rule::unique('population_group_members')->where('group_id', $group->id)->ignore($membership?->id),
            ],
            'member_number' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('population_group_members')->where('group_id', $group->id)->ignore($membership?->id),
            ],
            'position' => ['required', 'string', 'max:50'],
            'appointment_decree' => ['nullable', 'string', 'max:100'],
            'appointment_date' => ['nullable', 'date'],
            'dismissal_decree' => ['nullable', 'string', 'max:100'],
            'dismissal_date' => ['nullable', 'date', 'after_or_equal:appointment_date'],
            'period' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
