@extends('layouts.admin')
@section('title','Daftar Artikel')
@section('page-description','Kelola berita dan informasi desa')
@section('content')
<div class="box box-info news-list-box">
    <div class="box-header with-border news-list-header">
        <a href="{{ route('admin.resources.create','news') }}" class="btn btn-social btn-info btn-sm"><i class="fa fa-plus"></i> Tambah Artikel</a>
        <form id="bulk-delete-news" method="post" action="{{ route('admin.resources.bulk-destroy', 'news') }}" class="inline-form" data-confirm="Hapus artikel yang dipilih? Artikel akan dipindahkan ke tempat sampah." data-confirm-tone="danger" data-confirm-title="Hapus Artikel Terpilih" data-confirm-button="Ya, hapus">
            @csrf @method('delete')
            <button class="btn btn-danger btn-sm" type="submit" data-bulk-button disabled><i class="fa fa-trash"></i> Hapus Terpilih</button>
        </form>
        <a href="{{ route('admin.resources.index','categories') }}" class="btn btn-default btn-sm"><i class="fa fa-tags"></i> Kelola Kategori</a>
        <a href="{{ route('admin.resources.index',['resource'=>'news','status'=>'archived']) }}" class="btn btn-default btn-sm"><i class="fa fa-archive"></i> Arsip</a>
        <a href="{{ route('admin.news.trash') }}" class="btn btn-default btn-sm"><i class="fa fa-trash"></i> Tempat Sampah</a>
    </div>
    <div class="box-body">
        <form class="news-filters" method="get">
            <div class="form-group"><label for="news-search">Pencarian</label><div class="input-group"><input id="news-search" class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari judul, slug, atau ringkasan..."><span class="input-group-btn"><button class="btn btn-info"><i class="fa fa-search"></i> Cari</button></span></div></div>
            <div class="form-group"><label for="status">Status</label><select class="form-control select2" id="status" name="status" onchange="this.form.submit()"><option value="">Semua status</option>@foreach(['draft'=>'Draf','published'=>'Terbit','archived'=>'Arsip'] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="form-group"><label for="category">Kategori</label><select class="form-control select2" id="category" name="category" onchange="this.form.submit()"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)request('category')===(string)$category->id)>{{ $category->name }}</option>@endforeach</select></div>
        </form>
        <div class="table-responsive"><table class="table table-bordered table-striped table-hover news-table"><thead><tr><th style="width:38px"><input type="checkbox" aria-label="Pilih semua artikel pada halaman ini" data-bulk-select-all></th><th class="text-center" style="width:55px">No</th><th style="width:90px">Gambar</th><th>Judul Artikel</th><th style="width:150px">Kategori</th><th style="width:115px">Status</th><th style="width:170px">Diposting</th><th style="width:185px">Aksi</th></tr></thead><tbody>
        @forelse($items as $item)<tr>
            <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" form="bulk-delete-news" aria-label="Pilih {{ $item->title }}" data-bulk-item></td><td class="text-center">{{ $items->firstItem()+$loop->index }}</td><td>@if($item->featuredImage)<img class="news-thumb" src="{{ $item->featuredImage->url }}" alt="">@else<div class="news-thumb-placeholder"><i class="fa fa-image"></i></div>@endif</td>
            <td><a class="news-title" href="{{ route('admin.resources.edit',['news',$item]) }}">{{ $item->title }}</a><div class="news-meta">/{{ $item->slug }} · oleh {{ $item->author?->name ?? 'Administrator' }}</div><p>{{ Str::limit($item->excerpt ?: strip_tags($item->content),100) }}</p></td>
            <td>{{ $item->category?->name ?? '-' }}</td><td><x-admin.badge :value="$item->status"/></td><td>{{ $item->published_at?->format('d-m-Y H:i') ?? 'Belum dijadwalkan' }}</td>
            <td class="table-actions"><a href="{{ route('berita-desa.show',$item->slug) }}" target="_blank" class="btn btn-success btn-sm" title="Lihat"><i class="fa fa-eye"></i></a> <a href="{{ route('admin.resources.edit',['news',$item]) }}" class="btn bg-orange btn-sm" title="Ubah" data-no-ajax><i class="fa fa-edit"></i></a> @if($item->status !== 'archived')<form method="post" action="{{ route('admin.news.archive',$item) }}" data-confirm="Arsipkan artikel '{{ $item->title }}'?" data-confirm-tone="warning" data-confirm-title="Arsipkan Artikel" data-confirm-button="Ya, arsipkan">@csrf @method('patch')<button class="btn btn-default btn-sm" title="Arsipkan"><i class="fa fa-archive"></i></button></form> @endif<form method="post" action="{{ route('admin.resources.destroy',['news',$item]) }}" data-confirm="Hapus artikel '{{ $item->title }}'? Artikel akan dipindahkan ke tempat sampah." data-confirm-tone="danger" data-confirm-title="Hapus Artikel" data-confirm-button="Ya, hapus">@csrf @method('delete')<button class="btn bg-maroon btn-sm" title="Hapus"><i class="fa fa-trash"></i></button></form></td>
        </tr>@empty<tr><td colspan="8" class="empty-state"><i class="fa fa-newspaper-o fa-3x"></i><h4>Belum ada artikel</h4><p>Tambahkan berita pertama untuk Desa Sukomulyo.</p></td></tr>@endforelse
        </tbody></table></div>
    </div><div class="box-footer clearfix">{{ $items->links() }}</div>
</div>
@endsection
