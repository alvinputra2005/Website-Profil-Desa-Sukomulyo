<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buku Pemerintah Desa</title>
    <style>
        body{margin:28px;color:#111;font:12px Arial,sans-serif}h1,h2,p{text-align:center;margin:3px}.actions{margin-bottom:18px;text-align:right}.actions a,.actions button{padding:7px 12px;border:1px solid #777;background:#fff;color:#111;text-decoration:none;cursor:pointer}table{width:100%;margin-top:22px;border-collapse:collapse}th,td{padding:6px;border:1px solid #222;vertical-align:top}th{background:#eee;text-align:center;font-size:10px}td{font-size:10px}.nowrap{white-space:nowrap}@media print{body{margin:0}.actions{display:none}@page{size:landscape;margin:12mm}}
    </style>
</head>
<body>
    <div class="actions"><a href="{{ route('admin.officials.index') }}">Kembali</a> <button onclick="window.print()">Cetak</button></div>
    <h2>BUKU PEMERINTAH DESA</h2>
    <h1>{{ strtoupper(\App\Models\Setting::where('key', 'site.name')->value('value') ?: 'DESA SUKOMULYO') }}</h1>
    <p>Daftar Perangkat Desa</p>
    <table>
        <thead><tr><th>No</th><th>Nama / Identitas</th><th>Tempat, Tanggal Lahir</th><th>JK</th><th>Agama</th><th>Pangkat / Golongan</th><th>Jabatan</th><th>Pendidikan</th><th>SK Pengangkatan</th><th>SK Pemberhentian</th><th>Masa Jabatan</th></tr></thead>
        <tbody>
            @forelse($officials as $official)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $official->full_name }}</strong><br>NIP: {{ $official->nip ?: '-' }}<br>NIPD: {{ $official->village_employee_number ?: '-' }}<br>NIK: {{ $official->nik ?: '-' }}</td>
                    <td>{{ $official->birth_place ?: '-' }}{{ $official->birth_date ? ', '.$official->birth_date->translatedFormat('d F Y') : '' }}</td>
                    <td>{{ $official->sex ?: '-' }}</td><td>{{ $official->religion ?: '-' }}</td><td>{{ $official->rank_grade ?: '-' }}</td><td>{{ $official->position_label }}</td><td>{{ $official->education ?: '-' }}</td>
                    <td>{{ $official->appointment_decree ?: '-' }}<br>{{ $official->appointment_date?->format('d-m-Y') ?: '-' }}</td>
                    <td>{{ $official->dismissal_decree ?: '-' }}<br>{{ $official->dismissal_date?->format('d-m-Y') ?: '-' }}</td>
                    <td>{{ $official->term ?: '-' }}</td>
                </tr>
            @empty<tr><td colspan="11" style="text-align:center">Belum ada data.</td></tr>@endforelse
        </tbody>
    </table>
    <script>window.addEventListener('load',function(){if(new URLSearchParams(location.search).has('auto'))window.print()})</script>
</body>
</html>
