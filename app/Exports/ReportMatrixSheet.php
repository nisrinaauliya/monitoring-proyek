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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ReportMatrixSheet implements FromArray, WithTitle, WithStyles
{
    private array $statusList;
    private array $excelColumns; // kolom yang ditampilkan di Excel (sudah digabung)
    private int $totalRows;

    // Status yang digabung ke satu kolom di Excel
    const MERGED_VERIFIKASI = [
        'Verifikasi CMD/Dinov',
        'Edit by User - Verifikasi CMD/Dinov',
    ];
    const MERGED_VERIFIKASI_LABEL = 'Verifikasi CMD/Dinov (incl. Edit by User)';

    public function __construct(
        private int $tahun,
        private int $bulan,
    ) {
        $this->statusList = array_keys(config('status.colors'));

        // Build excelColumns — gabung Verifikasi jadi satu
        $columns = [];
        $verifikasiAdded = false;
        foreach ($this->statusList as $status) {
            if (in_array($status, self::MERGED_VERIFIKASI)) {
                if (!$verifikasiAdded) {
                    $columns[] = self::MERGED_VERIFIKASI_LABEL;
                    $verifikasiAdded = true;
                }
            } else {
                $columns[] = $status;
            }
        }
        $this->excelColumns = $columns;
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function array(): array
    {
        $cutoff      = Carbon::create($this->tahun, $this->bulan)->endOfMonth();
        $ppsmbs      = Ppsmb::with('department')
            ->whereYear('created_at', $this->tahun)
            ->where('created_at', '<=', $cutoff)
            ->get();
        $departments = Department::orderBy('code')->get();

        $header = ['Dept', 'Pengajuan PPSMB', ...$this->excelColumns];
        $rows[] = $header;

        $totalPengajuan = 0;
        $totalPerCol    = array_fill_keys($this->excelColumns, 0);

        foreach ($departments as $dept) {
            $deptPpsmbs = $ppsmbs->where('dept_id', $dept->id);
            $pengajuan  = $deptPpsmbs->count();
            if ($pengajuan === 0) continue;

            $totalPengajuan += $pengajuan;
            $row = [$dept->code, $pengajuan];

            foreach ($this->excelColumns as $col) {
                // Tentukan status mana yang dihitung untuk kolom ini
                $statuses = $col === self::MERGED_VERIFIKASI_LABEL
                    ? self::MERGED_VERIFIKASI
                    : [$col];

                $count = $deptPpsmbs->filter(function ($p) use ($statuses, $cutoff) {
                    $lastHistory = PpsmbHistory::where('ppsmb_id', $p->id)
                        ->where('created_at', '<=', $cutoff)
                        ->latest('created_at')->first();
                    return $lastHistory && in_array($lastHistory->status, $statuses);
                })->count();

                $row[] = $count ?: '';
                $totalPerCol[$col] += $count;
            }

            $rows[] = $row;
        }

        $totalRow = ['Total', $totalPengajuan];
        foreach ($this->excelColumns as $col) {
            $totalRow[] = $totalPerCol[$col] ?: '';
        }
        $rows[] = $totalRow;

        $this->totalRows = count($rows);
        return $rows;
    }

    public function styles(Worksheet $sheet): void
    {
        $colCount     = count($this->excelColumns);
        $lastColIndex = $colCount + 2;
        $lastCol      = Coordinate::stringFromColumnIndex($lastColIndex);

        $sheet->insertNewRowBefore(1, 3);

        // ── ROW 1: Title ──────────────────────────────────────
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'PPSMB Progress – YTD ' . Carbon::create($this->tahun, $this->bulan)->translatedFormat('M Y'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(36);

        // ── ROW 2: Spacer ─────────────────────────────────────
        $sheet->getRowDimension(2)->setRowHeight(6);

        // ── ROW 3: Header grup ────────────────────────────────
        $sheet->mergeCells('A3:A4');
        $sheet->setCellValue('A3', 'Dept./Div.');

        $sheet->mergeCells('B3:B4');
        $sheet->setCellValue('B3', 'Pengajuan PPSMB');

        $progressStartCol = Coordinate::stringFromColumnIndex(3);
        $sheet->mergeCells("{$progressStartCol}3:{$lastCol}3");
        $sheet->setCellValue("{$progressStartCol}3", 'Progress PPSMB');

        $sheet->getStyle("A3:{$lastCol}3")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB(ReportColors::HEADER);
        $sheet->getStyle("A3:{$lastCol}3")
            ->getFont()->setBold(true)->setSize(11)
            ->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A3:{$lastCol}3")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(28);

        // ── ROW 4: Header kolom ───────────────────────────────
        foreach ($this->excelColumns as $i => $col) {
            $cellCol = Coordinate::stringFromColumnIndex($i + 3);
            $color   = $col === self::MERGED_VERIFIKASI_LABEL
                ? ReportColors::status('Verifikasi CMD/Dinov')
                : ReportColors::status($col);

            $sheet->setCellValue("{$cellCol}4", $col);
            $sheet->getStyle("{$cellCol}4")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($color);
            $sheet->getStyle("{$cellCol}4")
                ->getFont()->setBold(true)->setSize(10)
                ->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$cellCol}4")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
        }

        foreach (['A4', 'B4'] as $cell) {
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
        $sheet->getRowDimension(4)->setRowHeight(40);

        // ── DATA ROWS ─────────────────────────────────────────
        // totalRows = 1 header + N data dept + 1 total = N+2
        // insert 3 rows di atas, header array jadi row 4
        // data mulai row 5, sampai row (3 + totalRows - 1) = totalRows + 2
        $dataStart   = 5;
        $totalRowNum = 3 + $this->totalRows; // row total (baris terakhir array)
        $dataEnd     = $totalRowNum - 1;

        for ($r = $dataStart; $r <= $dataEnd; $r++) {
            $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$r}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getRowDimension($r)->setRowHeight(20);

            if (($r - $dataStart) % 2 === 0) {
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F2F7FC');
            }
        }

        // ── TOTAL ROW ─────────────────────────────────────────
        $sheet->getStyle("A{$totalRowNum}:{$lastCol}{$totalRowNum}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB(ReportColors::HEADER);
        $sheet->getStyle("A{$totalRowNum}:{$lastCol}{$totalRowNum}")
            ->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$totalRowNum}:{$lastCol}{$totalRowNum}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A{$totalRowNum}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension($totalRowNum)->setRowHeight(22);

        // ── BORDER ────────────────────────────────────────────
        $sheet->getStyle("A3:{$lastCol}{$totalRowNum}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0C4DE']],
            ],
        ]);

        // ── COLUMN WIDTHS ─────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(16);
        for ($c = 3; $c <= $lastColIndex; $c++) {
            $col = Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($col)->setWidth(22);
        }
    }
}