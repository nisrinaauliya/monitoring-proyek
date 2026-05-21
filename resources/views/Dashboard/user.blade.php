@extends('layouts.app')

@section('title', 'Dashboard - Sistem Helpdesk')
@section('page_title', 'Dashboard')

@section('content')

{{-- Warning Banner --}}
@if($showWarning)
<div class="alert alert-danger d-flex align-items-center gap-2 mb-4 border-0 rounded-3" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div>
        <div class="fw-semibold">Pengajuan Project Baru Diblokir</div>
        <div style="font-size:14px;">
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
            @foreach($summaryCards as $sc)
            <div class="col-6 col-sm-3">
                <div class="card border-0 shadow-sm h-100 summary-card"
                     role="button"
                     data-filter="{{ $sc['filter'] }}"
                     style="cursor:pointer; transition:transform .15s, box-shadow .15s;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div class="fw-bold mb-1" style="font-size:30px; color:{{ $sc['color'] }}; line-height:1;">
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

        {{-- Donut + Aging --}}
        <div class="row g-3">

            {{-- Donut Chart --}}
            <div class="col-md-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="fw-semibold mb-1" style="font-size:16px;">Distribusi Status Project</div>
                        <div class="text-muted mb-3" style="font-size:14px;">Klik segmen untuk filter daftar project</div>
                        <div class="d-flex justify-content-center">
                            <canvas id="donutChart" style="max-height:180px; max-width:180px; cursor:pointer;"></canvas>
                        </div>
                        <div id="donutLegend"
                             class="mt-3 flex-grow-1 overflow-auto pe-1"
                             style="display:grid; grid-template-columns:1fr 1fr; gap:2px 8px; max-height:130px;">
                        </div>
                    </div>
                </div>
            </div>

            {{-- UAT Aging Tracker --}}
            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="fw-semibold" style="font-size:16px;">Aging Tracker — UAT</div>
                                <div class="text-muted" style="font-size:14px;">Batas ideal: 10 hari</div>
                            </div>
                            <span class="badge bg-warning text-dark" style="font-size:14px;">
                                {{ count($uatAging) }} project
                            </span>
                        </div>

                        <div class="flex-grow-1 overflow-auto pe-1" style="max-height:310px;">
                            @forelse($uatAging as $ua)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div>
                                            <div style="font-size:16px; font-weight:500;">{{ $ua['ppsmb']->nama_project }}</div>
                                            <div class="text-muted" style="font-size:14px;">Masuk UAT: {{ $ua['masuk_uat'] }}</div>
                                        </div>
                                        <div class="text-end flex-shrink-0 ms-2">
                                            <span class="fw-bold {{ $ua['txtCls'] }}" style="font-size:18px;">{{ $ua['hari'] }}</span>
                                            <span class="text-muted" style="font-size:14px;"> hari</span>
                                            <div>
                                                <span class="badge {{ $ua['badgeCls'] }}" style="font-size:14px;">{{ $ua['badgeTxt'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress rounded-pill" style="height:6px;">
                                        <div class="progress-bar {{ $ua['barCls'] }} rounded-pill" style="width:{{ $ua['pct'] }}%;"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span class="text-muted" style="font-size:14px;">0</span>
                                        <span class="text-muted" style="font-size:14px;">10 hari</span>
                                    </div>
                                </div>
                            @empty
                                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center py-4">
                                    <i class="bi bi-check-circle text-success mb-2" style="font-size:32px;"></i>
                                    <div style="font-size:16px; font-weight:500;">Clear!</div>
                                    <div class="text-muted" style="font-size:14px;">Tidak ada project di tahap UAT.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- KANAN: Sidebar --}}
    <div class="col-md-3">

        {{-- Status Departemen --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="fw-semibold mb-2" style="font-size:16px;">Status Departemen</div>
                @if($showWarning)
                    <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#fff3f3;">
                        <i class="bi bi-x-circle-fill text-danger"></i>
                        <div>
                            <div class="fw-semibold text-danger" style="font-size:16px;">Pengajuan project baru diblokir</div>
                            <div class="text-muted" style="font-size:14px;">Project UAT melebihi batas aging 10 hari</div>
                        </div>
                    </div>
                @else
                    <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#f0fff6;">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <div>
                            <div class="fw-semibold text-success" style="font-size:16px;">Aman</div>
                            <div class="text-muted" style="font-size:14px;">Pengajuan project baru dapat dilakukan</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Countdown Revisi --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="fw-semibold mb-1" style="font-size:16px;">
                    <i class="bi bi-hourglass-split me-1 text-secondary"></i>Countdown Revisi
                </div>
                <div class="text-muted mb-3" style="font-size:14px;">Auto reject di hari ke-30</div>

                @forelse($revisiCountdown as $rc)
                {{-- Project milik user sendiri --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span style="font-size:16px; font-weight:500; max-width:140px;" class="text-truncate">
                            {{ $rc['ppsmb']->nama_project }}
                        </span>
                        <span class="fw-bold {{ $rc['txtCls'] }}" style="font-size:18px;">
                            {{ $rc['sisa_hari'] }}<span class="text-muted fw-normal" style="font-size:14px;"> hari lagi</span>
                        </span>
                    </div>
                    <div class="progress rounded-pill" style="height:5px; background:#e9ecef;">
                        <div class="progress-bar {{ $rc['barCls'] }} rounded-pill" style="width:{{ $rc['pct'] }}%;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-muted" style="font-size:14px;">Hari ke-{{ $rc['hari_ke'] }}</span>
                        <a href="{{ route('ppsmbbyuser.edit', $rc['ppsmb']->id) }}"
                        style="font-size:14px; color:#0d6efd;">Revisi sekarang →</a>
                    </div>
                </div>

            @empty
                {{-- Cek apakah ada milik orang lain --}}
                @if($revisiCountdownOther->isEmpty())
                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-3">
                        <i class="bi bi-check-circle text-success mb-2" style="font-size:32px;"></i>
                        <div style="font-size:16px; font-weight:500;">Aman</div>
                        <div class="text-muted" style="font-size:14px;">Tidak ada project revisi saat ini</div>
                    </div>
                @endif
            @endforelse

            {{-- Project revisi milik anggota departemen lain --}}
            @foreach($revisiCountdownOther as $rc)
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span style="font-size:16px; font-weight:500; max-width:140px;" class="text-truncate">
                            {{ $rc['ppsmb']->nama_project }}
                        </span>
                        <span class="fw-bold {{ $rc['txtCls'] }}" style="font-size:18px;">
                            {{ $rc['sisa_hari'] }}<span class="text-muted fw-normal" style="font-size:14px;"> hari lagi</span>
                        </span>
                    </div>
                    <div class="progress rounded-pill" style="height:5px; background:#e9ecef;">
                        <div class="progress-bar {{ $rc['barCls'] }} rounded-pill" style="width:{{ $rc['pct'] }}%;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-muted" style="font-size:14px;">Hari ke-{{ $rc['hari_ke'] }}</span>
                        {{-- Bukan milik user ini → trigger modal, bukan link edit --}}
                        <a href="#"
                        style="font-size:14px; color:#0d6efd;"
                        onclick="showWrongUserModal('{{ addslashes($rc['ppsmb']->user->name) }}'); return false;">
                        Revisi sekarang →</a>
                    </div>
                </div>
            @endforeach
            </div>
        </div>

        {{-- Riwayat Auto Reject --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="fw-semibold mb-3" style="font-size:16px;">Riwayat Auto Reject</div>
                @forelse($autoRejected as $ar)
                    <div class="d-flex align-items-start gap-2 mb-2 pb-2 border-bottom">
                        <div class="rounded-circle bg-danger flex-shrink-0 mt-2" style="width:8px; height:8px;"></div>
                        <div>
                            <div class="text-muted" style="font-size:16px; font-weight:500;">{{ $ar['nama_project'] }}</div>
                            <div class="text-muted" style="font-size:14px;">{{ $ar['tanggal'] }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-2">
                        <i class="bi bi-check-circle text-success mb-1" style="font-size:32px;"></i>
                        <div class="text-muted" style="font-size:14px;">Tidak ada riwayat auto reject</div>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>{{-- end row --}}

{{-- Daftar Project --}}
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white border-0 px-3 pt-3 pb-3">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="fw-semibold" style="font-size:16px;">Daftar Project</div>
                <div class="text-muted" id="tableSubtitle" style="font-size:14px;">Menampilkan semua project</div>
            </div>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" id="resetFilter"
                    style="font-size:14px; display:none;">
                <i class="bi bi-x me-1"></i>Reset Filter
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:14px;">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th class="px-3 py-3 border-0 text-muted fw-semibold">No PPSMB</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Nama Project</th>
                        <th class="py-3 border-0 text-muted fw-semibold">User</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Status</th>
                        <th class="py-3 border-0 text-muted fw-semibold">Aging</th>
                        <th class="py-3 border-0"></th>
                    </tr>
                </thead>
                <tbody id="projectTableBody">
                    @foreach($ppsmbs as $ppsmb)
                        @php
                            $sc      = config('status.colors')[$ppsmb->status] ?? '#6c757d';
                            $isAktif = !in_array($ppsmb->status, ['Done (Live)', 'Rejected']);
                        @endphp
                        <tr class="border-top project-row"
                            data-status="{{ $ppsmb->status }}"
                            data-aktif="{{ $isAktif ? 'true' : 'false' }}">
                            <td class="px-3 py-3">
                                <span class="text-muted">{{ $ppsmb->no_ppsmb ?? '—' }}</span>
                            </td>
                            <td class="py-3 fw-medium">{{ $ppsmb->nama_project }}</td>
                            <td class="py-3 fw-medium">{{ $ppsmb->user->name }}</td>
                            <td class="py-3">
                                <span class="px-2 py-1 rounded"
                                    style="background:{{ $sc }}; color:white; white-space:nowrap;">
                                    {{ $ppsmb->status }}
                                </span>
                            </td>
                            <td class="py-3">
                                @if($ppsmb->aging_hari !== null)
                                    <span style="color:{{ $ppsmb->aging_hari > 10 ? '#dc3545' : '#6c757d' }};
                                                font-weight:{{ $ppsmb->aging_hari > 10 ? '600' : '400' }};">
                                        {{ $ppsmb->aging_hari }} hari
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="py-3 pe-3">
                                <div class="d-flex flex-column gap-1">
                                    @if($ppsmb->status === 'Revisi User' && $ppsmb->user_id === Auth::id())
                                        <a href="{{ route('ppsmbbyuser.edit', $ppsmb->id) }}"
                                        class="btn btn-sm btn-warning text-white" style="width:70px;">Edit</a>
                                    @endif
                                    <a href="{{ route('ppsmbbyuser.show', $ppsmb->id) }}"
                                    class="btn btn-sm btn-info text-white" style="width:70px;">Rincian</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex align-items-center justify-content-between px-3 py-3 border-top">
            <div class="text-muted" id="paginationInfo" style="font-size:14px;"></div>
            <div class="d-flex gap-1" id="paginationBtns"></div>
        </div>
    </div>
</div>

{{-- Modal: akses revisi salah user --}}
<div class="modal fade" id="wrongUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-sm rounded-4">
            <div class="modal-body p-4 text-center">
                <i class="bi bi-person-lock text-warning mb-3" style="font-size:36px;"></i>
                <div class="fw-semibold mb-1" style="font-size:16px;">Bukan project Anda</div>
                <div class="text-muted mb-4" id="wrongUserMsg" style="font-size:14px;"></div>
                <button class="btn btn-sm btn-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    // ── DATA ──────────────────────────────────────────────
    const labels = [
        @foreach(config('status.colors') as $status => $color)
            '{{ $status }}',
        @endforeach
    ];
    const data = [
        @foreach(config('status.colors') as $status => $color)
            {{ $ppsmbs->where('status', $status)->count() }},
        @endforeach
    ];
    const colors = [
        @foreach(config('status.colors') as $status => $color)
            '{{ $color }}',
        @endforeach
    ];
    const total = {{ $total }};

    // ── DONUT CHART ───────────────────────────────────────
    const canvas = document.getElementById('donutChart');
    const state  = { val: total, lbl: 'Total' };

    const chart = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 8,
            }],
        },
        options: {
            cutout: '68%',
            plugins: {
                legend:  { display: false },
                tooltip: { enabled: false },
            },
            onClick(evt, elements) {
                applyFilter(elements.length ? labels[elements[0].index] : 'all');
            },
            onHover(evt, elements) {
                canvas.style.cursor = elements.length ? 'pointer' : 'default';
                if (elements.length) {
                    const i   = elements[0].index;
                    const pct = total > 0 ? Math.round(data[i] / total * 100) : 0;
                    state.val = pct + '%';
                    state.lbl = labels[i].length > 13 ? labels[i].slice(0, 12) + '…' : labels[i];
                } else {
                    state.val = total;
                    state.lbl = 'Total';
                }
                chart.draw();
            },
        },
        plugins: [{
            id: 'center',
            afterDraw(c) {
                const { ctx, chartArea: { left, top, right, bottom } } = c;
                const cx = (left + right) / 2;
                const cy = (top + bottom) / 2;
                ctx.save();
                ctx.textAlign    = 'center';
                ctx.textBaseline = 'middle';
                ctx.font      = 'bold 24px sans-serif';
                ctx.fillStyle = '#212529';
                ctx.fillText(state.val, cx, cy - 9);
                ctx.font      = '10px sans-serif';
                ctx.fillStyle = '#6c757d';
                ctx.fillText(state.lbl, cx, cy + 11);
                ctx.restore();
            },
        }],
    });

    // ── LEGEND ────────────────────────────────────────────
    const legendEl = document.getElementById('donutLegend');
    labels.forEach((lbl, i) => {
        if (data[i] === 0) return;
        const pct = total > 0 ? Math.round(data[i] / total * 100) : 0;
        const row = document.createElement('div');
        row.className = 'd-flex align-items-center justify-content-between py-1';
        row.style.cssText = 'font-size:16px; cursor:pointer;';
        row.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <span style="width:8px; height:8px; border-radius:50%; background:${colors[i]}; flex-shrink:0; display:inline-block;"></span>
                <span class="text-muted">${lbl}</span>
            </div>
            <span style="font-weight:600; color:${colors[i]};">
                ${data[i]} <span style="color:#aaa; font-weight:400;">(${pct}%)</span>
            </span>
        `;
        row.addEventListener('click', () => applyFilter(lbl));
        legendEl.appendChild(row);
    });

    // ── FILTER + PAGINATION ───────────────────────────────
    const allRows    = Array.from(document.querySelectorAll('.project-row'));
    const perPage    = 5;
    let currentPage  = 1;
    let activeFilter = 'all';

    function getVisibleRows() {
        return allRows.filter(row => {
            if (activeFilter === 'all')   return true;
            if (activeFilter === 'aktif') return row.dataset.aktif === 'true';
            return row.dataset.status === activeFilter;
        });
    }

    function renderTable() {
        const visible = getVisibleRows();
        const pages   = Math.max(1, Math.ceil(visible.length / perPage));
        currentPage   = Math.min(currentPage, pages);
        const start   = (currentPage - 1) * perPage;
        const end     = start + perPage;

        allRows.forEach(r => r.style.display = 'none');
        visible.slice(start, end).forEach(r => r.style.display = '');

        // Info
        const info = document.getElementById('paginationInfo');
        info.textContent = visible.length === 0
            ? 'Tidak ada project'
            : `Menampilkan ${start + 1}–${Math.min(end, visible.length)} dari ${visible.length} project`;

        // Subtitle
        const sub = document.getElementById('tableSubtitle');
        sub.textContent = activeFilter === 'all'   ? 'Menampilkan semua project'
                        : activeFilter === 'aktif' ? 'Project Aktif'
                        : activeFilter;

        // Reset button
        document.getElementById('resetFilter').style.display = activeFilter !== 'all' ? '' : 'none';

        // Pagination buttons
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
        document.getElementById('projectTableBody')
            .closest('.card')
            .scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Summary card events
    document.querySelectorAll('.summary-card').forEach(card => {
        card.addEventListener('click', () => applyFilter(card.dataset.filter));
        card.addEventListener('mouseenter', () => {
            card.style.transform  = 'translateY(-2px)';
            card.style.boxShadow  = '0 6px 20px rgba(0,0,0,.1)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
            card.style.boxShadow = '';
        });
    });

    document.getElementById('resetFilter').addEventListener('click', () => applyFilter('all'));

    renderTable();
})();

function showWrongUserModal(pengaju) {
        document.getElementById('wrongUserMsg').textContent =
            'Login sebagai ' + pengaju + ' untuk merevisi project ini.';
        new bootstrap.Modal(document.getElementById('wrongUserModal')).show();
}

</script>
@endpush