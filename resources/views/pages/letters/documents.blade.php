<x-layouts.app title="Unggah Dokumen Persyaratan" robots="noindex, nofollow">
<div class="letter-service-page letter-documents-page"><div class="container">
<ol class="letter-steps" aria-label="Tahapan pengajuan layanan">
@foreach(['Data Diri','Data Keperluan','Unggah Dokumen','Konfirmasi'] as $step)<li @class(['is-active'=>$loop->iteration<=3]) @if($loop->iteration===3) aria-current="step" @endif><span>{{ $loop->iteration <= 2 ? '✓' : $loop->iteration }}</span><strong>{{ $step }}</strong></li>@endforeach
</ol>
<div class="letter-form-heading"><h2>Unggah Dokumen Persyaratan</h2><p>Unggah dokumen sesuai persyaratan yang dibutuhkan.</p></div>
<form class="letter-documents-form" method="post" action="{{ route('letter-services.application.documents.upload',$token) }}" enctype="multipart/form-data" data-r2-documents data-presigned="{{ config('filesystems.letter_documents_disk') === 'r2_letters' ? 'true' : 'false' }}" data-presign-url="{{ route('letter-services.application.documents.presign',$token) }}" data-complete-url="{{ route('letter-services.application.documents.complete',$token) }}">@csrf
<div class="letter-upload-list">@foreach($requirements as $index=>$requirement)
<label class="letter-upload-row @if(!($requirement['required'] ?? true)) letter-upload-row--optional @endif" data-requirement-key="{{ $requirement['key'] ?? 'requirement_'.($index + 1) }}" data-required-document="{{ ($requirement['required'] ?? true) ? 'true' : 'false' }}"><span class="letter-upload-icon"><i class="fas fa-file-image" aria-hidden="true"></i></span><span class="letter-upload-copy"><strong>{{ $requirement['label'] ?? 'Dokumen persyaratan' }} @if($requirement['required'] ?? true)<em>*</em>@else <small>(Opsional)</small>@endif</strong></span><span class="letter-file-picker"><i class="fas fa-upload" aria-hidden="true"></i><span>Pilih File</span><input type="file" name="documents[{{ $index }}]" accept=".jpg,.jpeg,.png,.pdf" @required($requirement['required'] ?? true)></span></label>
@endforeach
</div>
<div class="letter-form-actions"><a class="letter-form-back" href="{{ route('letter-services.track.token',$token) }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali</a><button class="letter-button" type="submit">Lanjutkan <i class="fas fa-arrow-right" aria-hidden="true"></i></button></div>
</form></div></div>
</x-layouts.app>
