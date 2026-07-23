@extends('layouts.admin')

@section('title', 'Identitas Desa')
@section('page-description', 'Ringkasan informasi desa')

@php
    $profileSection ??= null;
    $location = $location ?: 'Wilayah administratif belum diisi';
@endphp

@section('content')
    <div class="identity-summary-actions">
        <a href="{{ route('admin.village-content.profile-edit') }}" class="btn btn-social btn-warning">
            <i class="fa fa-edit"></i> Ubah Data Identitas Desa
        </a>
        <a href="{{ route('profile-desa') }}" target="_blank" rel="noopener" class="btn btn-social btn-info">
            <i class="fa fa-globe"></i> Lihat Halaman Publik
        </a>
    </div>

    <div class="box box-info identity-summary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-home"></i> Identitas Desa</h3>
        </div>
        <div class="box-body">
            <div class="identity-hero" style="background-image: url('{{ asset('assets/village-rice-fields.jpg') }}');">
                <img src="{{ asset('assets/logo-brighter-sukomulyo.svg') }}" alt="Logo {{ $siteName }}" class="identity-hero-logo">
                <h3>{{ $siteName }}</h3>
                <p>{{ $location }}</p>
            </div>

            <div class="nav-tabs-custom identity-tabs">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#identitas-umum" data-toggle="tab">Umum</a></li>
                    <li><a href="#identitas-profil" data-toggle="tab">Profil</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="identitas-umum">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover identity-table">
                                <tbody>
                                    @foreach ($identityGroups as $group)
                                        <tr>
                                            <th colspan="3" class="identity-table-section">
                                                <strong>{{ strtoupper($group['title']) }}</strong>
                                            </th>
                                        </tr>
                                        @foreach ($group['rows'] as $row)
                                            <tr>
                                                <td class="identity-table-label">{{ $row['label'] }}</td>
                                                <td class="identity-table-separator">:</td>
                                                <td>
                                                    @if (($row['type'] ?? null) === 'email' && $row['value'])
                                                        <a href="mailto:{{ $row['value'] }}">{{ $row['value'] }}</a>
                                                    @elseif (($row['type'] ?? null) === 'url' && filter_var($row['value'], FILTER_VALIDATE_URL))
                                                        <a href="{{ $row['value'] }}" target="_blank" rel="noopener noreferrer">{{ $row['value'] }}</a>
                                                    @else
                                                        {{ $row['value'] ?: '-' }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane" id="identitas-profil">
                        @if ($profileSection)
                            <article class="identity-profile-preview">
                                <h3>{{ $profileSection->title }}</h3>
                                @if ($profileSection->image)
                                    <img src="{{ $profileSection->image->url }}" alt="{{ $profileSection->image->alt_text ?: $profileSection->title }}">
                                @endif
                                <div>{!! $profileSection->content !!}</div>
                            </article>
                        @else
                            <div class="empty-state">
                                <i class="fa fa-file-text-o fa-2x"></i>
                                <p>Profil desa belum diisi.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
