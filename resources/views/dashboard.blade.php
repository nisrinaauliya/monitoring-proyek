@extends('layouts.app')
@php use Carbon\Carbon; @endphp

@section('title', 'Dashboard - Sistem Helpdesk')
@section('page_title', 'Dashboard')

@section('content')

@if(!in_array(Auth::user()->dept, ['IT', 'CMD', 'DINOV']) && Auth::user()->role !== 'admin')

    {{-- Warning Banner --}}
    @if($showWarning)
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 border-0 rounded-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div>
            <div class="fw-semibold">Pengajuan Project Baru Diblokir</div>
            <div style="font-size:13px;">
                Project <strong>{{ $blockingProject }}</strong> sudah lebih dari 10 hari di tahap UAT.
                Selesaikan UAT tersebut sebelum mengajukan project baru.
            </div>
        </div>
    </div>
    @endif

    <div class="row g-3">

    {{-- KIRI: Summary + Donut + Aging --}}
    <div class="col-md-9">

        {{-- Summary Cards --}}
        <div class="row g-3 mb-3">
            @php
            $summaryCards = [
                ['label'=>'Total Project',  'val'=>$total,      'color'=>'#117e1a','bg'=>'#ffffff','filter'=>'all',         'sub'=>'Semua project departemen'],
                ['label'=>'Project Aktif',  'val'=>$totalAktif, 'color'=>'#0d6efd','bg'=>'#ffffff','filter'=>'aktif',       'sub'=>'Project yang sedang berjalan'],
                ['label'=>'Revisi User',    'val'=>$revisi,     'color'=>'#dc3545','bg'=>'#ffffff','filter'=>'Revisi User', 'sub'=>'Perlu tindakan segera'],
                ['label'=>'UAT',            'val'=>$uat,        'color'=>'#e6a817','bg'=>'#ffffff','filter'=>'UAT',         'sub'=>'Project dalam pengujian'],
            ];
            @endphp
            @foreach($summaryCards as $sc)
            <div class="col-6 col-sm-3">
                <div class="card border-0 shadow-sm h-100 summary-card" role="button"
                     data-filter="{{ $sc['filter'] }}"
                     style="cursor:pointer;transition:transform .15s,box-shadow .15s;background:{{ $sc['bg'] }};">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                        <div class="fw-bold mb-1" style="font-size:28px;color:{{ $sc['color'] }};line-height:1;">{{ $sc['val'] }}</div>
    
                        <i class="bi bi-arrow-right text-muted" style="font-size:12px;"></i>
                        </div>
                        <div class="fw-semibold" style="font-size:14px;">{{ $sc['label'] }}</div>
                        <div class="text-muted mt-1" style="font-size:10px;">{{ $sc['sub'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Donut + Aging --}}
        <div class="row g-3">
            <div class="col-md-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="fw-semibold mb-1" style="font-size:14px;">Distribusi Status Project</div>
                        <div class="text-muted mb-3" style="font-size:10px;">Klik segmen untuk filter daftar project</div>
                        <div class="d-flex justify-content-center">
                            <canvas id="donutChart" style="max-height:250px;max-width:180px;cursor:pointer;"></canvas>
                        </div>
                        <div class="mt-3" id="donutLegend"
                             style="display:grid;grid-template-columns:1fr 1fr;gap:2px 8px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="fw-semibold" style="font-size:14px;">
                                    Aging Tracker — UAT
                                </div>
                                <div class="text-muted" style="font-size:10px;">Batas ideal: 10 hari</div>
                            </div>
                            <span class="badge bg-warning text-dark" style="font-size:10px;">{{ count($uatAging) }} project</span>
                        </div>
                        <div class="flex-grow-1 overflow-auto pe-1" style="max-height:250px;">
                            @if(count($uatAging) > 0)
                                @foreach($uatAging as $ua)
                                @php
                                    $h = (int)$ua['hari'];
                                    $barCls  = $h >= 10 ? 'bg-danger' : ($h >= 7 ? 'bg-warning' : 'bg-success');
                                    $txtCls  = $h >= 10 ? 'text-danger' : ($h >= 7 ? 'text-warning' : 'text-success');
                                    $badgeTxt = $h >= 10 ? 'Lewat batas' : ($h >= 7 ? 'Hampir batas' : 'Aman');
                                @endphp
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div>
                                            <div style="font-size:12px;font-weight:500;">{{ $ua['ppsmb']->nama_project }}</div>
                                            <div class="text-muted" style="font-size:10px;">Masuk UAT: {{ $ua['masuk_uat'] }}</div>
                                        </div>
                                        <div class="text-end flex-shrink-0 ms-2">
                                            <span class="fw-bold {{ $txtCls }}" style="font-size:16px;">{{ $h }}</span>
                                            <span class="text-muted" style="font-size:10px;"> hari</span>
                                            <div><span class="badge {{ $h >= 10 ? 'bg-danger' : ($h >= 7 ? 'bg-warning text-dark' : 'bg-success') }}" style="font-size:10px;">{{ $badgeTxt }}</span></div>
                                        </div>
                                    </div>
                                    <div class="progress rounded-pill" style="height:6px;">
                                        <div class="progress-bar {{ $barCls }} rounded-pill" style="width:{{ $ua['pct'] }}%;"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span class="text-muted" style="font-size:10px;">0</span>
                                        <span class="text-muted" style="font-size:10px;">10 hari</span>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center py-4">
                                    <i class="bi bi-check-circle text-success mb-2" style="font-size:32px;"></i>
                                    <div style="font-size:14px;font-weight:500;">clear!</div>
                                    <div class="text-muted" style="font-size:12px;">Tidak ada project di tahap UAT.</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- KANAN: Sidebar --}}
    <div class="col-md-3">
        {{-- Status Dept --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="fw-semibold mb-2" style="font-size:14px;">Status Departemen</div>
                @if($showWarning)
                <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#fff3f3;">
                    <i class="bi bi-x-circle-fill text-danger"></i>
                    <div>
                        <div class="fw-semibold text-danger" style="font-size:12px;">Pengajuan project baru diblokir</div>
                        <div class="text-muted" style="font-size:10px;">Project UAT melebihi batas dan salah satunya memiliki aging &gt;10 hari</div>
                    </div>
                </div>
                @else
                <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#f0fff6;">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <div>
                        <div class="fw-semibold text-success" style="font-size:12px;">Aman</div>
                        <div class="text-muted" style="font-size:10px;">Pengajuan project baru dapat dilakukan</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Countdown Revisi --}}
        @if($revisiCountdown->count() > 0)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="fw-semibold mb-1" style="font-size:14px;">
                    <i class="bi bi-hourglass-split me-1 text-warning"></i>Countdown Revisi
                </div>
                <div class="text-muted mb-3" style="font-size:10px;">Auto reject di hari ke-30</div>
                <div class="overflow-auto pe-1" style="max-height:150px;">
                @foreach($revisiCountdown as $rc)
                @php
                    $sisa = $rc['sisa_hari'];
                    $barCls = $sisa <= 5 ? 'bg-danger' : ($sisa <= 10 ? 'bg-warning' : 'bg-success');
                    $txtCls = $sisa <= 5 ? 'text-danger' : ($sisa <= 10 ? 'text-warning' : 'text-success');
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span style="font-size:12px;font-weight:500;max-width:140px;" class="text-truncate">
                            {{ $rc['ppsmb']->nama_project }}
                        </span>
                        <span class="fw-bold {{ $txtCls }}" style="font-size:14px;">
                            {{ $sisa }}<span class="text-muted fw-normal" style="font-size:10px;"> hari lagi</span>
                        </span>
                    </div>
                    <div class="progress rounded-pill" style="height:5px;background:#e9ecef;">
                        <div class="progress-bar {{ $barCls }} rounded-pill" style="width:{{ $rc['pct'] }}%;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-muted" style="font-size:10px;">Hari ke-{{ $rc['hari_ke'] }}</span>
                        <a href="{{ route('ppsmbbyuser.edit', $rc['ppsmb']->id) }}"
                           style="font-size:10px;color:#0d6efd;">Revisi sekarang →</a>
                    </div>
                </div>
                @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Riwayat Auto Reject --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="fw-semibold mb-3" style="font-size:14px;">
                    Riwayat Auto Reject
                </div>
                @if($autoRejected->count() > 0)
                <div class="overflow-auto pe-1" style="max-height:100px;">
                    @foreach($autoRejected as $ar)
                    <div class="d-flex align-items-start gap-2 mb-2 pb-2 border-bottom">
                        <div class="rounded-circle bg-danger flex-shrink-0 mt-2" style="width:8px;height:8px;"></div>
                        <div>
                            <div class="text-muted" style="font-size:12px;font-weight:500;">{{ $ar->nama_project }}</div>
                            <div class="text-muted" style="font-size:10px;">
                                {{ $ar->updated_at ? Carbon::parse($ar->updated_at)->translatedFormat('d F Y') : '-' }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                    <div class="text-center py-2">
                        <i class="bi bi-check-circle text-success mb-1" style="font-size:24px;"></i>
                        <div class="text-muted" style="font-size:12px;">Tidak ada riwayat auto reject</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>{{-- end row --}}

{{-- List Project FULL WIDTH --}}
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white border-0 px-3 pt-3 pb-3">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="fw-semibold" style="font-size:14px;">Daftar Project</div>
                <div class="text-muted" id="tableSubtitle" style="font-size:12px;">Menampilkan semua project</div>
            </div>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" id="resetFilter"
                    style="font-size:10px;display:none;">
                <i class="bi bi-x me-1"></i>Reset Filter
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:12px;">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th class="px-3 py-3 border-0 text-muted fw-semibold" style="font-size:12px;">NO PPSMB</th>
                        <th class="py-3 border-0 text-muted fw-semibold" style="font-size:12px;">NAMA PROJECT</th>
                        <th class="py-3 border-0 text-muted fw-semibold" style="font-size:12px;">STATUS</th>
                        <th class="py-3 border-0 text-muted fw-semibold" style="font-size:12px;">AGING</th>
                        <th class="py-3 border-0 text-muted fw-semibold" style="font-size:12px;">PROGRESS</th>
                        <th class="py-3 border-0"></th>
                    </tr>
                </thead>
                <tbody id="projectTableBody">
                    @php
                    $statusColorMap = [
                        'Revisi User'           => '#dc3545',
                        'Antrian Analisa BA IT' => '#0dcaf0',
                        'Analisa BA IT'         => '#0d6efd',
                        'Antrian Development'   => '#6c757d',
                        'Proses Development'    => '#4e8ef7',
                        'UAT'                   => '#e6a817',
                        'Done (Live)'           => '#198754',
                        'Rejected'              => '#adb5bd',
                        'Verifikasi CMD/Dinov'  => '#6f42c1',
                        'Edit by User'          => '#fd7e14',
                    ];
                    @endphp
                    @foreach($ppsmbs as $ppsmb)
                    @php
                        $latestHistory = $ppsmb->histories()->latest()->first();
                        $agingHari = $latestHistory
                            ? (int) Carbon::parse($latestHistory->created_at)->diffInDays(now())
                            : null;
                        $sc = $statusColorMap[$ppsmb->status] ?? '#6c757d';
                        $isAktif = !in_array($ppsmb->status, ['Done (Live)', 'Rejected']);
                    @endphp
                    <tr class="border-top project-row"
                        data-status="{{ $ppsmb->status }}"
                        data-aktif="{{ $isAktif ? 'true' : 'false' }}">
                        <td class="px-3 py-3">
                            <span class="text-muted" style="font-size:12px;">{{ $ppsmb->no_ppsmb ?? '—' }}</span>
                        </td>
                        <td class="py-3 fw-medium">{{ $ppsmb->nama_project }}</td>
                        <td class="py-3">
                            <span class="px-2 py-1 rounded-pill"
                                  style="font-size:12px;background:{{ $sc }}15;color:{{ $sc }};border:1px solid {{ $sc }}30;">
                                {{ $ppsmb->status }}
                            </span>
                        </td>
                        <td class="py-3">
                            @if($agingHari !== null)
                            <span style="font-size:12px;color:{{ $agingHari > 10 ? '#dc3545' : '#6c757d' }};font-weight:{{ $agingHari > 10 ? '600' : '400' }};">
                                {{ $agingHari }} hari
                            </span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="py-3" style="min-width:120px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress rounded-pill flex-grow-1" style="height:5px;background:#e9ecef;">
                                    <div class="progress-bar rounded-pill"
                                         style="width:{{ $ppsmb->progress }}%;background:{{ $sc }};"></div>
                                </div>
                                <span style="font-size:12px;color:#888;width:34px;text-align:right;">
                                    {{ number_format($ppsmb->progress, 0) }}%
                                </span>
                            </div>
                        </td>
                        <td class="py-3 pe-3">
                            @if($ppsmb->status === 'Revisi User')
                            <a href="{{ route('ppsmbbyuser.edit', $ppsmb->id) }}"
                               class="btn btn-sm rounded-pill px-3"
                               style="font-size:12px;background:#fff3cd;color:#856404;border:1px solid #ffc10750;">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </a>
                            @else
                            <a href="{{ route('ppsmbbyuser.show', $ppsmb->id) }}"
                               class="btn btn-sm rounded-pill px-3"
                               style="font-size:12px;background:#e7f1ff;color:#0d6efd;border:1px solid #0d6efd30;">
                                <i class="bi bi-eye me-1"></i>Rincian
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex align-items-center justify-content-between px-3 py-3 border-top">
            <div class="text-muted" id="paginationInfo" style="font-size:12px;"></div>
            <div class="d-flex gap-1" id="paginationBtns"></div>
        </div>
    </div>
</div>

@else
    <div class="card border-0 shadow-sm">
        <div class="card-body py-5 text-center">
            <i class="bi bi-grid text-muted mb-3" style="font-size:40px;"></i>
            <div class="fw-semibold mb-1">Dashboard</div>
            <div class="text-muted" style="font-size:13px;">Fitur dashboard untuk role ini sedang dalam pengembangan.</div>
        </div>
    </div>
@endif

@endsection

@push('scripts')
@if(!in_array(Auth::user()->dept, ['IT', 'CMD', 'DINOV']) && Auth::user()->role !== 'admin')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    // ── DATA ──────────────────────────────────────────────
    const labels = ['Verifikasi CMD/Dinov','Edit by User','Revisi User','Antrian Analisa BA IT','Analisa BA IT',
                    'Antrian Development','Proses Development','UAT','Done (Live)','Rejected'];
    const data   = [{{ $verifikasiCmd }},{{ $editByUser }},{{ $revisi }},{{ $antrianAnalisa }},{{ $analisaBa }},
                    {{ $antrianDev }},{{ $prosesDev }},{{ $uat }},{{ $done }},{{ $rejected }}];
    const colors = ['#6f42c1','#fd7e14','#dc3545','#0dcaf0','#0d6efd','#6c757d','#4e8ef7','#e6a817','#198754','#adb5bd'];
    const total  = {{ $total }};

    // ── DONUT CHART ───────────────────────────────────────
    const canvas = document.getElementById('donutChart');
    const state  = { val: total, lbl: 'Total' };

    const chart = new Chart(canvas, {
        type: 'doughnut',
        data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 2, borderColor:'#fff', hoverOffset:8 }] },
        options: {
            cutout: '68%',
            plugins: { legend: { display:false }, tooltip: { enabled:false } },
            onClick(evt, elements) {
                if (!elements.length) { applyFilter('all'); return; }
                const i = elements[0].index;
                applyFilter(labels[i]);
            },
            onHover(evt, elements) {
                canvas.style.cursor = elements.length ? 'pointer' : 'default';
                if (elements.length) {
                    const i = elements[0].index;
                    const pct = total > 0 ? Math.round(data[i]/total*100) : 0;
                    state.val = pct + '%';
                    state.lbl = labels[i].length > 13 ? labels[i].slice(0,12)+'…' : labels[i];
                } else {
                    state.val = total; state.lbl = 'Total';
                }
                chart.draw();
            }
        },
        plugins: [{
            id: 'center',
            afterDraw(c) {
                const { ctx, chartArea:{left,top,right,bottom} } = c;
                const cx=(left+right)/2, cy=(top+bottom)/2;
                ctx.save();
                ctx.textAlign='center'; ctx.textBaseline='middle';
                ctx.font='bold 24px sans-serif'; ctx.fillStyle='#212529';
                ctx.fillText(state.val, cx, cy-9);
                ctx.font='10px sans-serif'; ctx.fillStyle='#6c757d';
                ctx.fillText(state.lbl, cx, cy+11);
                ctx.restore();
            }
        }]
    });

    // ── LEGEND ────────────────────────────────────────────
    const legendEl = document.getElementById('donutLegend');
    labels.forEach((lbl, i) => {
        if (data[i] === 0) return;
        const pct = total > 0 ? Math.round(data[i]/total*100) : 0;
        const row = document.createElement('div');
        row.className = 'd-flex align-items-center justify-content-between py-1';
        row.style.cssText = 'font-size:11px; cursor:pointer;';
        row.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <span style="width:8px;height:8px;border-radius:50%;background:${colors[i]};flex-shrink:0;display:inline-block;"></span>
                <span class="text-muted">${lbl}</span>
            </div>
            <span style="font-weight:600;color:${colors[i]};">${data[i]} <span style="color:#aaa;font-weight:400;">(${pct}%)</span></span>
        `;
        row.addEventListener('click', () => applyFilter(lbl));
        legendEl.appendChild(row);
    });

    // ── FILTER + PAGINATION ───────────────────────────────
    const allRows   = Array.from(document.querySelectorAll('.project-row'));
    const perPage   = 5;
    let currentPage = 1;
    let activeFilter = 'all';

    function getVisibleRows() {
        return allRows.filter(row => {
            if (activeFilter === 'all') return true;
            if (activeFilter === 'aktif') return row.dataset.aktif === 'true';
            return row.dataset.status === activeFilter;
        });
    }

    function renderTable() {
        const visible = getVisibleRows();
        const total   = visible.length;
        const pages   = Math.max(1, Math.ceil(total / perPage));
        currentPage   = Math.min(currentPage, pages);
        const start   = (currentPage - 1) * perPage;
        const end     = start + perPage;

        allRows.forEach(r => r.style.display = 'none');
        visible.slice(start, end).forEach(r => r.style.display = '');

        // info
        const info = document.getElementById('paginationInfo');
        if (total === 0) {
            info.textContent = 'Tidak ada project';
        } else {
            info.textContent = `Menampilkan ${start+1}–${Math.min(end,total)} dari ${total} project`;
        }

        // subtitle
        const sub = document.getElementById('tableSubtitle');
        if (activeFilter === 'all') sub.textContent = 'Menampilkan semua project';
        else if (activeFilter === 'aktif') sub.textContent = 'Project Aktif';
        else sub.textContent = activeFilter;

        // reset btn
        document.getElementById('resetFilter').style.display = activeFilter !== 'all' ? '' : 'none';

        // pagination buttons
        const btns = document.getElementById('paginationBtns');
        btns.innerHTML = '';
        for (let p = 1; p <= pages; p++) {
            const btn = document.createElement('button');
            btn.className = `btn btn-sm rounded-pill ${p === currentPage ? 'btn-dark' : 'btn-outline-secondary'}`;
            btn.style.cssText = 'font-size:11px; width:30px; padding:0; height:28px;';
            btn.textContent = p;
            btn.addEventListener('click', () => { currentPage = p; renderTable(); });
            btns.appendChild(btn);
        }
    }

    function applyFilter(filter) {
        activeFilter = filter;
        currentPage  = 1;
        renderTable();

        // scroll ke tabel
        document.getElementById('projectTableBody').closest('.card').scrollIntoView({ behavior:'smooth', block:'nearest' });
    }

    // summary card click
    document.querySelectorAll('.summary-card').forEach(card => {
        card.addEventListener('click', () => applyFilter(card.dataset.filter));
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-2px)';
            card.style.boxShadow = '0 6px 20px rgba(0,0,0,.1)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
            card.style.boxShadow = '';
        });
    });

    // reset filter
    document.getElementById('resetFilter').addEventListener('click', () => applyFilter('all'));

    // init
    renderTable();
})();
</script>
@endif
@endpush