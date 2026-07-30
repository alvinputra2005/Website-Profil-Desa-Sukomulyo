@extends('layouts.admin')

@section('title', 'Detail Permohonan Surat')

@push('head')
<style>
    .letter-application-detail { --letter-detail-border: var(--admin-border, #dfe3da); --letter-detail-muted: var(--admin-muted, #6f7870); }
    .letter-application-detail .application-header { display: flex; padding: 18px 20px; align-items: flex-start; justify-content: space-between; gap: 16px; }
    .letter-application-detail .application-header__eyebrow { margin: 0 0 4px; color: var(--letter-detail-muted); font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
    .letter-application-detail .application-header__number { margin: 0; color: var(--admin-ink, #252a31); font-size: 22px; font-weight: 700; line-height: 1.2; }
    .letter-application-detail .application-header__service { margin: 6px 0 0; color: var(--letter-detail-muted); font-size: 13px; }
    .letter-application-detail .application-header .label { margin-top: 3px; padding: 6px 9px; font-size: 11px; }
    .letter-application-detail .detail-box { margin-bottom: 18px; }
    .letter-application-detail .detail-box .box-header { padding: 12px 15px; }
    .letter-application-detail .detail-box .box-title { font-size: 15px; }
    .letter-application-detail .detail-box .box-title .fa { margin-right: 6px; color: var(--admin-primary, #526b42); }
    .letter-application-detail .detail-box .box-body { padding: 16px 15px; }
    .letter-application-detail .application-facts { margin: 0; }
    .letter-application-detail .application-facts > div { display: grid; padding: 10px 0; border-bottom: 1px solid #edf0ea; grid-template-columns: 150px minmax(0, 1fr); gap: 15px; }
    .letter-application-detail .application-facts > div:first-child { padding-top: 0; }
    .letter-application-detail .application-facts > div:last-child { padding-bottom: 0; border-bottom: 0; }
    .letter-application-detail .application-facts dt { margin: 0; color: var(--letter-detail-muted); font-size: 12px; font-weight: 600; }
    .letter-application-detail .application-facts dd { margin: 0; color: var(--admin-ink, #252a31); font-size: 14px; line-height: 1.45; }
    .letter-application-detail .documents-table { width: 100%; min-width: 720px; margin-bottom: 0; table-layout: fixed; }
    .letter-application-detail .documents-table__requirement { width: 22%; }
    .letter-application-detail .documents-table__file { width: 25%; }
    .letter-application-detail .documents-table__status { width: 15%; }
    .letter-application-detail .documents-table__review { width: 38%; }
    .letter-application-detail .documents-table > thead > tr > th { color: var(--admin-ink, #252a31); font-size: 11px; letter-spacing: .03em; text-transform: uppercase; }
    .letter-application-detail .documents-table > tbody > tr > td { vertical-align: top; font-size: 13px; }
    .letter-application-detail .document-name { display: block; margin-bottom: 2px; font-weight: 600; }
    .letter-application-detail .document-name.btn { display: block; width: 100%; padding: 0; border: 0; background: transparent; color: var(--admin-primary, #526b42); line-height: 1.35; text-align: left; white-space: normal; }
    .letter-application-detail .document-meta { color: var(--letter-detail-muted); font-size: 11px; }
    .letter-application-detail .document-status { min-width: 120px; }
    .letter-application-detail .document-status .label { display: inline-block; margin-top: 2px; }
    .letter-application-detail .document-review { min-width: 0; }
    .letter-application-detail .document-review .form-control { height: 30px; margin-top: 7px; border-radius: 2px; font-size: 12px; }
    .letter-application-detail .document-review.is-rejected [data-review-note] { height: 72px; resize: vertical; }
    .letter-application-detail .document-review .btn { margin-top: 6px; }
    .letter-application-detail .document-review__note-label { display: block; margin: 8px 0 -3px; color: var(--letter-detail-muted); font-size: 11px; font-weight: 600; }
    .letter-application-detail .document-review__note-label .required-mark { color: #b23b30; }
    .letter-application-detail .document-review__help { margin: 5px 0 0; color: #9c5d24; font-size: 11px; line-height: 1.35; }
    .letter-application-detail .status-current { margin-bottom: 16px; padding: 11px 12px; border: 1px solid var(--letter-detail-border); border-left: 3px solid var(--admin-primary, #526b42); background: #f8f9f6; }
    .letter-application-detail .status-current small { display: block; margin-bottom: 3px; color: var(--letter-detail-muted); font-size: 11px; }
    .letter-application-detail .status-current strong { color: var(--admin-ink, #252a31); font-size: 15px; }
    .letter-application-detail .status-form label { color: var(--admin-ink, #252a31); font-size: 12px; font-weight: 600; }
    .letter-application-detail .status-form .form-control { border-radius: 2px; }
    .letter-application-detail .whatsapp-option { display: flex; margin: 14px 0 0; padding: 10px; align-items: flex-start; border: 1px solid #cfe0d3; background: #f3f8f3; color: #365c3d; font-size: 12px; gap: 8px; }
    .letter-application-detail .whatsapp-option input { margin: 2px 0 0; }
    .letter-application-detail .whatsapp-option small { display: block; margin-top: 2px; color: #5d7460; line-height: 1.4; }
    .letter-application-detail .whatsapp-option .fa { color: #168a43; }
    .letter-application-detail .status-form .btn { margin-top: 14px; }
    .letter-application-detail .whatsapp-manual { margin-top: 10px; }
    .letter-application-detail .status-history { position: relative; margin: 0; padding: 0; list-style: none; }
    .letter-application-detail .status-history::before { position: absolute; top: 10px; bottom: 10px; left: 6px; width: 1px; background: #d4ddd0; content: ''; }
    .letter-application-detail .status-history__item { position: relative; padding: 0 0 17px 25px; }
    .letter-application-detail .status-history__item:last-child { padding-bottom: 0; }
    .letter-application-detail .status-history__marker { position: absolute; top: 4px; left: 0; z-index: 1; width: 13px; height: 13px; border: 3px solid #fff; border-radius: 50%; background: var(--admin-primary, #526b42); box-shadow: 0 0 0 1px #bfcabb; }
    .letter-application-detail .status-history__title { margin: 0; color: var(--admin-ink, #252a31); font-size: 13px; font-weight: 700; line-height: 1.35; }
    .letter-application-detail .status-history__title .fa { margin: 0 4px; color: var(--letter-detail-muted); font-size: 10px; }
    .letter-application-detail .status-history__meta { margin: 3px 0 0; color: var(--letter-detail-muted); font-size: 11px; line-height: 1.45; }
    .letter-application-detail .status-history__note { margin: 7px 0 0; padding: 8px 10px; border-left: 2px solid #d5ded0; background: #f8f9f6; color: #4d5950; font-size: 12px; line-height: 1.45; white-space: pre-line; }
    .letter-application-detail .status-history__notification { margin-top: 6px; color: #397049; font-size: 11px; }
    .document-viewer-modal .modal-dialog { width: calc(100% - 48px); max-width: 1120px; height: 82vh; margin: 7vh auto 0; }
    .document-viewer-modal .modal-content { height: 100%; overflow: hidden; border: 1px solid rgba(255,255,255,.15); border-radius: 3px; background: #1a2227; box-shadow: 0 12px 38px rgba(0,0,0,.45); }
    .document-viewer-modal .modal-header { display: flex; min-height: 58px; padding: 11px 16px; align-items: center; border-bottom: 1px solid rgba(255,255,255,.14); background: #263238; color: #fff; gap: 12px; }
    .document-viewer-modal .modal-title { overflow: hidden; margin: 0; color: #fff; font-size: 15px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
    .document-viewer-modal .modal-title small { display: block; margin-top: 2px; color: #b8c5c9; font-size: 11px; font-weight: 400; }
    .document-viewer-modal .viewer-toolbar { display: flex; margin-left: auto; align-items: center; gap: 5px; }
    .document-viewer-modal .viewer-toolbar .btn { min-width: 32px; padding: 6px 8px; border-color: rgba(255,255,255,.18); background: rgba(255,255,255,.08); color: #fff; }
    .document-viewer-modal .viewer-toolbar .btn:hover, .document-viewer-modal .viewer-toolbar .btn:focus { background: rgba(255,255,255,.19); color: #fff; }
    .document-viewer-modal .viewer-toolbar .btn-download { border-color: #4e955e; background: #367d47; }
    .document-viewer-modal .viewer-toolbar .close { margin-left: 6px; color: #fff; font-size: 25px; opacity: .85; text-shadow: none; }
    .document-viewer-modal .modal-body { display: flex; height: calc(100% - 58px); padding: 0; align-items: center; justify-content: center; overflow: hidden; background: #151b1f; }
    .document-viewer-modal .viewer-loading { color: #c7d2d6; font-size: 13px; text-align: center; }
    .document-viewer-modal .viewer-loading .fa { display: block; margin-bottom: 10px; font-size: 25px; }
    .document-viewer-modal .viewer-image-wrap { display: flex; width: 100%; height: 100%; align-items: center; justify-content: center; overflow: auto; }
    .document-viewer-modal .viewer-image { max-width: 94%; max-height: 94%; transition: transform .18s ease; transform-origin: center center; }
    .document-viewer-modal .viewer-pdf { width: 100%; height: 100%; border: 0; background: #fff; }
    .document-viewer-modal .is-hidden { display: none !important; }
    @media (max-width: 767px) { .document-viewer-modal .modal-dialog { width: calc(100% - 20px); height: 88vh; margin-top: 4vh; } .document-viewer-modal .modal-header { align-items: flex-start; flex-wrap: wrap; } .document-viewer-modal .viewer-toolbar { width: 100%; margin-left: 0; } .document-viewer-modal .viewer-toolbar .btn { flex: 1; } .document-viewer-modal .viewer-toolbar .btn-close { max-width: 42px; } }
    @media (max-width: 767px) { .letter-application-detail .application-header { padding: 15px; flex-direction: column; gap: 9px; } .letter-application-detail .application-header__number { font-size: 19px; } .letter-application-detail .application-facts > div { grid-template-columns: 1fr; gap: 3px; } .letter-application-detail .document-review { min-width: 180px; } }
</style>
@endpush

@section('content')
<div class="letter-application-detail">
    <div class="box box-primary">
        <div class="application-header">
            <div>
                <p class="application-header__eyebrow">Detail permohonan surat</p>
                <h1 class="application-header__number">{{ $application->application_number }}</h1>
                <p class="application-header__service">{{ $application->service_snapshot_json['name'] ?? $application->service->name }}</p>
            </div>
            <span class="label {{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <section class="box box-primary detail-box" aria-labelledby="applicant-heading">
                <div class="box-header with-border"><h2 id="applicant-heading" class="box-title"><i class="fa fa-user" aria-hidden="true"></i> Data Pemohon</h2></div>
                <div class="box-body">
                    <dl class="application-facts">
                        <div><dt>Nama pemohon</dt><dd>{{ $application->applicant_name }}</dd></div>
                        <div><dt>NIK</dt><dd>{{ $application->maskedNik() }}</dd></div>
                        <div><dt>WhatsApp</dt><dd>{{ $application->applicant_phone }}</dd></div>
                        <div><dt>Tempat, tanggal lahir</dt><dd>{{ $application->birth_place }}@if($application->birth_date), {{ $application->birth_date->format('d-m-Y') }}@endif</dd></div>
                        <div><dt>Alamat</dt><dd>{{ $application->address }}, Dusun {{ $application->hamlet }}, RT {{ $application->rt }}/RW {{ $application->rw }}</dd></div>
                        <div><dt>Keperluan</dt><dd>{{ $application->purpose }}</dd></div>
                    </dl>
                </div>
            </section>

            <section class="box box-primary detail-box" aria-labelledby="documents-heading">
                <div class="box-header with-border"><h2 id="documents-heading" class="box-title"><i class="fa fa-files-o" aria-hidden="true"></i> Dokumen Persyaratan</h2></div>
                <div class="box-body table-responsive">
                    <table class="table table-striped documents-table">
                        <colgroup><col class="documents-table__requirement"><col class="documents-table__file"><col class="documents-table__status"><col class="documents-table__review"></colgroup>
                        <thead><tr><th>Persyaratan</th><th>File</th><th>Status Saat Ini</th><th>Review Dokumen</th></tr></thead>
                        <tbody>
                            @forelse($application->documents as $document)
                                <tr>
                                    <td>{{ $requirementLabels[$document->requirement_key] ?? $document->label }}</td>
                                    <td><button type="button" class="document-name btn btn-link" data-document-preview data-preview-url="{{ route('admin.letter-applications.document.preview-url', [$application, $document->id]) }}" data-download-url="{{ route('admin.letter-applications.document', [$application, $document->id]) }}" data-document-name="{{ $document->original_name }}" data-document-size="{{ number_format(($document->size_bytes ?: $document->file_size) / 1024, 0) }} KB">{{ $document->original_name }}</button><span class="document-meta">{{ number_format(($document->size_bytes ?: $document->file_size) / 1024, 0) }} KB · Klik untuk pratinjau</span></td>
                                    <td class="document-status"><span class="label label-{{ $document->review_status === 'approved' ? 'success' : ($document->review_status === 'rejected' ? 'danger' : 'warning') }}">{{ $document->review_status === 'approved' ? 'Sesuai' : ($document->review_status === 'rejected' ? 'Perlu diganti' : 'Menunggu review') }}</span></td>
                                    <td class="document-review"><form method="post" action="{{ route('admin.letter-applications.document.review', [$application, $document->id]) }}" data-document-review-form>@csrf @method('PATCH')<label class="sr-only" for="document-status-{{ $document->id }}">Status dokumen</label><select id="document-status-{{ $document->id }}" name="review_status" class="form-control input-sm" data-review-status><option value="approved" @selected($document->review_status === 'approved')>Sesuai</option><option value="rejected" @selected($document->review_status === 'rejected')>Minta diganti</option></select><label class="document-review__note-label" for="document-note-{{ $document->id }}">Catatan perbaikan <span class="required-mark" data-review-required-mark hidden>*</span></label><textarea id="document-note-{{ $document->id }}" name="review_note" class="form-control input-sm" rows="2" placeholder="Jelaskan dokumen yang perlu diperbaiki" data-review-note>{{ $document->review_note }}</textarea><p class="document-review__help" data-review-note-help hidden>Catatan wajib diisi agar pemohon mengetahui perbaikan yang diperlukan.</p><button class="btn btn-xs btn-success" type="submit">Simpan Review</button></form></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Belum ada dokumen yang diunggah.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="col-md-4">
            <section class="box box-warning detail-box" aria-labelledby="status-heading">
                <div class="box-header with-border"><h2 id="status-heading" class="box-title"><i class="fa fa-refresh" aria-hidden="true"></i> Perbarui Status</h2></div>
                <div class="box-body">
                    <div class="status-current"><small>Status saat ini</small><strong>{{ $application->status->label() }}</strong></div>
                    @if($application->status->allowedTransitions())
                        <form class="status-form" method="post" action="{{ route('admin.letter-applications.status.update', $application) }}">
                            @csrf @method('PATCH')
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}"><label for="status">Status berikutnya</label><select id="status" class="form-control" name="status" required>@foreach($application->status->allowedTransitions() as $status)<option value="{{ $status->value }}" @selected(old('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select>@error('status')<span class="help-block">{{ $message }}</span>@enderror</div>
                            <div class="form-group {{ $errors->has('public_note') ? 'has-error' : '' }}"><label for="public_note">Catatan untuk pemohon</label><textarea id="public_note" class="form-control" name="public_note" rows="3" maxlength="2000" placeholder="Wajib untuk status perlu diperbaiki atau ditolak.">{{ old('public_note') }}</textarea>@error('public_note')<span class="help-block">{{ $message }}</span>@enderror</div>
                            <div class="form-group {{ $errors->has('internal_note') ? 'has-error' : '' }}"><label for="internal_note">Catatan internal <small>(opsional)</small></label><textarea id="internal_note" class="form-control" name="internal_note" rows="2" maxlength="3000" placeholder="Hanya terlihat oleh petugas.">{{ old('internal_note') }}</textarea>@error('internal_note')<span class="help-block">{{ $message }}</span>@enderror</div>
                            <label class="whatsapp-option" for="send_whatsapp"><input id="send_whatsapp" type="checkbox" name="send_whatsapp" value="1" @checked(old('send_whatsapp', true))><span><i class="fa fa-whatsapp" aria-hidden="true"></i> Buka WhatsApp pemohon setelah disimpan<small>Pesan status akan terisi otomatis. Klik Kirim di WhatsApp untuk mengirim notifikasi.</small></span></label>
                            <button class="btn btn-warning btn-block" type="submit"><i class="fa fa-save" aria-hidden="true"></i> Simpan Perubahan Status</button>
                        </form>
                    @else
                        <p class="text-muted mb-0">Status ini sudah final dan tidak dapat diubah lagi.</p>
                    @endif
                    <form class="whatsapp-manual" method="post" action="{{ route('admin.letter-applications.whatsapp.open', $application) }}">@csrf<button class="btn btn-success btn-block" type="submit"><i class="fa fa-whatsapp" aria-hidden="true"></i> Kirim Ulang Notifikasi WhatsApp</button></form>
                </div>
            </section>

            <section class="box box-primary detail-box" aria-labelledby="history-heading">
                <div class="box-header with-border"><h2 id="history-heading" class="box-title"><i class="fa fa-history" aria-hidden="true"></i> Riwayat Perubahan Status</h2></div>
                <div class="box-body">
                    <ol class="status-history">
                        @forelse($application->statusHistories as $history)
                            <li class="status-history__item"><span class="status-history__marker" aria-hidden="true"></span><p class="status-history__title">@if($history->from_status){{ $history->from_status->label() }} <i class="fa fa-arrow-right" aria-hidden="true"></i> @endif{{ $history->to_status->label() }}</p><p class="status-history__meta">{{ $history->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} · {{ $history->actor?->name ?? 'Sistem / Pemohon' }}</p>@if($history->public_note)<p class="status-history__note"><strong>Catatan pemohon:</strong><br>{{ $history->public_note }}</p>@endif @if($history->internal_note)<p class="status-history__note"><strong>Catatan internal:</strong><br>{{ $history->internal_note }}</p>@endif @if(data_get($history->metadata_json, 'whatsapp_opened_at'))<p class="status-history__notification"><i class="fa fa-whatsapp" aria-hidden="true"></i> Notifikasi WhatsApp dibuka {{ \Illuminate\Support\Carbon::parse(data_get($history->metadata_json, 'whatsapp_opened_at'))->timezone('Asia/Jakarta')->format('d M Y, H:i') }}.</p>@endif</li>
                        @empty
                            <li class="text-muted">Belum ada riwayat status.</li>
                        @endforelse
                    </ol>
                </div>
            </section>
        </aside>
    </div>
</div>

<div class="modal fade document-viewer-modal" id="document-viewer" tabindex="-1" role="dialog" aria-labelledby="document-viewer-title" aria-modal="true">
    <div class="modal-dialog" role="document"><div class="modal-content">
        <div class="modal-header">
            <div><h2 class="modal-title" id="document-viewer-title">Pratinjau dokumen</h2><small id="document-viewer-meta"></small></div>
            <div class="viewer-toolbar" role="toolbar" aria-label="Kontrol pratinjau dokumen">
                <button type="button" class="btn btn-sm viewer-image-control" data-viewer-action="zoom-out" title="Perkecil" aria-label="Perkecil"><i class="fa fa-search-minus" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-sm viewer-image-control" data-viewer-action="zoom-in" title="Perbesar" aria-label="Perbesar"><i class="fa fa-search-plus" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-sm viewer-image-control" data-viewer-action="rotate" title="Putar gambar" aria-label="Putar gambar"><i class="fa fa-repeat" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-sm" data-viewer-action="print" title="Cetak" aria-label="Cetak"><i class="fa fa-print" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-sm" data-viewer-action="open" title="Buka di tab baru" aria-label="Buka di tab baru"><i class="fa fa-external-link" aria-hidden="true"></i></button>
                <a id="document-viewer-download" class="btn btn-sm btn-download" href="#"><i class="fa fa-download" aria-hidden="true"></i><span class="hidden-xs"> Unduh</span></a>
                <button type="button" class="btn btn-sm btn-close" data-dismiss="modal" title="Tutup" aria-label="Tutup"><i class="fa fa-times" aria-hidden="true"></i></button>
            </div>
        </div>
        <div class="modal-body"><div class="viewer-loading" id="document-viewer-loading"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i>Menyiapkan pratinjau aman...</div><div class="viewer-image-wrap is-hidden" id="document-viewer-image-wrap"><img id="document-viewer-image" class="viewer-image" alt=""></div><iframe id="document-viewer-pdf" class="viewer-pdf is-hidden" title="Pratinjau PDF"></iframe></div>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = $('#document-viewer');
    const image = document.getElementById('document-viewer-image');
    const imageWrap = document.getElementById('document-viewer-image-wrap');
    const pdf = document.getElementById('document-viewer-pdf');
    const loading = document.getElementById('document-viewer-loading');
    const title = document.getElementById('document-viewer-title');
    const meta = document.getElementById('document-viewer-meta');
    const download = document.getElementById('document-viewer-download');
    const imageControls = document.querySelectorAll('.viewer-image-control');
    let viewerUrl = '';
    let isImage = false;
    let zoom = 1;
    let rotation = 0;

    function updateImageTransform() { image.style.transform = `scale(${zoom}) rotate(${rotation}deg)`; }
    function resetViewer() { viewerUrl = ''; zoom = 1; rotation = 0; image.removeAttribute('src'); pdf.src = 'about:blank'; imageWrap.classList.add('is-hidden'); pdf.classList.add('is-hidden'); loading.classList.remove('is-hidden'); imageControls.forEach(function (button) { button.classList.add('is-hidden'); }); }
    function showPreview(data) {
        viewerUrl = data.url;
        isImage = Boolean(data.is_image);
        title.textContent = data.name;
        meta.textContent = `${data.mime_type === 'application/pdf' ? 'PDF' : 'Gambar'} · URL sementara berlaku hingga ${new Date(data.expires_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;
        loading.classList.add('is-hidden');
        download.href = download.dataset.url;
        if (isImage) {
            image.alt = `Pratinjau ${data.name}`;
            image.src = viewerUrl;
            updateImageTransform();
            imageWrap.classList.remove('is-hidden');
            imageControls.forEach(function (button) { button.classList.remove('is-hidden'); });
        } else {
            pdf.src = viewerUrl;
            pdf.classList.remove('is-hidden');
        }
    }

    document.querySelectorAll('[data-document-preview]').forEach(function (button) {
        button.addEventListener('click', function () {
            resetViewer();
            title.textContent = button.dataset.documentName;
            meta.textContent = button.dataset.documentSize;
            download.dataset.url = button.dataset.downloadUrl;
            download.href = button.dataset.downloadUrl;
            modal.modal('show');
            fetch(button.dataset.previewUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                .then(function (response) { if (!response.ok) throw new Error('Gagal membuat URL pratinjau.'); return response.json(); })
                .then(showPreview)
                .catch(function () { loading.innerHTML = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>Pratinjau tidak dapat dimuat. Silakan unduh dokumen untuk membukanya.'; });
        });
    });

    document.querySelectorAll('[data-viewer-action]').forEach(function (button) {
        button.addEventListener('click', function () {
            const action = button.dataset.viewerAction;
            if (action === 'zoom-in' && isImage) { zoom = Math.min(zoom + .2, 3); updateImageTransform(); }
            if (action === 'zoom-out' && isImage) { zoom = Math.max(zoom - .2, .4); updateImageTransform(); }
            if (action === 'rotate' && isImage) { rotation = (rotation + 90) % 360; updateImageTransform(); }
            if (action === 'open' && viewerUrl) {
                const tab = window.open(viewerUrl, '_blank');
                if (tab) tab.opener = null;
            }
            if (action === 'print') {
                if (isImage) {
                    const printWindow = window.open('', '_blank');
                    if (!printWindow) { window.alert('Izinkan pop-up untuk mencetak gambar.'); return; }
                    printWindow.opener = null;
                    printWindow.document.write('<!doctype html><html><head><title>Cetak dokumen</title><style>body{margin:0;text-align:center}img{max-width:100%;max-height:100vh}</style></head><body></body></html>');
                    const printableImage = printWindow.document.createElement('img');
                    printableImage.src = viewerUrl;
                    printableImage.alt = title.textContent;
                    printableImage.onload = function () { printWindow.focus(); printWindow.print(); };
                    printWindow.document.body.appendChild(printableImage);
                    return;
                }
                const printWindow = window.open(viewerUrl, '_blank');
                if (printWindow) printWindow.opener = null;
                if (!printWindow) window.alert('Izinkan pop-up untuk mencetak dokumen PDF.');
            }
        });
    });

    document.querySelectorAll('[data-document-review-form]').forEach(function (form) {
        const status = form.querySelector('[data-review-status]');
        const note = form.querySelector('[data-review-note]');
        const requiredMark = form.querySelector('[data-review-required-mark]');
        const help = form.querySelector('[data-review-note-help]');

        function syncReviewRequirement() {
            const needsNote = status.value === 'rejected';
            note.required = needsNote;
            note.setAttribute('aria-required', needsNote ? 'true' : 'false');
            requiredMark.hidden = !needsNote;
            help.hidden = !needsNote;
            form.closest('.document-review').classList.toggle('is-rejected', needsNote);
        }

        status.addEventListener('change', syncReviewRequirement);
        syncReviewRequirement();
    });

    modal.on('hidden.bs.modal', resetViewer);
});
</script>
@endpush
