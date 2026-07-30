<?php

namespace App\Services\Letters;

class SensitiveDataHasher
{
    public function nik(string $nik): string
    {
        return hash_hmac('sha256', $this->normalizeNik($nik), (string) config('app.key'));
    }

    public function normalizeNik(string $nik): string
    {
        return preg_replace('/\D+/', '', $nik) ?? '';
    }
}
