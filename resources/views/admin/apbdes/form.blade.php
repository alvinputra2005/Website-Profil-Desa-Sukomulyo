@extends('layouts.admin')
@section('title',$apbdes->exists ? 'Ubah APBDes '.$apbdes->year : 'Tambah APBDes')
@section('page-description','Form APBDes sesuai komponen LPPD Desa Sukomulyo')
@section('content')
<form method="post"
      action="{{ $apbdes->exists ? route('admin.apbdes.update', $apbdes) : route('admin.apbdes.store') }}"
      data-dirty-form data-apbdes-form>
    @csrf
    @if ($apbdes->exists) @method('put') @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Data belum dapat disimpan.</strong>
            <ul class="apbdes-error-list">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-info-circle"></i> Identitas dan Publikasi</h3></div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="required" for="apbdes-year">Tahun Anggaran</label>
                        <input id="apbdes-year" type="number" name="year" class="form-control"
                               min="1900" max="2100" value="{{ old('year', $apbdes->year) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="required" for="apbdes-title">Judul</label>
                        <input id="apbdes-title" name="title" class="form-control" maxlength="255"
                               value="{{ old('title', $apbdes->title) }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="required" for="apbdes-status">Status</label>
                        <select id="apbdes-status" name="status" class="form-control" required>
                            <option value="draft" @selected(old('status', $apbdes->status) === 'draft')>Draf</option>
                            <option value="published" @selected(old('status', $apbdes->status) === 'published')>Publik</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="apbdes-description">Deskripsi Ringkas</label>
                <textarea id="apbdes-description" name="description" class="form-control" rows="3" maxlength="5000">{{ old('description', $apbdes->description) }}</textarea>
            </div>
            <label>
                <input type="checkbox" name="is_partial_year" value="1" @checked(old('is_partial_year', $apbdes->is_partial_year))>
                Data tahun berjalan/semester (realisasi belum satu tahun penuh)
            </label>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-calculator"></i> Ringkasan Target dan Realisasi</h3></div>
        <div class="box-body">
            <p class="help-block apbdes-section-help">Masukkan nilai total sebagaimana tabel target dan realisasi LPPD. Panel pratinjau membandingkan total ini dengan penjumlahan rincian di bawah.</p>
            <div class="row">
                @foreach ([
                    ['income_budget','Target Pendapatan','data-summary-income-budget'],
                    ['income_realization','Realisasi Pendapatan','data-summary-income-realization'],
                    ['spending_budget','Anggaran Belanja','data-summary-spending-budget'],
                    ['spending_realization','Realisasi Belanja','data-summary-spending-realization'],
                    ['financing_receipt','Penerimaan Pembiayaan',''],
                    ['financing_expenditure','Pengeluaran Pembiayaan',''],
                ] as [$name,$label,$attribute])
                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label class="required" for="apbdes-{{ str_replace('_','-',$name) }}">{{ $label }} (Rp)</label>
                            <input id="apbdes-{{ str_replace('_','-',$name) }}" type="number" name="{{ $name }}"
                                   class="form-control" min="0" step="0.01"
                                   value="{{ old($name, $apbdes->{$name} ?? 0) }}" required
                                   {{ $attribute }} data-money-input>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="apbdes-reconciliation" data-reconciliation aria-live="polite">
                <div><span>Rincian pendapatan</span><strong data-revenue-total>Rp0</strong><small data-revenue-difference></small></div>
                <div><span>Rincian belanja</span><strong data-spending-total>Rp0</strong><small data-spending-difference></small></div>
                <div><span>Serapan belanja</span><strong data-spending-percentage>0%</strong><small>Realisasi ÷ anggaran</small></div>
            </div>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-sign-in"></i> Rincian Pendapatan Desa</h3>
            <div class="box-tools">
                <button type="button" class="btn btn-primary btn-sm" data-add-row="revenue"><i class="fa fa-plus"></i> Tambah Sumber</button>
            </div>
        </div>
        <div class="box-body">
            <p class="help-block apbdes-section-help">Mencakup PADes, Dana Desa, ADD, bagi hasil pajak/retribusi, bantuan pemerintah, dan pendapatan sah lainnya.</p>
            <div data-row-list="revenue">
                @foreach ($revenueRows as $index => $row)
                    @include('admin.apbdes.partials.line-row', ['type' => 'revenue', 'index' => $index, 'row' => $row])
                @endforeach
            </div>
        </div>
    </div>

    <div class="box box-warning">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-sign-out"></i> Rincian Belanja Desa</h3>
            <div class="box-tools">
                <button type="button" class="btn btn-warning btn-sm" data-add-row="spending"><i class="fa fa-plus"></i> Tambah Bidang</button>
            </div>
        </div>
        <div class="box-body">
            <p class="help-block apbdes-section-help">LPPD membagi belanja ke lima bidang utama. Baris dapat ditambah bila dokumen sumber memiliki klasifikasi lain.</p>
            <div data-row-list="spending">
                @foreach ($spendingRows as $index => $row)
                    @include('admin.apbdes.partials.line-row', ['type' => 'spending', 'index' => $index, 'row' => $row])
                @endforeach
            </div>
        </div>
    </div>

    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-tasks"></i> Program dan Kegiatan</h3>
            <div class="box-tools">
                <button type="button" class="btn btn-success btn-sm" data-add-row="program"><i class="fa fa-plus"></i> Tambah Program</button>
            </div>
        </div>
        <div class="box-body">
            <p class="help-block apbdes-section-help">Bagian ini opsional. Isi bila LPPD/APBDes menyediakan rincian kegiatan pada bidang pemerintahan, pembangunan, pembinaan, pemberdayaan, atau penanggulangan bencana.</p>
            <div data-row-list="program">
                @forelse ($programRows as $index => $row)
                    @include('admin.apbdes.partials.program-row', ['index' => $index, 'row' => $row])
                @empty
                    <div class="apbdes-empty-program" data-program-empty>Belum ada rincian program/kegiatan.</div>
                @endforelse
            </div>
            <div class="form-group">
                <label for="apbdes-programs-note">Keterangan bila rincian program tidak tersedia</label>
                <input id="apbdes-programs-note" name="programs_note" class="form-control" maxlength="2000"
                       value="{{ old('programs_note', $apbdes->programs_note) }}"
                       placeholder="Contoh: Dokumen LPPD tidak menyediakan nominal program per tahun secara lengkap.">
            </div>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text-o"></i> Catatan LPPD dan Sumber Data</h3></div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="apbdes-problems">Permasalahan Pelaksanaan Anggaran</label>
                        <textarea id="apbdes-problems" name="problems" class="form-control" rows="7" maxlength="20000" placeholder="Satu butir per baris agar mudah dibaca.">{{ old('problems', $apbdes->problems) }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="apbdes-solutions">Penyelesaian/Upaya yang Ditempuh</label>
                        <textarea id="apbdes-solutions" name="solutions" class="form-control" rows="7" maxlength="20000" placeholder="Satu butir per baris agar mudah dibaca.">{{ old('solutions', $apbdes->solutions) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="apbdes-quality">Catatan Kualitas/Rekonsiliasi Data</label>
                <textarea id="apbdes-quality" name="data_quality_notes" class="form-control" rows="4" maxlength="10000" placeholder="Contoh: total pada dokumen berbeda dengan penjumlahan lima bidang; satu catatan per baris.">{{ old('data_quality_notes', $apbdes->data_quality_notes) }}</textarea>
            </div>
            <div class="form-group">
                <label for="apbdes-quarters-note">Keterangan Data Triwulanan</label>
                <input id="apbdes-quarters-note" name="quarters_note" class="form-control" maxlength="2000"
                       value="{{ old('quarters_note', $apbdes->quarters_note) }}"
                       placeholder="Contoh: LPPD hanya menyediakan data tahunan.">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="apbdes-source-document">Dokumen Sumber</label>
                        <input id="apbdes-source-document" name="source_document" class="form-control" maxlength="255"
                               value="{{ old('source_document', $apbdes->source_document) }}" placeholder="LPPD Desa Sukomulyo">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="apbdes-source-reference">Halaman/Referensi</label>
                        <input id="apbdes-source-reference" name="source_reference" class="form-control" maxlength="2000"
                               value="{{ old('source_reference', $apbdes->source_reference) }}" placeholder="Pendapatan hlm. 12–15; belanja hlm. 16–20">
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <a href="{{ route('admin.apbdes.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
            @if ($apbdes->exists && $apbdes->status === 'published')
                <a href="{{ route('transparansi-apbdes.show', $apbdes->year) }}" target="_blank" class="btn btn-info">
                    <i class="fa fa-external-link"></i> Lihat Halaman Publik
                </a>
            @endif
            <button class="btn btn-social btn-info pull-right"><i class="fa fa-save"></i> Simpan APBDes</button>
        </div>
    </div>
</form>

<template data-row-template="revenue">
    @include('admin.apbdes.partials.line-row', ['type' => 'revenue', 'index' => '__INDEX__', 'row' => []])
</template>
<template data-row-template="spending">
    @include('admin.apbdes.partials.line-row', ['type' => 'spending', 'index' => '__INDEX__', 'row' => []])
</template>
<template data-row-template="program">
    @include('admin.apbdes.partials.program-row', ['index' => '__INDEX__', 'row' => []])
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('[data-apbdes-form]');
    if (!form) return;

    var currency = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 2 });
    var percentage = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });

    function nextIndex(type) {
        var list = form.querySelector('[data-row-list="' + type + '"]');
        var maximum = -1;
        list.querySelectorAll('[name]').forEach(function (input) {
            var match = input.name.match(/^[a-z]+\\[(\\d+)\\]/);
            if (match) maximum = Math.max(maximum, Number(match[1]));
        });
        return maximum + 1;
    }

    function bindRow(row) {
        var name = row.querySelector('[data-row-name]');
        var title = row.querySelector('[data-row-title]');
        if (name && title) {
            name.addEventListener('input', function () {
                title.textContent = name.value || 'Baris baru';
            });
        }
        var category = row.querySelector('[data-program-category]');
        var categoryLabel = row.querySelector('[data-program-category-label]');
        if (category && categoryLabel) {
            var syncCategory = function () {
                var option = category.options[category.selectedIndex];
                categoryLabel.value = option && option.value ? option.text.replace(/^\\S+\\s+—\\s+/, '') : '';
            };
            category.addEventListener('change', syncCategory);
            if (category.value) syncCategory();
        }
    }

    function addRow(type) {
        var template = document.querySelector('[data-row-template="' + type + '"]');
        var list = form.querySelector('[data-row-list="' + type + '"]');
        if (!template || !list) return;
        var wrapper = document.createElement('div');
        wrapper.innerHTML = template.innerHTML.split('__INDEX__').join(String(nextIndex(type)));
        var row = wrapper.firstElementChild;
        var empty = list.querySelector('[data-program-empty]');
        if (empty) empty.remove();
        list.appendChild(row);
        bindRow(row);
        row.querySelector('input:not([type="hidden"]), textarea, select')?.focus();
        updateReconciliation();
    }

    function sum(selector) {
        return Array.from(form.querySelectorAll(selector)).reduce(function (total, input) {
            return total + (Number(input.value) || 0);
        }, 0);
    }

    function differenceText(detailTotal, summaryTotal) {
        var difference = detailTotal - summaryTotal;
        if (Math.abs(difference) < 0.005) return 'Sesuai dengan nilai ringkasan';
        return (difference > 0 ? 'Rincian lebih besar ' : 'Rincian lebih kecil ') + currency.format(Math.abs(difference));
    }

    function updateReconciliation() {
        var revenueTotal = sum('[data-row-list="revenue"] [data-line-budget]');
        var spendingTotal = sum('[data-row-list="spending"] [data-line-budget]');
        var summaryRevenue = Number(form.querySelector('[data-summary-income-budget]').value) || 0;
        var summarySpending = Number(form.querySelector('[data-summary-spending-budget]').value) || 0;
        var summaryRealization = Number(form.querySelector('[data-summary-spending-realization]').value) || 0;
        form.querySelector('[data-revenue-total]').textContent = currency.format(revenueTotal);
        form.querySelector('[data-spending-total]').textContent = currency.format(spendingTotal);
        form.querySelector('[data-revenue-difference]').textContent = differenceText(revenueTotal, summaryRevenue);
        form.querySelector('[data-spending-difference]').textContent = differenceText(spendingTotal, summarySpending);
        form.querySelector('[data-spending-percentage]').textContent = percentage.format(summarySpending ? summaryRealization / summarySpending * 100 : 0) + '%';
    }

    form.querySelectorAll('[data-apbdes-row]').forEach(bindRow);
    form.querySelectorAll('[data-add-row]').forEach(function (button) {
        button.addEventListener('click', function () { addRow(button.dataset.addRow); });
    });
    form.addEventListener('click', function (event) {
        var button = event.target.closest('[data-remove-row]');
        if (!button) return;
        var row = button.closest('[data-apbdes-row]');
        var list = row.parentElement;
        var type = list.dataset.rowList;
        if ((type === 'revenue' || type === 'spending') && list.querySelectorAll('[data-apbdes-row]').length === 1) {
            window.alert('Minimal satu baris rincian harus tersedia.');
            return;
        }
        row.remove();
        updateReconciliation();
    });
    form.addEventListener('input', function (event) {
        if (event.target.matches('[data-money-input]')) updateReconciliation();
    });
    updateReconciliation();
});
</script>
@endpush
@endsection
