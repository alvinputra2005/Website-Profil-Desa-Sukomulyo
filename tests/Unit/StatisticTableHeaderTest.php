<?php

namespace Tests\Unit;

use App\Support\StatisticTableHeader;
use PHPUnit\Framework\TestCase;

class StatisticTableHeaderTest extends TestCase
{
    public function test_it_merges_shared_header_paths_and_keeps_leaf_labels_unique(): void
    {
        $headers = StatisticTableHeader::make([
            ['key' => 'area_code', 'label' => 'KODE', 'header_path' => ['KODE']],
            ['key' => 'area_name', 'label' => 'RW', 'header_path' => ['RW']],
            ['key' => 'female_heads', 'label' => 'JUMLAH KEPALA KELUARGA PEREMPUAN', 'header_path' => ['JUMLAH KEPALA KELUARGA PEREMPUAN']],
            ['key' => 'under_15', 'label' => 'KELOMPOK UMUR / < 15', 'header_path' => ['KELOMPOK UMUR', '< 15']],
            ['key' => 'age_15_19', 'label' => 'KELOMPOK UMUR / 15 - 19', 'header_path' => ['KELOMPOK UMUR', '15 - 19']],
            ['key' => 'age_20_24', 'label' => 'KELOMPOK UMUR / 20 - 24', 'header_path' => ['KELOMPOK UMUR', '20 - 24']],
        ]);

        $this->assertSame([
            ['label' => 'KODE', 'colspan' => 1, 'rowspan' => 2],
            ['label' => 'RW', 'colspan' => 1, 'rowspan' => 2],
            ['label' => 'JUMLAH KEPALA KELUARGA PEREMPUAN', 'colspan' => 1, 'rowspan' => 2],
            ['label' => 'KELOMPOK UMUR', 'colspan' => 3, 'rowspan' => 1],
        ], $headers[0]);
        $this->assertSame([
            ['label' => '< 15', 'colspan' => 1, 'rowspan' => 1],
            ['label' => '15 - 19', 'colspan' => 1, 'rowspan' => 1],
            ['label' => '20 - 24', 'colspan' => 1, 'rowspan' => 1],
        ], $headers[1]);
    }

    public function test_it_supports_three_levels_of_header_grouping(): void
    {
        $headers = StatisticTableHeader::make([
            ['key' => 'under_19_count', 'header_path' => ['PEREMPUAN', '< 19 TAHUN', 'JUMLAH']],
            ['key' => 'under_19_percentage', 'header_path' => ['PEREMPUAN', '< 19 TAHUN', '%']],
            ['key' => 'age_19_count', 'header_path' => ['PEREMPUAN', '≥ 19 TAHUN', 'JUMLAH']],
        ]);

        $this->assertSame([['label' => 'PEREMPUAN', 'colspan' => 3, 'rowspan' => 1]], $headers[0]);
        $this->assertSame([
            ['label' => '< 19 TAHUN', 'colspan' => 2, 'rowspan' => 1],
            ['label' => '≥ 19 TAHUN', 'colspan' => 1, 'rowspan' => 1],
        ], $headers[1]);
        $this->assertSame([
            ['label' => 'JUMLAH', 'colspan' => 1, 'rowspan' => 1],
            ['label' => '%', 'colspan' => 1, 'rowspan' => 1],
            ['label' => 'JUMLAH', 'colspan' => 1, 'rowspan' => 1],
        ], $headers[2]);
    }
}
