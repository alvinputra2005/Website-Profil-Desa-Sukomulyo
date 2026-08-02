<?php

namespace App\Support;

final class InventoryCategory
{
    public const ORIGINS = [
        'Pembelian Sendiri', 'Bantuan Pemerintah', 'Bantuan Provinsi',
        'Bantuan Kabupaten', 'Sumbangan', 'Lainnya',
    ];

    public const CONDITIONS = ['Baik', 'Rusak Ringan', 'Rusak Berat'];

    private const CATEGORIES = [
        'tanah' => [
            'label' => 'Tanah', 'icon' => 'fa-map',
            'description' => 'Tanah kas desa dan bidang tanah milik desa.',
            'fields' => [
                'area' => ['Luas Tanah (m²)', 'number', true],
                'location' => ['Letak / Alamat', 'text', true],
                'right_type' => ['Status Hak', 'text', true],
                'certificate_number' => ['Nomor Sertifikat', 'text', false],
                'certificate_date' => ['Tanggal Sertifikat', 'date', false],
                'usage' => ['Penggunaan', 'text', true],
            ],
        ],
        'peralatan' => [
            'label' => 'Peralatan dan Mesin', 'icon' => 'fa-cogs',
            'description' => 'Kendaraan, mesin, perangkat, dan peralatan kantor.',
            'fields' => [
                'brand' => ['Merek / Tipe', 'text', false], 'size' => ['Ukuran', 'text', false],
                'material' => ['Bahan', 'text', false], 'factory_number' => ['Nomor Pabrik', 'text', false],
                'frame_number' => ['Nomor Rangka', 'text', false], 'engine_number' => ['Nomor Mesin', 'text', false],
                'police_number' => ['Nomor Polisi', 'text', false], 'bpkb_number' => ['Nomor BPKB', 'text', false],
            ],
        ],
        'gedung' => [
            'label' => 'Gedung dan Bangunan', 'icon' => 'fa-building',
            'description' => 'Gedung, kantor, dan bangunan milik desa.',
            'fields' => [
                'building_condition' => ['Kondisi Bangunan', 'text', true], 'floors' => ['Bertingkat', 'select:Ya,Tidak', false],
                'concrete' => ['Konstruksi Beton', 'select:Ya,Tidak', false], 'building_area' => ['Luas Bangunan (m²)', 'number', true],
                'location' => ['Letak / Alamat', 'text', true], 'document_number' => ['Nomor Dokumen', 'text', false],
                'document_date' => ['Tanggal Dokumen', 'date', false], 'land_area' => ['Luas Tanah (m²)', 'number', false],
                'land_status' => ['Status Tanah', 'text', false], 'land_code' => ['Kode Tanah', 'text', false],
            ],
        ],
        'jalan' => [
            'label' => 'Jalan, Irigasi, dan Jaringan', 'icon' => 'fa-road',
            'description' => 'Jalan, jembatan, saluran, irigasi, dan jaringan desa.',
            'fields' => [
                'construction' => ['Konstruksi', 'text', true], 'length' => ['Panjang (m)', 'number', true],
                'width' => ['Lebar (m)', 'number', true], 'area' => ['Luas (m²)', 'number', true],
                'location' => ['Letak / Lokasi', 'text', true], 'document_number' => ['Nomor Dokumen', 'text', false],
                'document_date' => ['Tanggal Dokumen', 'date', false], 'land_status' => ['Status Tanah', 'text', false],
                'land_code' => ['Kode Tanah', 'text', false],
            ],
        ],
        'aset-lain' => [
            'label' => 'Aset Tetap Lainnya', 'icon' => 'fa-cubes',
            'description' => 'Buku, kesenian, hewan, tanaman, dan aset tetap lainnya.',
            'fields' => [
                'asset_type' => ['Jenis Aset', 'text', true], 'title' => ['Judul / Nama Rinci', 'text', false],
                'specification' => ['Spesifikasi', 'text', false], 'region_origin' => ['Asal Daerah', 'text', false],
                'creator' => ['Pencipta', 'text', false], 'material' => ['Bahan', 'text', false],
                'animal_type' => ['Jenis Hewan', 'text', false], 'animal_size' => ['Ukuran Hewan', 'text', false],
                'plant_type' => ['Jenis Tumbuhan', 'text', false], 'plant_size' => ['Ukuran Tumbuhan', 'text', false],
            ],
        ],
        'konstruksi' => [
            'label' => 'Konstruksi dalam Pengerjaan', 'icon' => 'fa-wrench',
            'description' => 'Bangunan dan prasarana yang masih dalam pengerjaan.',
            'fields' => [
                'physical_condition' => ['Kondisi Fisik', 'text', true], 'floors' => ['Bertingkat', 'select:Ya,Tidak', false],
                'concrete' => ['Konstruksi Beton', 'select:Ya,Tidak', false], 'building_area' => ['Luas Bangunan (m²)', 'number', true],
                'location' => ['Letak / Alamat', 'text', true], 'document_number' => ['Nomor Dokumen', 'text', false],
                'document_date' => ['Tanggal Dokumen', 'date', false], 'start_date' => ['Tanggal Mulai', 'date', false],
                'land_status' => ['Status Tanah', 'text', false], 'land_code' => ['Kode Tanah', 'text', false],
            ],
        ],
    ];

    public static function all(): array { return self::CATEGORIES; }
    public static function slugs(): array { return array_keys(self::CATEGORIES); }
    public static function get(string $slug): ?array { return self::CATEGORIES[$slug] ?? null; }
    public static function label(string $slug): string { return self::CATEGORIES[$slug]['label'] ?? $slug; }
}
