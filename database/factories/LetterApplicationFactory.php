<?php

namespace Database\Factories;

use App\Enums\LetterApplicationStatus;
use App\Models\LetterApplication;
use App\Models\LetterService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<LetterApplication> */
class LetterApplicationFactory extends Factory
{
    public function definition(): array
    {
        $nik = fake()->numerify('35##############');

        return ['public_id' => (string) Str::ulid(), 'application_number' => 'PS-TST-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)), 'tracking_token_hash' => hash('sha256', Str::random(64)), 'tracking_pin_hash' => Hash::make('123456'), 'letter_service_id' => LetterService::factory(), 'service_snapshot_json' => ['name' => 'Surat Keterangan', 'processing_days' => 3, 'requirements' => []], 'applicant_name' => fake()->name(), 'applicant_nik' => $nik, 'applicant_nik_hash' => hash_hmac('sha256', $nik, config('app.key')), 'applicant_phone' => '628123456789', 'address' => fake()->address(), 'purpose' => 'Keperluan administrasi', 'status' => LetterApplicationStatus::Submitted, 'submitted_at' => now()];
    }
}
