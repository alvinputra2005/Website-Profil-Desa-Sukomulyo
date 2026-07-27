<?php

namespace App\Actions\Officials;

use App\Models\Official;
use App\Services\ActivityLogger;
use App\Services\Officials\OfficialDataMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreateOfficialAction
{
    public function __construct(
        private OfficialDataMapper $mapper,
        private ActivityLogger $logger,
    ) {}

    public function execute(Request $request, array $data): Official
    {
        return DB::transaction(function () use ($request, $data): Official {
            $official = Official::create($this->mapper->prepare($request, $data));
            $this->logger->log('created', 'perangkat-desa', $official, null, $official->toArray());

            return $official;
        });
    }
}
