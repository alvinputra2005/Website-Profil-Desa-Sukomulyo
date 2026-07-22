@extends('layouts.admin')
@section('title', 'Media')
@section('page-description', 'Pustaka gambar dan dokumen')
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="box box-success">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-upload"></i> Unggah Media</h3></div>
            <form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label class="required">Pilih File</label>
                        <input type="file" name="file" required>
                        <p class="help-block">JPG, PNG, WEBP, PDF, Office, CSV. Maksimal 10 MB.</p>
                    </div>
                    <div class="form-group">
                        <label class="required">Kategori penyimpanan</label>
                        <select class="form-control" name="category" required>
                            <option value="news" @selected(old('category') === 'news')>Gambar berita</option>
                            <option value="gallery" @selected(old('category') === 'gallery')>Gambar galeri</option>
                            <option value="officials" @selected(old('category') === 'officials')>Foto perangkat desa</option>
                            <option value="banners" @selected(old('category') === 'banners')>Banner</option>
                            <option value="documents" @selected(old('category') === 'documents')>Dokumen publik</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="required">Nama konten / folder</label>
                        <input class="form-control" name="folder_name" maxlength="255" required value="{{ old('folder_name') }}" placeholder="Contoh: Musyawarah Desa 2026">
                        <p class="help-block">Nama otomatis diubah menjadi folder aman, misalnya <code>musyawarah-desa-2026</code>.</p>
                    </div>
                    <div class="form-group"><label>Teks Alternatif</label><input class="form-control" name="alt_text" value="{{ old('alt_text') }}"></div>
                    <div class="form-group"><label>Keterangan</label><textarea class="form-control" name="caption" rows="3">{{ old('caption') }}</textarea></div>
                </div>
                <div class="box-footer"><button class="btn btn-social btn-success"><i class="fa fa-upload"></i> Unggah</button></div>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar Media</h3>
                <div class="box-tools"><form><div class="input-group input-group-sm" style="width:220px"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari file..."><span class="input-group-btn"><button class="btn btn-default"><i class="fa fa-search"></i></button></span></div></form></div>
            </div>
            <div class="box-body"><div class="media-grid">
                @forelse($items as $item)
                    <div class="media-card">
                        <div class="media-preview">@if(str_starts_with($item->mime_type, 'image/'))<img src="{{ $item->url }}" alt="{{ $item->alt_text }}">@else<i class="fa fa-file-o fa-4x text-muted"></i>@endif</div>
                        <div class="media-info">
                            <strong title="{{ $item->original_name }}">{{ $item->original_name }}</strong>
                            <small>ID: {{ $item->id }} · {{ number_format($item->file_size / 1024) }} KB</small>
                            <form method="post" action="{{ route('admin.media.destroy', $item) }}" data-confirm="Hapus media ini?" class="pull-right">@csrf @method('delete')<button class="btn btn-xs bg-maroon"><i class="fa fa-trash"></i></button></form>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Belum ada media.</div>
                @endforelse
            </div></div>
            <div class="box-footer">{{ $items->links() }}</div>
        </div>
    </div>
</div>
@endsection
