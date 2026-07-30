<?php

namespace App\Services\Announcements;

use App\Models\Publication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class AnnouncementQueryService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = $this->publicQuery()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('title', 'like', '%'.$search.'%')
                        ->orWhere('excerpt', 'like', '%'.$search.'%');
                });
            });

        match ($filters['sort'] ?? 'latest') {
            'oldest' => $query->orderByRaw('COALESCE(published_at, created_at) asc')->orderBy('id'),
            'most_downloaded' => $query->orderByDesc('total_downloads')
                ->orderByRaw('COALESCE(published_at, created_at) desc'),
            default => $query->orderByRaw('COALESCE(published_at, created_at) desc')->orderByDesc('id'),
        };

        return $query->paginate($filters['per_page'] ?? 10)->withQueryString();
    }

    public function findPublished(string $slug): Publication
    {
        return $this->publicQuery()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function publicQuery(): Builder
    {
        return Publication::query()
            ->announcements()
            ->published()
            ->with([
                'attachments' => fn ($query) => $query
                    ->whereHas('media', fn ($media) => $media->where('mime_type', 'application/pdf'))
                    ->with('media'),
            ])
            ->withCount([
                'attachments as attachments_count' => fn ($query) => $query
                    ->whereHas('media', fn ($media) => $media->where('mime_type', 'application/pdf')),
            ])
            ->withSum([
                'attachments as total_downloads' => fn ($query) => $query
                    ->whereHas('media', fn ($media) => $media->where('mime_type', 'application/pdf')),
            ], 'download_count');
    }
}
