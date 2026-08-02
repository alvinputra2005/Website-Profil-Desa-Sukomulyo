@extends('layouts.admin')
@section('title', 'Tinjau Komentar')
@section('page-description', 'Lihat detail komentar dan tentukan statusnya')
@section('content')
@php($pageLabels=['identitas'=>'Identitas Desa','history'=>'Sejarah Desa','vision'=>'Visi Desa','mission'=>'Misi Desa','struktur-pemerintahan'=>'Struktur Pemerintahan','potential'=>'Potensi Desa','sejarah'=>'Sejarah Desa','visi-misi'=>'Visi dan Misi','potensi-desa'=>'Potensi Desa'])
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">{{ $comment->name }}</h3>
        <div class="box-tools pull-right">
            <a href="{{ route('admin.comments.index') }}" class="btn btn-box-tool"><i class="fa fa-times"></i></a>
        </div>
    </div>
    <div class="box-body">
        <p class="text-muted">
            Halaman: <strong>{{ $pageLabels[$comment->page_key] ?? $comment->page_key }}</strong> |
            Status: <span class="label {{ ['pending' => 'label-warning', 'approved' => 'label-success', 'rejected' => 'label-danger'][$comment->status] ?? 'label-default' }}">{{ ucfirst($comment->status) }}</span> |
            Dikirim: {{ $comment->created_at->format('d M Y H:i') }}
        </p>
        <div style="white-space:pre-wrap;font-size:16px;line-height:1.7">{{ $comment->comment }}</div>
        <hr>
        <dl class="dl-horizontal">
            <dt>Alamat</dt><dd>{{ $comment->address }}</dd>
            <dt>Nomor HP</dt><dd>{{ $comment->phone }}</dd>
            <dt>Like</dt><dd>{{ $comment->like_count }}</dd>
            <dt>Ditinjau</dt><dd>{{ $comment->reviewed_at?->format('d M Y H:i') ?? '-' }}</dd>
            <dt>Catatan</dt><dd>{{ $comment->review_note ?: '-' }}</dd>
        </dl>
    </div>
    <div class="box-footer">
        <form method="post" action="{{ route('admin.comments.review', $comment) }}" class="form-inline" style="display:inline-block">
            @csrf
            @method('patch')
            <div class="form-group" style="margin-right:10px">
                <textarea name="review_note" class="form-control" rows="2" placeholder="Catatan tinjauan">{{ old('review_note', $comment->review_note) }}</textarea>
            </div>
            <button name="status" value="approved" class="btn btn-success"><i class="fa fa-check"></i> Setujui</button>
            <button name="status" value="rejected" class="btn btn-danger"><i class="fa fa-times"></i> Tolak</button>
            <button name="status" value="pending" class="btn btn-default"><i class="fa fa-clock-o"></i> Kembalikan Pending</button>
        </form>
        <a href="{{ route('admin.comments.index') }}" class="btn btn-default pull-right"><i class="fa fa-reply"></i> Kembali</a>
    </div>
</div>
@endsection
