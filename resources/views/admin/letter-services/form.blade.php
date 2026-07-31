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
    .letter-service-form .requirement-table { margin-bottom: 0; min-width: 820px; }
    .letter-service-form .requirement-table > thead > tr > th { padding: 11px 12px; border-bottom: 2px solid var(--letter-green); background: #f2f5ef; color: var(--admin-ink, #252a31); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; vertical-align: middle; }
    .letter-service-form .requirement-table > tbody > tr > td { padding: 12px; border-color: #e7eeea; vertical-align: top; }
    .letter-service-form .requirement-table .form-group { margin: 0; }
    .letter-service-form .requirement-table .checkbox { margin: 9px 0; white-space: nowrap; }
    .letter-service-form .requirement-table .btn-remove-requirement { min-width: 38px; min-height: 38px; }
    .letter-service-form .requirements-scroll { overflow-x: auto; border: 1px solid var(--letter-border); border-radius: 2px; }
    .letter-service-form .requirements-actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 14px; }
    .letter-service-form .requirements-count { color: #607168; font-size: 13px; }
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
    $requirements = old('requirements', $letterService->requirements_json ?: $defaultRequirements);
    $isActive = old('is_active', $letterService->exists ? $letterService->is_active : true);
    $iconOptions = config('letter_services.icons', []);
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
                        <p>Tentukan dokumen yang harus diunggah warga untuk jenis surat ini.</p>
                    </div>
                </div>
                <div class="form-section__body">
                    @error('requirements') <div class="alert alert-danger" role="alert">{{ $message }}</div> @enderror
                    <div class="requirements-scroll">
                        <table class="table requirement-table" aria-describedby="requirements-help">
                            <thead>
                                <tr>
                                    <th style="width: 20%">Kode dokumen <span class="required-mark">*</span></th>
                                    <th style="width: 27%">Nama persyaratan <span class="required-mark">*</span></th>
                                    <th>Keterangan untuk warga</th>
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
        <td><div class="checkbox"><input type="hidden" data-field="required-hidden" value="0"><label><input type="checkbox" data-field="required" value="1" checked> Wajib</label></div></td>
        <td class="text-center"><button type="button" class="btn btn-default btn-remove-requirement" title="Hapus persyaratan" aria-label="Hapus persyaratan"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button></td>
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
        const fieldNames = { code: 'code', label: 'label', description: 'description', 'required-hidden': 'required', required: 'required' };
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
});
</script>
@endpush
