@extends('layouts.admin')
@section('title', $config['title'])
@section('page-description', 'Daftar dan pengelolaan data')
@section('content')
@php($isStatistics = $resource === 'statistics')
@php($createParams = array_filter(['resource' => $resource, 'type' => request('type')]))
<div class="box box-info">
    <div class="box-header with-border">
        <a href="{{ route('admin.resources.create', $createParams) }}" class="btn btn-social btn-info btn-sm"><i class="fa fa-plus"></i> Tambah {{ $config['title'] }}</a>
        @if ($isStatistics)
            <form id="statistics-bulk-delete-form" method="post" action="{{ route('admin.statistics.bulk-destroy') }}" class="inline-form" data-confirm="Hapus semua dataset statistik yang dipilih? Data akan disembunyikan dari halaman publik." data-confirm-tone="danger" data-confirm-title="Hapus Dataset Terpilih" data-confirm-button="Ya, hapus">
                @csrf @method('delete')
                <button class="btn btn-danger btn-sm" type="submit" data-statistics-bulk-delete disabled><i class="fa fa-trash"></i> Hapus Terpilih</button>
            </form>
        @endif
        <div class="box-tools">
            <form method="get">@if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif<div class="input-group input-group-sm" style="width:250px"><input type="text" name="q" class="form-control pull-right" value="{{ request('q') }}" placeholder="Cari..."><div class="input-group-btn"><button class="btn btn-default"><i class="fa fa-search"></i></button></div></div></form>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table id="tabeldata" class="table table-bordered table-striped table-hover">
                <thead><tr>
                    @if ($isStatistics)<th style="width:38px"><input type="checkbox" aria-label="Pilih semua dataset pada halaman ini" data-statistics-select-all></th>@endif
                    <th style="width:50px">No</th>
                    @foreach ($config['columns'] as $label)<th>{{ $label }}</th>@endforeach
                    <th style="width:{{ $resource === 'galleries' ? '185px' : '130px' }}">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            @if ($isStatistics)<td><input type="checkbox" name="ids[]" value="{{ $item->id }}" form="statistics-bulk-delete-form" aria-label="Pilih {{ $item->title }}" data-statistics-check></td>@endif
                            <td>{{ $items->firstItem() + $loop->index }}</td>
                            @foreach ($config['columns'] as $key => $label)
                                <td>
                                    @php($value = data_get($item, $key))
                                    @if (in_array($key, ['status', 'is_active', 'is_visible', 'is_public']))<x-admin.badge :value="$value"/>
                                    @elseif ($value instanceof \Carbon\CarbonInterface){{ $value->format('d-m-Y H:i') }}
                                    @else{{ Str::limit((string) $value, 70) }}@endif
                                </td>
                            @endforeach
                            <td class="table-actions">
                                @if ($resource === 'galleries' && $item->status === 'published')<a href="{{ route('galeri-desa') }}" target="_blank" class="btn btn-success btn-sm" title="Lihat"><i class="fa fa-eye"></i></a> @endif
                                @if ($resource === 'publications' && $item->type === 'announcement' && $item->status === 'published')<a href="{{ route('announcements.show', $item->slug) }}" target="_blank" class="btn btn-success btn-sm" title="Lihat Pengumuman"><i class="fa fa-eye"></i></a> @endif
                                <a href="{{ route('admin.resources.edit', [$resource, $item]) }}" class="btn bg-orange btn-sm" title="Ubah"><i class="fa fa-edit"></i></a>
                                @if ($resource === 'galleries' && $item->status !== 'archived')<form method="post" action="{{ route('admin.galleries.archive', $item) }}" data-confirm="Arsipkan galeri '{{ $item->title }}'?" data-confirm-tone="warning" data-confirm-title="Arsipkan Galeri" data-confirm-button="Ya, arsipkan">@csrf @method('patch')<button class="btn btn-default btn-sm" title="Arsipkan"><i class="fa fa-archive"></i></button></form> @endif
                                <form method="post" action="{{ route('admin.resources.destroy', [$resource, $item]) }}" @if ($resource === 'galleries') data-confirm="Hapus galeri '{{ $item->title }}'? Galeri akan dipindahkan ke tempat sampah." data-confirm-tone="danger" data-confirm-title="Hapus Galeri" data-confirm-button="Ya, hapus" @else data-confirm="Apakah Anda yakin ingin menghapus data ini?" @endif>@csrf @method('delete')<button class="btn bg-maroon btn-sm" title="Hapus"><i class="fa fa-trash"></i></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($config['columns']) + 2 + ($isStatistics ? 1 : 0) }}" class="empty-state"><i class="fa fa-folder-open-o fa-3x"></i><h4>Belum ada data</h4><p>Silakan tambahkan data baru.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer clearfix">{{ $items->links() }}</div>
</div>
@endsection
