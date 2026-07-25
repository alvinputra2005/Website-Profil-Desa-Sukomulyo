@extends('auth.layout')

@section('title', 'Reset Kata Sandi')
@section('eyebrow', 'Pemulihan Akun')
@section('heading', 'Buat Kata Sandi Baru')
@section('description', 'Gunakan kata sandi yang kuat dan berbeda dari akun lainnya.')

@section('content')
    <form method="post" action="{{ route('password.update') }}" class="auth-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="auth-field">
            <label for="email">Alamat Email <span aria-hidden="true">*</span></label>
            <div class="auth-input-wrap @error('email') is-invalid @enderror">
                <i class="fa fa-envelope-o auth-input-icon" aria-hidden="true"></i>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $email) }}"
                    placeholder="nama@domain-desa.id"
                    autocomplete="username"
                    inputmode="email"
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                    required
                >
            </div>
            @error('email')
                <p class="auth-field-error" id="email-error" role="alert">
                    <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password">Kata Sandi Baru <span aria-hidden="true">*</span></label>
            <div class="auth-input-wrap @error('password') is-invalid @enderror">
                <i class="fa fa-lock auth-input-icon" aria-hidden="true"></i>
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Minimal 12 karakter, huruf dan angka"
                    autocomplete="new-password"
                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                    required
                >
                <button
                    class="auth-password-toggle"
                    type="button"
                    aria-label="Tampilkan kata sandi"
                    aria-controls="password"
                    aria-pressed="false"
                    data-password-toggle="password"
                >
                    <i class="fa fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            @error('password')
                <p class="auth-field-error" id="password-error" role="alert">
                    <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password_confirmation">Ulangi Kata Sandi <span aria-hidden="true">*</span></label>
            <div class="auth-input-wrap">
                <i class="fa fa-lock auth-input-icon" aria-hidden="true"></i>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    placeholder="Ketik ulang kata sandi baru"
                    autocomplete="new-password"
                    required
                >
            </div>
        </div>

        @error('token')
            <div class="auth-alert auth-alert-danger" role="alert">
                <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                <span>{{ $message }}</span>
            </div>
        @enderror

        <button type="submit" class="auth-submit">
            <span>Simpan Kata Sandi</span>
            <i class="fa fa-check" aria-hidden="true"></i>
        </button>

        <div class="auth-back-link">
            <span aria-hidden="true"></span>
            <a href="{{ route('login') }}">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                Kembali ke halaman masuk
            </a>
            <span aria-hidden="true"></span>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            var toggle = document.querySelector('[data-password-toggle]');
            if (!toggle) return;

            var input = document.getElementById(toggle.getAttribute('data-password-toggle'));
            var confirmation = document.getElementById('password_confirmation');
            var icon = toggle.querySelector('i');

            toggle.addEventListener('click', function () {
                var willShow = input.type === 'password';
                input.type = willShow ? 'text' : 'password';
                confirmation.type = willShow ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', willShow ? 'true' : 'false');
                toggle.setAttribute('aria-label', willShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                icon.className = willShow ? 'fa fa-eye-slash' : 'fa fa-eye';
                input.focus();
            });
        })();
    </script>
@endpush
