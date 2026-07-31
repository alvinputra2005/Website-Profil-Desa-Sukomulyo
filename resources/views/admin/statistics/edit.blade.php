@extends('layouts.admin')

@php
    $isCreate = $isCreate ?? false;
@endphp
@section('title', $isCreate ? 'Tambah Periode Statistik' : 'Edit Data Statistik')
@section('page-description', $category->name.' · '.($dataset->short_title ?: $dataset->title))

@section('content')
<form method="post" action="{{ $isCreate ? route('admin.statistics.categories.store', $category->slug) : route('admin.statistics.categories.update', ['category' => $category->slug, 'dataset' => $dataset->slug]) }}" class="statistics-edit-form">
    @csrf @unless($isCreate) @method('put') @endunless
    @if($isCreate && $templateId)<input type="hidden" name="template_id" value="{{ $templateId }}">@endif
    @if($isCreate && $templateId)<div class="callout callout-info"><h4>Periode baru</h4><p>Struktur tabel dan daftar RW disalin dari periode sebelumnya. Semua nilai statistik dan total telah dikosongkan; isi data baru sebelum menerbitkannya.</p></div>@endif
    <div class="box box-success">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi Dataset</h3><div class="box-tools">
            <a href="{{ $isCreate ? route('admin.statistics.categories.show', $category->slug) : route('admin.statistics.categories.show', ['category' => $category->slug, 'data' => ($dataset->family && $dataset->table_number ? $dataset->family.':'.$dataset->table_number : 'dataset:'.$dataset->id), 'period' => $dataset->period]) }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-save"></i> Simpan Perubahan</button>
        </div></div>
        <div class="box-body">
            <div class="row">
                <div class="form-group col-md-8 @error('title') has-error @enderror"><label for="title">Judul Lengkap</label><input id="title" name="title" class="form-control" value="{{ old('title', $dataset->title) }}" required maxlength="255">@error('title')<span class="help-block">{{ $message }}</span>@enderror</div>
                <div class="form-group col-md-4 @error('short_title') has-error @enderror"><label for="short_title">Nama pada Dropdown</label><input id="short_title" name="short_title" class="form-control" value="{{ old('short_title', $dataset->short_title) }}" maxlength="255">@error('short_title')<span class="help-block">{{ $message }}</span>@enderror</div>
            </div>
            <div class="row">
                <div class="form-group col-md-3 @error('period') has-error @enderror"><label for="period">Tahun/Periode</label><input id="period" name="period" class="form-control" value="{{ old('period', $dataset->period) }}" placeholder="Contoh: 2023" required maxlength="50">@error('period')<span class="help-block">{{ $message }}</span>@enderror</div>
                <div class="form-group col-md-3 @error('unit') has-error @enderror"><label for="unit">Satuan</label><input id="unit" name="unit" class="form-control" value="{{ old('unit', $dataset->unit) }}" required maxlength="50">@error('unit')<span class="help-block">{{ $message }}</span>@enderror</div>
                <div class="form-group col-md-3 @error('status') has-error @enderror"><label for="status">Status Publikasi</label><select id="status" name="status" class="form-control">@foreach (['draft' => 'Draf', 'published' => 'Terbit', 'needs_review' => 'Perlu Ditinjau', 'archived' => 'Arsip'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $dataset->status) === $value)>{{ $label }}</option>@endforeach</select>@error('status')<span class="help-block">{{ $message }}</span>@enderror</div>
                <div class="form-group col-md-3 @error('source') has-error @enderror"><label for="source">Sumber</label><input id="source" name="source" class="form-control" value="{{ old('source', $dataset->source) }}" maxlength="255">@error('source')<span class="help-block">{{ $message }}</span>@enderror</div>
            </div>
            <div class="form-group @error('description') has-error @enderror"><label for="description">Deskripsi</label><textarea id="description" name="description" class="form-control" rows="3" maxlength="5000">{{ old('description', $dataset->description) }}</textarea>@error('description')<span class="help-block">{{ $message }}</span>@enderror</div>
        </div>
    </div>

    <div class="box box-warning" data-table-builder style="{{ $isCreate && !$templateId ? '' : 'display:none' }}">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-table"></i> Buat Struktur Tabel</h3></div>
        <div class="box-body">
            <p class="text-muted">Tentukan nama dan tipe setiap kolom. Kunci kolom dibuat otomatis dan dapat diubah bila diperlukan.</p>
            @if($errors->has('columns') || $errors->has('columns.*'))<div class="alert alert-danger">Periksa kembali struktur kolom tabel.</div>@endif
            <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Nama Kolom</th><th>Kunci</th><th>Tipe Data</th><th style="width:45px"></th></tr></thead><tbody data-column-list></tbody></table></div>
            <button type="button" class="btn btn-default btn-sm" data-add-column><i class="fa fa-plus"></i> Tambah Kolom</button>
            <hr>
            <div class="table-responsive"><table class="table table-bordered statistics-edit-table"><thead data-data-head></thead><tbody data-data-rows></tbody></table></div>
            <button type="button" class="btn btn-default btn-sm" data-add-row><i class="fa fa-plus"></i> Tambah Baris</button>
        </div>
        <div class="box-footer text-right"><a href="{{ route('admin.statistics.categories.show', $category->slug) }}" class="btn btn-default">Batal</a> <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan Dataset</button></div>
    </div>
    <div class="box box-warning" style="{{ $isCreate && !$templateId ? 'display:none' : '' }}">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-table"></i> Baris Data</h3><div class="box-tools"><span class="label label-default">{{ $dataset->rows->count() }} baris</span></div></div>
        <div class="box-body">
            <p class="text-muted">Ubah nilai pada sel yang diperlukan. Kolom persentase menggunakan angka tanpa tanda persen.</p>
            @if ($errors->has('rows') || $errors->has('rows.*'))<div class="alert alert-danger">Periksa kembali nilai pada tabel data.</div>@endif
            <div class="table-responsive statistics-edit-table-wrap">
                <table class="table table-bordered statistics-edit-table">
                    <x-statistic-table-header
                        :columns="$dataset->columns_json ?? []"
                        :leading="[['label' => 'No', 'class' => 'statistics-row-number']]"
                    />
                    <tbody>
                        @forelse ($dataset->rows as $rowIndex => $row)
                            <tr @if(!$isCreate) id="row-{{ $row->id }}" @endif><td class="text-center">{{ $rowIndex + 1 }}@unless($isCreate)<input type="hidden" name="rows[{{ $rowIndex }}][id]" value="{{ $row->id }}">@endunless</td>
                                @foreach ($dataset->columns_json ?? [] as $column)
                                    @php
                                        $key = $column['key'];
                                        $type = $column['type'] ?? 'string';
                                        $currentValue = match ($key) { 'area_code' => $row->area_code, 'area_name' => $row->area_name, default => data_get($row->values_json, $key) };
                                        $fieldName = "rows.$rowIndex.$key";
                                    @endphp
                                    <td class="{{ $key === 'area_name' ? 'statistics-area-name' : 'statistics-data-cell' }} @error($fieldName) has-error @enderror"><input name="rows[{{ $rowIndex }}][{{ $key }}]" value="{{ old($fieldName, $currentValue) }}" class="form-control input-sm" type="{{ in_array($type, ['integer', 'percentage'], true) ? 'number' : 'text' }}" @if ($type === 'integer') step="1" @elseif ($type === 'percentage') step="0.01" @endif aria-label="{{ $column['label'] ?? $key }}, baris {{ $rowIndex + 1 }}">@error($fieldName)<span class="help-block">{{ $message }}</span>@enderror</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($dataset->columns_json ?? []) + 1 }}" class="empty-state">Belum ada baris data.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($dataset->totals_json)
                        <tfoot><tr><th>JUMLAH TOTAL</th>
                            @foreach ($dataset->columns_json ?? [] as $column)
                                @php
                                    $key = $column['key'];
                                    $type = $column['type'] ?? 'string';
                                    $fieldName = "totals.$key";
                                @endphp
                                <th class="@error($fieldName) has-error @enderror">@if (in_array($key, ['area_code', 'area_name'], true))<span class="text-muted">—</span>@else<input name="totals[{{ $key }}]" value="{{ old($fieldName, data_get($dataset->totals_json, $key)) }}" class="form-control input-sm" type="{{ in_array($type, ['integer', 'percentage'], true) ? 'number' : 'text' }}" @if ($type === 'integer') step="1" @elseif ($type === 'percentage') step="0.01" @endif aria-label="Total {{ $column['label'] ?? $key }}">@error($fieldName)<span class="help-block">{{ $message }}</span>@enderror</th>@endif
                            @endforeach
                        </tr></tfoot>
                    @endif
                </table>
            </div>
        </div>
        <div class="box-footer text-right"><a href="{{ route('admin.statistics.categories.show', $category->slug) }}" class="btn btn-default">Batal</a> <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan Semua Perubahan</button></div>
    </div>
</form>
@php
    $manualColumns = old('columns', [
        ['label' => 'Nama Wilayah', 'key' => 'area_name', 'type' => 'string'],
        ['label' => 'Jumlah', 'key' => 'jumlah', 'type' => 'integer'],
    ]);
    $manualRows = old('rows', [[], [], []]);
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    var builder = document.querySelector('[data-table-builder]');
    if (!builder) return;
    var columns = {{ Illuminate\Support\Js::from($manualColumns) }};
    var rows = {{ Illuminate\Support\Js::from($manualRows) }};
    var columnList = builder.querySelector('[data-column-list]');
    var dataHead = builder.querySelector('[data-data-head]');
    var dataRows = builder.querySelector('[data-data-rows]');
    function slug(value) { return value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '') || 'kolom'; }
    function snapshotRows() {
        rows = Array.from(dataRows.querySelectorAll('tr')).map(function (tr) {
            var row = {}; tr.querySelectorAll('[data-key]').forEach(function (input) { row[input.dataset.key] = input.value; }); return row;
        });
    }
    function renderData() {
        dataHead.innerHTML = '<tr><th style="width:45px">No</th>' + columns.map(function (column) { return '<th>' + column.label + '</th>'; }).join('') + '<th style="width:45px"></th></tr>';
        dataRows.innerHTML = rows.map(function (row, rowIndex) {
            return '<tr><td class="text-center">' + (rowIndex + 1) + '</td>' + columns.map(function (column) {
                var type = column.type === 'string' ? 'text' : 'number'; var step = column.type === 'percentage' ? '0.01' : '1';
                return '<td><input class="form-control input-sm" data-key="' + column.key + '" name="rows[' + rowIndex + '][' + column.key + ']" type="' + type + '" step="' + step + '" value="' + String(row[column.key] == null ? '' : row[column.key]).replace(/&/g,'&amp;').replace(/"/g,'&quot;') + '"></td>';
            }).join('') + '<td><button type="button" class="btn btn-danger btn-xs" data-remove-row="' + rowIndex + '"><i class="fa fa-times"></i></button></td></tr>';
        }).join('');
    }
    function renderColumns() {
        columnList.innerHTML = columns.map(function (column, index) { return '<tr><td><input class="form-control input-sm" name="columns[' + index + '][label]" data-column-label="' + index + '" value="' + column.label + '" required></td><td><input class="form-control input-sm" name="columns[' + index + '][key]" data-column-key="' + index + '" value="' + column.key + '" pattern="[a-z][a-z0-9_]*" required></td><td><select class="form-control input-sm" name="columns[' + index + '][type]" data-column-type="' + index + '"><option value="string"' + (column.type === 'string' ? ' selected' : '') + '>Teks</option><option value="integer"' + (column.type === 'integer' ? ' selected' : '') + '>Bilangan Bulat</option><option value="percentage"' + (column.type === 'percentage' ? ' selected' : '') + '>Desimal/Persentase</option></select></td><td><button type="button" class="btn btn-danger btn-xs" data-remove-column="' + index + '"><i class="fa fa-times"></i></button></td></tr>'; }).join('');
        renderData();
    }
    builder.addEventListener('input', function (event) {
        var index;
        if (event.target.hasAttribute('data-column-label')) { index = Number(event.target.dataset.columnLabel); columns[index].label = event.target.value; if (!event.target.dataset.touched) { columns[index].key = slug(event.target.value); columnList.querySelector('[data-column-key="' + index + '"]').value = columns[index].key; } renderData(); }
        if (event.target.hasAttribute('data-column-key')) { index = Number(event.target.dataset.columnKey); event.target.dataset.touched = '1'; snapshotRows(); columns[index].key = event.target.value; renderData(); }
    });
    builder.addEventListener('change', function (event) { if (event.target.hasAttribute('data-column-type')) { snapshotRows(); columns[Number(event.target.dataset.columnType)].type = event.target.value; renderData(); } });
    builder.addEventListener('click', function (event) {
        var button = event.target.closest('button'); if (!button) return;
        snapshotRows();
        if (button.hasAttribute('data-add-column')) { var n = columns.length + 1; columns.push({label:'Kolom ' + n,key:'kolom_' + n,type:'string'}); renderColumns(); }
        if (button.hasAttribute('data-remove-column') && columns.length > 1) { columns.splice(Number(button.dataset.removeColumn), 1); renderColumns(); }
        if (button.hasAttribute('data-add-row')) { rows.push({}); renderData(); }
        if (button.hasAttribute('data-remove-row')) { rows.splice(Number(button.dataset.removeRow), 1); renderData(); }
    });
    renderColumns();
});
</script>
@endsection
