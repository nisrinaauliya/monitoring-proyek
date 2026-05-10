<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportDeptSheet implements FromArray, WithTitle, WithStyles
{
    private array $data = [];
    private array $statusList;
    private string $bulanIniLabel;
    private string $bulanLaluLabel;
    private array $sectionMeta = []; // track posisi tiap section untuk styling

    public function __construct(
        private int $tahun,
        private int $bulan,
        private Department $dept,
    ) {
        $this->statusList     = array_keys(config('status.colors'));
        $this->bulanIniLabel  = Carbon::create($tahun, $bulan)->translatedFormat('M Y');
        $this->bulanLaluLabel = Carbon::create($tahun, $bulan)->subMonth()->translatedFormat('M Y');
    }

    public function title(): string
    {
        return $this->dept->code;
    }

    public function array(): array
    {
        $cutoffBulanIni  = Carbon::create($this->tahun, $this->bulan)->endOfMonth();
        $cutoffBulanLalu = Carbon::create($this->tahun, $this->bulan)->subMonth()->endOfMonth();

        $ppsmbs = Ppsmb::with(['user', 'department'])
            ->where('dept_id', $this->dept->id)
            ->whereYear('created_at', $this->tahun)
            ->where('created_at', '<=', $cutoffBulanIni)
            ->get();

        if ($ppsmbs->isEmpty()) {
            $this->data = [['Tidak ada data']];
            return $this->data;
        }

        // Ambil status terakhir tiap ppsmb di cutoff bulan ini
        $ppsmbs = $ppsmbs->map(function ($p) use ($cutoffBulanIni, $cutoffBulanLalu) {
            $historyIni  = PpsmbHistory::where('ppsmb_id', $p->id)
                ->where('created_at', '<=', $cutoffBulanIni)
                ->latest('created_at')->first();
            $historyLalu = PpsmbHistory::where('ppsmb_id', $p->id)
                ->where('created_at', '<=', $cutoffBulanLalu)
                ->latest('created_at')->first();

            $p->_statusIni     = $historyIni?->status ?? '-';
            $p->_statusLalu    = $historyLalu?->status ?? '-';
            $p->_progressIni   = $historyIni?->progress ?? 0;
            $p->_progressLalu  = $historyLalu?->progress ?? 0;
            $p->_catatanRevisi = $historyIni?->catatan ?? '-';
            return $p;
        });

        // ── SUMMARY ───────────────────────────────────────────
        $summaryHeader = ['Dept./Div.', 'Pengajuan PPSMB'];
        foreach ($this->statusList as $s) $summaryHeader[] = $s;

        $summaryData = [$this->dept->code, $ppsmbs->count()];
        foreach ($this->statusList as $s) {
            $summaryData[] = $ppsmbs->where('_statusIni', $s)->count() ?: '';
        }

        $rows   = [];
        $rows[] = $summaryHeader;
        $rows[] = $summaryData;
        $rows[] = [];

        // Header kolom standar
        $headerStandar = [
            'Nama Project', 'Model Aplikasi', 'User',
            "Status {$this->bulanLaluLabel}",
            "Status {$this->bulanIniLabel}",
            "Progress {$this->bulanLaluLabel}",
            "Progress {$this->bulanIniLabel}",
            "% {$this->bulanLaluLabel}",
            "% {$this->bulanIniLabel}",
            'Estimasi Selesai',
        ];

        // Header kolom khusus (Revisi User & Verifikasi CMD/Dinov)
        $headerKhusus = [
            'Nama Project', 'Model Aplikasi', 'User',
            'Progress', 'Notes',
        ];

        // Definisi bagian
        $bagian = [
            'Done (Live)'  => ['statuses' => ['Done (Live)'],  'type' => 'standar'],
            'UAT'          => ['statuses' => ['UAT'],           'type' => 'standar'],
            'Proses IT'    => ['statuses' => [
                'Antrian Analisa BA IT', 'Analisa BA IT',
                'Antrian Development', 'Proses Development',
            ], 'type' => 'standar'],
            'Revisi User'  => ['statuses' => ['Revisi User'],   'type' => 'khusus'],
            'Verifikasi CMD/Dinov' => ['statuses' => [
                'Verifikasi CMD/Dinov', 'Edit by User - Verifikasi CMD/Dinov',
            ], 'type' => 'khusus'],
            'Rejected'     => ['statuses' => ['Rejected'],      'type' => 'standar'],
        ];

        foreach ($bagian as $label => $config) {
            $filtered = $ppsmbs->filter(fn($p) => in_array($p->_statusIni, $config['statuses']))
                ->sortBy(fn($p) => $p->estimasi_selesai ?? '9999-12-31')
                ->values();

            if ($filtered->isEmpty()) continue;

            // Section label row
            $rows[] = [$label];
            $sectionLabelRow = count($rows) + 2; // +2 karena insert title nanti

            // Header
            $rows[] = $config['type'] === 'standar' ? $headerStandar : $headerKhusus;
            $headerRow = count($rows) + 2;

            $dataStart = count($rows) + 3;

            // Data rows
            foreach ($filtered as $p) {
                if ($config['type'] === 'standar') {
                    $rows[] = [
                        $p->nama_project,
                        $p->model_aplikasi,
                        $p->user->name,
                        $p->_statusLalu ?: '-',
                        $p->_statusIni  ?: '-',
                        $p->_progressLalu,
                        $p->_progressIni,
                        number_format($p->_progressLalu, 1) . '%',
                        number_format($p->_progressIni, 1)  . '%',
                        $p->estimasi_selesai ? $p->estimasi_selesai->translatedFormat('M Y') : '-',
                    ];
                } else {
                    $rows[] = [
                        $p->nama_project,
                        $p->model_aplikasi,
                        $p->user->name,
                        number_format($p->_progressIni, 1) . '%',
                        $p->_catatanRevisi,
                    ];
                }
            }

            $dataEnd = count($rows) + 2;

            $this->sectionMeta[] = [
                'label'    => $label,
                'type'     => $config['type'],
                'labelRow' => $sectionLabelRow,
                'headerRow'=> $headerRow,
                'dataStart'=> $dataStart,
                'dataEnd'  => $dataEnd,
            ];

            $rows[] = []; // spacer antar section
        }

        $this->data = $rows;
        return $rows;
    }

    public function styles(Worksheet $sheet): void
    {
        // Insert 2 baris title di atas
        $sheet->insertNewRowBefore(1, 2);

        $lastColStandar = 'J';
        $lastColKhusus  = 'E';

        // Title
        $sheet->mergeCells("A1:{$lastColStandar}1");
        $sheet->setCellValue('A1', "PPSMB – {$this->dept->code}");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Summary header (row 3)
        $summaryHeaderCols = count($this->statusList) + 2;
        $summaryLastCol    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($summaryHeaderCols);

        $sheet->getStyle("A3:{$summaryLastCol}3")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(40);

        // Summary data (row 4)
        $sheet->getStyle("A4:{$summaryLastCol}4")->applyFromArray([
            'font'      => ['bold' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF3FB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle("A3:{$summaryLastCol}4")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0C4DE']]],
        ]);

        // Section styling
        $sectionColors = [
            'Done (Live)'          => '375623',
            'UAT'                  => 'F97316',
            'Proses IT'            => '1F4E79',
            'Revisi User'          => 'C00000',
            'Verifikasi CMD/Dinov' => '7811bd',
            'Rejected'             => '373e49',
        ];

        foreach ($this->sectionMeta as $section) {
            $color    = $sectionColors[$section['label']] ?? '444444';
            $lastCol  = $section['type'] === 'standar' ? $lastColStandar : $lastColKhusus;
            $labelRow = $section['labelRow'];
            $hdrRow   = $section['headerRow'];
            $dStart   = $section['dataStart'];
            $dEnd     = $section['dataEnd'];

            // Section label row
            $sheet->getStyle("A{$labelRow}:{$lastCol}{$labelRow}")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);
            $sheet->getRowDimension($labelRow)->setRowHeight(22);

            // Header row
            $sheet->getStyle("A{$hdrRow}:{$lastCol}{$hdrRow}")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ]);
            $sheet->getRowDimension($hdrRow)->setRowHeight(35);

            // Data rows
            for ($r = $dStart; $r <= $dEnd; $r++) {
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                if (($r - $dStart) % 2 === 0) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F2F2F2');
                }
            }

            // Border
            if ($dStart <= $dEnd) {
                $sheet->getStyle("A{$hdrRow}:{$lastCol}{$dEnd}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0C4DE']]],
                ]);
            }
        }

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(16);
        $sheet->getColumnDimension('I')->setWidth(16);
        $sheet->getColumnDimension('J')->setWidth(14);
    }
}