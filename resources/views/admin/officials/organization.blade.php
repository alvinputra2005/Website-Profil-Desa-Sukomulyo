@extends('layouts.admin')
@section('title', 'Bagan Organisasi Pemerintah Desa')
@section('page-description', 'Struktur atasan dan perangkat desa aktif')

@section('content')
<div class="box box-info">
    <div class="box-header with-border">
        <a href="{{ route('admin.officials.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
        <button type="button" onclick="window.print()" class="btn btn-social bg-purple btn-sm"><i class="fa fa-print"></i> Cetak Bagan</button>
    </div>
    <div class="box-body organization-chart">
        @forelse($nodes as $node)
            @include('admin.officials.partials.organization-node', ['node' => $node])
        @empty
            <div class="empty-state"><i class="fa fa-sitemap fa-3x"></i><h4>Belum ada perangkat desa aktif</h4></div>
        @endforelse
    </div>
</div>
@endsection
