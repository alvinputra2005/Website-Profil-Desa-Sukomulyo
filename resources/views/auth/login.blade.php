@extends('auth.layout')

@section('title', 'Login Admin')
@section('heading', 'Masuk ke Akun')
@section('description', 'Masukkan kredensial Anda untuk mengakses panel admin.')

@section('content')
    <form method="post" action="{{ route('login.store') }}" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="email">Alamat Email <span aria-hidden="true">*</span></label>
            <div class="auth-input-wrap @error('email') is-invalid @enderror">
                <i class="fa fa-envelope-o auth-input-icon" aria-hidden="true"></i>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    placeholder="nama@domain-desa.id"
                    autocomplete="username"
                    inputmode="email"
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                    required
                    autofocus
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
            <label for="password">Kata Sandi <span aria-hidden="true">*</span></label>
            <div class="auth-input-wrap @error('password') is-invalid @enderror">
                <i class="fa fa-lock auth-input-icon" aria-hidden="true"></i>
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Masukkan kata sandi Anda"
                    autocomplete="current-password"
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

        <div class="auth-form-options">
            <label class="auth-checkbox">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <span class="auth-checkbox-box" aria-hidden="true">
                    <i class="fa fa-check"></i>
                </span>
                <span>Ingat Saya</span>
            </label>
            <a href="{{ route('password.request') }}">Lupa kata sandi?</a>
        </div>

        <button type="submit" class="auth-submit">
            <span>Masuk ke Panel</span>
            <i class="fa fa-long-arrow-right" aria-hidden="true"></i>
        </button>

        <div class="auth-back-link">
            <span aria-hidden="true"></span>
            <a href="{{ route('beranda') }}">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                Kembali ke situs desa
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
            var icon = toggle.querySelector('i');

            toggle.addEventListener('click', function () {
                var willShow = input.type === 'password';
                input.type = willShow ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', willShow ? 'true' : 'false');
                toggle.setAttribute('aria-label', willShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                icon.className = willShow ? 'fa fa-eye-slash' : 'fa fa-eye';
                input.focus();
            });
        })();
    </script>
@endpush
