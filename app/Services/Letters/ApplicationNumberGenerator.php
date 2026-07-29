<?php

namespace App\Services\Letters;

use App\Models\LetterApplication;
use Illuminate\Support\Str;
use RuntimeException;

class ApplicationNumberGenerator
{
    public function generate(string $serviceCode): string
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $suffix = Str::upper(Str::random(6));
            $number = 'PS-'.Str::upper($serviceCode).'-'.now('Asia/Jakarta')->format('Ymd').'-'.$suffix;
            if (! LetterApplication::query()->where('application_number', $number)->exists()) {
                return $number;
            }
        }
        throw new RuntimeException('Nomor permohonan tidak dapat dibuat.');
    }
}
