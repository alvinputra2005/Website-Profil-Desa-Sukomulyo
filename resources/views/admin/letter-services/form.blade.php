@extends('layouts.admin')

@section('title', $letterService->exists ? 'Edit Jenis Surat' : 'Tambah Jenis Surat')

@push('head')
<style>
    .letter-service-form { --letter-green: var(--admin-primary, #526b42); --letter-green-soft: #eef5e9; --letter-border: var(--admin-border, #dfe3da); }
    .letter-service-form .form-section { margin-bottom: 18px; overflow: hidden; border: 1px solid var(--letter-border); border-radius: 2px; box-shadow: none; }
    .letter-service-form .form-section__heading { display: flex; align-items: flex-start; gap: 12px; padding: 14px 15px; border-bottom: 1px solid var(--letter-border); background: #f8f9f6; }
    .letter-service-form .form-section__icon { display: grid; width: 34px; height: 34px; flex: 0 0 34px; place-items: center; border-radius: 2px; background: var(--letter-green-soft); color: var(--letter-green); font-size: 16px; }
    .letter-service-form .form-section__heading h3 { margin: 1px 0 4px; color: var(--admin-ink, #252a31); font-size: 16px; font-weight: 700; }
    .letter-service-form .form-section__heading p { margin: 0; color: var(--admin-muted, #6f7870); font-size: 13px; line-height: 1.5; }
    .letter-service-form .form-section__body { padding: 18px 15px; }
    .letter-service-form .control-label { color: var(--admin-ink, #252a31); font-weight: 600; }
    .letter-service-form .required-mark { color: #c33d32; }
    .letter-service-form .help-block { color: #708077; font-size: 12px; margin: 5px 0 0; }
    .letter-service-form .form-control { min-height: 34px; border-color: #ccd3c8; border-radius: 2px; box-shadow: none; }
    .letter-service-form textarea.form-control { min-height: auto; }
    .letter-service-form .form-control:focus { border-color: var(--letter-green); box-shadow: 0 0 0 2px rgba(82, 107, 66, .14); }
    .letter-service-form .icon-picker { display: grid; grid-template-columns: repeat(auto-fill, minmax(112px, 1fr)); gap: 8px; }
    .letter-service-form .icon-picker__option { position: relative; margin: 0; cursor: pointer; }
    .letter-service-form .icon-picker__option input { position: absolute; width: 1px; height: 1px; opacity: 0; }
    .letter-service-form .icon-picker__content { display: flex; min-height: 72px; align-items: center; gap: 9px; padding: 10px; border: 1px solid #ccd3c8; border-radius: 4px; background: #fff; color: #4a534d; transition: border-color .15s, background .15s, box-shadow .15s; }
    .letter-service-form .icon-picker__content i { display: grid; width: 34px; height: 34px; flex: 0 0 34px; place-items: center; border-radius: 50%; background: var(--letter-green-soft); color: var(--letter-green); font-size: 17px; }
    .letter-service-form .icon-picker__content span { font-size: 12px; font-weight: 600; line-height: 1.25; }
    .letter-service-form .icon-picker__option input:checked + .icon-picker__content { border-color: var(--letter-green); background: #f5f8f2; box-shadow: 0 0 0 2px rgba(82, 107, 66, .15); }
    .letter-service-form .icon-picker__option input:focus + .icon-picker__content { outline: 2px solid #72a4d4; outline-offset: 2px; }
    .letter-service-form .requirement-table { margin-bottom: 0; min-width: 1320px; }
    .letter-service-form .requirement-table > thead > tr > th { padding: 11px 12px; border-bottom: 2px solid var(--letter-green); background: #f2f5ef; color: var(--admin-ink, #252a31); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; vertical-align: middle; }
    .letter-service-form .requirement-table > tbody > tr > td { padding: 12px; border-color: #e7eeea; vertical-align: top; }
    .letter-service-form .requirement-table .form-group { margin: 0; }
    .letter-service-form .requirement-table .checkbox { margin: 9px 0; white-space: nowrap; }
    .letter-service-form .requirement-table .btn-remove-requirement { min-width: 38px; min-height: 38px; }
    .letter-service-form .requirements-scroll { overflow-x: auto; border: 1px solid var(--letter-border); border-radius: 2px; }
    .letter-service-form .requirements-actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 14px; }
    .letter-service-form .requirements-count { color: #607168; font-size: 13px; }
    .letter-service-form .form-field-table { min-width: 1180px; }
    .letter-service-form .form-field-table textarea { min-width: 220px; resize: vertical; }
    .letter-service-form .condition-input { min-width: 150px; }
    .letter-service-form .field-options-help { display: block; margin-top: 4px; color: #758078; font-size: 11px; line-height: 1.35; }
    .letter-service-form .service-status { display: flex; align-items: center; gap: 10px; min-height: 40px; padding: 8px 12px; border: 1px solid #cbd7c4; border-radius: 2px; background: #f3f7ef; color: #3d5032; }
    .letter-service-form .service-status input { width: 17px; height: 17px; margin: 0; }
    .letter-service-form .footer-actions { display: flex; justify-content: flex-end; gap: 10px; }
    .letter-service-form .btn-primary { border-color: var(--admin-primary-dark, #3d5032); background: var(--letter-green); }
    .letter-service-form .btn-primary:hover, .letter-service-form .btn-primary:focus { border-color: #304027; background: var(--admin-primary-dark, #3d5032); }
    @media (max-width: 767px) { .letter-service-form .form-section__body { padding: 15px; } .letter-service-form .requirements-actions { align-items: flex-start; flex-direction: column; } .letter-service-form .footer-actions { justify-content: stretch; flex-direction: column-reverse; } .letter-service-form .footer-actions .btn { width: 100%; } }
</style>
@endpush

@section('content')
@php
    $defaultRequirements = [
        ['code' => 'ktp-pemohon', 'label' => 'Fotokopi KTP Pemohon', 'description' => 'KTP pemohon yang masih berlaku', 'required' => true],
        ['code' => 'kartu-keluarga', 'label' => 'Fotokopi Kartu Keluarga', 'description' => 'Kartu Keluarga (KK)', 'required' => true],
    ];
    $rawRequirements = old('requirements', $letterService->requirements_json ?: $defaultRequirements);
    $requirements = collect($rawRequirements)->map(function (array $requirement) {
        $requiredWhen = $requirement['required_when'] ?? null;
        $legacyRequiredFor = $requirement['required_for'] ?? null;
        $conditionField = $requirement['condition_field'] ?? data_get($requiredWhen, 'field');
        $conditionValues = $requirement['condition_values'] ?? data_get($requiredWhen, 'values');
        if (! $conditionField && is_array($legacyRequiredFor)) {
            $conditionField = 'jenis_pengajuan_ktp';
            $conditionValues = $legacyRequiredFor;
        }

        return [
            ...$requirement,
            'condition_field' => $conditionField ?? '',
            'condition_values' => is_array($conditionValues) ? implode(', ', $conditionValues) : ($conditionValues ?? ''),
        ];
    })->values()->all();
    $storedFormFields = collect($letterService->form_schema_json ?? [])->map(function (array $field) {
        return [
            ...$field,
            'options' => is_array($field['options'] ?? null)
                ? collect($field['options'])->map(fn ($label, $value) => $value.'='.$label)->implode("\n")
                : ($field['options'] ?? ''),
        ];
    })->values()->all();
    $formFields = old('form_fields_present') ? old('form_fields', []) : $storedFormFields;
    $isActive = old('is_active', $letterService->exists ? $letterService->is_active : true);
    $iconOptions = config('letter_services.icons', []);
    $fieldTypeOptions = config('letter_services.field_types', []);
    $selectedIcon = old('icon', $letterService->icon ?: config('letter_services.default_icon'));
@endphp

<div class="box box-primary letter-service-form">
    <div class="box-header with-border">
        <h3 class="box-title">{{ $letterService->exists ? 'Perbarui Jenis Surat' : 'Buat Jenis Surat Baru' }}</h3>
        <div class="box-tools pull-right">
            <a href="{{ route('admin.letter-services.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Kembali</a>
        </div>
    </div>

    <form method="post" action="{{ $letterService->exists ? route('admin.letter-services.update', $letterService) : route('admin.letter-services.store') }}" novalidate>
        @csrf
        @if($letterService->exists) @method('PUT') @endif

        <div class="box-body">
            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Data belum dapat disimpan.</strong>
                    <span>Periksa kembali kolom yang ditandai di bawah.</span>
                </div>
            @endif

            <section class="form-section" aria-labelledby="service-information-heading">
                <div class="form-section__heading">
                    <span class="form-section__icon"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
                    <div>
                        <h3 id="service-information-heading">Informasi Layanan</h3>
                        <p>Informasi ini ditampilkan kepada warga saat memilih jenis surat.</p>
                    </div>
                </div>
                <div class="form-section__body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                <label class="control-label" for="name">Nama jenis surat <span class="required-mark">*</span></label>
                                <input id="name" class="form-control" name="name" value="{{ old('name', $letterService->name) }}" required autofocus placeholder="Contoh: Surat Keterangan Domisili">
                                @error('name') <p class="help-block">{{ $message }}</p> @else <p class="help-block">Gunakan nama yang mudah dipahami warga.</p> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                <label class="control-label" for="code">Kode layanan <span class="required-mark">*</span></label>
                                <input id="code" class="form-control text-uppercase" name="code" value="{{ old('code', $letterService->code) }}" required maxlength="20" pattern="[A-Za-z0-9]+" placeholder="Contoh: SKD">
                                @error('code') <p class="help-block">{{ $message }}</p> @else <p class="help-block">Huruf atau angka saja; dipakai sebagai kode internal.</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group {{ $errors->has('display_order') ? 'has-error' : '' }}">
                                <label class="control-label" for="display_order">Urutan tampil <span class="required-mark">*</span></label>
                                <input id="display_order" type="number" min="0" class="form-control" name="display_order" value="{{ old('display_order', $letterService->display_order ?? 0) }}" required>
                                @error('display_order') <p class="help-block">{{ $message }}</p> @else <p class="help-block">Angka lebih kecil tampil lebih dahulu.</p> @enderror
                            </div>
                        </div>
                    </div>
                    <fieldset class="form-group {{ $errors->has('icon') ? 'has-error' : '' }}">
                        <legend class="control-label" style="border: 0; margin-bottom: 8px; font-size: 14px;">Ikon layanan <span class="required-mark">*</span></legend>
                        <div class="icon-picker">
                            @foreach($iconOptions as $iconClass => $iconLabel)
                                <label class="icon-picker__option">
                                    <input type="radio" name="icon" value="{{ $iconClass }}" @checked($selectedIcon === $iconClass) required>
                                    <span class="icon-picker__content">
                                        <i class="fa {{ $iconClass }}" aria-hidden="true"></i>
                                        <span>{{ $iconLabel }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('icon') <p class="help-block">{{ $message }}</p> @else <p class="help-block">Pilih ikon yang paling sesuai dengan jenis surat.</p> @enderror
                    </fieldset>
                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                        <label class="control-label" for="description">Deskripsi layanan <span class="required-mark">*</span></label>
                        <textarea id="description" class="form-control" name="description" rows="4" required placeholder="Jelaskan kegunaan atau tujuan surat ini.">{{ old('description', $letterService->description) }}</textarea>
                        @error('description') <p class="help-block">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="form-section" aria-labelledby="document-requirements-heading">
                <div class="form-section__heading">
                    <span class="form-section__icon"><i class="fa fa-files-o" aria-hidden="true"></i></span>
                    <div>
                        <h3 id="document-requirements-heading">Persyaratan Dokumen</h3>
                        <p>Tentukan dokumen yang harus diunggah warga. Kondisi dapat digunakan agar dokumen hanya muncul untuk pilihan tertentu.</p>
                    </div>
                </div>
                <div class="form-section__body">
                    @error('requirements') <div class="alert alert-danger" role="alert">{{ $message }}</div> @enderror
                    <div class="requirements-scroll">
                        <table class="table requirement-table" aria-describedby="requirements-help">
                            <thead>
                                <tr>
                                    <th style="width: 16%">Kode dokumen <span class="required-mark">*</span></th>
                                    <th style="width: 22%">Nama persyaratan <span class="required-mark">*</span></th>
                                    <th>Keterangan untuk warga</th>
                                    <th style="width: 15%">Field kondisi</th>
                                    <th style="width: 16%">Nilai kondisi</th>
                                    <th style="width: 105px">Status</th>
                                    <th class="text-center" style="width: 62px"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody id="requirements-list">
                                @foreach($requirements as $index => $requirement)
                                    <tr class="requirement-row">
                                        <td><div class="form-group {{ $errors->has("requirements.$index.code") ? 'has-error' : '' }}"><label class="sr-only" for="requirement-code-{{ $index }}">Kode dokumen</label><input id="requirement-code-{{ $index }}" class="form-control" name="requirements[{{ $index }}][code]" value="{{ $requirement['key'] ?? $requirement['code'] ?? '' }}" required maxlength="50" pattern="[A-Za-z0-9-]+" placeholder="contoh: ktp-pemohon">@error("requirements.$index.code") <p class="help-block">{{ $message }}</p> @enderror</div></td>
                                        <td><div class="form-group {{ $errors->has("requirements.$index.label") ? 'has-error' : '' }}"><label class="sr-only" for="requirement-label-{{ $index }}">Nama persyaratan</label><input id="requirement-label-{{ $index }}" class="form-control" name="requirements[{{ $index }}][label]" value="{{ $requirement['label'] ?? '' }}" required maxlength="150" placeholder="Contoh: Fotokopi KTP Pemohon">@error("requirements.$index.label") <p class="help-block">{{ $message }}</p> @enderror</div></td>
                                        <td><div class="form-group {{ $errors->has("requirements.$index.description") ? 'has-error' : '' }}"><label class="sr-only" for="requirement-description-{{ $index }}">Keterangan dokumen</label><input id="requirement-description-{{ $index }}" class="form-control" name="requirements[{{ $index }}][description]" value="{{ $requirement['description'] ?? '' }}" maxlength="500" placeholder="Contoh: KTP pemohon yang masih berlaku">@error("requirements.$index.description") <p class="help-block">{{ $message }}</p> @enderror</div></td>
                                        <td><div class="form-group {{ $errors->has("requirements.$index.condition_field") ? 'has-error' : '' }}"><label class="sr-only" for="requirement-condition-field-{{ $index }}">Field kondisi</label><input id="requirement-condition-field-{{ $index }}" class="form-control condition-input" name="requirements[{{ $index }}][condition_field]" value="{{ $requirement['condition_field'] ?? '' }}" maxlength="100" pattern="[a-z][a-z0-9_]*" placeholder="contoh: jenis_pengajuan_ktp">@error("requirements.$index.condition_field") <p class="help-block">{{ $message }}</p> @enderror</div></td>
                                        <td><div class="form-group {{ $errors->has("requirements.$index.condition_values") ? 'has-error' : '' }}"><label class="sr-only" for="requirement-condition-values-{{ $index }}">Nilai kondisi</label><input id="requirement-condition-values-{{ $index }}" class="form-control condition-input" name="requirements[{{ $index }}][condition_values]" value="{{ $requirement['condition_values'] ?? '' }}" maxlength="1000" placeholder="hilang, rusak"><small class="field-options-help">Pisahkan beberapa nilai dengan koma.</small>@error("requirements.$index.condition_values") <p class="help-block">{{ $message }}</p> @enderror</div></td>
                                        <td><div class="checkbox"><input type="hidden" name="requirements[{{ $index }}][required]" value="0"><label><input type="checkbox" name="requirements[{{ $index }}][required]" value="1" @checked($requirement['required'] ?? false)> Wajib</label></div></td>
                                        <td class="text-center"><button type="button" class="btn btn-default btn-remove-requirement" title="Hapus persyaratan" aria-label="Hapus persyaratan"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="requirements-actions">
                        <p id="requirements-help" class="requirements-count" aria-live="polite">{{ count($requirements) }} persyaratan dokumen ditambahkan.</p>
                        <button type="button" class="btn btn-success" id="add-requirement"><i class="fa fa-plus" aria-hidden="true"></i> Tambah Dokumen</button>
                    </div>
                </div>
            </section>

            <section class="form-section" aria-labelledby="form-fields-heading">
                <div class="form-section__heading">
                    <span class="form-section__icon"><i class="fa fa-list-alt" aria-hidden="true"></i></span>
                    <div>
                        <h3 id="form-fields-heading">Formulir Tambahan</h3>
                        <p>Atur data khusus yang harus diisi warga setelah data identitas umum. Bagian ini boleh dikosongkan.</p>
                    </div>
                </div>
                <div class="form-section__body">
                    <input type="hidden" name="form_fields_present" value="1">
                    @error('form_fields') <div class="alert alert-danger" role="alert">{{ $message }}</div> @enderror
                    <div class="requirements-scroll">
                        <table class="table requirement-table form-field-table" aria-describedby="form-fields-help">
                            <thead>
                                <tr>
                                    <th style="width: 15%">Kunci field <span class="required-mark">*</span></th>
                                    <th style="width: 19%">Label untuk warga <span class="required-mark">*</span></th>
                                    <th style="width: 14%">Jenis <span class="required-mark">*</span></th>
                                    <th>Opsi pilihan</th>
                                    <th style="width: 85px">Min.</th>
                                    <th style="width: 85px">Maks.</th>
                                    <th style="width: 90px">Status</th>
                                    <th class="text-center" style="width: 62px"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody id="form-fields-list">
                                @foreach($formFields as $index => $field)
                                    <tr class="form-field-row">
                                        <td><div class="form-group {{ $errors->has("form_fields.$index.key") ? 'has-error' : '' }}"><label class="sr-only" for="form-field-key-{{ $index }}">Kunci field</label><input id="form-field-key-{{ $index }}" class="form-control" name="form_fields[{{ $index }}][key]" value="{{ $field['key'] ?? '' }}" required maxlength="100" pattern="[a-z][a-z0-9_]*" placeholder="contoh: jenis_pengajuan">@error("form_fields.$index.key") <p class="help-block">{{ $message }}</p> @enderror</div></td>
                                        <td><div class="form-group {{ $errors->has("form_fields.$index.label") ? 'has-error' : '' }}"><label class="sr-only" for="form-field-label-{{ $index }}">Label field</label><input id="form-field-label-{{ $index }}" class="form-control" name="form_fields[{{ $index }}][label]" value="{{ $field['label'] ?? '' }}" required maxlength="150" placeholder="Contoh: Jenis pengajuan">@error("form_fields.$index.label") <p class="help-block">{{ $message }}</p> @enderror</div></td>
                                        <td><div class="form-group {{ $errors->has("form_fields.$index.type") ? 'has-error' : '' }}"><label class="sr-only" for="form-field-type-{{ $index }}">Jenis field</label><select id="form-field-type-{{ $index }}" class="form-control" name="form_fields[{{ $index }}][type]" required data-form-field-type>@foreach($fieldTypeOptions as $type => $label)<option value="{{ $type }}" @selected(($field['type'] ?? 'text') === $type)>{{ $label }}</option>@endforeach</select>@error("form_fields.$index.type") <p class="help-block">{{ $message }}</p> @enderror</div></td>
                                        <td><div class="form-group {{ $errors->has("form_fields.$index.options") ? 'has-error' : '' }}"><label class="sr-only" for="form-field-options-{{ $index }}">Opsi pilihan</label><textarea id="form-field-options-{{ $index }}" class="form-control" name="form_fields[{{ $index }}][options]" rows="3" maxlength="5000" placeholder="baru=Baru&#10;hilang=Hilang" data-form-field-options>{{ $field['options'] ?? '' }}</textarea><small class="field-options-help">Untuk dropdown/radio: satu baris per opsi dengan format nilai=Label.</small>@error("form_fields.$index.options") <p class="help-block">{{ $message }}</p> @enderror</div></td>
                                        <td><div class="form-group {{ $errors->has("form_fields.$index.min") ? 'has-error' : '' }}"><label class="sr-only" for="form-field-min-{{ $index }}">Minimum</label><input id="form-field-min-{{ $index }}" type="number" min="0" max="100000" class="form-control" name="form_fields[{{ $index }}][min]" value="{{ $field['min'] ?? '' }}" data-form-field="min">@error("form_fields.$index.min") <p class="help-block">{{ $message }}</p> @enderror</div></td>
                                        <td><div class="form-group {{ $errors->has("form_fields.$index.max") ? 'has-error' : '' }}"><label class="sr-only" for="form-field-max-{{ $index }}">Maksimum</label><input id="form-field-max-{{ $index }}" type="number" min="1" max="100000" class="form-control" name="form_fields[{{ $index }}][max]" value="{{ $field['max'] ?? '' }}" data-form-field="max">@error("form_fields.$index.max") <p class="help-block">{{ $message }}</p> @enderror</div></td>
                                        <td><div class="checkbox"><input type="hidden" name="form_fields[{{ $index }}][required]" value="0"><label><input type="checkbox" name="form_fields[{{ $index }}][required]" value="1" @checked($field['required'] ?? true)> Wajib</label></div></td>
                                        <td class="text-center"><button type="button" class="btn btn-default btn-remove-form-field" title="Hapus field" aria-label="Hapus field"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="requirements-actions">
                        <p id="form-fields-help" class="requirements-count" aria-live="polite">{{ count($formFields) }} field tambahan ditambahkan.</p>
                        <button type="button" class="btn btn-success" id="add-form-field"><i class="fa fa-plus" aria-hidden="true"></i> Tambah Field</button>
                    </div>
                </div>
            </section>

            <section class="form-section" aria-labelledby="service-settings-heading">
                <div class="form-section__heading">
                    <span class="form-section__icon"><i class="fa fa-cog" aria-hidden="true"></i></span>
                    <div>
                        <h3 id="service-settings-heading">Ketentuan Layanan</h3>
                        <p>Lengkapi informasi waktu, biaya, dan pengambilan surat.</p>
                    </div>
                </div>
                <div class="form-section__body">
                    <div class="row">
                        <div class="col-md-4"><div class="form-group {{ $errors->has('processing_days') ? 'has-error' : '' }}"><label class="control-label" for="processing_days">Estimasi hari kerja <span class="required-mark">*</span></label><div class="input-group"><input id="processing_days" type="number" min="1" max="30" class="form-control" name="processing_days" value="{{ old('processing_days', $letterService->processing_days ?? 1) }}" required><span class="input-group-addon">hari</span></div>@error('processing_days') <p class="help-block">{{ $message }}</p> @enderror</div></div>
                        <div class="col-md-8"><div class="form-group {{ $errors->has('fee_information') ? 'has-error' : '' }}"><label class="control-label" for="fee_information">Informasi biaya <span class="required-mark">*</span></label><input id="fee_information" class="form-control" name="fee_information" value="{{ old('fee_information', $letterService->fee_information) }}" required maxlength="100" placeholder="Contoh: Gratis"><p class="help-block">Tampilkan nominal atau keterangan biaya untuk warga.</p>@error('fee_information') <p class="help-block">{{ $message }}</p> @enderror</div></div>
                    </div>
                    <div class="form-group {{ $errors->has('pickup_instructions') ? 'has-error' : '' }}"><label class="control-label" for="pickup_instructions">Instruksi pengambilan</label><textarea id="pickup_instructions" class="form-control" name="pickup_instructions" rows="3" maxlength="3000" placeholder="Contoh: Bawa dokumen asli saat pengambilan di kantor desa.">{{ old('pickup_instructions', $letterService->pickup_instructions) }}</textarea>@error('pickup_instructions') <p class="help-block">{{ $message }}</p> @enderror</div>
                    <label class="service-status" for="is_active"><input id="is_active" type="checkbox" name="is_active" value="1" @checked($isActive)> <span><strong>Layanan aktif</strong><br><small>Warga dapat memilih layanan ini pada halaman layanan surat.</small></span></label>
                </div>
            </section>
        </div>
        <div class="box-footer footer-actions">
            <a href="{{ route('admin.letter-services.index') }}" class="btn btn-default">Batal</a>
            <button class="btn btn-primary" type="submit"><i class="fa fa-save" aria-hidden="true"></i> {{ $letterService->exists ? 'Simpan Perubahan' : 'Simpan Jenis Surat' }}</button>
        </div>
    </form>
</div>

<template id="requirement-row-template">
    <tr class="requirement-row">
        <td><div class="form-group"><label class="sr-only">Kode dokumen</label><input class="form-control" data-field="code" required maxlength="50" pattern="[A-Za-z0-9-]+" placeholder="contoh: surat-pengantar"></div></td>
        <td><div class="form-group"><label class="sr-only">Nama persyaratan</label><input class="form-control" data-field="label" required maxlength="150" placeholder="Contoh: Surat Pengantar RT/RW"></div></td>
        <td><div class="form-group"><label class="sr-only">Keterangan dokumen</label><input class="form-control" data-field="description" maxlength="500" placeholder="Keterangan singkat untuk warga"></div></td>
        <td><div class="form-group"><label class="sr-only">Field kondisi</label><input class="form-control condition-input" data-field="condition_field" maxlength="100" pattern="[a-z][a-z0-9_]*" placeholder="contoh: jenis_pengajuan"></div></td>
        <td><div class="form-group"><label class="sr-only">Nilai kondisi</label><input class="form-control condition-input" data-field="condition_values" maxlength="1000" placeholder="baru, perubahan"><small class="field-options-help">Pisahkan dengan koma.</small></div></td>
        <td><div class="checkbox"><input type="hidden" data-field="required-hidden" value="0"><label><input type="checkbox" data-field="required" value="1" checked> Wajib</label></div></td>
        <td class="text-center"><button type="button" class="btn btn-default btn-remove-requirement" title="Hapus persyaratan" aria-label="Hapus persyaratan"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button></td>
    </tr>
</template>

<template id="form-field-row-template">
    <tr class="form-field-row">
        <td><div class="form-group"><label class="sr-only">Kunci field</label><input class="form-control" data-form-field="key" required maxlength="100" pattern="[a-z][a-z0-9_]*" placeholder="contoh: jenis_pengajuan"></div></td>
        <td><div class="form-group"><label class="sr-only">Label field</label><input class="form-control" data-form-field="label" required maxlength="150" placeholder="Contoh: Jenis pengajuan"></div></td>
        <td><div class="form-group"><label class="sr-only">Jenis field</label><select class="form-control" data-form-field="type" data-form-field-type required>@foreach($fieldTypeOptions as $type => $label)<option value="{{ $type }}">{{ $label }}</option>@endforeach</select></div></td>
        <td><div class="form-group"><label class="sr-only">Opsi pilihan</label><textarea class="form-control" data-form-field="options" data-form-field-options rows="3" maxlength="5000" placeholder="baru=Baru&#10;hilang=Hilang"></textarea><small class="field-options-help">Untuk dropdown/radio: format nilai=Label.</small></div></td>
        <td><div class="form-group"><label class="sr-only">Minimum</label><input type="number" min="0" max="100000" class="form-control" data-form-field="min"></div></td>
        <td><div class="form-group"><label class="sr-only">Maksimum</label><input type="number" min="1" max="100000" class="form-control" data-form-field="max"></div></td>
        <td><div class="checkbox"><input type="hidden" data-form-field="required-hidden" value="0"><label><input type="checkbox" data-form-field="required" value="1" checked> Wajib</label></div></td>
        <td class="text-center"><button type="button" class="btn btn-default btn-remove-form-field" title="Hapus field" aria-label="Hapus field"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button></td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('requirements-list');
    const template = document.getElementById('requirement-row-template');
    const addButton = document.getElementById('add-requirement');
    const countLabel = document.getElementById('requirements-help');
    let nextIndex = {{ count($requirements) }};

    function updateRows() {
        const rows = list.querySelectorAll('.requirement-row');
        countLabel.textContent = `${rows.length} persyaratan dokumen ditambahkan.`;
        rows.forEach(function (row) {
            const button = row.querySelector('.btn-remove-requirement');
            button.disabled = rows.length === 1;
            button.title = rows.length === 1 ? 'Minimal satu persyaratan dokumen' : 'Hapus persyaratan';
        });
    }

    addButton.addEventListener('click', function () {
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('.requirement-row');
        const fieldNames = { code: 'code', label: 'label', description: 'description', condition_field: 'condition_field', condition_values: 'condition_values', 'required-hidden': 'required', required: 'required' };
        row.querySelectorAll('[data-field]').forEach(function (field) {
            const fieldName = fieldNames[field.dataset.field];
            field.name = `requirements[${nextIndex}][${fieldName}]`;
            field.id = `requirement-${field.dataset.field}-${nextIndex}`;
        });
        row.querySelectorAll('label.sr-only').forEach(function (label) {
            const input = label.parentElement.querySelector('input[data-field]');
            if (input) label.htmlFor = input.id;
        });
        nextIndex++;
        list.appendChild(fragment);
        updateRows();
        list.querySelector('.requirement-row:last-child input[data-field="code"]').focus();
    });

    list.addEventListener('click', function (event) {
        const button = event.target.closest('.btn-remove-requirement');
        if (!button || list.querySelectorAll('.requirement-row').length <= 1) return;
        button.closest('.requirement-row').remove();
        updateRows();
    });

    updateRows();

    const formFieldsList = document.getElementById('form-fields-list');
    const formFieldTemplate = document.getElementById('form-field-row-template');
    const addFormFieldButton = document.getElementById('add-form-field');
    const formFieldsCountLabel = document.getElementById('form-fields-help');
    let nextFormFieldIndex = {{ count($formFields) }};

    function syncFieldOptions(row) {
        const type = row.querySelector('[data-form-field-type]')?.value;
        const options = row.querySelector('[data-form-field-options]');
        if (!options) return;
        const usesOptions = type === 'select' || type === 'radio';
        options.disabled = !usesOptions;
        options.required = usesOptions;
        options.closest('.form-group').classList.toggle('text-muted', !usesOptions);
        const usesLimits = type === 'text' || type === 'textarea' || type === 'number';
        row.querySelectorAll('[data-form-field="min"], [data-form-field="max"]').forEach(function (field) {
            field.disabled = !usesLimits;
        });
    }

    function updateFormFieldRows() {
        const rows = formFieldsList.querySelectorAll('.form-field-row');
        formFieldsCountLabel.textContent = `${rows.length} field tambahan ditambahkan.`;
        rows.forEach(syncFieldOptions);
    }

    addFormFieldButton.addEventListener('click', function () {
        const fragment = formFieldTemplate.content.cloneNode(true);
        const row = fragment.querySelector('.form-field-row');
        const fieldNames = { key: 'key', label: 'label', type: 'type', options: 'options', min: 'min', max: 'max', 'required-hidden': 'required', required: 'required' };
        row.querySelectorAll('[data-form-field]').forEach(function (field) {
            field.name = `form_fields[${nextFormFieldIndex}][${fieldNames[field.dataset.formField]}]`;
            field.id = `form-field-${field.dataset.formField}-${nextFormFieldIndex}`;
        });
        row.querySelectorAll('label.sr-only').forEach(function (label) {
            const input = label.parentElement.querySelector('[data-form-field]');
            if (input) label.htmlFor = input.id;
        });
        nextFormFieldIndex++;
        formFieldsList.appendChild(fragment);
        updateFormFieldRows();
        formFieldsList.querySelector('.form-field-row:last-child [data-form-field="key"]').focus();
    });

    formFieldsList.addEventListener('click', function (event) {
        const button = event.target.closest('.btn-remove-form-field');
        if (!button) return;
        button.closest('.form-field-row').remove();
        updateFormFieldRows();
    });

    formFieldsList.addEventListener('change', function (event) {
        if (!event.target.matches('[data-form-field-type]')) return;
        syncFieldOptions(event.target.closest('.form-field-row'));
    });

    updateFormFieldRows();
});
</script>
@endpush
