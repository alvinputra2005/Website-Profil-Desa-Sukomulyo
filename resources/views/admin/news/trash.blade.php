@extends('layouts.admin')
@section('title','Tempat Sampah Artikel')
@section('page-description','Pulihkan atau hapus permanen artikel')
@section('content')
<div class="box box-info"><div class="box-header with-border">
 <a href="{{ route('admin.resources.index','news') }}" class="btn btn-info btn-sm"><i class="fa fa-arrow-left"></i> Daftar Artikel</a>
 @if($items->total())<form class="pull-right" method="post" action="{{ route('admin.news.empty-trash') }}" data-confirm="Hapus permanen semua artikel di tempat sampah?">@csrf @method('delete')<button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Kosongkan Tempat Sampah</button></form>@endif
</div><div class="box-body"><div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Judul</th><th>Kategori</th><th>Dihapus</th><th style="width:190px">Aksi</th></tr></thead><tbody>
@forelse($items as $item)<tr><td><strong>{{ $item->title }}</strong><div class="news-meta">/{{ $item->slug }}</div></td><td>{{ $item->category?->name ?? '-' }}</td><td>{{ $item->deleted_at?->format('d-m-Y H:i') }}</td><td class="table-actions"><form method="post" action="{{ route('admin.news.restore',$item) }}">@csrf @method('patch')<button class="btn btn-success btn-sm"><i class="fa fa-undo"></i> Pulihkan</button></form> <form method="post" action="{{ route('admin.news.force-delete',$item) }}" data-confirm="Hapus permanen artikel '{{ $item->title }}'?">@csrf @method('delete')<button class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Permanen</button></form></td></tr>
@empty<tr><td colspan="4" class="empty-state"><i class="fa fa-trash-o fa-3x"></i><h4>Tempat sampah kosong</h4></td></tr>@endforelse
</tbody></table></div></div><div class="box-footer clearfix">{{ $items->links() }}</div></div>
@endsection
