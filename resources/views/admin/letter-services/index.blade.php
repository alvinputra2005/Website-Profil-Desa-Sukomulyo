@extends('layouts.admin')
@section('title','Layanan Surat')
@section('content')
<div class="box box-primary">
    <div class="box-header">
        <a class="btn btn-primary" href="{{ route('admin.letter-services.create') }}"><i class="fa fa-plus"></i> Tambah Layanan</a>
        <form id="bulk-delete-letter-services" method="post" action="{{ route('admin.letter-services.bulk-destroy') }}" class="inline-form" data-confirm="Hapus layanan surat yang dipilih? Layanan yang sudah memiliki permohonan tidak dapat dihapus." data-confirm-tone="danger" data-confirm-title="Hapus Layanan Terpilih" data-confirm-button="Ya, hapus">
            @csrf
            @method('delete')
            <button class="btn btn-danger btn-sm" type="submit" data-bulk-button disabled><i class="fa fa-trash"></i> Hapus Terpilih</button>
        </form>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width:38px"><input type="checkbox" aria-label="Pilih semua layanan surat" data-bulk-select-all></th>
                    <th>Kode</th>
                    <th>Ikon</th>
                    <th>Layanan</th>
                    <th>Status</th>
                    <th>Permohonan</th>
                    <th style="width:150px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $service->id }}" form="bulk-delete-letter-services" aria-label="Pilih {{ $service->name }}" data-bulk-item></td>
                        <td>{{ $service->code }}</td>
                        <td><i class="fa {{ $service->icon ?: config('letter_services.default_icon') }}" aria-hidden="true"></i></td>
                        <td>{{ $service->name }}</td>
                        <td>{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td>{{ $service->applications_count }}</td>
                        <td class="table-actions">
                            <a class="btn btn-xs btn-warning" href="{{ route('admin.letter-services.edit',$service) }}"><i class="fa fa-edit"></i> Edit</a>
                            <form method="post" action="{{ route('admin.letter-services.destroy',$service) }}" data-confirm="Hapus layanan surat '{{ $service->name }}'?" data-confirm-tone="danger">
                                @csrf
                                @method('delete')
                                <button class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">Belum ada layanan surat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
