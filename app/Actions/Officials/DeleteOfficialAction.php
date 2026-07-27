<?php

namespace App\Actions\Officials;

use App\Models\Official;
use App\Services\ActivityLogger;

class DeleteOfficialAction
{
    public function __construct(private ActivityLogger $logger) {}

    public function execute(Official $official): void
    {
        $this->logger->log('deleted', 'perangkat-desa', $official, $official->toArray());
        $official->delete();
    }
}
