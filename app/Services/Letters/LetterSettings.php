<?php

namespace App\Services\Letters;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class LetterSettings
{
    public function get(string $key, mixed $fallback = null): mixed
    {
        if (! Schema::hasTable('settings')) {
            return $fallback;
        }

        return Setting::query()->where('key', 'letter_service.'.$key)->value('value') ?? $fallback;
    }

    public function whatsappNumber(): string
    {
        return $this->normalizePhone((string) $this->get('whatsapp_number', env('VILLAGE_WHATSAPP_NUMBER', '')));
    }

    public function officeHours(): string
    {
        return (string) $this->get('office_hours', env('LETTER_OFFICE_HOURS', 'Senin–Jumat, 08.00–14.00 WIB'));
    }

    public function pickupAddress(): string
    {
        return (string) $this->get('pickup_address', env('LETTER_PICKUP_ADDRESS', 'Kantor Desa Sukomulyo'));
    }

    public function retentionDays(): int
    {
        return max(1, (int) $this->get('tracking_retention_days', env('LETTER_TRACKING_RETENTION_DAYS', 90)));
    }

    public function enabled(): bool
    {
        return filter_var($this->get('enabled', true), FILTER_VALIDATE_BOOL);
    }

    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        return $phone;
    }
}
