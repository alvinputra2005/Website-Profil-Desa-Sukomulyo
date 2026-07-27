<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PopulationPeriodRequest;
use App\Services\PopulationStatistics;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PopulationReportController extends PopulationController
{
    public function index(PopulationPeriodRequest $request, PopulationStatistics $statistics): View
    {
        [$year, $month] = $this->period($request);

        return view('admin.population.report', array_merge(
            $statistics->monthlyReport($year, $month),
            compact('year', 'month')
        ));
    }

    public function export(PopulationPeriodRequest $request, PopulationStatistics $statistics): Response
    {
        [$year, $month] = $this->period($request);
        $report = $statistics->monthlyReport($year, $month);
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['LAPORAN PENDUDUK BULANAN', $report['start']->translatedFormat('F Y')], ';');
        fputcsv($stream, ['Uraian', 'Laki-laki', 'Perempuan', 'Total'], ';');
        fputcsv($stream, ['Penduduk awal bulan', $report['beginning']['male'], $report['beginning']['female'], $report['beginning']['total']], ';');
        foreach (['birth' => 'Lahir', 'arrival' => 'Datang', 'departure' => 'Pindah', 'death' => 'Meninggal'] as $type => $label) {
            fputcsv($stream, [$label, $report['changes'][$type]['male'], $report['changes'][$type]['female'], $report['changes'][$type]['total']], ';');
        }
        fputcsv($stream, ['Penduduk akhir bulan', $report['ending']['male'], $report['ending']['female'], $report['ending']['total']], ';');
        fputcsv($stream, [], ';');
        fputcsv($stream, ['Tanggal', 'Peristiwa', 'NIK', 'Nama', 'Jenis Kelamin', 'Keterangan'], ';');
        foreach ($report['events'] as $event) {
            fputcsv($stream, [
                $event->event_date->format('d-m-Y'), $event->event_label, $event->nik,
                $event->resident_name, $event->sex === 'L' ? 'Laki-laki' : 'Perempuan',
                $event->destination_address ?: $event->cause ?: $event->notes,
            ], ';');
        }
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laporan-penduduk-'.$year.'-'.str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.csv"',
        ]);
    }

    private function period(PopulationPeriodRequest $request): array
    {
        return $request->period();
    }
}
