<?php

namespace App\Actions\Village;

use App\Services\Village\VillageSectionWriter;
use Illuminate\Support\Facades\DB;

class UpdateVisionMissionAction
{
    public function __construct(private VillageSectionWriter $writer) {}

    public function execute(array $data): void
    {
        DB::transaction(function () use ($data): void {
            $this->writer->save('vision', 'Visi Desa Sukomulyo', $data['vision'], $data['status'], 10);
            $this->writer->save('mission', 'Misi Desa Sukomulyo', $data['mission'], $data['status'], 20);
        });
    }
}
