<?php

namespace App\Actions\Officials;

use App\Models\Official;
use App\Services\ActivityLogger;
use App\Services\Officials\OfficialDataMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpdateOfficialAction
{
    public function __construct(
        private OfficialDataMapper $mapper,
        private ActivityLogger $logger,
    ) {}

    public function execute(Request $request, Official $official, array $data): Official
    {
        return DB::transaction(function () use ($request, $official, $data): Official {
            $old = $official->toArray();
            $official->update($this->mapper->prepare($request, $data, $official));
            $this->logger->log('updated', 'perangkat-desa', $official, $old, $official->fresh()->toArray());

            return $official->fresh();
        });
    }
}
