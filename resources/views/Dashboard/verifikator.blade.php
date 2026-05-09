@extends('layouts.app')
@php use Carbon\Carbon; @endphp

@section('title', 'Dashboard - Sistem Helpdesk')
@section('page_title', 'Dashboard')

@section('content')

{{-- Filter Model Aplikasi --}}
@php
$modelMap = [
    'Semua'          => null,
    'Eksternal'      => 'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot',
    'Internal MD'    => 'Aplikasi Internal MD',
    'Improvement IT' => 'Improvement IT System',
];
@endphp
<div class="d-flex align-items-center gap-2 mb-4" id="modelFilterBtns">
    <span class="text-muted me-1" style="font-size:16px;">Filter:</span>
    @foreach($modelMap as $label => $val)
    <button class="btn btn-sm rounded-pill {{ $label === 'Semua' ? 'btn-dark' : 'btn-outline-secondary' }}"
            data-model="{{ $val }}"
            style="font-size:14px;">
        {{ $label }}
    </button>
    @endforeach
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-3" id="summaryCards">
    @php
    $summaryDefs = [
        ['label'=>'Menunggu Verifikasi', 'key'=>'menunggu',  'color'=>'#7811bd', 'sub'=>'Perlu ditindaklanjuti'],
        ['label'=>'Revisi User',         'key'=>'revisi',    'color'=>'#dc3545', 'sub'=>'Menunggu revisi user'],
        ['label'=>'UAT Aging >10 Hari',  'key'=>'uatAging', 'color'=>'#ec7b11', 'sub'=>'Melewati batas UAT'],
        ['label'=>'Project Aktif',        'key'=>'totalAktif',     'color'=>'#4b4d50', 'sub'=>'Semua project aktif'],
    ];
    @endphp
    @foreach($summaryDefs as $sd)
    <div class="col-6 col-sm-3">
        <div class="card border-0 shadow-sm h-100 verif-summary-card" role="button"
             data-key="{{ $sd['key'] }}"
             style="cursor:pointer;transition:transform .15s,box-shadow .15s;">
            <div class="card-body p-3">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="fw-bold summary-val" style="font-size:30px;color:{{ $sd['color'] }};line-height:1;">—</div>
                    <i class="bi bi-arrow-right text-muted" style="font-size:14px;"></i>
                </div>
                <div class="fw-semibold" style="font-size:16px;">{{ $sd['label'] }}</div>
                <div class="text-muted mt-1" style="font-size:14px;">{{ $sd['sub'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Row: Matrix + Antrian --}}
<div class="row g-3 mb-4">

    {{-- Matrix Dept x Status --}}
    <div class="col-md-9">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="fw-semibold mb-1" style="font-size:16px;">Matrix Project per Departemen & Status</div>
                <div class="text-muted mb-3" style="font-size:14px;">Klik cell untuk lihat daftar project</div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-10" style="font-size:14px;">
                        <thead style="background:#f8f9fa;">
                            <tr>
                                <th class="py-2 px-3 border fw-semibold text-muted" style="white-space:nowrap;min-width:100px;">Dept</th>
                                @foreach($statusList as $status)
                                @php $sc = config('status.colors')[$status] ?? '#6c757d'; @endphp
                                <th class="py-2 px-2 text-center border" style="white-space:nowrap;">
                                    <span class="px-2 py-1 rounded" style="font-size:14px;background:{{ $sc }};color:white;">
                                        {{ $status }}
                                    </span>
                                </th>
                                @endforeach
                                <th class="py-2 px-2 text-center border fw-semibold text-muted" style="white-space:nowrap;">Total</th>
                            </tr>
                        </thead>
                        <tbody id="matrixBody">
                            @foreach($depts as $dept)
                            <tr data-dept="{{ $dept }}">
                                <td class="py-2 px-3 fw-semibold border" style="white-space:nowrap;">{{ $dept }}</td>
                                @foreach($statusList as $status)
                                <td class="py-2 px-2 text-center border matrix-cell"
                                    data-dept="{{ $dept }}"
                                    data-status="{{ $status }}"
                                    style="color:{{ config('status.colors')[$status] ?? '#6c757d' }};">—</td>
                                @endforeach
                                <td class="py-2 px-2 text-center border fw-bold matrix-row-total"
                                    data-dept="{{ $dept }}"
                                    style="color:#0d6efd;">—</td>
                            </tr>
                            @endforeach
                            {{-- Row total per status --}}
                            <tr style="background:#f8f9fa;">
                                <td class="py-2 px-3 fw-semibold border text-muted" style="white-space:nowrap;">Total</td>
                                @foreach($statusList as $status)
                                <td class="py-2 px-2 text-center border fw-bold matrix-status-total"
                                    data-status="{{ $status }}"
                                    style="color:{{ config('status.colors')[$status] ?? '#6c757d' }};">—</td>
                                @endforeach
                                <td class="py-2 px-2 text-center border fw-bold" id="matrixGrandTotal"
                                    style="color:#0d6efd;">—</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Detail project dari cell yang diklik --}}
                <div id="matrixDetail" class="overflow-auto mt-3" style="max-height:200px;display:none;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-semibold" id="matrixDetailTitle" style="font-size:14px;"></div>
                        <button class="btn btn-sm btn-outline-secondary rounded-pill" id="matrixDetailClose"
                                style="font-size:14px;">Tutup</button>
                    </div>
                    <div id="matrixDetailList"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Antrian / List berdasar summary card --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="fw-semibold mb-1" style="font-size:16px;" id="antrianTitle">
                    <i class="bi bi-list-check me-1"></i>Detail Summary Card
                </div>
                <div class="text-muted mb-3" style="font-size:14px;" id="antrianSubtitle">
                    Klik summary card untuk lihat daftar
                </div>
                <div class="overflow-auto" style="max-height:400px;" id="antrianList">
                    <div class="text-center py-4 text-muted" style="font-size:16px;">
                        <i class="bi bi-hand-index mb-2 d-block" style="font-size:32px;"></i>
                        Klik summary card untuk lihat daftar
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Area Chart --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="fw-semibold mb-1" style="font-size:16px;">Timeline Estimasi Selesai Project</div>
        <div class="text-muted mb-3" style="font-size:14px;">
            Distribusi project aktif berdasarkan estimasi selesai per bulan
        </div>
        @if(count($chartData) > 0)
        <canvas id="areaChart" style="max-height:250px;"></canvas>
        @else
        <div class="text-center py-4 text-muted" style="font-size:16px;">
            <i class="bi bi-bar-chart mb-2 d-block" style="font-size:32px;"></i>
            Belum ada data estimasi selesai
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {

    // ── RAW DATA FROM PHP ──────────────────────────────────
    @php
    $allProjectsJson = $allPpsmbs->map(fn($p) => [
        'id'             => $p->id,
        'nama_project'   => $p->nama_project,
        'no_ppsmb'       => $p->no_ppsmb ?? '—',
        'user'           => $p->user->name,
        'dept'           => $p->department->code,
        'status'         => $p->status,
        'model_aplikasi' => $p->model_aplikasi,
        'created_at'     => $p->created_at,
    ])->values();

    $antrianJson = $antrian->map(fn($p) => [
        'id'             => $p->id,
        'nama_project'   => $p->nama_project,
        'no_ppsmb'       => $p->no_ppsmb ?? '—',
        'user'           => $p->user->name,
        'dept'           => $p->department->code,
        'status'         => $p->status,
        'model_aplikasi' => $p->model_aplikasi,
        'created_at'     => $p->created_at,
    ])->values();
    @endphp

    const allProjects  = @json($allProjectsJson);
    const antrianRaw   = @json($antrianJson);
    const statusColors = @json(config('status.colors'));
    const depts        = @json($depts);
    const statusList   = @json($statusList);

    const modelMap = {
        'Eksternal':      'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot',
        'Internal MD':    'Aplikasi Internal MD',
        'Improvement IT': 'Improvement IT System',
    };

    let activeModel   = null; // null = semua
    let activeCard    = null;

    // ── FILTER MODEL APLIKASI ─────────────────────────────
    document.querySelectorAll('#modelFilterBtns button').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#modelFilterBtns button').forEach(b => {
                b.className = 'btn btn-sm rounded-pill btn-outline-secondary';
                b.style.fontSize = '13px';
            });
            this.className = 'btn btn-sm rounded-pill btn-dark';
            this.style.fontSize = '13px';

            activeModel = this.dataset.model || null;
            activeCard  = null;

            updateAll();
        });
    });

    function filterProjects(projects) {
        if (!activeModel) return projects;
        return projects.filter(p => p.model_aplikasi === activeModel);
    }

    // ── UPDATE ALL ────────────────────────────────────────
    function updateAll() {
        const filtered = filterProjects(allProjects);
        updateSummaryCards(filtered);
        updateMatrix(filtered);
        updateAntrian(null);
    }

    // ── SUMMARY CARDS ─────────────────────────────────────
    const menungguStatuses = ['Verifikasi CMD/Dinov', 'Edit by User - Verifikasi CMD/Dinov'];

    function updateSummaryCards(filtered) {
        const counts = {
            menunggu:   filtered.filter(p => menungguStatuses.includes(p.status)).length,
            revisi:     filtered.filter(p => p.status === 'Revisi User').length,
            uatAging:   filtered.filter(p => p.status === 'UAT').length, // simplified
            totalAktif: filtered.filter(p => !['Done (Live)','Rejected'].includes(p.status)).length,
        };

        document.querySelectorAll('.verif-summary-card').forEach(card => {
            const key = card.dataset.key;
            card.querySelector('.summary-val').textContent = counts[key] ?? 0;
        });
    }

    document.querySelectorAll('.verif-summary-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.verif-summary-card').forEach(c => {
                c.style.transform = '';
                c.style.boxShadow = '';
            });
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 6px 20px rgba(0,0,0,.1)';

            activeCard = this.dataset.key;
            updateAntrian(activeCard);
        });

        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 6px 20px rgba(0,0,0,.1)';
        });
        card.addEventListener('mouseleave', function () {
            if (activeCard !== this.dataset.key) {
                this.style.transform = '';
                this.style.boxShadow = '';
            }
        });
    });

    // ── ANTRIAN / LIST ────────────────────────────────────
    function updateAntrian(key) {
        const filtered = filterProjects(allProjects);
        const titles = {
            menunggu:  'Menunggu Verifikasi',
            revisi:    'Revisi User',
            uatAging:  'UAT >10 Hari',
            totalAktif:     'Project Aktif',
        };
        const subtitles = {
            menunggu:  'Segera tindak lanjut pengajuan project',
            revisi:    'Project yang sedang dalam revisi user',
            uatAging:  'Project melewati batas UAT',
            totalAktif:     'Semua project yang sedang aktif',
        };

        let list = [];
        if (key === 'menunggu') {
            list = filtered.filter(p => menungguStatuses.includes(p.status));
        } else if (key === 'revisi') {
            list = filtered.filter(p => p.status === 'Revisi User');
        } else if (key === 'uatAging') {
            list = filtered.filter(p => p.status === 'UAT');
        } else if (key === 'totalAktif') {
            list = filtered.filter(p => !['Done (Live)','Rejected'].includes(p.status));
;
        }

        document.getElementById('antrianTitle').innerHTML =
            `<i class="bi bi-list-check me-1"></i>${key ? titles[key] : 'Detail Summary Card'}`;
        document.getElementById('antrianSubtitle').textContent =
            key ? subtitles[key] : 'Klik summary card untuk lihat daftar';

        const container = document.getElementById('antrianList');

        if (!key) {
            container.innerHTML = `
                <div class="text-center py-4 text-muted" style="font-size:16px;">
                    <i class="bi bi-hand-index mb-2 d-block" style="font-size:32px;"></i>
                    Klik summary card untuk lihat daftar
                </div>`;
            return;
        }

        if (list.length === 0) {
            container.innerHTML = `
                <div class="d-flex flex-column align-items-center justify-content-center text-center py-4">
                    <i class="bi bi-check-circle text-success mb-2" style="font-size:32px;"></i>
                    <div style="font-size:16px;font-weight:500;">Semua clear!</div>
                    <div class="text-muted" style="font-size:14px;">Tidak ada project di kategori ini.</div>
                </div>`;
            return;
        }

        container.innerHTML = list.map(p => {
            const color = statusColors[p.status] || '#6c757d';
            const createdDate = new Date(p.created_at);
            const now = new Date();
            const hariMenunggu = Math.floor((now - createdDate) / (1000 * 60 * 60 * 24));
            return `
                <div class="d-flex align-items-start justify-content-between mb-3 pb-3 border-bottom">
                    <div style="min-width:0;">
                        <div class="fw-medium text-truncate" style="font-size:16px;max-width:160px;">
                            ${p.nama_project}
                        </div>
                        <div class="text-muted" style="font-size:14px;">${p.dept} · ${p.user}</div>
                        <span class="px-2 py-1 rounded mt-1 d-inline-block"
                              style="font-size:14px;background:${color};color:white;">
                            ${p.status}
                        </span>
                    </div>
                    <div class="text-end flex-shrink-0 ms-2">
                        <div class="fw-bold ${hariMenunggu > 7 ? 'text-danger' : 'text-muted'}"
                             style="font-size:18px;">${hariMenunggu}</div>
                        <div class="text-muted" style="font-size:14px;">hari</div>
                        <a href="/ppsmbbycmd/${p.id}" class="btn btn-sm btn-info mt-1"
                           style="font-size:14px;">
                            Rincian
                        </a>
                    </div>
                </div>
            `;
        }).join('');
    }

    // ── MATRIX ────────────────────────────────────────────
    function updateMatrix(filtered) {
        // update cells
        document.querySelectorAll('.matrix-cell').forEach(cell => {
            const dept   = cell.dataset.dept;
            const status = cell.dataset.status;
            const color  = statusColors[status] || '#6c757d';
            const count  = filtered.filter(p => p.dept === dept && p.status === status).length;

            cell.style.cursor = count > 0 ? 'pointer' : 'default';
            cell.innerHTML = count > 0
                ? `<span class="fw-bold" style="color:${color};font-size:14px;">${count}</span>`
                : `<span class="text-muted">—</span>`;
        });

        // grand total
        const grandTotal = filtered.length;
        const grandEl = document.getElementById('matrixGrandTotal');
        if (grandEl) grandEl.textContent = grandTotal > 0 ? grandTotal : '—';


        // update column totals
        document.querySelectorAll('.matrix-status-total').forEach(cell => {
            const status = cell.dataset.status;
            const count  = filtered.filter(p => p.status === status).length;
            cell.textContent = count > 0 ? count : '—';
        });

        // hide matrix detail saat filter berubah
        document.getElementById('matrixDetail').style.display = 'none';
    }

    // matrix cell click
    document.querySelector('#matrixBody').addEventListener('click', function (e) {
        const cell = e.target.closest('.matrix-cell');
        if (!cell) return;

        const dept     = cell.dataset.dept;
        const status   = cell.dataset.status;
        const color    = statusColors[status] || '#6c757d';
        const filtered = filterProjects(allProjects);
        const projects = filtered.filter(p => p.dept === dept && p.status === status);

        if (projects.length === 0) return;

        const detail = document.getElementById('matrixDetail');
        const title  = document.getElementById('matrixDetailTitle');
        const list   = document.getElementById('matrixDetailList');

        title.innerHTML = `
            <span class="px-2 py-1 rounded me-2"
                  style="background:${color};color:white;font-size:14px;">
                ${status}
            </span>
            ${dept} — ${projects.length} project
        `;

        list.innerHTML = projects.map(p => `
            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                <div>
                    <div style="font-size:16px;font-weight:500;">${p.nama_project}</div>
                    <div style="font-size:14px;color:#888;">${p.no_ppsmb} · ${p.user}</div>
                </div>
                <a href="/ppsmbbycmd/${p.id}" class="btn btn-sm btn-info"
                   style="font-size:14px;">
                    Rincian
                </a>
            </div>
        `).join('');

        detail.style.display = '';
        document.querySelectorAll('.matrix-cell').forEach(c => c.style.background = '');
        cell.style.background = color + '20';
    });

    document.getElementById('matrixDetailClose').addEventListener('click', () => {
        document.getElementById('matrixDetail').style.display = 'none';
        document.querySelectorAll('.matrix-cell').forEach(c => c.style.background = '');
    });

    // ── AREA CHART ────────────────────────────────────────
    const areaCanvas = document.getElementById('areaChart');
    if (areaCanvas) {
        const chartLabels = @json(collect($chartData)->pluck('bulan'));
        const chartValues = @json(collect($chartData)->pluck('jumlah'));

        new Chart(areaCanvas, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Project Selesai',
                    data: chartValues,
                    fill: true,
                    backgroundColor: 'rgba(13, 110, 253, 0.08)',
                    borderColor: '#0d6efd',
                    borderWidth: 2,
                    pointBackgroundColor: '#0d6efd',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.y} project estimasi selesai`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 13 } },
                        grid: { color: '#f0f0f0' }
                    },
                    x: {
                        ticks: { font: { size: 13 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ── INIT ──────────────────────────────────────────────
    updateAll();

})();
</script>
@endpush