<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportExport implements WithMultipleSheets
{
    public function __construct(
        private int $tahun,
        private int $bulan,
    ) {}

    public function sheets(): array
    {
        $departments = Department::orderBy('code')->get();
        $sheets = [new ReportMatrixSheet($this->tahun, $this->bulan)];

        foreach ($departments as $dept) {
            $sheets[] = new ReportDeptSheet($this->tahun, $this->bulan, $dept);
        }

        return $sheets;
    }
}