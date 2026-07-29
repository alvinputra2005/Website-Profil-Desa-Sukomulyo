<?php

namespace App\Models;

use App\Enums\LetterApplicationStatus;
use Database\Factories\LetterApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterApplication extends Model
{
    /** @use HasFactory<LetterApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'public_id', 'application_number', 'tracking_token_hash', 'tracking_pin_hash', 'tracking_expires_at',
        'letter_service_id', 'resident_id', 'service_snapshot_json', 'applicant_name', 'applicant_nik',
        'applicant_nik_hash', 'applicant_phone', 'birth_place', 'birth_date', 'sex', 'address', 'hamlet',
        'rt', 'rw', 'purpose', 'form_data_json', 'status', 'public_note', 'internal_note', 'assigned_to',
        'submitted_at', 'reviewed_at', 'processing_at', 'ready_at', 'completed_at', 'rejected_at',
        'cancelled_at', 'whatsapp_confirmation_opened_at', 'whatsapp_admin_opened_at',
        'last_whatsapp_opened_by', 'archived_at',
    ];

    protected $hidden = ['tracking_token_hash', 'tracking_pin_hash', 'applicant_nik_hash'];

    protected function casts(): array
    {
        return [
            'applicant_nik' => 'encrypted', 'applicant_phone' => 'encrypted',
            'service_snapshot_json' => 'array', 'form_data_json' => 'array',
            'status' => LetterApplicationStatus::class, 'birth_date' => 'date',
            'submitted_at' => 'datetime', 'reviewed_at' => 'datetime', 'processing_at' => 'datetime',
            'ready_at' => 'datetime', 'completed_at' => 'datetime', 'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime', 'tracking_expires_at' => 'datetime',
            'whatsapp_confirmation_opened_at' => 'datetime', 'whatsapp_admin_opened_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function service()
    {
        return $this->belongsTo(LetterService::class, 'letter_service_id');
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function statusHistories()
    {
        return $this->hasMany(LetterApplicationStatusHistory::class)->orderBy('created_at');
    }

    public function lastWhatsAppOpener()
    {
        return $this->belongsTo(User::class, 'last_whatsapp_opened_by');
    }

    public function maskedName(): string
    {
        $parts = preg_split('/\s+/', trim($this->applicant_name)) ?: [];

        return collect($parts)->map(fn (string $part, int $index) => $index === 0 ? $part : mb_substr($part, 0, 1).str_repeat('*', max(3, mb_strlen($part) - 1)))->implode(' ');
    }

    public function maskedNik(): string
    {
        return substr($this->applicant_nik, 0, 4).'********'.substr($this->applicant_nik, -4);
    }

    public function maskedPhone(): string
    {
        return substr($this->applicant_phone, 0, 2).str_repeat('*', max(6, strlen($this->applicant_phone) - 4)).substr($this->applicant_phone, -2);
    }

    public function isTrackingExpired(): bool
    {
        return $this->tracking_expires_at?->isPast() ?? false;
    }

    public function canBeEditedByApplicant(): bool
    {
        return $this->status === LetterApplicationStatus::RevisionRequired && ! $this->isTrackingExpired();
    }

    public function trackingTimeline()
    {
        return $this->statusHistories()->get();
    }
}
