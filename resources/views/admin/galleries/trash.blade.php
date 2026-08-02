@extends('layouts.admin')
@section('title','Tempat Sampah Galeri')
@section('page-description','Pulihkan atau hapus permanen galeri')
@section('content')
<div class="box box-info">
    <div class="box-header with-border">
        <a href="{{ route('admin.resources.index','galleries') }}" class="btn btn-info btn-sm"><i class="fa fa-arrow-left"></i> Daftar Galeri</a>
        @if($items->total())
            <form id="bulk-galleries-trash" method="post" action="{{ route('admin.galleries.bulk-restore') }}" class="inline-form" data-confirm="Lanjutkan aksi untuk galeri yang dipilih?" data-confirm-tone="warning" data-confirm-title="Aksi Galeri Terpilih" data-confirm-button="Ya, lanjutkan">@csrf <button class="btn btn-success btn-sm" type="submit" name="_method" value="patch" data-bulk-button disabled><i class="fa fa-undo"></i> Pulihkan Terpilih</button> <button class="btn btn-danger btn-sm" type="submit" name="_method" value="delete" formaction="{{ route('admin.galleries.bulk-force-delete') }}" data-bulk-button disabled><i class="fa fa-times"></i> Permanen Terpilih</button></form>
            <form class="pull-right" method="post" action="{{ route('admin.galleries.empty-trash') }}" data-confirm="Hapus permanen semua galeri di tempat sampah?" data-confirm-tone="danger" data-confirm-title="Kosongkan Tempat Sampah" data-confirm-button="Ya, hapus semua">@csrf @method('delete')<button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Kosongkan Tempat Sampah</button></form>
        @endif
    </div>
    <div class="box-body">
        <form method="get" class="news-filters gallery-trash-filter"><div class="form-group"><label for="trash-search">Pencarian</label><div class="input-group"><input id="trash-search" class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari judul atau slug galeri..."><span class="input-group-btn"><button class="btn btn-info"><i class="fa fa-search"></i> Cari</button></span></div></div></form>
        <div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th style="width:38px"><input type="checkbox" aria-label="Pilih semua galeri pada tempat sampah" data-bulk-select-all></th><th>Judul</th><th>Jumlah Foto</th><th>Dihapus</th><th style="width:190px">Aksi</th></tr></thead><tbody>
        @forelse($items as $item)<tr><td><input type="checkbox" name="ids[]" value="{{ $item->id }}" form="bulk-galleries-trash" data-bulk-item aria-label="Pilih {{ $item->title }}"></td><td><strong>{{ $item->title }}</strong><div class="news-meta">/{{ $item->slug }}</div></td><td>{{ $item->items_count }}</td><td>{{ $item->deleted_at?->format('d-m-Y H:i') }}</td><td class="table-actions"><form method="post" action="{{ route('admin.galleries.restore',$item) }}">@csrf @method('patch')<button class="btn btn-success btn-sm"><i class="fa fa-undo"></i> Pulihkan</button></form> <form method="post" action="{{ route('admin.galleries.force-delete',$item) }}" data-confirm="Hapus permanen galeri '{{ $item->title }}'?" data-confirm-tone="danger" data-confirm-title="Hapus Permanen" data-confirm-button="Ya, hapus">@csrf @method('delete')<button class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Permanen</button></form></td></tr>
        @empty<tr><td colspan="5" class="empty-state"><i class="fa fa-trash-o fa-3x"></i><h4>Tempat sampah kosong</h4></td></tr>@endforelse
        </tbody></table></div>
    </div><div class="box-footer clearfix">{{ $items->links() }}</div>
</div>
@endsection
