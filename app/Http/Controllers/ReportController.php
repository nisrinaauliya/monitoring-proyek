<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Models\Department;
use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tahun              = $request->input('tahun', now()->year);
        $bulan              = $request->input('bulan', now()->month);
        $selectedDeptCode   = $request->input('dept', null);

        $cutoffBulanIni  = Carbon::create($tahun, $bulan)->endOfMonth();
        $cutoffBulanLalu = Carbon::create($tahun, $bulan)->subMonth()->endOfMonth();

        $ppsmbs = Ppsmb::with(['user', 'department'])
            ->whereYear('created_at', $tahun)
            ->where('created_at', '<=', $cutoffBulanIni)
            ->get();

        $departments = Department::orderBy('code')->get();
        $statusList  = array_keys(config('status.colors'));

        // ── MATRIX SUMMARY ────────────────────────────────────
        $matrix         = [];
        $totalPerStatus = array_fill_keys($statusList, 0);
        $totalPengajuan = 0;

        foreach ($departments as $dept) {
            $deptPpsmbs = $ppsmbs->where('dept_id', $dept->id);
            $pengajuan  = $deptPpsmbs->count();

            if ($pengajuan === 0) continue;

            $totalPengajuan += $pengajuan;
            $row = ['pengajuan' => $pengajuan];

            foreach ($statusList as $status) {
                $count = $deptPpsmbs->filter(function ($p) use ($status, $cutoffBulanIni) {
                    $lastHistory = PpsmbHistory::where('ppsmb_id', $p->id)
                        ->where('created_at', '<=', $cutoffBulanIni)
                        ->latest('created_at')
                        ->first();
                    return $lastHistory && $lastHistory->status === $status;
                })->count();

                $row[$status] = $count;
                $totalPerStatus[$status] += $count;
            }

            $matrix[$dept->code] = $row;
        }

        // ── DETAIL PER DEPT ───────────────────────────────────
        $detailDept = null;
        $selectedDept = null;
        $filterStatus = $request->input('filter_status', '');
        $sortBy       = $request->input('sort_by', 'estimasi_selesai'); 

        if ($selectedDeptCode) {
            $selectedDept = $departments->firstWhere('code', $selectedDeptCode);

            if ($selectedDept) {
                $deptPpsmbs = $ppsmbs->where('dept_id', $selectedDept->id);

                $detailDept = $deptPpsmbs->map(function ($p) use ($cutoffBulanIni, $cutoffBulanLalu) {
                    $historyBulanIni = PpsmbHistory::where('ppsmb_id', $p->id)
                        ->where('created_at', '<=', $cutoffBulanIni)
                        ->latest('created_at')
                        ->first();

                    $historyBulanLalu = PpsmbHistory::where('ppsmb_id', $p->id)
                        ->where('created_at', '<=', $cutoffBulanLalu)
                        ->latest('created_at')
                        ->first();

                    return [
                        'ppsmb'                 => $p,
                        'nama_project'          => $p->nama_project,
                        'model_aplikasi'        => $p->model_aplikasi,
                        'user'                  => $p->user->name,
                        'status_bulan_lalu'     => $historyBulanLalu?->status ?? '-',
                        'status_bulan_ini'      => $historyBulanIni?->status ?? '-',
                        'progress_bulan_lalu'   => $historyBulanLalu?->progress ?? 0,
                        'progress_bulan_ini'    => $historyBulanIni?->progress ?? 0,
                        'estimasi_selesai_raw'  => $p->estimasi_selesai,
                        'estimasi_selesai'      => $p->estimasi_selesai
                            ? $p->estimasi_selesai->translatedFormat('M Y')
                            : '-',
                    ];
                })->values();

                //filter status
                if ($filterStatus) {
                    $detailDept = $detailDept->filter(fn($item) => $item['status_bulan_ini'] === $filterStatus)->values();
                }

                // Sort
                if ($sortBy === 'status') {
                    $detailDept = $detailDept->sortBy('status_bulan_ini')->values();
                } else {
                    $detailDept = $detailDept->sortBy(fn($item) => $item['estimasi_selesai_raw'] ?? '9999-12-31')->values();
                }

                // Pagination manual
                $perPage    = 15;
                $page       = $request->input('page', 1);
                $total      = $detailDept->count();
                $detailDept = new \Illuminate\Pagination\LengthAwarePaginator(
                    $detailDept->forPage($page, $perPage),
                    $total,
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            }
        }

        // Tahun list untuk dropdown
        $tahunList = Ppsmb::selectRaw('YEAR(created_at) as tahun')
            ->groupBy(DB::raw('YEAR(created_at)'))
            ->orderByDesc('tahun')
            ->pluck('tahun');

        $bulanIniLabel  = Carbon::create($tahun, $bulan)->translatedFormat('M Y');
        $bulanLaluLabel = Carbon::create($tahun, $bulan)->subMonth()->translatedFormat('M Y');

        return view('report.index', compact(
            'matrix', 'statusList', 'departments',
            'totalPerStatus', 'totalPengajuan',
            'detailDept', 'selectedDept',
            'tahun', 'bulan', 'tahunList', 'selectedDeptCode',
            'bulanIniLabel', 'bulanLaluLabel',
            'cutoffBulanIni', 'cutoffBulanLalu',
            'filterStatus', 'sortBy',
        ));
    }

    public function export(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);
        $bulan = $request->input('bulan', now()->month);

        $filename = 'PPSMB_Report_' . Carbon::create($tahun, $bulan)->format('Y_m') . '.xlsx';

        return Excel::download(new ReportExport($tahun, $bulan), $filename);
    }
}