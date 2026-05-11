<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ReportDeptSheet implements WithTitle, WithEvents
{
    private string $bulanIniLabel;
    private string $bulanLaluLabel;
    private array $statusList;

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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->buildSheet($event->sheet->getDelegate());
            },
        ];
    }

    private function buildSheet(Worksheet $sheet): void
    {
        $cutoffBulanIni  = Carbon::create($this->tahun, $this->bulan)->endOfMonth();
        $cutoffBulanLalu = Carbon::create($this->tahun, $this->bulan)->subMonth()->endOfMonth();

        $ppsmbs = Ppsmb::with(['user'])
            ->where('dept_id', $this->dept->id)
            ->whereYear('created_at', $this->tahun)
            ->where('created_at', '<=', $cutoffBulanIni)
            ->get();

        $row = 1;

        // ── TITLE ─────────────────────────────────────────────
        $sheet->setCellValue("A{$row}", "PPSMB – {$this->dept->code}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
        $sheet->getRowDimension($row)->setRowHeight(30);
        $row += 2; // spacer

        if ($ppsmbs->isEmpty()) {
            $sheet->setCellValue("A{$row}", 'Tidak ada data');
            $this->setColumnWidths($sheet);
            return;
        }

        $ppsmbs = $ppsmbs->map(function ($p) use ($cutoffBulanIni, $cutoffBulanLalu) {
            $hi = PpsmbHistory::where('ppsmb_id', $p->id)
                ->where('created_at', '<=', $cutoffBulanIni)
                ->latest('created_at')->first();
            $hl = PpsmbHistory::where('ppsmb_id', $p->id)
                ->where('created_at', '<=', $cutoffBulanLalu)
                ->latest('created_at')->first();

            $p->_statusIni   = $hi?->status ?? '-';
            $p->_statusLalu  = $hl?->status ?? '-';
            $p->_progressIni = $hi?->progress ?? 0;
            $p->_progressLalu= $hl?->progress ?? 0;
            $p->_catatan     = $hi?->catatan ?? '-';
            return $p;
        });

        // ── SUMMARY ───────────────────────────────────────────

        //summary header row 2 — nama status (Verifikasi digabung)
        $excelCols = [];
        $verifikasiAdded = false;
        foreach ($this->statusList as $status) {
            if (in_array($status, ReportMatrixSheet::MERGED_VERIFIKASI)) {
                if (!$verifikasiAdded) {
                    $excelCols[] = ReportMatrixSheet::MERGED_VERIFIKASI_LABEL;
                    $verifikasiAdded = true;
                }
            } else {
                $excelCols[] = $status;
            }
        }

        $summaryLastColIdx = count($excelCols) + 2;
        $summaryLastCol    = Coordinate::stringFromColumnIndex($summaryLastColIdx);
        $progressStartCol  = Coordinate::stringFromColumnIndex(3);
        $summaryStartRow   = $row; // ← tambah ini

        // Summary header row 1 — grup
        $sheet->setCellValue("A{$row}", 'Dept./Div.');
        $sheet->setCellValue("B{$row}", 'Pengajuan PPSMB');
        $sheet->setCellValue("{$progressStartCol}{$row}", 'Progress PPSMB');
        $sheet->mergeCells("A{$row}:A".($row + 1));
        $sheet->mergeCells("B{$row}:B".($row + 1));
        $sheet->mergeCells("{$progressStartCol}{$row}:{$summaryLastCol}{$row}");

        $sheet->getStyle("A{$row}:{$summaryLastCol}{$row}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB(ReportColors::HEADER);
        $sheet->getStyle("A{$row}:{$summaryLastCol}{$row}")
            ->getFont()->setBold(true)->setSize(11)
            ->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$row}:{$summaryLastCol}{$row}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(28);
        $row++;

        // Summary header row 2
        foreach ($excelCols as $i => $s) {
            $col   = Coordinate::stringFromColumnIndex($i + 3);
            $color = $s === ReportMatrixSheet::MERGED_VERIFIKASI_LABEL
                ? ReportColors::status('Verifikasi CMD/Dinov')
                : ReportColors::status($s);
            $sheet->setCellValue("{$col}{$row}", $s);
            $sheet->getStyle("{$col}{$row}")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($color);
            $sheet->getStyle("{$col}{$row}")
                ->getFont()->setBold(true)->setSize(10)
                ->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
        }

        // A dan B di row ini sudah di-merge, tapi tetap style
        foreach (["A{$row}", "B{$row}"] as $cell) {
            $sheet->getStyle($cell)
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB(ReportColors::HEADER);
            $sheet->getStyle($cell)
                ->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($cell)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }
        $sheet->getRowDimension($row)->setRowHeight(40);
        $row++;

        // Summary data
        $sheet->setCellValue("A{$row}", $this->dept->code);
        $sheet->setCellValue("B{$row}", $ppsmbs->count());
        foreach ($excelCols as $i => $s) {
            $col      = Coordinate::stringFromColumnIndex($i + 3);
            $statuses = $s === ReportMatrixSheet::MERGED_VERIFIKASI_LABEL
                ? ReportMatrixSheet::MERGED_VERIFIKASI
                : [$s];
            $count = $ppsmbs->filter(fn($p) => in_array($p->_statusIni, $statuses))->count();
            $sheet->setCellValue("{$col}{$row}", $count ?: '');
        }
        $sheet->getStyle("A{$row}:{$summaryLastCol}{$row}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EBF3FB');
        $sheet->getStyle("A{$row}:{$summaryLastCol}{$row}")
            ->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:{$summaryLastCol}{$row}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$summaryStartRow}:{$summaryLastCol}{$row}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0C4DE']]],
        ]);
        $row += 2; // spacer

        // ── SECTIONS ──────────────────────────────────────────
        $bagian = [
            'Done (Live)'          => ['statuses' => ['Done (Live)'],                                                                        'type' => 'standar'],
            'UAT'                  => ['statuses' => ['UAT'],                                                                                'type' => 'standar'],
            'Proses IT'            => ['statuses' => ['Antrian Analisa BA IT', 'Analisa BA IT', 'Antrian Development', 'Proses Development'], 'type' => 'standar'],
            'Revisi User'          => ['statuses' => ['Revisi User'],                                                                        'type' => 'khusus'],
            'Verifikasi CMD/Dinov' => ['statuses' => ['Verifikasi CMD/Dinov', 'Edit by User - Verifikasi CMD/Dinov'],                        'type' => 'khusus'],
            'Rejected'             => ['statuses' => ['Rejected'],                                                                           'type' => 'standar'],
        ];

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
        $headerKhusus = ['Nama Project', 'Model Aplikasi', 'User', 'Progress', 'Notes'];

        foreach ($bagian as $label => $config) {
            $filtered = $ppsmbs->filter(fn($p) => in_array($p->_statusIni, $config['statuses']))
                ->sortBy(fn($p) => $p->estimasi_selesai ?? '9999-12-31')
                ->values();

            if ($filtered->isEmpty()) continue;

            $color   = ReportColors::section($label);
            $lastCol = $config['type'] === 'standar' ? 'J' : 'E';

            // Section label
            $sheet->setCellValue("A{$row}", $label);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($color);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                ->getFont()->setBold(true)->setSize(11)
                ->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getRowDimension($row)->setRowHeight(22);
            $row++;

            // Header
            $headers = $config['type'] === 'standar' ? $headerStandar : $headerKhusus;
            foreach ($headers as $i => $h) {
                $col = Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue("{$col}{$row}", $h);
            }
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($color);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                ->getFont()->setBold(true)
                ->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setWrapText(true);
            $sheet->getRowDimension($row)->setRowHeight(35);
            $headerRow = $row;
            $row++;

            // Data
            foreach ($filtered as $idx => $p) {
                $data = $config['type'] === 'standar' ? [
                    $p->nama_project,
                    $p->model_aplikasi,
                    $p->user->name,
                    $p->_statusLalu,
                    $p->_statusIni,
                    $p->_progressLalu,
                    $p->_progressIni,
                    number_format($p->_progressLalu, 1) . '%',
                    number_format($p->_progressIni, 1) . '%',
                    $p->estimasi_selesai ? $p->estimasi_selesai->translatedFormat('M Y') : '-',
                ] : [
                    $p->nama_project,
                    $p->model_aplikasi,
                    $p->user->name,
                    number_format($p->_progressIni, 1) . '%',
                    $p->_catatan,
                ];

                foreach ($data as $i => $val) {
                    $col = Coordinate::stringFromColumnIndex($i + 1);
                    $sheet->setCellValue("{$col}{$row}", $val);
                }

                $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setWrapText(true);
                $sheet->getStyle("B{$row}")
                    ->getAlignment()
                    ->setWrapText(true);
                $sheet->getRowDimension($row)->setRowHeight(-1);

                if ($idx % 2 === 0) {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F2F2F2');
                }
                $row++;
            }

            // Border section
            $sheet->getStyle("A{$headerRow}:{$lastCol}".($row - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0C4DE']]],
            ]);

            $row++; // spacer
        }

        $this->setColumnWidths($sheet);
    }

    private function setColumnWidths(Worksheet $sheet): void
    {
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