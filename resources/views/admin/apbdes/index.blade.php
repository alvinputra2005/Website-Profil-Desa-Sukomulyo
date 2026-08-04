@extends('layouts.admin')
@section('title','APBDes')
@section('page-description','Kelola pendapatan, belanja, pembiayaan, dan realisasi anggaran desa')
@section('content')
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-money"></i> Data APBDes Desa Sukomulyo</h3>
        <div class="box-tools admin-list-actions">
            <a href="{{ route('admin.apbdes.create') }}" class="btn btn-social btn-info btn-sm">
                <i class="fa fa-plus"></i> Tambah APBDes
            </a>
            <form id="bulk-delete-apbdes" method="post" action="{{ route('admin.apbdes.bulk-destroy') }}" class="inline-form" data-confirm="Hapus APBDes yang dipilih?" data-confirm-tone="danger" data-confirm-title="Hapus APBDes Terpilih" data-confirm-button="Ya, hapus">
                @csrf
                @method('delete')
                <button class="btn btn-danger btn-sm" type="submit" data-bulk-button disabled><i class="fa fa-trash"></i> Hapus Terpilih</button>
            </form>
        </div>
    </div>
    <div class="box-body">
        <div class="callout callout-info">
            <h4>Struktur mengikuti LPPD</h4>
            <p>Setiap tahun memuat target dan realisasi pendapatan, lima bidang belanja, pembiayaan, program/kegiatan, permasalahan, penyelesaian, serta referensi sumber. Hanya data berstatus publik yang tampil pada website desa.</p>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover apbdes-index-table">
                <thead>
                    <tr>
                        <th style="width:38px"><input type="checkbox" aria-label="Pilih semua APBDes pada halaman ini" data-bulk-select-all></th>
                        <th>Tahun</th>
                        <th>Target Pendapatan</th>
                        <th>Anggaran Belanja</th>
                        <th>Realisasi Belanja</th>
                        <th>Serapan</th>
                        <th>Status</th>
                        <th>Diperbarui</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($budgets as $budget)
                        @php
                            $spending = (float) $budget->spending_budget;
                            $realization = (float) $budget->spending_realization;
                            $percentage = $spending > 0 ? $realization / $spending * 100 : 0;
                        @endphp
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $budget->id }}" form="bulk-delete-apbdes" aria-label="Pilih APBDes {{ $budget->year }}" data-bulk-item></td>
                            <td>
                                <strong>{{ $budget->year }}</strong>
                                @if ($budget->is_partial_year)
                                    <span class="label label-warning">Berjalan</span>
                                @endif
                            </td>
                            <td>Rp{{ number_format((float) $budget->income_budget, 2, ',', '.') }}</td>
                            <td>Rp{{ number_format($spending, 2, ',', '.') }}</td>
                            <td>Rp{{ number_format($realization, 2, ',', '.') }}</td>
                            <td><strong>{{ number_format($percentage, 2, ',', '.') }}%</strong></td>
                            <td>
                                <span class="label {{ $budget->status === 'published' ? 'label-success' : 'label-default' }}">
                                    {{ $budget->status === 'published' ? 'Publik' : 'Draf' }}
                                </span>
                            </td>
                            <td>
                                {{ $budget->updated_at?->format('d-m-Y H:i') }}
                                @if ($budget->updatedBy)
                                    <small class="text-muted">oleh {{ $budget->updatedBy->name }}</small>
                                @endif
                            </td>
                            <td class="table-actions">
                                @if ($budget->status === 'published')
                                    <a href="{{ route('transparansi-apbdes.show', $budget->year) }}" target="_blank" class="btn btn-xs btn-info" aria-label="Lihat APBDes {{ $budget->year }}">
                                        <i class="fa fa-external-link"></i>
                                    </a>
                                @endif
                                <a href="{{ route('admin.apbdes.edit', $budget) }}" class="btn btn-xs btn-warning" aria-label="Ubah APBDes {{ $budget->year }}">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form method="post" action="{{ route('admin.apbdes.toggle-publication', $budget) }}">
                                    @csrf @method('patch')
                                    <button class="btn btn-xs {{ $budget->status === 'published' ? 'btn-default' : 'btn-success' }}"
                                            aria-label="{{ $budget->status === 'published' ? 'Sembunyikan' : 'Publikasikan' }} APBDes {{ $budget->year }}">
                                        <i class="fa {{ $budget->status === 'published' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    </button>
                                </form>
                                <form method="post" action="{{ route('admin.apbdes.destroy', $budget) }}"
                                      data-confirm="Hapus seluruh data APBDes tahun {{ $budget->year }}?"
                                      data-confirm-tone="danger">
                                    @csrf @method('delete')
                                    <button class="btn btn-xs btn-danger" aria-label="Hapus APBDes {{ $budget->year }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-state">
                                <i class="fa fa-money"></i><br>
                                Belum ada data APBDes. Tambahkan tahun anggaran pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $budgets->links() }}
    </div>
</div>
@endsection
