<?php

namespace App\Actions\Population;

use App\Models\Resident;
use App\Services\ActivityLogger;
use Illuminate\Validation\ValidationException;

class DeleteResidentAction
{
    public function __construct(private ActivityLogger $logger) {}

    public function execute(Resident $resident): void
    {
        if ($resident->headedFamilies()->exists() || $resident->chairedGroups()->exists()) {
            throw ValidationException::withMessages([
                'resident' => 'Penduduk masih tercatat sebagai kepala keluarga atau ketua kelompok. Ganti penanggung jawab terlebih dahulu.',
            ]);
        }

        $this->logger->log('archived', 'penduduk', $resident);
        $resident->delete();
    }
}
