<?php

namespace App\Services\Letters;

use App\Models\LetterApplication;
use Illuminate\Validation\ValidationException;

class LetterWhatsAppUrlBuilder
{
    public function __construct(private readonly LetterSettings $settings) {}

    public function villageConfirmation(LetterApplication $application): string
    {
        $message = "Halo Admin Desa Sukomulyo.\n\nSaya {$application->applicant_name} telah mengajukan {$application->service_snapshot_json['name']} melalui website Desa Sukomulyo.\n\nNomor permohonan: {$application->application_number}\n\nMohon dilakukan pemeriksaan. Terima kasih.\n\nTautan petugas:\n".route('admin.letter-applications.show', $application);

        return $this->url($this->settings->whatsappNumber(), $message);
    }

    public function applicantNotification(LetterApplication $application, string $trackingUrl): string
    {
        $name = $application->applicant_name;
        $service = $application->service_snapshot_json['name'];
        $number = $application->application_number;
        $message = match ($application->status->value) {
            'revision_required' => "Halo Bapak/Ibu {$name}.\n\nPermohonan {$service} dengan nomor {$number} perlu diperbaiki.\n\nCatatan petugas:\n{$application->public_note}\n\nSilakan buka:\n{$trackingUrl}\n\nTerima kasih.",
            'processing' => "Halo Bapak/Ibu {$name}.\n\nPermohonan {$service} dengan nomor {$number} telah diverifikasi dan sedang diproses.\n\nPantau status:\n{$trackingUrl}",
            'ready_for_pickup' => "Halo Bapak/Ibu {$name}.\n\nPermohonan {$service} dengan nomor {$number} sudah siap diambil di {$this->settings->pickupAddress()}.\n\nBawa dokumen asli dan nomor permohonan. Surat diberikan dalam bentuk fisik.\n\n{$trackingUrl}",
            'completed' => "Halo Bapak/Ibu {$name}.\n\nPermohonan {$service} dengan nomor {$number} telah selesai dan surat telah diambil. Terima kasih.",
            'rejected', 'cancelled' => "Halo Bapak/Ibu {$name}.\n\nStatus permohonan {$service} dengan nomor {$number}: {$application->status->label()}.\n\nCatatan: {$application->public_note}\n\n{$trackingUrl}",
            default => "Halo Bapak/Ibu {$name}.\n\nPermohonan {$service} dengan nomor {$number} sedang diverifikasi oleh petugas Desa Sukomulyo.\n\nPantau status:\n{$trackingUrl}\n\nTerima kasih.",
        };

        return $this->url($application->applicant_phone, $message);
    }

    public function url(string $phone, string $message): string
    {
        $phone = $this->settings->normalizePhone($phone);
        if (! preg_match('/^62[0-9]{8,13}$/', $phone)) {
            throw ValidationException::withMessages([
                'whatsapp' => 'Nomor WhatsApp desa belum dikonfigurasi dengan benar.',
            ]);
        }

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }
}
