@php($value = fn($key, $fallback = '') => old($key, $application?->{$key} ?? $fallback))
@if($errors->any())<div class="letter-notice danger"><strong>Periksa kembali isian berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="letter-fields">
    <label class="full">Nama lengkap <em>*</em><input name="applicant_name" value="{{ $value('applicant_name') }}" required maxlength="150" autocomplete="name"></label>
    <label>NIK <em>*</em><input name="applicant_nik" value="{{ $value('applicant_nik') }}" required inputmode="numeric" minlength="16" maxlength="16"></label>
    <label>Nomor WhatsApp <em>*</em><input name="applicant_phone" value="{{ $value('applicant_phone') }}" required inputmode="tel" autocomplete="tel"></label>
    <label>Tempat lahir <em>*</em><input name="birth_place" value="{{ $value('birth_place') }}" required maxlength="100"></label>
    <label>Tanggal lahir <em>*</em><input type="date" name="birth_date" value="{{ old('birth_date', $application?->birth_date?->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required></label>
    <label>Jenis kelamin <em>*</em><select name="sex" required><option value="">Pilih</option><option value="L" @selected($value('sex')==='L')>Laki-laki</option><option value="P" @selected($value('sex')==='P')>Perempuan</option></select></label>
    <label>Dusun <em>*</em><select name="hamlet" required><option value="">Pilih dusun</option>@foreach(($hamlets ?? ['Gumul', 'Talasan', 'Bakir', 'Kedungrejo', 'Biyan']) as $hamlet)<option value="{{ $hamlet }}" @selected($value('hamlet') === $hamlet)>{{ $hamlet }}</option>@endforeach</select></label><label>RT <em>*</em><input name="rt" value="{{ $value('rt') }}" inputmode="numeric" required></label><label>RW <em>*</em><input name="rw" value="{{ $value('rw') }}" inputmode="numeric" required></label>
    <label class="full">Alamat lengkap <em>*</em><textarea name="address" required rows="4">{{ $value('address') }}</textarea></label>
    <label class="full">Keperluan surat <em>*</em><textarea name="purpose" required rows="4">{{ $value('purpose') }}</textarea></label>
    @foreach($fields as $field)@php($fieldValue=old('form_data.'.$field['key'], $application?->form_data_json[$field['key']] ?? ''))<label class="{{ $field['type']==='textarea'?'full':'' }}">{{ $field['label'] }} <em>*</em>
        @if($field['type']==='textarea')<textarea name="form_data[{{ $field['key'] }}]" required>{{ $fieldValue }}</textarea>
        @elseif(in_array($field['type'], ['select','radio']) && isset($field['options']))<select name="form_data[{{ $field['key'] }}]" required><option value="">Pilih {{ strtolower($field['label']) }}</option>@foreach($field['options'] as $key=>$option)<option value="{{ $key }}" @selected((string)$fieldValue===(string)$key)>{{ $option }}</option>@endforeach</select>
        @else<input type="{{ in_array($field['type'], ['date','number'])?$field['type']:'text' }}" name="form_data[{{ $field['key'] }}]" value="{{ $fieldValue }}" required>@endif
    </label>@endforeach
</div>
