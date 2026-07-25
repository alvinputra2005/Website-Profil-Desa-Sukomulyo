@extends('auth.layout')

@section('title', 'Lupa Kata Sandi')
@section('eyebrow', 'Pemulihan Akun')
@section('heading', 'Lupa Kata Sandi?')
@section('description', 'Masukkan alamat email akun Anda untuk menerima tautan pemulihan.')

@section('content')
    <form method="post" action="{{ route('password.email') }}" class="auth-form">
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
                    autocomplete="email"
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

        <button type="submit" class="auth-submit">
            <span>Kirim Tautan Pemulihan</span>
            <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
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
