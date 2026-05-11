@extends('layouts.app')

@section('title', 'Dashboard - Sistem Helpdesk')
@section('page_title', 'Dashboard')

@section('content')

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    @foreach($summaryCards as $sc)
    <div class="col-6 col-sm-3">
        <div class="card border-0 shadow-sm h-100 admin-summary-card" role="button"
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

{{-- Detail Summary Card --}}
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
                        <th class="py-3 border-0 text-muted fw-semibold">Tim</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Status</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Estimasi Selesai</th>
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

{{-- Chart Status + Workload per Tim --}}
<div class="row g-3 mb-4">

    {{-- Chart Distribusi Status --}}
    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="fw-semibold mb-1" style="font-size:16px;">Distribusi Status Project</div>
                <div class="text-muted mb-3" style="font-size:14px;">Jumlah project per status saat ini</div>
                <canvas id="chartStatus" style="max-height:280px;"></canvas>
            </div>
        </div>
    </div>

    {{-- Workload per Tim --}}
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="fw-semibold mb-1" style="font-size:16px;">Workload per Tim</div>
                <div class="text-muted mb-3" style="font-size:14px;">Klik tim untuk lihat detail project</div>
                @if($perTim->count() > 0)
                    @php $totalTim = $perTim->sum(); @endphp
                    @foreach($perTim as $tim => $count)
                    @php $pct = $totalTim > 0 ? round($count / $totalTim * 100) : 0; @endphp
                    <div class="mb-3 admin-tim-row" role="button" data-tim="{{ $tim }}"
                         style="cursor:pointer;padding:8px;border-radius:8px;transition:background .15s;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-medium" style="font-size:14px;">{{ $tim ?: '—' }}</span>
                            <span class="text-muted" style="font-size:14px;">{{ $count }} project</span>
                        </div>
                        <div class="progress rounded-pill" style="height:8px;background:#e9ecef;">
                            <div class="progress-bar rounded-pill bg-primary"
                                 style="width:{{ $pct }}%;transition:width .6s ease;"></div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center text-muted py-4" style="font-size:14px;">Tidak ada data</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Detail Tim Card --}}
<div class="card border-0 shadow-sm mb-4" id="timListCard" style="display:none;">
    <div class="card-header bg-white border-0 px-3 pt-3 pb-2">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="fw-semibold" id="timListTitle" style="font-size:16px;"></div>
                <div class="text-muted" id="timListSubtitle" style="font-size:14px;"></div>
            </div>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" id="timListClose"
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
                        <th class="py-3 border-0 text-muted fw-semibold">Estimasi Selesai</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Progress</th>
                        <th class="py-3 border-0"></th>
                    </tr>
                </thead>
                <tbody id="timListBody"></tbody>
            </table>
        </div>
        <div class="d-flex align-items-center justify-content-between px-3 py-3 border-top">
            <div class="text-muted" id="timListInfo" style="font-size:14px;"></div>
            <div class="d-flex gap-1" id="timListPagination"></div>
        </div>
    </div>
</div>

{{-- Chart Trend --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="fw-semibold mb-1" style="font-size:16px;">Trend Project Masuk vs Selesai</div>
        <div class="text-muted mb-3" style="font-size:14px;">6 bulan terakhir</div>
        <canvas id="chartTrend" style="max-height:260px;"></canvas>
    </div>
</div>

{{-- Workload per BA + Developer --}}
<div class="row g-3 mb-4">

    {{-- Workload per BA --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="fw-semibold mb-1" style="font-size:16px;">Workload BA</div>
                <div class="text-muted mb-3" style="font-size:14px;">Klik nama BA untuk lihat detail project</div>
                @if($perBaDetail->count() > 0)
                    @php $totalBa = $perBaDetail->sum('count'); @endphp
                    @foreach($perBaDetail as $ba => $data)
                    @php $pct = $totalBa > 0 ? round($data['count'] / $totalBa * 100) : 0; @endphp
                    <div class="mb-3 pb-3 border-bottom admin-ba-row" role="button"
                         data-ba="{{ $ba }}"
                         style="cursor:pointer;padding:8px;border-radius:8px;transition:background .15s;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold" style="font-size:15px;">{{ $ba }}</span>
                            <span class="badge rounded-pill bg-primary" style="font-size:13px;">
                                {{ $data['count'] }} project
                            </span>
                        </div>
                        <div class="progress rounded-pill" style="height:6px;background:#e9ecef;">
                            <div class="progress-bar rounded-pill bg-primary"
                                 style="width:{{ $pct }}%;transition:width .6s ease;"></div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center text-muted py-4" style="font-size:14px;">Tidak ada data</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Workload per Developer --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="fw-semibold mb-1" style="font-size:16px;">Workload Developer</div>
                <div class="text-muted mb-3" style="font-size:14px;">Klik nama Developer untuk lihat detail project</div>
                @if($perDeveloperDetail->count() > 0)
                    @php $totalDev = $perDeveloperDetail->sum('count'); @endphp
                    @foreach($perDeveloperDetail as $dev => $data)
                    @php $pct = $totalDev > 0 ? round($data['count'] / $totalDev * 100) : 0; @endphp
                    <div class="mb-3 pb-3 border-bottom admin-dev-row" role="button"
                         data-dev="{{ $dev }}"
                         style="cursor:pointer;padding:8px;border-radius:8px;transition:background .15s;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold" style="font-size:15px;">{{ $dev }}</span>
                            <span class="badge rounded-pill" style="font-size:13px;background:#6f42c1;">
                                {{ $data['count'] }} project
                            </span>
                        </div>
                        <div class="progress rounded-pill" style="height:6px;background:#e9ecef;">
                            <div class="progress-bar rounded-pill"
                                 style="width:{{ $pct }}%;background:#6f42c1;transition:width .6s ease;"></div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center text-muted py-4" style="font-size:14px;">Tidak ada data</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Detail BA/Developer Card --}}
<div class="card border-0 shadow-sm mb-4" id="personListCard" style="display:none;">
    <div class="card-header bg-white border-0 px-3 pt-3 pb-2">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="fw-semibold" id="personListTitle" style="font-size:16px;"></div>
                <div class="text-muted" id="personListSubtitle" style="font-size:14px;"></div>
            </div>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" id="personListClose"
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
                        <th class="py-3 border-0 text-muted fw-semibold">Estimasi Selesai</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Progress</th>
                        <th class="py-3 border-0"></th>
                    </tr>
                </thead>
                <tbody id="personListBody"></tbody>
            </table>
        </div>
        <div class="d-flex align-items-center justify-content-between px-3 py-3 border-top">
            <div class="text-muted" id="personListInfo" style="font-size:14px;"></div>
            <div class="d-flex gap-1" id="personListPagination"></div>
        </div>
    </div>
</div>

{{-- Project Telat --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 px-3 pt-3 pb-2">
        <div class="fw-semibold" style="font-size:16px;">⚠️ Project Telat</div>
        <div class="text-muted" style="font-size:14px;">Project aktif yang sudah melewati estimasi selesai</div>
    </div>
    <div class="card-body p-0">
        @if($projectTelat->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:14px;">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th class="px-3 py-3 border-0 text-muted fw-semibold">No PPSMB</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Nama Project</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Tim</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Status</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Estimasi Selesai</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Keterlambatan</th>
                        <th class="py-3 border-0"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projectTelat as $pt)
                    @php $sc = config('status.colors')[$pt['ppsmb']->status] ?? '#6c757d'; @endphp
                    <tr class="border-top table-danger">
                        <td class="px-3 py-3">
                            <span class="text-muted" style="font-size:13px;">{{ $pt['ppsmb']->no_ppsmb ?? '—' }}</span>
                        </td>
                        <td class="py-3 fw-medium">{{ $pt['ppsmb']->nama_project }}</td>
                        <td class="py-3" style="font-size:14px;">{{ $pt['ppsmb']->tim ?? '—' }}</td>
                        <td class="py-3">
                            <span class="px-2 py-1 rounded"
                                  style="font-size:13px;background:{{ $sc }};color:white;white-space:nowrap;">
                                {{ $pt['ppsmb']->status }}
                            </span>
                        </td>
                        <td class="py-3" style="font-size:14px;">{{ $pt['estimasi_formatted'] }}</td>
                        <td class="py-3">
                            <span class="fw-bold text-danger" style="font-size:14px;">{{ $pt['telat_hari'] }} hari</span>
                        </td>
                        <td class="py-3 pe-3">
                            <a href="{{ route('ppsmbbyit.show', $pt['ppsmb']->id) }}"
                               class="btn btn-sm btn-info" style="font-size:14px;">Rincian</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="d-flex flex-column align-items-center justify-content-center text-center py-4">
            <i class="bi bi-check-circle text-success mb-2" style="font-size:32px;"></i>
            <div style="font-size:14px;font-weight:500;">Tidak ada project yang telat</div>
        </div>
        @endif
    </div>
</div>

{{-- Aktivitas Terbaru --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 px-3 pt-3 pb-2">
        <div class="fw-semibold" style="font-size:16px;">Aktivitas Terbaru</div>
        <div class="text-muted" style="font-size:14px;">10 perubahan status terakhir</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:14px;">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th class="px-3 py-3 border-0 text-muted fw-semibold">Waktu</th>
                        <th class="py-3 border-0 text-muted fw-semibold">No PPSMB</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Nama Project</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Status Baru</th>
                        <th class="py-3 border-0"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($aktivitasTerbaru as $ak)
                    @php $sc = config('status.colors')[$ak['log']->status] ?? '#6c757d'; @endphp
                    <tr class="border-top">
                        <td class="px-3 py-3 text-muted" style="font-size:13px;white-space:nowrap;">
                            {{ $ak['waktu_formatted'] }}
                        </td>
                        <td class="py-3">
                            <span class="text-muted" style="font-size:13px;">{{ $ak['log']->ppsmb->no_ppsmb ?? '—' }}</span>
                        </td>
                        <td class="py-3 fw-medium">{{ $ak['log']->ppsmb->nama_project ?? '—' }}</td>
                        <td class="py-3">
                            <span class="px-2 py-1 rounded"
                                  style="font-size:13px;background:{{ $sc }};color:white;white-space:nowrap;">
                                {{ $ak['log']->status }}
                            </span>
                        </td>
                        <td class="py-3 pe-3">
                            @if($ak['log']->ppsmb)
                            <a href="{{ route('ppsmbbyit.show', $ak['log']->ppsmb->id) }}"
                               class="btn btn-sm btn-info" style="font-size:14px;">Rincian</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {

    const allProjects = @json($allPpsmbJson);
    const statusAktif = @json($statusAktif);
    const baseUrl     = '{{ url("/ppsmbbyit") }}';
    const perPage     = 5;

    // ── helpers ───────────────────────────────────────────────
    function renderPagination(list, page, infoEl, pagEl, onPage) {
        const total = list.length;
        const pages = Math.max(1, Math.ceil(total / perPage));
        const start = (page - 1) * perPage;
        const end   = Math.min(start + perPage, total);
        infoEl.textContent = total === 0
            ? 'Tidak ada project'
            : `Menampilkan ${start + 1}–${end} dari ${total} project`;
        pagEl.innerHTML = '';
        for (let p = 1; p <= pages; p++) {
            const btn = document.createElement('button');
            btn.className = `btn btn-sm rounded-pill ${p === page ? 'btn-dark' : 'btn-outline-secondary'}`;
            btn.style.cssText = 'font-size:13px;width:30px;padding:0;height:28px;';
            btn.textContent = p;
            btn.addEventListener('click', () => onPage(p));
            pagEl.appendChild(btn);
        }
        return list.slice(start, end);
    }

    function renderRow(p, showTim = false) {
        const timCol = showTim
            ? `<td class="py-3" style="font-size:14px;">${p.tim}</td>` : '';
        return `
            <tr class="border-top">
                <td class="px-3 py-3"><span class="text-muted" style="font-size:13px;">${p.no_ppsmb}</span></td>
                <td class="py-3 fw-medium">${p.nama_project}</td>
                ${timCol}
                <td class="py-3">
                    <span class="px-2 py-1 rounded"
                          style="font-size:13px;background:${p.color};color:white;white-space:nowrap;">
                        ${p.status}
                    </span>
                </td>
                <td class="py-3" style="font-size:14px;">${p.estimasi_selesai}</td>
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
                    <a href="${baseUrl}/${p.id}" class="btn btn-sm btn-info" style="font-size:14px;">Rincian</a>
                </td>
            </tr>`;
    }

    // ── Summary Cards ─────────────────────────────────────────
    let summaryPage = 1, summaryList = [], activeSummaryKey = null;
    const summaryCard     = document.getElementById('summaryListCard');
    const summaryBody     = document.getElementById('summaryListBody');
    const summaryTitle    = document.getElementById('summaryListTitle');
    const summarySubtitle = document.getElementById('summaryListSubtitle');
    const summaryInfo     = document.getElementById('summaryListInfo');
    const summaryPag      = document.getElementById('summaryListPagination');

    const summaryTitleMap = {
        all: 'Semua Project', aktif: 'Project Aktif',
        done: 'Done (Live)',  rejected: 'Rejected',
    };

    function renderSummaryList(page) {
        summaryPage = page;
        const slice = renderPagination(summaryList, page, summaryInfo, summaryPag, renderSummaryList);
        summaryBody.innerHTML = slice.map(p => renderRow(p, true)).join('');
    }

    document.querySelectorAll('.admin-summary-card').forEach(card => {
        card.addEventListener('click', function () {
            const key = this.dataset.key;
            if (activeSummaryKey === key) {
                summaryCard.style.display = 'none';
                activeSummaryKey = null;
                resetCards('.admin-summary-card');
                return;
            }
            activeSummaryKey = key;
            resetCards('.admin-summary-card');
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 6px 20px rgba(0,0,0,.1)';

            summaryList = allProjects.filter(p => {
                if (key === 'all')      return true;
                if (key === 'aktif')    return statusAktif.includes(p.status);
                if (key === 'done')     return p.status === 'Done (Live)';
                if (key === 'rejected') return p.status === 'Rejected';
                return true;
            });

            summaryTitle.textContent    = summaryTitleMap[key] || key;
            summarySubtitle.textContent = `${summaryList.length} project`;
            summaryCard.style.display   = '';
            renderSummaryList(1);
            summaryCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
        card.addEventListener('mouseenter', function () {
            if (activeSummaryKey !== this.dataset.key) {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 6px 20px rgba(0,0,0,.1)';
            }
        });
        card.addEventListener('mouseleave', function () {
            if (activeSummaryKey !== this.dataset.key) {
                this.style.transform = ''; this.style.boxShadow = '';
            }
        });
    });

    document.getElementById('summaryListClose').addEventListener('click', () => {
        summaryCard.style.display = 'none';
        activeSummaryKey = null;
        resetCards('.admin-summary-card');
    });

    // ── Workload per Tim ──────────────────────────────────────
    let timPage = 1, timList = [], activeTimKey = null;
    const timCard  = document.getElementById('timListCard');
    const timBody  = document.getElementById('timListBody');
    const timTitle = document.getElementById('timListTitle');
    const timSub   = document.getElementById('timListSubtitle');
    const timInfo  = document.getElementById('timListInfo');
    const timPag   = document.getElementById('timListPagination');

    function renderTimList(page) {
        timPage = page;
        const slice = renderPagination(timList, page, timInfo, timPag, renderTimList);
        timBody.innerHTML = slice.map(p => renderRow(p, false)).join('');
    }

    document.querySelectorAll('.admin-tim-row').forEach(row => {
        row.addEventListener('click', function () {
            const tim = this.dataset.tim;
            if (activeTimKey === tim) {
                timCard.style.display = 'none';
                activeTimKey = null;
                this.style.background = '';
                return;
            }
            document.querySelectorAll('.admin-tim-row').forEach(r => r.style.background = '');
            activeTimKey = tim;
            this.style.background = '#f0f4ff';

            timList = allProjects.filter(p =>
                statusAktif.includes(p.status) && p.tim === tim
            );
            timTitle.textContent  = `Tim ${tim}`;
            timSub.textContent    = `${timList.length} project aktif`;
            timCard.style.display = '';
            renderTimList(1);
            timCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
        row.addEventListener('mouseenter', function () {
            if (activeTimKey !== this.dataset.tim) this.style.background = '#f8f9fa';
        });
        row.addEventListener('mouseleave', function () {
            if (activeTimKey !== this.dataset.tim) this.style.background = '';
        });
    });

    document.getElementById('timListClose').addEventListener('click', () => {
        timCard.style.display = 'none';
        activeTimKey = null;
        document.querySelectorAll('.admin-tim-row').forEach(r => r.style.background = '');
    });

    // ── Workload BA & Developer ───────────────────────────────
    let personPage = 1, personList = [], activePersonKey = null;
    const personCard  = document.getElementById('personListCard');
    const personBody  = document.getElementById('personListBody');
    const personTitle = document.getElementById('personListTitle');
    const personSub   = document.getElementById('personListSubtitle');
    const personInfo  = document.getElementById('personListInfo');
    const personPag   = document.getElementById('personListPagination');

    function renderPersonList(page) {
        personPage = page;
        const slice = renderPagination(personList, page, personInfo, personPag, renderPersonList);
        personBody.innerHTML = slice.map(p => renderRow(p, false)).join('');
    }

    function handlePersonClick(key, name, filterFn) {
        if (activePersonKey === key) {
            personCard.style.display = 'none';
            activePersonKey = null;
            resetPersonRows();
            return;
        }
        resetPersonRows();
        activePersonKey = key;
        const el = document.querySelector(`[data-ba="${name}"], [data-dev="${name}"]`);
        if (el) el.style.background = '#f0f4ff';

        personList = allProjects.filter(filterFn);
        personTitle.textContent  = name;
        personSub.textContent    = `${personList.length} project aktif`;
        personCard.style.display = '';
        renderPersonList(1);
        personCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function resetPersonRows() {
        document.querySelectorAll('.admin-ba-row, .admin-dev-row').forEach(r => r.style.background = '');
    }

    document.querySelectorAll('.admin-ba-row').forEach(row => {
        row.addEventListener('click', function () {
            const ba = this.dataset.ba;
            handlePersonClick('ba_' + ba, ba, p => statusAktif.includes(p.status) && p.pic_ba === ba);
        });
        row.addEventListener('mouseenter', function () {
            if (activePersonKey !== 'ba_' + this.dataset.ba) this.style.background = '#f8f9fa';
        });
        row.addEventListener('mouseleave', function () {
            if (activePersonKey !== 'ba_' + this.dataset.ba) this.style.background = '';
        });
    });

    document.querySelectorAll('.admin-dev-row').forEach(row => {
        row.addEventListener('click', function () {
            const dev = this.dataset.dev;
            handlePersonClick('dev_' + dev, dev, p => statusAktif.includes(p.status) && p.developer === dev);
        });
        row.addEventListener('mouseenter', function () {
            if (activePersonKey !== 'dev_' + this.dataset.dev) this.style.background = '#f8f9fa';
        });
        row.addEventListener('mouseleave', function () {
            if (activePersonKey !== 'dev_' + this.dataset.dev) this.style.background = '';
        });
    });

    document.getElementById('personListClose').addEventListener('click', () => {
        personCard.style.display = 'none';
        activePersonKey = null;
        resetPersonRows();
    });

    // ── helper reset cards ────────────────────────────────────
    function resetCards(selector) {
        document.querySelectorAll(selector).forEach(c => {
            c.style.transform = ''; c.style.boxShadow = '';
        });
    }

    // ── Charts ────────────────────────────────────────────────
    const ctxStatus = document.getElementById('chartStatus').getContext('2d');
    new Chart(ctxStatus, {
        type: 'bar',
        data: {
            labels: @json($statusLabels),
            datasets: [{
                data: @json($statusValues),
                backgroundColor: @json($statusColors),
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.raw} project` } }
            },
            scales: {
                x: { ticks: { font: { size: 12 } }, grid: { display: false } },
                y: { ticks: { stepSize: 1, font: { size: 12 } }, grid: { color: '#f0f0f0' } }
            }
        }
    });

    const ctxTrend = document.getElementById('chartTrend').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: @json($trendLabels),
            datasets: [
                {
                    label: 'Project Masuk',
                    data: @json($trendMasuk),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.08)',
                    borderWidth: 2,
                    pointBackgroundColor: '#0d6efd',
                    pointRadius: 4,
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'Project Selesai',
                    data: @json($trendSelesai),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25,135,84,0.08)',
                    borderWidth: 2,
                    pointBackgroundColor: '#198754',
                    pointRadius: 4,
                    tension: 0.4,
                    fill: true,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'top', labels: { font: { size: 13 }, usePointStyle: true } },
                tooltip: { callbacks: { label: ctx => ` ${ctx.raw} project` } }
            },
            scales: {
                x: { ticks: { font: { size: 12 } }, grid: { display: false } },
                y: { ticks: { stepSize: 1, font: { size: 12 } }, grid: { color: '#f0f0f0' }, beginAtZero: true }
            }
        }
    });

})();
</script>
@endpush