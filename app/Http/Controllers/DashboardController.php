<?php

namespace App\Http\Controllers;

use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return view('dashboard');
        }

        // dashboard user (non-IT, non-CMD, non-DINOV)
        if (!in_array($user->dept, ['IT', 'CMD', 'DINOV'])) {
            $ppsmbs = Ppsmb::where('dept', $user->dept)->latest()->get();

            $total              = $ppsmbs->count();
            $verifikasiCmd      = $ppsmbs->where('status', 'Verifikasi CMD/Dinov')->count();
            $editByUser         = $ppsmbs->where('status', 'Edit by User')->count();
            $revisi             = $ppsmbs->where('status', 'Revisi User')->count();
            $antrianAnalisa     = $ppsmbs->where('status', 'Antrian Analisa BA IT')->count();
            $analisaBa          = $ppsmbs->where('status', 'Analisa BA IT')->count();
            $antrianDev         = $ppsmbs->where('status', 'Antrian Development')->count();
            $prosesDev          = $ppsmbs->where('status', 'Proses Development')->count();
            $uat                = $ppsmbs->where('status', 'UAT')->count();
            $done               = $ppsmbs->where('status', 'Done (Live)')->count();
            $rejected           = $ppsmbs->where('status', 'Rejected')->count();

            // aging per project UAT
            $projectUat   = $ppsmbs->where('status', 'UAT');
            $agingWarning = false;
            $uatAging     = [];

            foreach ($projectUat as $p) {
                $masukUat = PpsmbHistory::where('ppsmb_id', $p->id)
                    ->where('status', 'UAT')
                    ->latest()
                    ->value('created_at');

                $hari = $masukUat ? Carbon::parse($masukUat)->diffInDays(Carbon::now()) : 0;

                $uatAging[] = [
                    'ppsmb'     => $p,
                    'hari'      => $hari,
                    'masuk_uat' => $masukUat ? Carbon::parse($masukUat)->translatedFormat('d F Y') : '-',
                    'pct'       => min(100, round(($hari / 10) * 100)),
                ];

                if ($hari >= 10) {
                    $agingWarning = true;
                }
            }

            $projectAktif = $ppsmbs->whereNotIn('status', [
                'Done (Live)',
                'Rejected',
            ])->count();

            $showWarning = $projectAktif >= 3 && $agingWarning;

            // nama project yang blocking (untuk warning banner)
            $blockingProject = null;
            if ($showWarning) {
                foreach ($uatAging as $ua) {
                    if ($ua['hari'] >= 10) {
                        $blockingProject = $ua['ppsmb']->nama_project;
                        break;
                    }
                }
            }

            // project rejected otomatis (sidebar)
            $autoRejected = Ppsmb::where('dept', $user->dept)
                ->where('status', 'Rejected')
                ->whereHas('histories', function ($q) {
                    $q->where('pemeriksa', 'System')->where('status', 'Rejected');
                })
                ->latest('updated_at')
                ->take(5)
                ->get();

            // countdown revisi sebelum auto reject (sidebar)
            $revisiCountdown = $ppsmbs->where('status', 'Revisi User')
                ->filter(fn($p) => $p->revisi_at !== null)
                ->map(function ($p) {
                    $hariKe   = (int) Carbon::parse($p->revisi_at)->diffInDays(now());
                    $sisaHari = max(0, 30 - $hariKe);
                    return [
                        'ppsmb'     => $p,
                        'hari_ke'   => $hariKe,
                        'sisa_hari' => $sisaHari,
                        'pct'       => min(100, round(($hariKe / 30) * 100)),
                    ];
                })->values();
            
            $totalAktif = $ppsmbs->whereNotIn('status', [
                'Done (Live)',
                'Rejected',
            ])->count();

            return view('dashboard', compact(
                'ppsmbs',
                'totalAktif',
                'total',
                'verifikasiCmd',
                'editByUser',
                'revisi',
                'antrianAnalisa',
                'analisaBa',
                'antrianDev',
                'prosesDev',
                'uat',
                'done',
                'rejected',
                'uatAging',
                'showWarning',
                'blockingProject',
                'autoRejected',
                'revisiCountdown',
            ));
        }
        
        return view('dashboard');
    }
}