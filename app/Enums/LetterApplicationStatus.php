<?php

namespace App\Enums;

enum LetterApplicationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case RevisionRequired = 'revision_required';
    case Processing = 'processing';
    case ReadyForPickup = 'ready_for_pickup';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Permohonan Dikirim',
            self::UnderReview => 'Sedang Diverifikasi',
            self::RevisionRequired => 'Perlu Diperbaiki',
            self::Processing => 'Surat Sedang Diproses',
            self::ReadyForPickup => 'Siap Diambil',
            self::Completed => 'Surat Telah Diambil',
            self::Rejected => 'Permohonan Ditolak',
            self::Cancelled => 'Permohonan Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'label-default',
            self::Submitted => 'label-info',
            self::UnderReview => 'label-primary',
            self::RevisionRequired => 'label-warning',
            self::Processing => 'label-default',
            self::ReadyForPickup => 'label-success',
            self::Completed => 'label-success',
            self::Rejected, self::Cancelled => 'label-danger',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Rejected, self::Cancelled], true);
    }

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Submitted, self::Cancelled],
            self::Submitted => [self::UnderReview, self::Cancelled],
            self::UnderReview => [self::RevisionRequired, self::Processing, self::Rejected, self::Cancelled],
            self::RevisionRequired => [self::Submitted, self::UnderReview, self::Cancelled],
            self::Processing => [self::RevisionRequired, self::ReadyForPickup, self::Rejected, self::Cancelled],
            self::ReadyForPickup => [self::Completed],
            default => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
