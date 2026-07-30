@extends('layouts.admin')

@section('title', 'Edit Data Statistik')
@section('page-description', $category->name.' · '.($dataset->short_title ?: $dataset->title))

@section('content')
<form method="post" action="{{ route('admin.statistics.categories.update', ['category' => $category->slug, 'dataset' => $dataset->slug]) }}" class="statistics-edit-form">
    @csrf @method('put')
    <div class="box box-success">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi Dataset</h3><div class="box-tools">
            <a href="{{ route('admin.statistics.categories.show', ['category' => $category->slug, 'data' => ($dataset->family && $dataset->table_number ? $dataset->family.':'.$dataset->table_number : 'dataset:'.$dataset->id), 'period' => $dataset->period]) }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-save"></i> Simpan Perubahan</button>
        </div></div>
        <div class="box-body">
            <div class="row">
                <div class="form-group col-md-8 @error('title') has-error @enderror"><label for="title">Judul Lengkap</label><input id="title" name="title" class="form-control" value="{{ old('title', $dataset->title) }}" required maxlength="255">@error('title')<span class="help-block">{{ $message }}</span>@enderror</div>
                <div class="form-group col-md-4 @error('short_title') has-error @enderror"><label for="short_title">Nama pada Dropdown</label><input id="short_title" name="short_title" class="form-control" value="{{ old('short_title', $dataset->short_title) }}" maxlength="255">@error('short_title')<span class="help-block">{{ $message }}</span>@enderror</div>
            </div>
            <div class="row">
                <div class="form-group col-md-3 @error('period') has-error @enderror"><label for="period">Tahun/Periode</label><input id="period" name="period" class="form-control" value="{{ old('period', $dataset->period ?: $dataset->year) }}" required maxlength="50">@error('period')<span class="help-block">{{ $message }}</span>@enderror</div>
                <div class="form-group col-md-3 @error('unit') has-error @enderror"><label for="unit">Satuan</label><input id="unit" name="unit" class="form-control" value="{{ old('unit', $dataset->unit) }}" required maxlength="50">@error('unit')<span class="help-block">{{ $message }}</span>@enderror</div>
                <div class="form-group col-md-3 @error('status') has-error @enderror"><label for="status">Status Publikasi</label><select id="status" name="status" class="form-control">@foreach (['draft' => 'Draf', 'published' => 'Terbit', 'needs_review' => 'Perlu Ditinjau', 'archived' => 'Arsip'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $dataset->status) === $value)>{{ $label }}</option>@endforeach</select>@error('status')<span class="help-block">{{ $message }}</span>@enderror</div>
                <div class="form-group col-md-3 @error('source') has-error @enderror"><label for="source">Sumber</label><input id="source" name="source" class="form-control" value="{{ old('source', $dataset->source) }}" maxlength="255">@error('source')<span class="help-block">{{ $message }}</span>@enderror</div>
            </div>
            <div class="form-group @error('description') has-error @enderror"><label for="description">Deskripsi</label><textarea id="description" name="description" class="form-control" rows="3" maxlength="5000">{{ old('description', $dataset->description) }}</textarea>@error('description')<span class="help-block">{{ $message }}</span>@enderror</div>
        </div>
    </div>

    <div class="box box-warning">
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
                            <tr id="row-{{ $row->id }}"><td class="text-center">{{ $rowIndex + 1 }}<input type="hidden" name="rows[{{ $rowIndex }}][id]" value="{{ $row->id }}"></td>
                                @foreach ($dataset->columns_json ?? [] as $column)
                                    @php($key = $column['key']) @php($type = $column['type'] ?? 'string')
                                    @php($currentValue = match ($key) { 'area_code' => $row->area_code, 'area_name' => $row->area_name, default => data_get($row->values_json, $key) })
                                    @php($fieldName = "rows.$rowIndex.$key")
                                    <td class="@error($fieldName) has-error @enderror"><input name="rows[{{ $rowIndex }}][{{ $key }}]" value="{{ old($fieldName, $currentValue) }}" class="form-control input-sm" type="{{ in_array($type, ['integer', 'percentage'], true) ? 'number' : 'text' }}" @if ($type === 'integer') step="1" @elseif ($type === 'percentage') step="0.01" @endif aria-label="{{ $column['label'] ?? $key }}, baris {{ $rowIndex + 1 }}">@error($fieldName)<span class="help-block">{{ $message }}</span>@enderror</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($dataset->columns_json ?? []) + 1 }}" class="empty-state">Belum ada baris data.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($dataset->totals_json)
                        <tfoot><tr><th>JUMLAH TOTAL</th>
                            @foreach ($dataset->columns_json ?? [] as $column)
                                @php($key = $column['key']) @php($type = $column['type'] ?? 'string') @php($fieldName = "totals.$key")
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
@endsection
