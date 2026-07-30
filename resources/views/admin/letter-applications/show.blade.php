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
    .letter-application-detail .documents-table { margin-bottom: 0; }
    .letter-application-detail .documents-table > thead > tr > th { color: var(--admin-ink, #252a31); font-size: 11px; letter-spacing: .03em; text-transform: uppercase; }
    .letter-application-detail .documents-table > tbody > tr > td { vertical-align: top; font-size: 13px; }
    .letter-application-detail .document-name { display: block; margin-bottom: 2px; font-weight: 600; }
    .letter-application-detail .document-meta { color: var(--letter-detail-muted); font-size: 11px; }
    .letter-application-detail .document-review { min-width: 210px; }
    .letter-application-detail .document-review .form-control { height: 30px; margin-top: 7px; border-radius: 2px; font-size: 12px; }
    .letter-application-detail .document-review .btn { margin-top: 6px; }
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
                        <thead><tr><th>Persyaratan</th><th>File</th><th>Status &amp; Review</th></tr></thead>
                        <tbody>
                            @forelse($application->documents as $document)
                                <tr>
                                    <td>{{ $requirementLabels[$document->requirement_key] ?? $document->label }}</td>
                                    <td><a class="document-name" href="{{ route('admin.letter-applications.document', [$application, $document->id]) }}"><i class="fa fa-download" aria-hidden="true"></i> {{ $document->original_name }}</a><span class="document-meta">{{ number_format(($document->size_bytes ?: $document->file_size) / 1024, 0) }} KB</span></td>
                                    <td class="document-review"><span class="label label-{{ $document->review_status === 'approved' ? 'success' : ($document->review_status === 'rejected' ? 'danger' : 'warning') }}">{{ $document->review_status === 'approved' ? 'Sesuai' : ($document->review_status === 'rejected' ? 'Perlu diganti' : 'Menunggu review') }}</span><form method="post" action="{{ route('admin.letter-applications.document.review', [$application, $document->id]) }}">@csrf @method('PATCH')<label class="sr-only" for="document-status-{{ $document->id }}">Status dokumen</label><select id="document-status-{{ $document->id }}" name="review_status" class="form-control input-sm"><option value="approved" @selected($document->review_status === 'approved')>Sesuai</option><option value="rejected" @selected($document->review_status === 'rejected')>Minta diganti</option></select><label class="sr-only" for="document-note-{{ $document->id }}">Catatan review</label><input id="document-note-{{ $document->id }}" name="review_note" class="form-control input-sm" value="{{ $document->review_note }}" placeholder="Catatan bila perlu diganti"><button class="btn btn-xs btn-success" type="submit">Simpan Review</button></form></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Belum ada dokumen yang diunggah.</td></tr>
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
@endsection
