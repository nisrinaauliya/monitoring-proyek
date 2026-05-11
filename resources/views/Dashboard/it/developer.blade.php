@extends('layouts.app')

@section('title', 'Dashboard - Sistem Helpdesk')
@section('page_title', 'Dashboard')

@section('content')

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    @foreach($summaryCards as $sc)
    <div class="col-6 col-sm-3">
        <div class="card border-0 shadow-sm h-100 dev-summary-card" role="button"
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
                        <th class="py-3 border-0 text-muted fw-semibold">Status</th>
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

{{-- Workload & Timeline --}}
<div class="row g-3 mb-4">

    {{-- Workload Development --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="fw-semibold mb-1" style="font-size:16px;">Workload Development</div>
                <div class="text-muted mb-3" style="font-size:14px;">Project yang sedang dalam proses development</div>
                @php $workloadList = $projectList->filter(fn($pl) => $pl['ppsmb']->status === 'Proses Development'); @endphp
                @if($workloadList->count() > 0)
                    @foreach($workloadList as $pl)
                    @php
                        $p  = $pl['ppsmb'];
                        $sc = config('status.colors')[$p->status] ?? '#6c757d';
                    @endphp
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <div class="fw-medium" style="font-size:16px;">{{ $p->nama_project }}</div>
                                <div class="text-muted" style="font-size:14px;">{{ $p->no_ppsmb ?? '—' }}</div>
                            </div>
                            <span class="fw-bold" style="font-size:18px;color:{{ $sc }};">
                                {{ number_format($p->progress, 0) }}%
                            </span>
                        </div>
                        <div class="progress rounded-pill" style="height:8px;background:#e9ecef;">
                            <div class="progress-bar rounded-pill"
                                 style="width:{{ $p->progress }}%;background:{{ $sc }};transition:width .6s ease;"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="text-muted" style="font-size:14px;">Progress</span>
                            <a href="{{ route('ppsmbbyit.show', $p->id) }}"
                               class="btn btn-sm btn-info mt-1" style="font-size:14px;">Rincian</a>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-4">
                        <i class="bi bi-check-circle text-success mb-2" style="font-size:32px;"></i>
                        <div style="font-size:16px;font-weight:500;">Tidak ada project development aktif</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="fw-semibold mb-1" style="font-size:16px;">Timeline Project</div>
                <div class="text-muted mb-3" style="font-size:14px;">Diurutkan dari estimasi selesai terdekat</div>
                @php
                    $timelineList = $projectList
                        ->filter(fn($pl) => $pl['ppsmb']->estimasi_selesai !== null)
                        ->sortBy(fn($pl) => $pl['ppsmb']->estimasi_selesai)
                        ->values();
                @endphp
                @if($timelineList->count() > 0)
                <div class="overflow-auto" style="max-height:380px;">
                    @foreach($timelineList as $pl)
                    @php
                        $p  = $pl['ppsmb'];
                        $sc = config('status.colors')[$p->status] ?? '#6c757d';
                        $h  = $pl['sisa_hari'];
                    @endphp
                    <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                        <div class="d-flex flex-column align-items-center flex-shrink-0" style="width:12px;margin-top:4px;">
                            <div class="rounded-circle mt-1" style="width:10px;height:10px;background:{{ $sc }};flex-shrink:0;"></div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium" style="font-size:16px;">{{ $p->nama_project }}</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="px-2 py-1 rounded"
                                      style="font-size:14px;background:{{ $sc }};color:white;white-space:nowrap;">
                                    {{ $p->status }}
                                </span>
                            </div>
                            <div class="text-muted mt-1" style="font-size:14px;">
                                Estimasi: {{ $p->estimasi_selesai_formatted ?? '—' }}
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            @if($pl['telat'])
                                <div class="fw-bold text-danger" style="font-size:18px;">{{ abs($h) }}</div>
                                <div class="text-danger" style="font-size:14px;">hari telat</div>
                            @else
                                <div class="fw-bold {{ $h <= 7 ? 'text-warning' : 'text-muted' }}" style="font-size:18px;">{{ $h }}</div>
                                <div class="text-muted" style="font-size:14px;">hari lagi</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-4">
                        <i class="bi bi-calendar-x text-muted mb-2" style="font-size:32px;"></i>
                        <div style="font-size:16px;font-weight:500;">Belum ada estimasi selesai</div>
                        <div class="text-muted" style="font-size:14px;">Project belum memiliki estimasi selesai</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {

    const allProjects = @json($projectJson);
    const baseUrl     = '{{ url("/ppsmbbyit") }}';
    const perPage     = 5;

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

    function renderRow(p) {
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
                    <a href="${baseUrl}/${p.id}" class="btn btn-sm btn-info mt-1" style="font-size:14px;">Rincian</a>
                </td>
            </tr>
        `;
    }

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
        all:   'Semua Project',
        proses: 'Proses Development',
        uat:   'UAT',
        done:  'Done (Live)',
    };

    function renderSummaryList(page) {
        summaryPage = page;
        const slice = renderPagination(summaryList, page, summaryInfo, summaryPagBtns, renderSummaryList);
        summaryBody.innerHTML = slice.map(renderRow).join('');
    }

    document.querySelectorAll('.dev-summary-card').forEach(card => {
        card.addEventListener('click', function () {
            const key = this.dataset.key;

            if (activeCardKey === key) {
                summaryCard.style.display = 'none';
                activeCardKey = null;
                document.querySelectorAll('.dev-summary-card').forEach(c => {
                    c.style.transform = ''; c.style.boxShadow = '';
                });
                return;
            }

            activeCardKey = key;
            document.querySelectorAll('.dev-summary-card').forEach(c => {
                c.style.transform = ''; c.style.boxShadow = '';
            });
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 6px 20px rgba(0,0,0,.1)';

            summaryList = allProjects.filter(p => {
                if (key === 'all')    return true;
                if (key === 'proses') return p.status === 'Proses Development';
                if (key === 'uat')    return p.status === 'UAT';
                if (key === 'done')   return p.status === 'Done (Live)';
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
        document.querySelectorAll('.dev-summary-card').forEach(c => {
            c.style.transform = ''; c.style.boxShadow = '';
        });
    });

})();
</script>
@endpush