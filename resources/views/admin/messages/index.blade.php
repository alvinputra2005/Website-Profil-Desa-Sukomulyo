@extends('layouts.admin')
@section('title','Pesan Masuk')
@section('page-description','Kotak masuk layanan masyarakat')
@section('content')
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Inbox</h3>
        <div class="box-tools admin-list-actions">
            <form>
                <div class="input-group input-group-sm" style="width:250px">
                    <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari pesan...">
                    <span class="input-group-btn"><button class="btn btn-default"><i class="fa fa-search"></i></button></span>
                </div>
            </form>
            <form id="bulk-delete-messages" method="post" action="{{ route('admin.messages.bulk-destroy') }}" class="inline-form" data-confirm="Hapus pesan yang dipilih?" data-confirm-tone="danger" data-confirm-title="Hapus Pesan Terpilih" data-confirm-button="Ya, hapus">
                @csrf
                @method('delete')
                <button class="btn btn-danger btn-sm" type="submit" data-bulk-button disabled><i class="fa fa-trash"></i> Hapus Terpilih</button>
            </form>
        </div>
    </div>
    <div class="box-body no-padding">
        <div class="mailbox-controls"><span class="btn btn-default btn-sm"><i class="fa fa-inbox"></i> {{ $items->total() }} pesan</span></div>
        <div class="table-responsive mailbox-messages">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th style="width:38px"><input type="checkbox" aria-label="Pilih semua pesan pada halaman ini" data-bulk-select-all></th>
                        <th style="width:36px"></th>
                        <th>Nama</th>
                        <th>Pesan</th>
                        <th style="width:150px">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" form="bulk-delete-messages" aria-label="Pilih pesan dari {{ $item->name }}" data-bulk-item></td>
                            <td class="mailbox-star"><i class="fa fa-star text-yellow"></i></td>
                            <td class="mailbox-name"><a href="{{ route('admin.messages.show',$item) }}"><strong>{{ $item->name }}</strong></a></td>
                            <td class="mailbox-subject"><b>{{ $item->email }}</b> - {{ Str::limit($item->message,90) }}</td>
                            <td class="mailbox-date">{{ $item->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">Belum ada pesan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer">{{ $items->links() }}</div>
</div>
@endsection
