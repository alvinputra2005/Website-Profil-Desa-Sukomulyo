@extends('layouts.admin')
@section('title','Beranda')
@section('page-title','Beranda')
@section('page-description','Ringkasan Sistem Informasi Desa')
@section('content')
<div class="row">
@foreach($stats as $label=>$value)
@php($color=match($loop->index){0=>'bg-aqua',1=>'bg-green',2=>'bg-yellow',3=>'bg-red',4=>'bg-purple',default=>'bg-maroon'})
@php($icon=match($loop->index){0=>'fa-newspaper-o',1=>'fa-file-text-o',2=>'fa-picture-o',3=>'fa-folder-open',4=>'fa-bar-chart',default=>'fa-envelope'})
<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12"><div class="small-box {{ $color }}"><div class="inner"><h3>{{ number_format($value) }}</h3><p>{{ $label }}</p></div><div class="icon"><i class="fa {{ $icon }}"></i></div></div></div>
@endforeach
</div>
<div class="row"><div class="col-md-8"><div class="box box-info"><div class="box-header with-border"><h3 class="box-title"><i class="fa fa-envelope"></i> Pesan Terbaru</h3><div class="box-tools"><a href="{{ route('admin.messages.index') }}" class="btn btn-box-tool">Lihat Semua</a></div></div><div class="box-body no-padding"><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Pengirim</th><th>Pesan</th><th>Waktu</th></tr></thead><tbody>@forelse($messages as $message)<tr><td><a href="{{ route('admin.messages.show',$message) }}"><strong>{{ $message->name }}</strong></a><br><small>{{ $message->email }}</small></td><td>{{ Str::limit($message->message,70) }}</td><td><span class="label label-info">{{ $message->created_at->diffForHumans() }}</span></td></tr>@empty<tr><td colspan="3" class="empty-state">Belum ada pesan masuk.</td></tr>@endforelse</tbody></table></div></div></div></div>
<div class="col-md-4"><div class="box box-danger"><div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Aktivitas Terbaru</h3></div><div class="box-body">@forelse($activities as $activity)<div class="post"><div class="user-block"><span class="img-circle img-bordered-sm bg-maroon" style="float:left;width:40px;height:40px;line-height:40px;text-align:center;color:#fff">{{ strtoupper(substr($activity->user?->name??'S',0,1)) }}</span><span class="username">{{ $activity->user?->name??'Sistem' }}</span><span class="description">{{ $activity->created_at->diffForHumans() }}</span></div><p>{{ $activity->description }}</p></div>@empty<p class="text-muted">Belum ada aktivitas.</p>@endforelse</div></div><div class="box box-success"><div class="box-header with-border"><h3 class="box-title">Akses Cepat</h3></div><div class="box-body quick-actions"><a class="btn btn-app" href="{{ route('admin.resources.create','news') }}"><i class="fa fa-plus"></i> Artikel</a><a class="btn btn-app" href="{{ route('admin.resources.create','publications') }}"><i class="fa fa-file-text"></i> Publikasi</a><a class="btn btn-app" href="{{ route('admin.media.index') }}"><i class="fa fa-image"></i> Media</a></div></div></div></div>
@endsection
