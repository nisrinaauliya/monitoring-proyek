@extends('layouts.app')
@php use Carbon\Carbon; @endphp

@section('title', 'Dashboard - Sistem Helpdesk')
@section('page_title', 'Dashboard')

@section('content')

{{-- Summary Cards --}}
@php
$summaryCards = [
    ['label'=>'Project Aktif',        'val'=>$totalAktif,     'color'=>'#4b4d50', 'key'=>'all',             'sub'=>'Project sedang berjalan'],
    ['label'=>'Antrian Analisa BA IT',    'val'=>$antrianAnalisa, 'color'=>'#9cc0f1', 'key'=>'antrian_analisa', 'sub'=>'Menunggu BA analisa'],
    ['label'=>'Analisa BA IT',         'val'=>$analisaBa,      'color'=>'#4691ec', 'key'=>'analisa_ba',      'sub'=>'Sedang dianalisa BA'],
    ['label'=>'Antrian Development',        'val'=>$antrianDev,     'color'=>'#0b57b3', 'key'=>'antrian_dev',     'sub'=>'Menunggu developer'],
    ['label'=>'Proses Development', 'val'=>$prosesDev,      'color'=>'#052e70', 'key'=>'proses_dev',      'sub'=>'Sedang dikerjakan'],
    ['label'=>'UAT',                'val'=>$uat,            'color'=>'#F97316', 'key'=>'uat',             'sub'=>'Sedang pengujian'],
];
@endphp
<div class="row g-3 mb-4">
    @foreach($summaryCards as $sc)
    <div class="col-6 col-sm-4 col-md-2">
        <div class="card border-0 shadow-sm h-100 pl-summary-card" role="button"
             data-key="{{ $sc['key'] }}"
             style="cursor:pointer;transition:transform .15s,box-shadow .15s;">
            <div class="card-body p-3">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="fw-bold" style="font-size:30px;color:{{ $sc['color'] }};line-height:1;">
                        {{ $sc['val'] }}
                    </div>
                    <i class="bi bi-arrow-right text-muted" style="font-size:14px;"></i>
                </div>
                <div class="fw-semibold" style="font-size:16px;">{{ $sc['label'] }}</div>
                <div class="text-muted mt-1" style="font-size:14px;">{{ $sc['sub'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- List Project dari Summary Card --}}
<div class="card border-0 shadow-sm mb-4" id="summaryListCard" style="display:none;">
    <div class="card-header bg-white border-0 px-3 pt-3 pb-2">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="fw-semibold" id="summaryListTitle" style="font-size:16px;"></div>
                <div class="text-muted" id="summaryListSubtitle" style="font-size:14px;"></div>
            </div>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" id="summaryListClose"
                    style="font-size:14px;">Tutup</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:14px;">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th class="px-3 py-3 border-0 text-muted fw-semibold">No PPSMB</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Nama Project</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Status</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Business Analyst</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Developer</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Estimasi Selesai</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Sisa Hari</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Progress</th>
                        <th class="py-3 border-0"></th>
                    </tr>
                </thead>
                <tbody id="summaryListBody"></tbody>
            </table>
        </div>
        <div class="d-flex align-items-center justify-content-between px-3 py-3 border-top">
            <div class="text-muted" id="summaryListInfo" style="font-size:14px;"></div>
            <div class="d-flex gap-1" id="summaryListPagination"></div>
        </div>
    </div>
</div>

{{-- Beban Kerja --}}
<div class="row g-3 mb-4">

    {{-- Beban BA --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="fw-semibold mb-1" style="font-size:16px;">Workload Business Analyst</div>
                <div class="text-muted mb-3" style="font-size:14px;">Klik business analyst untuk lihat project</div>
                @if($bebanBa->count() > 0)
                    @foreach($bebanBa as $ba)
                    <div class="mb-3 ba-row" role="button" data-nama="{{ $ba['nama'] }}"
                         style="cursor:pointer;padding:6px;border-radius:8px;transition:background .15s;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size:16px;font-weight:500;">{{ $ba['nama'] }}</span>
                            <div class="d-flex gap-2 align-items-center">
                                @if($ba['secondary'] > 0)
                                <span class="badge bg-secondary" style="font-size:14px;">
                                    +{{ $ba['secondary'] }} secondary
                                </span>
                                @endif
                                <span class="fw-bold" style="font-size:18px;color:#0d6efd;">{{ $ba['total'] }}</span>
                                <span class="text-muted" style="font-size:14px;">project</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress rounded-pill flex-grow-1" style="height:6px;background:#e9ecef;">
                                @php $maxBa = $bebanBa->max('total') ?: 1; @endphp
                                <div class="progress-bar rounded-pill"
                                     style="width:{{ round(($ba['total'] / $maxBa) * 100) }}%;background:#4691ec;"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4 text-muted" style="font-size:16px;">
                        <i class="bi bi-person-x mb-2 d-block" style="font-size:32px;"></i>
                        Tidak ada business analyst di tim ini
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Beban Developer --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="fw-semibold mb-1" style="font-size:16px;">Workload Developer</div>
                <div class="text-muted mb-3" style="font-size:14px;">Klik developer untuk lihat project</div>
                @if($bebanDev->count() > 0)
                    @foreach($bebanDev as $dev)
                    <div class="mb-3 dev-row" role="button" data-nama="{{ $dev['nama'] }}"
                         style="cursor:pointer;padding:6px;border-radius:8px;transition:background .15s;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size:16px;font-weight:500;">{{ $dev['nama'] }}</span>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="fw-bold" style="font-size:18px;color:#052e70;">{{ $dev['total'] }}</span>
                                <span class="text-muted" style="font-size:14px;">project</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress rounded-pill flex-grow-1" style="height:6px;background:#e9ecef;">
                                @php $maxDev = $bebanDev->max('total') ?: 1; @endphp
                                <div class="progress-bar rounded-pill"
                                     style="width:{{ round(($dev['total'] / $maxDev) * 100) }}%;background:#052e70;"></div>
                            </div>
                            <span class="text-muted" style="font-size:14px;white-space:nowrap;">
                                avg {{ number_format($dev['progress'], 0) }}%
                            </span>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4 text-muted" style="font-size:16px;">
                        <i class="bi bi-person-x mb-2 d-block" style="font-size:32px;"></i>
                        Tidak ada developer di tim ini
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Detail Beban (muncul saat klik BA/Dev) --}}
<div class="card border-0 shadow-sm mb-4" id="bebanDetailCard" style="display:none;">
    <div class="card-header bg-white border-0 px-3 pt-3 pb-2">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="fw-semibold" id="bebanDetailTitle" style="font-size:16px;"></div>
                <div class="text-muted" id="bebanDetailSubtitle" style="font-size:14px;"></div>
            </div>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" id="bebanDetailClose"
                    style="font-size:14px;">Tutup</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:14px;">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th class="px-3 py-3 border-0 text-muted fw-semibold">No PPSMB</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Nama Project</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Status</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Progress</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Estimasi Selesai</th>
                        <th class="py-3 border-0"></th>
                    </tr>
                </thead>
                <tbody id="bebanDetailBody"></tbody>
            </table>
        </div>
        <div class="d-flex align-items-center justify-content-between px-3 py-3 border-top">
            <div class="text-muted" id="bebanDetailInfo" style="font-size:14px;"></div>
            <div class="d-flex gap-1" id="bebanDetailPagination"></div>
        </div>
    </div>
</div>

{{-- Chart Project Selesai & Rejected per Bulan --}}
<div class="row g-3 mb-4">
    {{-- Chart Selesai --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <div class="fw-semibold" style="font-size:16px;">Project Selesai per Bulan</div>
                        <div class="text-muted" style="font-size:14px;">Riwayat project Done (Live)</div>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-dark chart-range-btn" data-chart="selesai" data-range="6" style="font-size:14px;">6 Bln</button>
                        <button class="btn btn-sm btn-outline-secondary chart-range-btn" data-chart="selesai" data-range="12" style="font-size:14px;">12 Bln</button>
                        <button class="btn btn-sm btn-outline-secondary chart-range-btn" data-chart="selesai" data-range="all" style="font-size:14px;">Semua</button>
                    </div>
                </div>
                <canvas id="chartSelesai" style="max-height:260px;"></canvas>
            </div>
        </div>
    </div>
    {{-- Chart Rejected --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <div class="fw-semibold" style="font-size:16px;">Project Rejected per Bulan</div>
                        <div class="text-muted" style="font-size:14px;">Riwayat project yang ditolak</div>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-dark chart-range-btn" data-chart="rejected" data-range="6" style="font-size:14px;">6 Bln</button>
                        <button class="btn btn-sm btn-outline-secondary chart-range-btn" data-chart="rejected" data-range="12" style="font-size:14px;">12 Bln</button>
                        <button class="btn btn-sm btn-outline-secondary chart-range-btn" data-chart="rejected" data-range="all" style="font-size:14px;">Semua</button>
                    </div>
                </div>
                <canvas id="chartRejected" style="max-height:260px;"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {

    // ── CHART SELESAI & REJECTED PER BULAN ───────────────
    @php
    $chartSelesai = \App\Models\Ppsmb::where('status', 'Done (Live)')
        ->whereNotNull('updated_at')
        ->when($user->tim === 'internal', fn($q) => $q->whereIn('model_aplikasi', ['Aplikasi Internal MD', 'Improvement IT System']))
        ->when($user->tim === 'eksternal', fn($q) => $q->where('model_aplikasi', 'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot'))
        ->get()
        ->groupBy(fn($p) => \Carbon\Carbon::parse($p->updated_at)->format('Y-m'))
        ->map(fn($g, $bulan) => [
            'bulan'  => \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('M Y'),
            'key'    => $bulan,
            'jumlah' => $g->count(),
        ])
        ->sortKeys()
        ->values();

    $chartRejected = \App\Models\Ppsmb::where('status', 'Rejected')
        ->whereNotNull('updated_at')
        ->when($user->tim === 'internal', fn($q) => $q->whereIn('model_aplikasi', ['Aplikasi Internal MD', 'Improvement IT System']))
        ->when($user->tim === 'eksternal', fn($q) => $q->where('model_aplikasi', 'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot'))
        ->get()
        ->groupBy(fn($p) => \Carbon\Carbon::parse($p->updated_at)->format('Y-m'))
        ->map(fn($g, $bulan) => [
            'bulan'  => \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('M Y'),
            'key'    => $bulan,
            'jumlah' => $g->count(),
        ])
        ->sortKeys()
        ->values();
    @endphp

    const rawChartSelesai  = @json($chartSelesai);
    const rawChartRejected = @json($chartRejected);

    function getSlice(data, range) {
        if (range === 'all') return data;
        return data.slice(-parseInt(range));
    }

    function buildChart(canvasId, data, barColor, lineColor, label) {
        const labels = data.map(d => d.bulan);
        const values = data.map(d => d.jumlah);
        const avg    = data.length ? (values.reduce((a, b) => a + b, 0) / data.length).toFixed(1) : 0;

        return new Chart(document.getElementById(canvasId).getContext('2d'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label,
                        data: values,
                        backgroundColor: barColor,
                        borderColor: barColor.replace('0.15', '1'),
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                        order: 2,
                    },
                    {
                        label: 'Trend',
                        data: values,
                        type: 'line',
                        borderColor: lineColor,
                        borderWidth: 2,
                        pointBackgroundColor: lineColor,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: false,
                        tension: 0.4,
                        order: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            footer: () => `Rata-rata: ${avg} project/bln`,
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 12 } } },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 12 } },
                        grid: { color: 'rgba(0,0,0,.05)' },
                    },
                },
            },
        });
    }

    let chartSelesaiInstance  = buildChart('chartSelesai',  getSlice(rawChartSelesai, '6'),  'rgba(13,110,253,0.15)', '#F97316', 'Project Selesai');
    let chartRejectedInstance = buildChart('chartRejected', getSlice(rawChartRejected, '6'), 'rgba(220,53,69,0.15)',  '#6610f2', 'Project Rejected');

    document.querySelectorAll('.chart-range-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const chartKey = this.dataset.chart;
            const range    = this.dataset.range;

            document.querySelectorAll(`.chart-range-btn[data-chart="${chartKey}"]`).forEach(b => {
                b.className = 'btn btn-sm btn-outline-secondary chart-range-btn';
                b.style.fontSize = '14px';
            });
            this.className = 'btn btn-sm btn-dark chart-range-btn';
            this.style.fontSize = '14px';

            if (chartKey === 'selesai') {
                chartSelesaiInstance.destroy();
                chartSelesaiInstance = buildChart('chartSelesai', getSlice(rawChartSelesai, range), 'rgba(13,110,253,0.15)', '#F97316', 'Project Selesai');
            } else {
                chartRejectedInstance.destroy();
                chartRejectedInstance = buildChart('chartRejected', getSlice(rawChartRejected, range), 'rgba(220,53,69,0.15)', '#6610f2', 'Project Rejected');
            }
        });
    });

    @php
    $projectJson = $projectList->map(fn($pl) => [
        'id'               => $pl['ppsmb']->id,
        'no_ppsmb'         => $pl['ppsmb']->no_ppsmb ?? '—',
        'nama_project'     => $pl['ppsmb']->nama_project,
        'status'           => $pl['ppsmb']->status,
        'pic_ba'           => $pl['ppsmb']->pic_ba ?? '—',
        'secondary_ba'     => $pl['ppsmb']->secondary_ba ?? '—',
        'developer'        => $pl['ppsmb']->developer ?? '—',
        'estimasi_selesai' => $pl['ppsmb']->estimasi_selesai
            ? \Carbon\Carbon::parse($pl['ppsmb']->estimasi_selesai)->translatedFormat('d M Y')
            : '—',
        'sisa_hari'        => $pl['sisa_hari'],
        'telat'            => $pl['telat'],
        'progress'         => $pl['ppsmb']->progress,
        'color'            => config('status.colors')[$pl['ppsmb']->status] ?? '#6c757d',
    ])->values();
    @endphp

    const allProjects  = @json($projectJson);
    const statusColors = @json(config('status.colors'));
    const perPage      = 5;

    // ── HELPER: render pagination ──────────────────────────
    function renderPagination(list, page, infoEl, paginationEl, onPage) {
        const total = list.length;
        const pages = Math.max(1, Math.ceil(total / perPage));
        const start = (page - 1) * perPage;
        const end   = Math.min(start + perPage, total);

        infoEl.textContent = total === 0
            ? 'Tidak ada project'
            : `Menampilkan ${start + 1}–${end} dari ${total} project`;

        paginationEl.innerHTML = '';
        for (let p = 1; p <= pages; p++) {
            const btn = document.createElement('button');
            btn.className = `btn btn-sm rounded-pill ${p === page ? 'btn-dark' : 'btn-outline-secondary'}`;
            btn.style.cssText = 'font-size:14px;width:30px;padding:0;height:28px;';
            btn.textContent = p;
            btn.addEventListener('click', () => onPage(p));
            paginationEl.appendChild(btn);
        }

        return list.slice(start, end);
    }

    // ── HELPER: render row project (untuk summary card) ────
    function renderProjectRow(p) {
        const sisaText = p.sisa_hari === null ? '—'
            : p.telat ? `<span class="fw-bold text-danger">${Math.abs(p.sisa_hari)} hari telat</span>`
            : p.sisa_hari <= 7 ? `<span class="fw-bold text-warning">${p.sisa_hari} hari lagi</span>`
            : `<span class="text-muted">${p.sisa_hari} hari lagi</span>`;

        return `
            <tr class="border-top ${p.telat ? 'table-danger' : ''}">
                <td class="px-3 py-3"><span class="text-muted" style="font-size:14px;">${p.no_ppsmb}</span></td>
                <td class="py-3 fw-medium">${p.nama_project}</td>
                <td class="py-3">
                    <span class="px-2 py-1 rounded"
                          style="font-size:14px;background:${p.color};color:white;white-space:nowrap;">
                        ${p.status}
                    </span>
                </td>
                <td class="py-3" style="font-size:14px;">${p.pic_ba}</td>
                <td class="py-3" style="font-size:14px;">${p.developer}</td>
                <td class="py-3" style="font-size:14px;">${p.estimasi_selesai}</td>
                <td class="py-3">${sisaText}</td>
                <td class="py-3" style="min-width:120px;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress rounded-pill flex-grow-1" style="height:5px;background:#e9ecef;">
                            <div class="progress-bar rounded-pill"
                                 style="width:${p.progress}%;background:${p.color};"></div>
                        </div>
                        <span style="font-size:14px;color:#888;width:34px;text-align:right;">
                            ${Math.round(p.progress)}%
                        </span>
                    </div>
                </td>
                <td class="py-3 pe-3">
                    <a href="/ppsmbbyit/${p.id}"
                       class="btn btn-sm btn-info mt-1"
                       style="font-size:14px;">
                        Rincian
                    </a>
                </td>
            </tr>
        `;
    }

    // ── HELPER: render row beban ───────────────────────────
    function renderBebanRow(p) {
        return `
            <tr class="border-top ${p.telat ? 'table-danger' : ''}">
                <td class="px-3 py-3"><span class="text-muted" style="font-size:14px;">${p.no_ppsmb}</span></td>
                <td class="py-3 fw-medium">${p.nama_project}</td>
                <td class="py-3">
                    <span class="px-2 py-1 rounded"
                          style="font-size:14px;background:${p.color};color:white;white-space:nowrap;">
                        ${p.status}
                    </span>
                </td>
                <td class="py-3" style="min-width:120px;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress rounded-pill flex-grow-1" style="height:5px;background:#e9ecef;">
                            <div class="progress-bar rounded-pill"
                                 style="width:${p.progress}%;background:${p.color};"></div>
                        </div>
                        <span style="font-size:14px;color:#888;">${Math.round(p.progress)}%</span>
                    </div>
                </td>
                <td class="py-3" style="font-size:14px;">${p.estimasi_selesai}</td>
                <td class="py-3 pe-3">
                    <a href="/ppsmbbyit/${p.id}"
                       class="btn btn-sm btn-info mt-1"
                       style="font-size:14px;">
                        Rincian
                    </a>
                </td>
            </tr>
        `;
    }

    // ── SUMMARY CARD ──────────────────────────────────────
    let summaryPage   = 1;
    let summaryList   = [];
    let activeCardKey = null;

    const summaryCard     = document.getElementById('summaryListCard');
    const summaryBody     = document.getElementById('summaryListBody');
    const summaryTitle    = document.getElementById('summaryListTitle');
    const summarySubtitle = document.getElementById('summaryListSubtitle');
    const summaryInfo     = document.getElementById('summaryListInfo');
    const summaryPagBtns  = document.getElementById('summaryListPagination');

    const titleMap = {
        all:             'Semua Project Aktif',
        antrian_analisa: 'Antrian Analisa BA IT',
        analisa_ba:      'Analisa BA IT',
        antrian_dev:     'Antrian Development',
        proses_dev:      'Proses Development',
        uat:             'UAT',
    };

    function renderSummaryList(page) {
        summaryPage = page;
        const slice = renderPagination(summaryList, page, summaryInfo, summaryPagBtns, renderSummaryList);
        summaryBody.innerHTML = slice.map(renderProjectRow).join('');
    }

    document.querySelectorAll('.pl-summary-card').forEach(card => {
        card.addEventListener('click', function () {
            const key = this.dataset.key;

            if (activeCardKey === key) {
                summaryCard.style.display = 'none';
                activeCardKey = null;
                document.querySelectorAll('.pl-summary-card').forEach(c => {
                    c.style.transform = ''; c.style.boxShadow = '';
                });
                return;
            }

            activeCardKey = key;
            document.querySelectorAll('.pl-summary-card').forEach(c => {
                c.style.transform = ''; c.style.boxShadow = '';
            });
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 6px 20px rgba(0,0,0,.1)';

            summaryList = allProjects.filter(p => {
                if (key === 'all')             return true;
                if (key === 'antrian_analisa') return p.status === 'Antrian Analisa BA IT';
                if (key === 'analisa_ba')      return p.status === 'Analisa BA IT';
                if (key === 'antrian_dev')     return p.status === 'Antrian Development';
                if (key === 'proses_dev')      return p.status === 'Proses Development';
                if (key === 'uat')             return p.status === 'UAT';
                return true;
            });

            summaryTitle.textContent    = titleMap[key] || key;
            summarySubtitle.textContent = `${summaryList.length} project`;
            summaryCard.style.display   = '';
            renderSummaryList(1);
            summaryCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });

        card.addEventListener('mouseenter', function () {
            if (activeCardKey !== this.dataset.key) {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 6px 20px rgba(0,0,0,.1)';
            }
        });
        card.addEventListener('mouseleave', function () {
            if (activeCardKey !== this.dataset.key) {
                this.style.transform = ''; this.style.boxShadow = '';
            }
        });
    });

    document.getElementById('summaryListClose').addEventListener('click', () => {
        summaryCard.style.display = 'none';
        activeCardKey = null;
        document.querySelectorAll('.pl-summary-card').forEach(c => {
            c.style.transform = ''; c.style.boxShadow = '';
        });
    });

    // ── BEBAN BA / DEV ────────────────────────────────────
    let bebanPage = 1;
    let bebanList = [];

    const bebanCard     = document.getElementById('bebanDetailCard');
    const bebanBody     = document.getElementById('bebanDetailBody');
    const bebanTitle    = document.getElementById('bebanDetailTitle');
    const bebanSubtitle = document.getElementById('bebanDetailSubtitle');
    const bebanInfo     = document.getElementById('bebanDetailInfo');
    const bebanPagBtns  = document.getElementById('bebanDetailPagination');

    function renderBebanList(page) {
        bebanPage = page;
        const slice = renderPagination(bebanList, page, bebanInfo, bebanPagBtns, renderBebanList);
        bebanBody.innerHTML = slice.map(renderBebanRow).join('');
    }

    function showBeban(nama, type) {
        bebanList = allProjects.filter(p =>
            type === 'ba'
                ? p.pic_ba === nama || p.secondary_ba === nama
                : p.developer === nama
        );
        bebanTitle.textContent    = `${type === 'ba' ? 'Business Analyst' : 'Developer'}: ${nama}`;
        bebanSubtitle.textContent = `${bebanList.length} project assigned`;
        bebanCard.style.display   = '';
        renderBebanList(1);
        bebanCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    document.querySelectorAll('.ba-row').forEach(row => {
        row.addEventListener('click', function () { showBeban(this.dataset.nama, 'ba'); });
        row.addEventListener('mouseenter', function () { this.style.background = '#f8f9fa'; });
        row.addEventListener('mouseleave', function () { this.style.background = ''; });
    });

    document.querySelectorAll('.dev-row').forEach(row => {
        row.addEventListener('click', function () { showBeban(this.dataset.nama, 'dev'); });
        row.addEventListener('mouseenter', function () { this.style.background = '#f8f9fa'; });
        row.addEventListener('mouseleave', function () { this.style.background = ''; });
    });

    document.getElementById('bebanDetailClose').addEventListener('click', () => {
        bebanCard.style.display = 'none';
    });

})();
</script>
@endpush