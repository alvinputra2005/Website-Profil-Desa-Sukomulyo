<?php

namespace App\Actions\Letters;

use App\Models\LetterApplication;

final readonly class CreatedLetterApplication
{
    public function __construct(public LetterApplication $application, public string $trackingToken, public string $trackingPin) {}
}
