<?php

namespace App\Actions\Population;

use App\Models\PopulationArea;
use App\Models\Resident;
use App\Models\ResidentEvent;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;

class UpdateResidentAction
{
    public function __construct(private ActivityLogger $logger) {}

    public function execute(Resident $resident, array $data): Resident
    {
        return DB::transaction(function () use ($data, $resident): Resident {
            $oldStatus = $resident->status;
            $area = $this->resolveArea($data);
            $resident->update(array_merge(
                $this->residentData($data),
                ['area_id' => $area?->id],
            ));

            if ($oldStatus !== $resident->status) {
                $type = match ($resident->status) {
                    'moved' => 'departure',
                    'deceased' => 'death',
                    'missing' => 'missing',
                    default => 'reactivated',
                };
                $this->recordEvent($resident, $type, $data['event_date'] ?? now()->toDateString(), $data);
            }
            $this->logger->log('updated', 'penduduk', $resident);

            return $resident->fresh();
        });
    }

    private function resolveArea(array $data): ?PopulationArea
    {
        $hamlet = trim((string) ($data['hamlet'] ?? ''));
        if ($hamlet === '') {
            return null;
        }

        return PopulationArea::firstOrCreate([
            'hamlet' => $hamlet,
            'rw' => str_pad(trim((string) ($data['rw'] ?? '')), 2, '0', STR_PAD_LEFT),
            'rt' => str_pad(trim((string) ($data['rt'] ?? '')), 2, '0', STR_PAD_LEFT),
        ]);
    }

    private function residentData(array $data): array
    {
        return collect($data)->only([
            'nik', 'name', 'sex', 'birth_place', 'birth_date', 'religion', 'marital_status',
            'citizenship', 'education', 'occupation', 'blood_type', 'father_nik', 'father_name',
            'mother_nik', 'mother_name', 'phone', 'email', 'current_address', 'previous_address',
            'family_id', 'family_relationship',
            'resident_status', 'status', 'registered_at', 'notes',
        ])->all();
    }

    private function recordEvent(Resident $resident, string $type, string $date, array $data): void
    {
        $resident->loadMissing('family');
        ResidentEvent::create([
            'resident_id' => $resident->id,
            'event_type' => $type,
            'event_date' => $date,
            'reported_at' => $data['reported_at'] ?? now()->toDateString(),
            'resident_name' => $resident->name,
            'nik' => $resident->nik,
            'sex' => $resident->sex,
            'family_card_number' => $resident->family?->family_card_number,
            'origin_address' => $resident->previous_address,
            'destination_address' => $data['destination_address'] ?? null,
            'cause' => $data['event_cause'] ?? null,
            'notes' => $data['event_notes'] ?? null,
            'recorded_by' => auth()->id(),
        ]);
    }
}
