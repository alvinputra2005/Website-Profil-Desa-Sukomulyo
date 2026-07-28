@extends('layouts.admin')
@section('title','Daftar Galeri')
@section('page-description','Kelola dokumentasi kegiatan desa')
@section('content')
<div class="box box-info news-list-box">
    <div class="box-header with-border news-list-header">
        <a href="{{ route('admin.resources.create','galleries') }}" class="btn btn-social btn-info btn-sm"><i class="fa fa-plus"></i> Tambah Galeri</a>
        <a href="{{ route('admin.resources.index',['resource'=>'galleries','status'=>'archived']) }}" class="btn btn-default btn-sm"><i class="fa fa-archive"></i> Arsip</a>
        <a href="{{ route('admin.galleries.trash') }}" class="btn btn-default btn-sm"><i class="fa fa-trash"></i> Tempat Sampah</a>
    </div>
    <div class="box-body">
        <form class="news-filters gallery-filters" method="get">
            <div class="form-group"><label for="gallery-search">Pencarian</label><div class="input-group"><input id="gallery-search" class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari judul atau slug galeri..."><span class="input-group-btn"><button class="btn btn-info"><i class="fa fa-search"></i> Cari</button></span></div></div>
            <div class="form-group"><label for="status">Status</label><select class="form-control select2" id="status" name="status" onchange="this.form.submit()"><option value="">Semua status</option>@foreach(['draft'=>'Draf','published'=>'Terbit','archived'=>'Arsip'] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select></div>
        </form>
        <div class="table-responsive"><table class="table table-bordered table-striped table-hover news-table"><thead><tr><th class="text-center" style="width:55px">No</th><th style="width:90px">Sampul</th><th>Judul Galeri</th><th style="width:105px">Jumlah Foto</th><th style="width:115px">Status</th><th style="width:145px">Tanggal Kegiatan</th><th style="width:185px">Aksi</th></tr></thead><tbody>
        @forelse($items as $item)<tr>
            <td class="text-center">{{ $items->firstItem()+$loop->index }}</td>
            <td>@if($item->cover)<img class="news-thumb" src="{{ $item->cover->url }}" alt="">@else<div class="news-thumb-placeholder"><i class="fa fa-image"></i></div>@endif</td>
            <td><a class="news-title" href="{{ route('admin.resources.edit',['galleries',$item]) }}">{{ $item->title }}</a><div class="news-meta">/{{ $item->slug }} · oleh {{ $item->creator?->name ?? 'Administrator' }}</div><p>{{ Str::limit($item->description,100) }}</p></td>
            <td class="text-center">{{ $item->items_count }}</td><td><x-admin.badge :value="$item->status"/></td><td>{{ $item->event_date?->format('d-m-Y') ?? '-' }}</td>
            <td class="table-actions">@if($item->status==='published')<a href="{{ route('galeri-desa') }}" target="_blank" class="btn btn-success btn-sm" title="Lihat"><i class="fa fa-eye"></i></a> @endif<a href="{{ route('admin.resources.edit',['galleries',$item]) }}" class="btn bg-orange btn-sm" title="Ubah" data-no-ajax><i class="fa fa-edit"></i></a> @if($item->status!=='archived')<form method="post" action="{{ route('admin.galleries.archive',$item) }}" data-confirm="Arsipkan galeri '{{ $item->title }}'?" data-confirm-tone="warning" data-confirm-title="Arsipkan Galeri" data-confirm-button="Ya, arsipkan">@csrf @method('patch')<button class="btn btn-default btn-sm" title="Arsipkan"><i class="fa fa-archive"></i></button></form> @endif<form method="post" action="{{ route('admin.resources.destroy',['galleries',$item]) }}" data-confirm="Hapus galeri '{{ $item->title }}'? Galeri akan dipindahkan ke tempat sampah." data-confirm-tone="danger" data-confirm-title="Hapus Galeri" data-confirm-button="Ya, hapus">@csrf @method('delete')<button class="btn bg-maroon btn-sm" title="Hapus"><i class="fa fa-trash"></i></button></form></td>
        </tr>@empty<tr><td colspan="7" class="empty-state"><i class="fa fa-picture-o fa-3x"></i><h4>Belum ada galeri</h4><p>Tambahkan dokumentasi kegiatan pertama.</p></td></tr>@endforelse
        </tbody></table></div>
    </div><div class="box-footer clearfix">{{ $items->links() }}</div>
</div>
@endsection
