<?php

namespace App\Http\Controllers\Admin\Population\Residents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportResidentsRequest;
use App\Models\Resident;
use App\Services\ActivityLogger;
use App\Services\ResidentExcelImportService;
use App\Services\SiteCache;
use Illuminate\Http\RedirectResponse;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResidentImportController extends Controller
{
    public function __construct(
        private ActivityLogger $logger,
        private SiteCache $cache,
    ) {}

    public function store(
        ImportResidentsRequest $request,
        ResidentExcelImportService $importer,
    ): RedirectResponse {
        $this->authorize('create', Resident::class);
        $result = $importer->import($request->validated('file'));

        if ($result['created'] || $result['updated']) {
            $this->cache->invalidatePopulationStatistics();
            $this->logger->log('imported', 'penduduk', null, null, $result);
        }

        $message = "{$result['created']} data ditambahkan, {$result['updated']} diperbarui, {$result['failed']} gagal.";

        return back()
            ->with('success', "Impor selesai. {$message}")
            ->with('import_errors', $result['errors']);
    }

    public function template(): StreamedResponse
    {
        $this->authorize('create', Resident::class);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Penduduk');
        $sheet->fromArray(ResidentExcelImportService::headers(), null, 'A1');
        $sheet->fromArray([
            null, 'Contoh Penduduk', 'L', 'Sukomulyo', '1990-01-31',
            'Islam', 'Kawin', 'WNI', 'SLTA/Sederajat', 'Petani/Pekebun', 'O',
            'Jl. Desa No. 1', 'Sukomulyo', '01', '02', 'Tetap', 'Aktif',
            now()->toDateString(), '081234567890', 'contoh@example.com', '',
        ], null, 'A2');
        $sheet->setCellValueExplicit('A2', '3300000000000001', DataType::TYPE_STRING);
        $sheet->getStyle('A1:U1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        foreach (range('A', 'U') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'template-import-penduduk.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
