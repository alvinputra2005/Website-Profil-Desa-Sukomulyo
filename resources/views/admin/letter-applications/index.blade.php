@extends('layouts.admin')
@section('title','Permohonan Surat')
@section('content')
<div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Daftar Permohonan</h3><form id="bulk-delete-letter-applications" method="post" action="{{ route('admin.letter-applications.bulk-destroy') }}" class="inline-form pull-right" data-confirm="Hapus permohonan surat yang dipilih? Dokumen unggahan terkait juga akan dihapus." data-confirm-tone="danger" data-confirm-title="Hapus Permohonan Terpilih" data-confirm-button="Ya, hapus">@csrf @method('delete')<button class="btn btn-danger btn-sm" type="submit" data-bulk-button disabled><i class="fa fa-trash" aria-hidden="true"></i> Hapus Terpilih</button></form></div><div class="box-body">
<form class="row" method="get">
    <div class="col-md-3 form-group"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Nomor atau nama pemohon"></div>
    <div class="col-md-2 form-group"><select class="form-control" name="status"><option value="">Semua status</option>@foreach(\App\Enums\LetterApplicationStatus::cases() as $status)<option value="{{ $status->value }}" @selected(request('status')===$status->value)>{{ $status->label() }}</option>@endforeach</select></div>
    <div class="col-md-2 form-group"><select class="form-control" name="service"><option value="">Semua layanan</option>@foreach($services as $service)<option value="{{ $service->id }}" @selected((string)request('service')===(string)$service->id)>{{ $service->name }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group">
        <select class="form-control" name="assigned_to" aria-label="Filter petugas">
            <option value="">Semua petugas</option>
            <option value="unassigned" @selected(request('assigned_to') === 'unassigned')>Belum ditugaskan</option>
            @foreach($officers as $officer)<option value="{{ $officer->id }}" @selected((string) request('assigned_to') === (string) $officer->id)>{{ $officer->name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-2 form-group"><button class="btn btn-primary">Terapkan filter</button></div>
</form>
<div class="table-responsive"><table class="table table-striped table-hover"><thead><tr><th style="width:38px"><input type="checkbox" aria-label="Pilih semua permohonan pada halaman ini" data-bulk-select-all></th><th>Nomor</th><th>Pemohon</th><th>Layanan</th><th>Tanggal</th><th>Status</th><th>Petugas</th><th></th></tr></thead><tbody>@forelse($applications as $application)<tr><td><input type="checkbox" name="ids[]" value="{{ $application->id }}" form="bulk-delete-letter-applications" aria-label="Pilih permohonan {{ $application->application_number }}" data-bulk-item></td><td><strong>{{ $application->application_number }}</strong></td><td>{{ $application->applicant_name }}</td><td>{{ $application->service->name }}</td><td>@if($application->submitted_at){{ $application->submitted_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}@else<span class="text-muted">Belum dikirim</span><br><small>Dibuat {{ $application->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</small>@endif</td><td><span class="label {{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span></td><td>{{ $application->assignee?->name ?? 'Belum ditugaskan' }}</td><td><a class="btn btn-xs btn-primary" href="{{ route('admin.letter-applications.show',$application) }}">Detail</a></td></tr>@empty<tr><td colspan="8" class="text-center">Belum ada permohonan.</td></tr>@endforelse</tbody></table></div>{{ $applications->links() }}</div></div>
@endsection
