<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

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
        $ppsmbs = Ppsmb::where('dept_id', $user->dept_id)
            ->with(['user', 'histories'])
            ->latest()
            ->get();

        $total      = $ppsmbs->count();
        $totalAktif = $ppsmbs->whereNotIn('status', ['Done (Live)', 'Rejected'])->count();
        $revisi     = $ppsmbs->where('status', 'Revisi User')->count();
        $uat        = $ppsmbs->where('status', 'UAT')->count();

        $uatAging     = $this->buildUatAging($ppsmbs->where('status', 'UAT'));
        $agingWarning = collect($uatAging)->contains(fn($ua) => $ua['hari'] >= 10);

        $showWarning     = $totalAktif >= 3 && $agingWarning;
        $blockingProject = $showWarning
            ? collect($uatAging)->firstWhere(fn($ua) => $ua['hari'] >= 10)['ppsmb']->nama_project
            : null;

        $autoRejected    = $this->getAutoRejected($user->dept_id);
        $revisiCountdown = $this->buildRevisiCountdown($ppsmbs);
        $revisiCountdown      = $this->buildRevisiCountdown($ppsmbs->where('user_id', $user->id));
        $revisiCountdownOther = $this->buildRevisiCountdown(
            $ppsmbs->where('status', 'Revisi User')->where('user_id', '!=', $user->id)
        );

        $summaryCards = [
            ['label' => 'Total Project',  'val' => $total,      'color' => '#686464', 'filter' => 'all',         'sub' => 'Semua project departemen'],
            ['label' => 'Project Aktif',  'val' => $totalAktif, 'color' => '#0d6efd', 'filter' => 'aktif',       'sub' => 'Project yang sedang berjalan'],
            ['label' => 'Revisi User',    'val' => $revisi,     'color' => config('status.colors.Revisi User'),  'filter' => 'Revisi User', 'sub' => 'Perlu tindakan segera'],
            ['label' => 'UAT',            'val' => $uat,        'color' => config('status.colors.UAT'),          'filter' => 'UAT',         'sub' => 'Project dalam pengujian'],
        ];

        return view('dashboard.user', compact(
            'ppsmbs', 'total', 'totalAktif', 'revisi', 'uat',
            'uatAging', 'showWarning', 'blockingProject',
            'autoRejected', 'revisiCountdown', 'revisiCountdownOther', 'summaryCards',
        ));
    }

    private function buildUatAging($uatPpsmbs): array
    {
        return $uatPpsmbs->map(function ($p) {
            $masukUat = $p->histories
                ->where('status', 'UAT')
                ->sortByDesc('created_at')
                ->first()                
                ?->created_at;

            $hari = $masukUat ? (int) Carbon::parse($masukUat)->floatDiffInDays(now()) : 0;

            $barCls   = $hari >= 10 ? 'bg-danger'         : ($hari >= 7 ? 'bg-warning'            : 'bg-success');
            $txtCls   = $hari >= 10 ? 'text-danger'       : ($hari >= 7 ? 'text-warning'          : 'text-success');
            $badgeCls = $hari >= 10 ? 'bg-danger'         : ($hari >= 7 ? 'bg-warning text-dark'  : 'bg-success');
            $badgeTxt = $hari >= 10 ? 'Lewat batas'       : ($hari >= 7 ? 'Hampir batas'          : 'Aman');

            return [
                'ppsmb'     => $p,
                'hari'      => $hari,
                'masuk_uat' => $masukUat ? Carbon::parse($masukUat)->translatedFormat('d F Y') : '-',
                'pct'       => min(100, round(($hari / 10) * 100)),
                'barCls'    => $barCls,
                'txtCls'    => $txtCls,
                'badgeCls'  => $badgeCls,
                'badgeTxt'  => $badgeTxt,
            ];
        })->values()->toArray();
    }

    private function buildRevisiCountdown($ppsmbs): SupportCollection
    {
        return $ppsmbs
            ->where('status', 'Revisi User')
            ->filter(fn($p) => $p->revisi_at !== null)
            ->map(function ($p) {
                $hariKe   = (int) Carbon::parse($p->revisi_at)->diffInDays(now());
                $sisaHari = max(0, 30 - $hariKe);

                $barCls = $sisaHari <= 5  ? 'bg-danger'    : ($sisaHari <= 10 ? 'bg-warning'   : 'bg-success');
                $txtCls = $sisaHari <= 5  ? 'text-danger'  : ($sisaHari <= 10 ? 'text-warning' : 'text-success');

                return [
                    'ppsmb'     => $p,
                    'hari_ke'   => $hariKe,
                    'sisa_hari' => $sisaHari,
                    'pct'       => min(100, round(($hariKe / 30) * 100)),
                    'barCls'    => $barCls,
                    'txtCls'    => $txtCls,
                ];
            })
            ->values();
    }

    private function getAutoRejected(int $deptId): SupportCollection
    {
        return Ppsmb::where('dept_id', $deptId)
            ->where('status', 'Rejected')
            ->whereHas('histories', fn($q) => $q->whereNull('pemeriksa')->where('status', 'Rejected'))
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(fn($ar) => [
                'nama_project' => $ar->nama_project,
                'tanggal'      => $ar->updated_at
                    ? Carbon::parse($ar->updated_at)->translatedFormat('d F Y')
                    : '-',
            ]);
    }

    private function verifikatorDashboard($user)
    {
        $allPpsmbs = Ppsmb::with(['user', 'department', 'histories'])
            ->latest()
            ->get();

        $activePpsmbs = $allPpsmbs
            ->whereNotIn('status', ['Done (Live)', 'Rejected'])
            ->values();
        
        $menungguVerifikasi = $activePpsmbs->whereIn('status', [
            'Verifikasi CMD/Dinov',
            'Edit by User - Verifikasi CMD/Dinov',
        ])->count();

        $revisi  = $activePpsmbs->where('status', 'Revisi User')->count();
        $totalAktif = $activePpsmbs->count();
        
        $uatAging = $activePpsmbs->where('status', 'UAT')
            ->filter(function ($p) {
                $masukUat = $p->histories
                    ->where('status', 'UAT')
                    ->sortByDesc('created_at')
                    ->first()
                    ?->created_at;
                return $masukUat && (int) Carbon::parse($masukUat)->floatDiffInDays(now()) >= 10;
        })->count();

        // Matrix dept x status
        $statusList = array_keys(config('status.colors'));
        $depts      = $allPpsmbs->pluck('department.code')->unique()->sort()->values();

        $matrix = [];
        foreach ($depts as $dept) {
            foreach ($statusList as $status) {
                $matrix[$dept][$status] = $allPpsmbs
                    ->filter(fn($p) => $p->department->code === $dept && $p->status === $status)
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

        $antrian = $activePpsmbs
            ->whereIn('status', ['Verifikasi CMD/Dinov', 'Edit by User - Verifikasi CMD/Dinov'])
            ->sortBy('created_at')
            ->values();

        $allProjectsData = $allPpsmbs->map(fn($p) => [
            'id'             => $p->id,
            'nama_project'   => $p->nama_project,
            'no_ppsmb'       => $p->no_ppsmb ?? '—',
            'user'           => $p->user->name,
            'dept'           => $p->department->code,
            'status'         => $p->status,
            'model_aplikasi' => $p->model_aplikasi,
            'created_at'     => $p->created_at,
            'aging_hari'     => $p->aging_hari,
        ])->values();

        $antrianData = $antrian->map(fn($p) => [
            'id'             => $p->id,
            'nama_project'   => $p->nama_project,
            'no_ppsmb'       => $p->no_ppsmb ?? '—',
            'user'           => $p->user->name,
            'dept'           => $p->department->code,
            'status'         => $p->status,
            'model_aplikasi' => $p->model_aplikasi,
            'created_at'     => $p->created_at,
        ])->values();

        // Area chart — estimasi selesai per bulan
        $projectAktifDenganEstimasi = $activePpsmbs
            ->whereNotNull('estimasi_selesai');

        $chartData = [];
        if ($projectAktifDenganEstimasi->count() > 0) {
            $start   = Carbon::parse($projectAktifDenganEstimasi->min('estimasi_selesai'))->startOfMonth();
            $end     = Carbon::parse($projectAktifDenganEstimasi->max('estimasi_selesai'))->endOfMonth();
            $current = $start->copy();
            
            while ($current->lte($end)) {
                $bulan = $current->format('Y-m');
                $chartData[] = [
                    'bulan'   => $current->translatedFormat('M Y'),
                    'jumlah'  => $projectAktifDenganEstimasi
                        ->filter(fn($p) => Carbon::parse($p->estimasi_selesai)->format('Y-m') === $bulan)
                        ->count(),
                ];
                $current->addMonth();
            }
        }

        $modelMap = [
            'Semua'          => null,
            'Eksternal'      => 'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot',
            'Internal MD'    => 'Aplikasi Internal MD',
            'Improvement IT' => 'Improvement IT System',
        ];

        $summaryDefs = [
            ['label' => 'Menunggu Verifikasi', 'key' => 'menunggu',   'color' => config('status.colors.Verifikasi CMD/Dinov'), 'sub' => 'Perlu ditindaklanjuti'],
            ['label' => 'Revisi User',         'key' => 'revisi',     'color' => config('status.colors.Revisi User'),          'sub' => 'Menunggu revisi user'],
            ['label' => 'UAT Aging >10 Hari',  'key' => 'uatAging',   'color' => config('status.colors.UAT'),                  'sub' => 'Melewati batas UAT'],
            ['label' => 'Project Aktif',       'key' => 'totalAktif', 'color' => '#0d6efd',                                  'sub' => 'Semua project aktif'],
        ];

        $deptModels = match($user->department->code) {
            'CMD' => ['Aplikasi Internal MD', 'Improvement IT System'],
            'DIN' => ['Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot'],
            default => [],
        };

        $baseUrl = match($user->department->code) {
            'CMD' => url('/ppsmbbycmd'),
            'DIN' => url('/ppsmbbydinov'),
            default => url('/ppsmbbyuser'),
        };

        return view('dashboard.verifikator', compact(
            'allProjectsData', 'antrianData',
            'menungguVerifikasi', 'revisi', 'uatAging', 'totalAktif',
            'matrix', 'statusList', 'depts',
            'chartData', 'modelMap', 'summaryDefs', 'baseUrl', 'deptModels',
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

        $totalAktif     = $ppsmbs->count();
        $analisaBa      = $ppsmbs->where('status', 'Analisa BA IT')->count();
        $antrianAnalisa = $ppsmbs->where('status', 'Antrian Analisa BA IT')->count();
        $antrianDev     = $ppsmbs->where('status', 'Antrian Development')->count();
        $prosesDev      = $ppsmbs->where('status', 'Proses Development')->count();
        $uat            = $ppsmbs->where('status', 'UAT')->count();

        $projectTelat = $ppsmbs->filter(fn($p) =>
            $p->estimasi_selesai && Carbon::parse($p->estimasi_selesai)->isPast()
        )->count();

        $itDept = Department::where('code', 'IT')->first();

        $bebanBa = User::where('dept_id', $itDept->id)
            ->where('role', 'business_analyst')
            ->when($user->tim, fn($q) => $q->where('tim', $user->tim))
            ->get()
            ->map(fn($ba) => [
                'nama'      => $ba->name,
                'analisa'   => $ppsmbs->where('pic_ba', $ba->id)->where('status', 'Analisa BA IT')->count(),
                'total'     => $ppsmbs->where('pic_ba', $ba->id)->count(),
                'secondary' => $ppsmbs->where('secondary_ba', $ba->id)->count(),
            ]);

        $bebanDev = User::where('dept_id', $itDept->id)
            ->where('role', 'developer')
            ->when($user->tim, fn($q) => $q->where('tim', $user->tim))
            ->get()
            ->map(fn($dev) => [
                'nama'     => $dev->name,
                'aktif'    => $ppsmbs->where('developer', $dev->id)->whereIn('status', ['Proses Development', 'UAT'])->count(),
                'total'    => $ppsmbs->where('developer', $dev->id)->count(),
                'progress' => $ppsmbs->where('developer', $dev->id)->avg('progress') ?? 0,
            ]);

        $projectList = $ppsmbs->map(fn($p) => [
            'ppsmb'     => $p,
            'telat'     => $p->estimasi_selesai && Carbon::parse($p->estimasi_selesai)->isPast(),
            'sisa_hari' => $p->estimasi_selesai
                ? (int) Carbon::now()->diffInDays(Carbon::parse($p->estimasi_selesai), false)
                : null,
        ]);

        $projectJson = $projectList->map(fn($pl) => [
            'id'               => $pl['ppsmb']->id,
            'no_ppsmb'         => $pl['ppsmb']->no_ppsmb ?? '—',
            'nama_project'     => $pl['ppsmb']->nama_project,
            'status'           => $pl['ppsmb']->status,
            'pic_ba'           => $pl['ppsmb']->picBa->name ?? '—',
            'secondary_ba'     => $pl['ppsmb']->secondaryBa->name ?? '—',
            'developer'        => $pl['ppsmb']->developerUser->name ?? '—',
            'estimasi_selesai' => $pl['ppsmb']->estimasi_selesai
                ? Carbon::parse($pl['ppsmb']->estimasi_selesai)->translatedFormat('d M Y')
                : '—',
            'sisa_hari'        => $pl['sisa_hari'],
            'telat'            => $pl['telat'],
            'progress'         => $pl['ppsmb']->progress,
            'color'            => config('status.colors')[$pl['ppsmb']->status] ?? '#6c757d',
        ])->values();

        $chartSelesai = Ppsmb::where('status', 'Done (Live)')
            ->whereNotNull('updated_at')
            ->when($user->tim === 'internal', fn($q) => $q->whereIn('model_aplikasi', [
                'Aplikasi Internal MD',
                'Improvement IT System',
            ]))
            ->when($user->tim === 'eksternal', fn($q) => $q->where('model_aplikasi',
                'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot'
            ))
            ->get()
            ->groupBy(fn($p) => Carbon::parse($p->updated_at)->format('Y-m'))
            ->map(fn($g, $bulan) => [
                'bulan'  => Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('M Y'),
                'key'    => $bulan,
                'jumlah' => $g->count(),
            ])
            ->sortKeys()
            ->values();

        $chartRejected = Ppsmb::where('status', 'Rejected')
            ->whereNotNull('updated_at')
            ->when($user->tim === 'internal', fn($q) => $q->whereIn('model_aplikasi', [
                'Aplikasi Internal MD',
                'Improvement IT System',
            ]))
            ->when($user->tim === 'eksternal', fn($q) => $q->where('model_aplikasi',
                'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot'
            ))
            ->get()
            ->groupBy(fn($p) => Carbon::parse($p->updated_at)->format('Y-m'))
            ->map(fn($g, $bulan) => [
                'bulan'  => Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('M Y'),
                'key'    => $bulan,
                'jumlah' => $g->count(),
            ])
            ->sortKeys()
            ->values();

        $summaryCards = [
            ['label' => 'Project Aktif',         'val' => $totalAktif,     'color' => '#4b4d50',                                              'key' => 'all',             'sub' => 'Project sedang berjalan'],
            ['label' => 'Antrian Analisa BA IT',  'val' => $antrianAnalisa, 'color' => config('status.colors.Antrian Analisa BA IT'),          'key' => 'antrian_analisa', 'sub' => 'Menunggu BA analisa'],
            ['label' => 'Analisa BA IT',          'val' => $analisaBa,      'color' => config('status.colors.Analisa BA IT'),                  'key' => 'analisa_ba',      'sub' => 'Sedang dianalisa BA'],
            ['label' => 'Antrian Development',    'val' => $antrianDev,     'color' => config('status.colors.Antrian Development'),            'key' => 'antrian_dev',     'sub' => 'Menunggu developer'],
            ['label' => 'Proses Development',     'val' => $prosesDev,      'color' => config('status.colors.Proses Development'),             'key' => 'proses_dev',      'sub' => 'Sedang dikerjakan'],
            ['label' => 'UAT',                    'val' => $uat,            'color' => config('status.colors.UAT'),                            'key' => 'uat',             'sub' => 'Sedang pengujian'],
        ];

        return view('dashboard.it.project_leader', compact(
            'ppsmbs', 'totalAktif', 'antrianAnalisa', 'analisaBa', 'antrianDev',
            'prosesDev', 'uat', 'projectTelat',
            'bebanBa', 'bebanDev', 'projectList',
            'projectJson', 'chartSelesai', 'chartRejected',
            'summaryCards', 'user',
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
                $q->where('pic_ba', $user->id)
                ->orWhere('secondary_ba', $user->id);
            })
            ->latest()
            ->get();

        $totalAssigned = $ppsmbs->count();
        $perluAnalisa  = $ppsmbs->where('status', 'Analisa BA IT')->count();
        $antrian       = $ppsmbs->where('status', 'Antrian Development')->count();
        $sudahLanjut   = $ppsmbs->whereIn('status', ['Proses Development', 'UAT'])->count();
        $prosesDev     = $ppsmbs->where('status', 'Proses Development')->count();
        $uat           = $ppsmbs->where('status', 'UAT')->count();

        $projectList = $ppsmbs->map(fn($p) => [
            'ppsmb'        => $p,
            'is_primary'   => $p->pic_ba === $user->id,
            'is_secondary' => $p->secondary_ba === $user->id,
            'sisa_hari'    => $p->estimasi_selesai
                ? (int) Carbon::now()->diffInDays(Carbon::parse($p->estimasi_selesai), false)
                : null,
            'telat'        => $p->estimasi_selesai && Carbon::parse($p->estimasi_selesai)->isPast(),
        ]);

        $projectJson = $projectList->map(fn($pl) => [
            'id'               => $pl['ppsmb']->id,
            'no_ppsmb'         => $pl['ppsmb']->no_ppsmb ?? '—',
            'nama_project'     => $pl['ppsmb']->nama_project,
            'status'           => $pl['ppsmb']->status,
            'is_primary'       => $pl['is_primary'],
            'is_secondary'     => $pl['is_secondary'],
            'estimasi_selesai' => $pl['ppsmb']->estimasi_selesai
                ? Carbon::parse($pl['ppsmb']->estimasi_selesai)->translatedFormat('d M Y')
                : '—',
            'sisa_hari'        => $pl['sisa_hari'],
            'telat'            => $pl['telat'],
            'progress'         => $pl['ppsmb']->progress,
            'color'            => config('status.colors')[$pl['ppsmb']->status] ?? '#6c757d',
        ])->values();

        $summaryCards = [
            ['label' => 'Total Assigned',      'val' => $totalAssigned, 'color' => '#4b4d50',                                           'key' => 'all',     'sub' => 'Project yang di-assign ke anda'],
            ['label' => 'Analisa BA IT',       'val' => $perluAnalisa,  'color' => config('status.colors.Analisa BA IT'),               'key' => 'analisa', 'sub' => 'Perlu tindak lanjut Business Analyst'],
            ['label' => 'Antrian Development', 'val' => $antrian,       'color' => config('status.colors.Antrian Development'),         'key' => 'antrian', 'sub' => 'Menunggu development'],
            ['label' => 'Proses Development',  'val' => $prosesDev,     'color' => config('status.colors.Proses Development'),          'key' => 'proses',  'sub' => 'Sedang dikerjakan'],
            ['label' => 'UAT',                 'val' => $uat,           'color' => config('status.colors.UAT'),                         'key' => 'uat',     'sub' => 'Sedang pengujian'],
        ];

        return view('dashboard.it.business_analyst', compact(
            'ppsmbs', 'totalAssigned', 'perluAnalisa', 'antrian', 'sudahLanjut',
            'prosesDev', 'uat', 'projectList', 'projectJson', 'summaryCards', 'user',
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
            ->where('developer', $user->id)
            ->latest()
            ->get();

        $totalAssigned = $ppsmbs->count();
        $prosesDev     = $ppsmbs->where('status', 'Proses Development')->count();
        $uat           = $ppsmbs->where('status', 'UAT')->count();
        $doneLive      = $ppsmbs->where('status', 'Done (Live)')->count();

        $projectList = $ppsmbs->map(fn($p) => [
            'ppsmb'     => $p,
            'sisa_hari' => $p->estimasi_selesai
                ? (int) Carbon::now()->diffInDays(Carbon::parse($p->estimasi_selesai), false)
                : null,
            'telat'     => $p->estimasi_selesai && Carbon::parse($p->estimasi_selesai)->isPast(),
        ]);

        $projectJson = $projectList->map(fn($pl) => [
            'id'               => $pl['ppsmb']->id,
            'no_ppsmb'         => $pl['ppsmb']->no_ppsmb ?? '—',
            'nama_project'     => $pl['ppsmb']->nama_project,
            'status'           => $pl['ppsmb']->status,
            'estimasi_selesai' => $pl['ppsmb']->estimasi_selesai
                ? Carbon::parse($pl['ppsmb']->estimasi_selesai)->translatedFormat('d M Y')
                : '—',
            'sisa_hari'        => $pl['sisa_hari'],
            'telat'            => $pl['telat'],
            'progress'         => $pl['ppsmb']->progress,
            'color'            => config('status.colors')[$pl['ppsmb']->status] ?? '#6c757d',
        ])->values();

        $summaryCards = [
            ['label' => 'Total Assigned',    'val' => $totalAssigned, 'color' => '#4b4d50',                                        'key' => 'all',    'sub' => 'Project yang di-assign ke anda'],
            ['label' => 'Proses Development', 'val' => $prosesDev,    'color' => config('status.colors.Proses Development'),       'key' => 'proses', 'sub' => 'Sedang dikerjakan'],
            ['label' => 'UAT',               'val' => $uat,           'color' => config('status.colors.UAT'),                      'key' => 'uat',    'sub' => 'Sedang pengujian'],
            ['label' => 'Done (Live)',        'val' => $doneLive,      'color' => config('status.colors.Done (Live)'),              'key' => 'done',   'sub' => 'Project selesai'],
        ];

        return view('dashboard.it.developer', compact(
            'ppsmbs', 'totalAssigned', 'prosesDev', 'uat', 'doneLive',
            'projectList', 'projectJson', 'summaryCards', 'user',
        ));
    }

    private function adminDashboard()
    {
        $allPpsmb = Ppsmb::with(['user', 'picBa', 'secondaryBa', 'developerUser'])->latest()->get();

        $statusAktif = [
            'Verifikasi CMD/Dinov',
            'Edit by User - Verifikasi CMD/Dinov',
            'Revisi User',
            'Antrian Analisa BA IT',
            'Analisa BA IT',
            'Antrian Development',
            'Proses Development',
            'UAT',
        ];

        $totalProject  = $allPpsmb->count();
        $totalAktif    = $allPpsmb->whereIn('status', $statusAktif)->count();
        $totalSelesai  = $allPpsmb->where('status', 'Done (Live)')->count();
        $totalRejected = $allPpsmb->where('status', 'Rejected')->count();

        $perStatus = $allPpsmb->groupBy('status')->map->count()->sortDesc();

        $perTim = $allPpsmb->whereIn('status', $statusAktif)
            ->groupBy(fn($p) => match($p->model_aplikasi) {
                'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot' => 'Eksternal',
                default => 'Internal',
            })->map->count()->sortDesc();

        $perBa = $allPpsmb->whereIn('status', $statusAktif)
            ->whereNotNull('pic_ba')
            ->groupBy(fn($p) => $p->picBa->name ?? '—')
            ->map->count()->sortDesc();

        $perDeveloper = $allPpsmb->whereIn('status', $statusAktif)
            ->whereNotNull('developer')
            ->groupBy(fn($p) => $p->developerUser->name ?? '—')
            ->map->count()->sortDesc();

        $projectTelat = $allPpsmb->whereIn('status', $statusAktif)
            ->filter(fn($p) => $p->estimasi_selesai && Carbon::parse($p->estimasi_selesai)->isPast())
            ->sortBy('estimasi_selesai')
            ->map(fn($p) => [
                'ppsmb'            => $p,
                'telat_hari'       => abs((int) Carbon::now()->diffInDays(Carbon::parse($p->estimasi_selesai), false)),
                'estimasi_formatted' => Carbon::parse($p->estimasi_selesai)->translatedFormat('d M Y'),
            ])
            ->values();

        $aktivitasTerbaru = PpsmbHistory::with('ppsmb')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($log) => [
                'log'              => $log,
                'waktu_formatted'  => Carbon::parse($log->created_at)->translatedFormat('d M Y, H:i'),
            ]);

        $trendLabels  = collect();
        $trendMasuk   = collect();
        $trendSelesai = collect();

        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);
            $trendLabels->push($bulan->translatedFormat('M Y'));
            $trendMasuk->push(
                Ppsmb::whereYear('created_at', $bulan->year)
                    ->whereMonth('created_at', $bulan->month)
                    ->count()
            );
            $trendSelesai->push(
                Ppsmb::where('status', 'Done (Live)')
                    ->whereYear('updated_at', $bulan->year)
                    ->whereMonth('updated_at', $bulan->month)
                    ->count()
            );
        }

        $perBaDetail = $allPpsmb->whereIn('status', $statusAktif)
            ->whereNotNull('pic_ba')
            ->groupBy(fn($p) => $p->picBa->name ?? '—')
            ->map(fn($projects) => [
                'count'    => $projects->count(),
                'projects' => $projects->map(fn($p) => [
                    'id'           => $p->id,
                    'nama_project' => $p->nama_project,
                    'status'       => $p->status,
                    'color'        => config('status.colors')[$p->status] ?? '#6c757d',
                ])->values(),
            ])
            ->sortByDesc('count');

        $perDeveloperDetail = $allPpsmb->whereIn('status', $statusAktif)
            ->whereNotNull('developer')
            ->groupBy(fn($p) => $p->developerUser->name ?? '—')
            ->map(fn($projects) => [
                'count'    => $projects->count(),
                'projects' => $projects->map(fn($p) => [
                    'id'           => $p->id,
                    'nama_project' => $p->nama_project,
                    'status'       => $p->status,
                    'color'        => config('status.colors')[$p->status] ?? '#6c757d',
                ])->values(),
            ])
            ->sortByDesc('count');

        $allPpsmbJson = $allPpsmb->map(fn($p) => [
            'id'               => $p->id,
            'no_ppsmb'         => $p->no_ppsmb ?? '—',
            'nama_project'     => $p->nama_project,
            'tim'              => $p->tim ?? '—',
            'status'           => $p->status,
            'estimasi_selesai' => $p->estimasi_selesai
                ? Carbon::parse($p->estimasi_selesai)->translatedFormat('d M Y')
                : '—',
            'progress'         => $p->progress,
            'color'            => config('status.colors')[$p->status] ?? '#6c757d',
            'pic_ba'           => $p->picBa->name ?? '-',
            'developer'        => $p->developerUser->name ?? '-',
        ])->values();

        $statusLabels = $perStatus->keys();
        $statusValues = $perStatus->values();
        $statusColors = $statusLabels->map(fn($s) => config('status.colors')[$s] ?? '#6c757d');

        $summaryCards = [
            ['label' => 'Total Project', 'val' => $totalProject,  'color' => '#686464', 'key' => 'all',      'sub' => 'Semua project masuk'],
            ['label' => 'Project Aktif', 'val' => $totalAktif,    'color' => '#0d6efd', 'key' => 'aktif',    'sub' => 'Sedang berjalan'],
            ['label' => 'Done (Live)',   'val' => $totalSelesai,  'color' => config('status.colors.Done (Live)'),  'key' => 'done',     'sub' => 'Project selesai'],
            ['label' => 'Rejected',      'val' => $totalRejected, 'color' => config('status.colors.Rejected'),     'key' => 'rejected', 'sub' => 'Project ditolak'],
        ];

        return view('dashboard.admin', compact(
            'allPpsmb', 'totalProject', 'totalAktif', 'totalSelesai', 'totalRejected',
            'perStatus', 'perTim', 'perBa', 'perDeveloper',
            'perBaDetail', 'perDeveloperDetail',
            'trendLabels', 'trendMasuk', 'trendSelesai',
            'projectTelat', 'aktivitasTerbaru',
            'allPpsmbJson', 'statusLabels', 'statusValues', 'statusColors',
            'summaryCards', 'statusAktif',
        ));
    }
}