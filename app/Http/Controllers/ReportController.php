<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Filter tahun & bulan
        $tahun = $request->input('tahun', now()->year);
        $bulan = $request->input('bulan', now()->month);

        $cutoffBulanIni  = Carbon::create($tahun, $bulan)->endOfMonth();
        $cutoffBulanLalu = Carbon::create($tahun, $bulan)->subMonth()->endOfMonth();

        $startOfYear = Carbon::create($tahun)->startOfYear();

        // Ambil semua PPSMB yang dibuat dalam tahun yang dipilih (YTD)
        $ppsmbs = Ppsmb::with(['user', 'department', 'picBa', 'developerUser'])
            ->whereYear('created_at', $tahun)
            ->where('created_at', '<=', $cutoffBulanIni)
            ->get();

        // Semua department
        $departments = Department::orderBy('code')->get();

        // Status list
        $statusList = array_keys(config('status.colors'));

        // ── MATRIX SUMMARY ────────────────────────────────────
        $matrix = [];
        $totalPerStatus = array_fill_keys($statusList, 0);
        $totalPengajuan = 0;

        foreach ($departments as $dept) {
            $deptPpsmbs = $ppsmbs->where('dept_id', $dept->id);
            $pengajuan  = $deptPpsmbs->count();
            $totalPengajuan += $pengajuan;

            $row = ['pengajuan' => $pengajuan];

            foreach ($statusList as $status) {
                // Status saat ini = status terakhir ppsmb di cutoff bulan ini
                $count = $deptPpsmbs->filter(function ($p) use ($status, $cutoffBulanIni) {
                    // Cek status ppsmb berdasarkan history terakhir sebelum cutoff
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

        // ── DETAIL PER DEPARTMENT ─────────────────────────────
        $detailPerDept = [];

        foreach ($departments as $dept) {
            $deptPpsmbs = $ppsmbs->where('dept_id', $dept->id);

            if ($deptPpsmbs->isEmpty()) continue;

            $projects = $deptPpsmbs->map(function ($p) use ($cutoffBulanIni, $cutoffBulanLalu, $statusList) {

                // Status bulan ini
                $historyBulanIni = PpsmbHistory::where('ppsmb_id', $p->id)
                    ->where('created_at', '<=', $cutoffBulanIni)
                    ->latest('created_at')
                    ->first();

                // Status bulan lalu
                $historyBulanLalu = PpsmbHistory::where('ppsmb_id', $p->id)
                    ->where('created_at', '<=', $cutoffBulanLalu)
                    ->latest('created_at')
                    ->first();

                return [
                    'ppsmb'             => $p,
                    'nama_project'      => $p->nama_project,
                    'model_aplikasi'    => $p->model_aplikasi,
                    'user'              => $p->user->name,
                    'status_bulan_lalu' => $historyBulanLalu?->status ?? '-',
                    'status_bulan_ini'  => $historyBulanIni?->status ?? '-',
                    'progress_bulan_lalu' => $historyBulanLalu?->progress ?? 0,
                    'progress_bulan_ini'  => $historyBulanIni?->progress ?? 0,
                    'estimasi_selesai'  => $p->estimasi_selesai
                        ? $p->estimasi_selesai->translatedFormat('M Y')
                        : '-',
                ];
            })->values();

            // Summary status per dept untuk header tabel detail
            $summaryDept = [];
            foreach ($statusList as $status) {
                $summaryDept[$status] = $projects->where('status_bulan_ini', $status)->count();
            }

            $detailPerDept[$dept->code] = [
                'dept'      => $dept,
                'projects'  => $projects,
                'summary'   => $summaryDept,
                'pengajuan' => $deptPpsmbs->count(),
            ];
        }

        // Filter tahun yang tersedia untuk dropdown
        $tahunList = Ppsmb::selectRaw('YEAR(created_at) as tahun')
            ->groupBy('tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun');

        return view('report.index', compact(
            'matrix', 'statusList', 'departments',
            'totalPerStatus', 'totalPengajuan',
            'detailPerDept',
            'tahun', 'bulan', 'tahunList',
            'cutoffBulanIni', 'cutoffBulanLalu',
        ));
    }
}