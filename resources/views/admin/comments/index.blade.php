@extends('layouts.admin')
@section('title', 'Komentar Masyarakat')
@section('page-description', 'Antrian komentar warga untuk ditinjau')
@section('content')
@php($pageLabels=['identitas'=>'Identitas Desa','history'=>'Sejarah Desa','vision'=>'Visi Desa','mission'=>'Misi Desa','struktur-pemerintahan'=>'Struktur Pemerintahan','potential'=>'Potensi Desa','sejarah'=>'Sejarah Desa','visi-misi'=>'Visi dan Misi','potensi-desa'=>'Potensi Desa'])
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Daftar Komentar</h3>
        <span class="label label-warning" style="margin-left:8px">{{ $pendingCount }} pending</span>
        <div class="box-tools admin-list-actions">
            <form class="form-inline" style="display:flex;align-items:center;gap:8px;flex-wrap:nowrap">
                <div class="input-group input-group-sm" style="width:320px">
                    <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari nama, alamat, atau isi komentar...">
                    <span class="input-group-btn"><button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button></span>
                </div>
                <select name="status" class="form-control input-sm" style="width:140px">
                    <option value="">Semua Status</option>
                    @foreach (['pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
            <form id="bulk-delete-comments" method="post" action="{{ route('admin.comments.bulk-destroy') }}" class="inline-form" data-confirm="Hapus komentar yang dipilih?" data-confirm-tone="danger" data-confirm-title="Hapus Komentar Terpilih" data-confirm-button="Ya, hapus">
                @csrf
                @method('delete')
                <button class="btn btn-danger btn-sm" type="submit" data-bulk-button disabled><i class="fa fa-trash"></i> Hapus Terpilih</button>
            </form>
        </div>
    </div>
    <div class="box-body no-padding">
        <div class="table-responsive mailbox-messages">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th style="width:38px"><input type="checkbox" aria-label="Pilih semua komentar pada halaman ini" data-bulk-select-all></th>
                        <th>Nama</th>
                        <th>Isi Komentar</th>
                        <th style="width:150px">Status</th>
                        <th style="width:160px">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" form="bulk-delete-comments" aria-label="Pilih komentar dari {{ $item->name }}" data-bulk-item></td>
                            <td class="mailbox-name">
                                <a href="{{ route('admin.comments.show', $item) }}"><strong>{{ $item->name }}</strong></a>
                                <div class="text-muted small">{{ $pageLabels[$item->page_key] ?? $item->page_key }}</div>
                            </td>
                            <td class="mailbox-subject">{{ Str::limit($item->comment, 110) }}</td>
                            <td>
                                @php($badge = ['pending' => 'label-warning', 'approved' => 'label-success', 'rejected' => 'label-danger'][$item->status] ?? 'label-default')
                                <span class="label {{ $badge }}">{{ ucfirst($item->status) }}</span>
                            </td>
                            <td class="mailbox-date">{{ $item->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">Belum ada komentar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer">{{ $items->links() }}</div>
</div>
@endsection
