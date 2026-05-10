<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportMatrixSheet implements FromArray, WithTitle, WithStyles
{
    private array $data = [];
    private array $statusList;
    private int $totalRows;

    public function __construct(
        private int $tahun,
        private int $bulan,
    ) {
        $this->statusList = array_keys(config('status.colors'));
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function array(): array
    {
        $cutoff      = Carbon::create($this->tahun, $this->bulan)->endOfMonth();
        $bulanLabel  = Carbon::create($this->tahun, $this->bulan)->translatedFormat('M Y');

        $ppsmbs      = Ppsmb::with('department')
            ->whereYear('created_at', $this->tahun)
            ->where('created_at', '<=', $cutoff)
            ->get();

        $departments = Department::orderBy('code')->get();

        // Header row 1
        $header1 = ['Dept./Div.', 'Pengajuan PPSMB'];
        foreach ($this->statusList as $status) {
            $header1[] = $status;
        }
        $rows[] = $header1;

        // Data rows
        $totalPengajuan = 0;
        $totalPerStatus = array_fill_keys($this->statusList, 0);

        foreach ($departments as $dept) {
            $deptPpsmbs = $ppsmbs->where('dept_id', $dept->id);
            $pengajuan  = $deptPpsmbs->count();

            if ($pengajuan === 0) continue;

            $totalPengajuan += $pengajuan;
            $row = [$dept->code, $pengajuan];

            foreach ($this->statusList as $status) {
                $count = $deptPpsmbs->filter(function ($p) use ($status, $cutoff) {
                    $lastHistory = PpsmbHistory::where('ppsmb_id', $p->id)
                        ->where('created_at', '<=', $cutoff)
                        ->latest('created_at')
                        ->first();
                    return $lastHistory && $lastHistory->status === $status;
                })->count();

                $row[] = $count ?: '';
                $totalPerStatus[$status] += $count;
            }

            $rows[] = $row;
        }

        // Total row
        $totalRow = ['Total', $totalPengajuan];
        foreach ($this->statusList as $status) {
            $totalRow[] = $totalPerStatus[$status] ?: '';
        }
        $rows[] = $totalRow;

        $this->totalRows = count($rows);
        $this->data = $rows;

        return $rows;
    }

    public function styles(Worksheet $sheet): void
    {
        $lastCol  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->statusList) + 2);
        $lastRow  = $this->totalRows;
        $totalRow = $lastRow + 1; // +1 karena header row 1

        // Title
        $sheet->insertNewRowBefore(1, 2);
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', "PPSMB Progress – YTD " . Carbon::create($this->tahun, $this->bulan)->translatedFormat('M Y'));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Header row style (row 3 setelah insert)
        $headerRow = 3;
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(40);

        // Data rows style
        $dataStart = $headerRow + 1;
        $dataEnd   = $dataStart + $lastRow - 2; // -1 header, -1 total

        for ($r = $dataStart; $r <= $dataEnd; $r++) {
            $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            // Dept code kolom A — left align
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Alternating row color
            if (($r - $dataStart) % 2 === 0) {
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EBF3FB');
            }
        }

        // Total row
        $totalRowNum = $dataEnd + 1;
        $sheet->getStyle("A{$totalRowNum}:{$lastCol}{$totalRowNum}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Border semua
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$totalRowNum}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'B0C4DE'],
                ],
            ],
        ]);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(14);
        for ($c = 3; $c <= count($this->statusList) + 2; $c++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($col)->setWidth(16);
        }
    }
}