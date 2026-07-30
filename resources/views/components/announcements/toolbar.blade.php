@props(['filters'])

<form class="announcement-toolbar" method="get" action="{{ route('announcements.index') }}" role="search">
    <div class="announcement-search">
        <label class="screen-reader-text" for="announcement-q">Cari pengumuman</label>
        <i class="fas fa-search" aria-hidden="true"></i>
        <input
            id="announcement-q"
            name="q"
            type="search"
            value="{{ $filters['search'] }}"
            placeholder="Cari pengumuman"
            maxlength="100"
        >
        @if ($filters['search'])
            <a
                class="announcement-search-clear"
                href="{{ route('announcements.index', ['sort' => $filters['sort']]) }}"
                aria-label="Hapus pencarian"
                title="Hapus pencarian"
            >
                <i class="fas fa-times" aria-hidden="true"></i>
            </a>
        @endif
    </div>

    <div class="announcement-sort">
        <select id="announcement-sort" name="sort" onchange="this.form.submit()">
            <option value="latest" @selected($filters['sort'] === 'latest')>Terbaru</option>
            <option value="oldest" @selected($filters['sort'] === 'oldest')>Terlama</option>
            <option value="most_downloaded" @selected($filters['sort'] === 'most_downloaded')>Paling banyak diunduh</option>
        </select>
    </div>

    <button class="announcement-filter-submit" type="submit">
        <i class="fas fa-search" aria-hidden="true"></i>
        <span>Terapkan</span>
    </button>
</form>
