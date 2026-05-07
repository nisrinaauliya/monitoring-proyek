<?php

namespace App\Http\Controllers;

use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return match($user->role) {
            'admin'                                             => $this->adminDashboard(),
            'verifikator'                                       => $this->verifikatorDashboard($user),
            'project_leader', 'business_analyst', 'developer'   => $this->itDashboard($user),
            default                                             => $this->userDashboard($user),
        };
    }

    private function userDashboard($user)
    {
        $ppsmbs = Ppsmb::where('dept', $user->dept)->latest()->get();

        $total          = $ppsmbs->count();
        $totalAktif     = $ppsmbs->whereNotIn('status', ['Done (Live)', 'Rejected'])->count();
        $revisi         = $ppsmbs->where('status', 'Revisi User')->count();
        $verifikasiCmd  = $ppsmbs->where('status', 'Verifikasi CMD/Dinov')->count();
        $editByUser     = $ppsmbs->where('status', 'Edit by User - Verifikasi CMD/Dinov')->count();
        $antrianAnalisa = $ppsmbs->where('status', 'Antrian Analisa BA IT')->count();
        $analisaBa      = $ppsmbs->where('status', 'Analisa BA IT')->count();
        $antrianDev     = $ppsmbs->where('status', 'Antrian Development')->count();
        $prosesDev      = $ppsmbs->where('status', 'Proses Development')->count();
        $uat            = $ppsmbs->where('status', 'UAT')->count();
        $done           = $ppsmbs->where('status', 'Done (Live)')->count();
        $rejected       = $ppsmbs->where('status', 'Rejected')->count();

        // aging UAT
        $agingWarning = false;
        $uatAging     = [];

        foreach ($ppsmbs->where('status', 'UAT') as $p) {
            $masukUat = PpsmbHistory::where('ppsmb_id', $p->id)
                ->where('status', 'UAT')
                ->latest()
                ->value('created_at');

            $hari = $masukUat ? (int) Carbon::parse($masukUat)->floatDiffInDays(now()) : 0;

            $uatAging[] = [
                'ppsmb'     => $p,
                'hari'      => $hari,
                'masuk_uat' => $masukUat ? Carbon::parse($masukUat)->translatedFormat('d F Y') : '-',
                'pct'       => min(100, round(($hari / 10) * 100)),
            ];

            if ($hari >= 10) $agingWarning = true;
        }

        // warning blokir pengajuan
        $showWarning     = $totalAktif >= 3 && $agingWarning;
        $blockingProject = null;

        if ($showWarning) {
            foreach ($uatAging as $ua) {
                if ($ua['hari'] >= 10) {
                    $blockingProject = $ua['ppsmb']->nama_project;
                    break;
                }
            }
        }

        // sidebar: auto rejected
        $autoRejected = Ppsmb::where('dept', $user->dept)
            ->where('status', 'Rejected')
            ->whereHas('histories', fn($q) => $q->where('pemeriksa', 'System')->where('status', 'Rejected'))
            ->latest('updated_at')
            ->take(5)
            ->get();

        // sidebar: countdown revisi
        $revisiCountdown = $ppsmbs->where('status', 'Revisi User')
            ->filter(fn($p) => $p->revisi_at !== null)
            ->map(fn($p) => [
                'ppsmb'     => $p,
                'hari_ke'   => (int) Carbon::parse($p->revisi_at)->diffInDays(now()),
                'sisa_hari' => max(0, 30 - (int) Carbon::parse($p->revisi_at)->diffInDays(now())),
                'pct'       => min(100, round(((int) Carbon::parse($p->revisi_at)->diffInDays(now()) / 30) * 100)),
            ])->values();

        return view('dashboard.user', compact(
            'ppsmbs', 'total', 'totalAktif', 'revisi',
            'verifikasiCmd', 'editByUser', 'antrianAnalisa', 'analisaBa',
            'antrianDev', 'prosesDev', 'uat', 'done', 'rejected',
            'uatAging', 'showWarning', 'blockingProject',
            'autoRejected', 'revisiCountdown',
        ));
    }

    private function verifikatorDashboard($user)
    {
        $allPpsmbs = Ppsmb::with('user')->latest()->get();

        $activePpsmbs = $allPpsmbs
            ->whereNotIn('status', ['Done (Live)', 'Rejected'])
            ->values();
        
        // Summary cards
        $menungguVerifikasi = $activePpsmbs->whereIn('status', [
            'Verifikasi CMD/Dinov',
            'Edit by User - Verifikasi CMD/Dinov',
        ])->count();

        $revisi  = $activePpsmbs->where('status', 'Revisi User')->count();
        $uatAging = $activePpsmbs->where('status', 'UAT')->filter(function ($p) {
            $masukUat = PpsmbHistory::where('ppsmb_id', $p->id)
                ->where('status', 'UAT')->latest()->value('created_at');
            return $masukUat && (int) Carbon::parse($masukUat)->floatDiffInDays(now()) >= 10;
        })->count();

        $totalAktif = $activePpsmbs->count();

        // Matrix dept x status
        $statusList = array_keys(config('status.colors'));
        $depts      = $allPpsmbs->pluck('dept')->unique()->sort()->values();

        $matrix = [];
        foreach ($depts as $dept) {
            $matrix[$dept] = [];
            foreach ($statusList as $status) {
                $matrix[$dept][$status] = $allPpsmbs
                    ->where('dept', $dept)
                    ->where('status', $status)
                    ->map(fn($p) => [
                        'id'             => $p->id,
                        'nama_project'   => $p->nama_project,
                        'no_ppsmb'       => $p->no_ppsmb ?? '—',
                        'user'           => $p->user->name,
                        'model_aplikasi' => $p->model_aplikasi,
                    ])
                    ->values();
            }
        }

        // Antrian verifikasi (urut dari paling lama)
        $antrian = $activePpsmbs
            ->whereIn('status', ['Verifikasi CMD/Dinov', 'Edit by User - Verifikasi CMD/Dinov'])
            ->sortBy('created_at')
            ->values();

        // Area chart — estimasi selesai per bulan
        $projectAktifDenganEstimasi = $activePpsmbs
            ->whereNotNull('estimasi_selesai');

        $chartData = [];
        if ($projectAktifDenganEstimasi->count() > 0) {
            $minDate = $projectAktifDenganEstimasi->min('estimasi_selesai');
            $maxDate = $projectAktifDenganEstimasi->max('estimasi_selesai');

            $start = Carbon::parse($minDate)->startOfMonth();
            $end   = Carbon::parse($maxDate)->endOfMonth();

            $current = $start->copy();
            while ($current->lte($end)) {
                $bulan = $current->format('Y-m');
                $chartData[] = [
                    'bulan'   => $current->translatedFormat('M Y'),
                    'jumlah'  => $projectAktifDenganEstimasi->filter(function ($p) use ($bulan) {
                        return Carbon::parse($p->estimasi_selesai)->format('Y-m') === $bulan;
                    })->count(),
                ];
                $current->addMonth();
            }
        }

        // Trend pengajuan per bulan
        $trendPengajuan = Ppsmb::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as jumlah")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        return view('dashboard.verifikator', compact(
            'allPpsmbs', 'activePpsmbs',
            'menungguVerifikasi', 'revisi', 'uatAging',
            'matrix', 'statusList', 'depts',
            'antrian', 'chartData', 'trendPengajuan',
        ));
    }

    private function itDashboard($user)
    {
        return match($user->role) {
            'project_leader'   => $this->projectLeaderDashboard($user),
            'business_analyst' => $this->baDashboard($user),
            'developer'        => $this->developerDashboard($user),
            default            => view('dashboard.it'),
        };
    }
    private function projectLeaderDashboard($user)
    {
        // ambil project sesuai tim project leader
        $ppsmbs = Ppsmb::with('user')
            ->whereIn('status', [
                'Antrian Analisa BA IT',
                'Analisa BA IT',
                'Antrian Development',
                'Proses Development',
                'UAT',
            ])
            ->when($user->tim === 'internal', fn($q) => $q->whereIn('model_aplikasi', [
                'Aplikasi Internal MD',
                'Improvement IT System',
            ]))
            ->when($user->tim === 'eksternal', fn($q) => $q->where('model_aplikasi',
                'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot'
            ))
            ->latest()
            ->get();

        // summary cards
        $totalAktif      = $ppsmbs->count();
        $analisaBa       = $ppsmbs->where('status', 'Analisa BA IT')->count();
        $antrianAnalisa  = $ppsmbs->where('status', 'Antrian Analisa BA IT')->count();
        $antrianDev      = $ppsmbs->where('status', 'Antrian Development')->count();
        $prosesDev       = $ppsmbs->where('status', 'Proses Development')->count();
        $uat             = $ppsmbs->where('status', 'UAT')->count();

        // project yang estimasinya telat (estimasi_selesai < hari ini dan belum done)
        $projectTelat = $ppsmbs->filter(fn($p) =>
            $p->estimasi_selesai && Carbon::parse($p->estimasi_selesai)->isPast()
        )->count();

        // beban kerja per BA
        $bebanBa = User::where('dept', 'IT')
            ->where('role', 'business_analyst')
            ->when($user->tim, fn($q) => $q->where('tim', $user->tim))
            ->get()
            ->map(fn($ba)   => [
                'nama'      => $ba->name,
                'analisa'   => $ppsmbs->whereIn('pic_ba', $ba->name)
                                    ->where('status', 'Analisa BA IT')->count(),
                'total'     => $ppsmbs->where('pic_ba', $ba->name)->count(),
                'secondary' => $ppsmbs->where('secondary_ba', $ba->name)->count(),
            ]);

        // beban kerja per developer
        $bebanDev = User::where('dept', 'IT')
            ->where('role', 'developer')
            ->when($user->tim, fn($q) => $q->where('tim', $user->tim))
            ->get()
            ->map(fn($dev) => [
                'nama'     => $dev->name,
                'aktif'    => $ppsmbs->where('developer', $dev->name)
                                    ->whereIn('status', ['Proses Development', 'UAT'])->count(),
                'total'    => $ppsmbs->where('developer', $dev->name)->count(),
                'progress' => $ppsmbs->where('developer', $dev->name)->avg('progress') ?? 0,
            ]);

        // list project dengan info estimasi
        $projectList = $ppsmbs->map(fn($p) => [
            'ppsmb'            => $p,
            'telat'            => $p->estimasi_selesai && Carbon::parse($p->estimasi_selesai)->isPast(),
            'sisa_hari'        => $p->estimasi_selesai
                ? (int) Carbon::now()->diffInDays(Carbon::parse($p->estimasi_selesai), false)
                : null,
        ]);

        return view('dashboard.it.project_leader', compact(
            'ppsmbs', 'totalAktif', 'antrianAnalisa', 'analisaBa', 'antrianDev',
            'prosesDev', 'uat', 'projectTelat',
            'bebanBa', 'bebanDev', 'projectList', 'user',
        ));
    }

    private function baDashboard($user)
    {
        $ppsmbs = Ppsmb::with('user')
            ->whereIn('status', [
                'Analisa BA IT',
                'Antrian Development',
                'Proses Development',
                'UAT',
            ])
            ->where(function ($q) use ($user) {
                $q->where('pic_ba', $user->name)
                ->orWhere('secondary_ba', $user->name);
            })
            ->latest()
            ->get();

        $totalAssigned  = $ppsmbs->count();
        $perluAnalisa   = $ppsmbs->where('status', 'Analisa BA IT')->count();
        $antrian        = $ppsmbs->where('status', 'Antrian Development')->count();
        $sudahLanjut    = $ppsmbs->whereIn('status', ['Proses Development', 'UAT'])->count();
        $prosesDev      = $ppsmbs->where('status', 'Proses Development')->count();
        $uat            = $ppsmbs->where('status', 'UAT')->count();

        $projectList = $ppsmbs->map(fn($p) => [
            'ppsmb'        => $p,
            'is_primary'   => $p->pic_ba === $user->name,
            'is_secondary' => $p->secondary_ba === $user->name,
            'sisa_hari'    => $p->estimasi_selesai
                ? (int) Carbon::now()->diffInDays(Carbon::parse($p->estimasi_selesai), false)
                : null,
            'telat'        => $p->estimasi_selesai && Carbon::parse($p->estimasi_selesai)->isPast(),
        ]);

        return view('dashboard.it.business_analyst', compact(
            'ppsmbs', 'totalAssigned', 'perluAnalisa', 'antrian', 'sudahLanjut',
            'prosesDev', 'uat', 'projectList', 'user',
        ));
    }

    private function developerDashboard($user)
    {
        $ppsmbs = Ppsmb::with('user')
            ->whereIn('status', [
                'Proses Development',
                'UAT',
                'Done (Live)',
            ])
            ->where('developer', $user->name)
            ->latest()
            ->get();

        $totalAssigned = $ppsmbs->count();
        $prosesDev     = $ppsmbs->where('status', 'Proses Development')->count();
        $uat           = $ppsmbs->where('status', 'UAT')->count();
        $doneLive      = $ppsmbs->where('status', 'Done (Live)')->count();

        $projectList = $ppsmbs->map(fn($p) => [
            'ppsmb'    => $p,
            'sisa_hari' => $p->estimasi_selesai
                ? (int) Carbon::now()->diffInDays(Carbon::parse($p->estimasi_selesai), false)
                : null,
            'telat'    => $p->estimasi_selesai && Carbon::parse($p->estimasi_selesai)->isPast(),
        ]);

        return view('dashboard.it.developer', compact(
            'ppsmbs', 'totalAssigned', 'prosesDev', 'uat', 'doneLive',
            'projectList', 'user',
        ));
    }

    private function adminDashboard()
    {
        $allPpsmb = Ppsmb::with('user')->latest()->get();

        $statusAktif = [
            'Verifikasi CMD/DINOV', 'Edit by User', 'Revisi User',
            'Antrian Analisa BA IT', 'Analisa BA IT',
            'Antrian Development', 'Proses Development', 'UAT',
        ];

        // Summary Cards
        $totalProject   = $allPpsmb->count();
        $totalAktif     = $allPpsmb->whereIn('status', $statusAktif)->count();
        $totalSelesai   = $allPpsmb->where('status', 'Done (Live)')->count();
        $totalRejected  = $allPpsmb->where('status', 'Rejected')->count();

        // Chart — distribusi per status
        $perStatus = $allPpsmb->groupBy('status')->map->count()->sortDesc();

        // Workload per Tim
        $perTim = $allPpsmb->whereIn('status', $statusAktif)
            ->groupBy('tim')->map->count()->sortDesc();

        // Workload per BA
        $perBa = $allPpsmb->whereIn('status', $statusAktif)
            ->whereNotNull('pic_ba')
            ->groupBy('pic_ba')->map->count()->sortDesc();

        // Workload per Developer
        $perDeveloper = $allPpsmb->whereIn('status', $statusAktif)
            ->whereNotNull('developer')
            ->where('developer', '!=', '')
            ->groupBy('developer')->map->count()->sortDesc();

        // Project Telat
        $projectTelat = $allPpsmb->whereIn('status', $statusAktif)
            ->filter(fn($p) => $p->estimasi_selesai && Carbon::parse($p->estimasi_selesai)->isPast())
            ->sortBy('estimasi_selesai')
            ->values();

        // Aktivitas Terbaru
        $aktivitasTerbaru = PpsmbHistory::with('ppsmb')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.admin', compact(
            'totalProject', 'totalAktif', 'totalSelesai', 'totalRejected',
            'perStatus', 'perTim', 'perBa', 'perDeveloper',
            'projectTelat', 'aktivitasTerbaru',
        ));
    }
}