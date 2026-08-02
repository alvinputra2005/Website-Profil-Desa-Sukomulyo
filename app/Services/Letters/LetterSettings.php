<?php

namespace App\Services\Letters;

class LetterSettings
{
    public function get(string $key, mixed $fallback = null): mixed
    {
        return config('village.letter_service.'.$key, $fallback);
    }

    public function whatsappNumber(): string
    {
        return $this->normalizePhone((string) $this->get('whatsapp_number', ''));
    }

    public function officeHours(): string
    {
        return (string) $this->get('office_hours', 'Senin-Jumat, 08.00-14.00 WIB');
    }

    public function pickupAddress(): string
    {
        return (string) $this->get('pickup_address', 'Kantor Desa Sukomulyo');
    }

    public function retentionDays(): int
    {
        return max(1, (int) $this->get('tracking_retention_days', 90));
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
