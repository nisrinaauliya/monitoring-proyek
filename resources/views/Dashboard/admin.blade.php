@extends('layouts.app')
@php use Carbon\Carbon; @endphp

@section('title', 'Dashboard Admin - Sistem Helpdesk')
@section('page_title', 'Dashboard')

@section('content')

{{-- Summary Cards --}}
@php
$summaryCards = [
    ['label'=>'Total Project',    'val'=>$totalProject,  'color'=>'#0d6efd', 'sub'=>'Semua project masuk'],
    ['label'=>'Project Aktif',    'val'=>$totalAktif,    'color'=>'#f59e0b', 'sub'=>'Sedang berjalan'],
    ['label'=>'Done (Live)',      'val'=>$totalSelesai,  'color'=>'#198754', 'sub'=>'Project selesai'],
    ['label'=>'Rejected',         'val'=>$totalRejected, 'color'=>'#dc3545', 'sub'=>'Project ditolak'],
];
@endphp
<div class="row g-3 mb-4">
    @foreach($summaryCards as $sc)
    <div class="col-6 col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="fw-bold mb-1" style="font-size:30px;color:{{ $sc['color'] }};line-height:1;">
                    {{ $sc['val'] }}
                </div>
                <div class="fw-semibold" style="font-size:16px;">{{ $sc['label'] }}</div>
                <div class="text-muted mt-1" style="font-size:14px;">{{ $sc['sub'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
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
                <div class="text-muted mb-3" style="font-size:14px;">Project aktif per tim</div>
                @if($perTim->count() > 0)
                    @foreach($perTim as $tim => $count)
                    @php $pct = $perTim->sum() > 0 ? round($count / $perTim->sum() * 100) : 0; @endphp
                    <div class="mb-3">
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

{{-- Workload per BA + Developer --}}
<div class="row g-3 mb-4">

    {{-- Workload per BA --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="fw-semibold mb-1" style="font-size:16px;">Workload BA</div>
                <div class="text-muted mb-3" style="font-size:14px;">Project aktif per Business Analyst</div>
                @if($perBa->count() > 0)
                    @foreach($perBa as $ba => $count)
                    @php $pct = $perBa->sum() > 0 ? round($count / $perBa->sum() * 100) : 0; @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-medium" style="font-size:14px;">{{ $ba }}</span>
                            <span class="text-muted" style="font-size:14px;">{{ $count }} project</span>
                        </div>
                        <div class="progress rounded-pill" style="height:8px;background:#e9ecef;">
                            <div class="progress-bar rounded-pill"
                                 style="width:{{ $pct }}%;background:#4691ec;transition:width .6s ease;"></div>
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
                <div class="text-muted mb-3" style="font-size:14px;">Project aktif per Developer</div>
                @if($perDeveloper->count() > 0)
                    @foreach($perDeveloper as $dev => $count)
                    @php $pct = $perDeveloper->sum() > 0 ? round($count / $perDeveloper->sum() * 100) : 0; @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-medium" style="font-size:14px;">{{ $dev }}</span>
                            <span class="text-muted" style="font-size:14px;">{{ $count }} project</span>
                        </div>
                        <div class="progress rounded-pill" style="height:8px;background:#e9ecef;">
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
                    @foreach($projectTelat as $p)
                    @php
                        $sc      = config('status.colors')[$p->status] ?? '#6c757d';
                        $telat   = (int) Carbon::now()->diffInDays(Carbon::parse($p->estimasi_selesai), false);
                    @endphp
                    <tr class="border-top table-danger">
                        <td class="px-3 py-3">
                            <span class="text-muted" style="font-size:13px;">{{ $p->no_ppsmb ?? '—' }}</span>
                        </td>
                        <td class="py-3 fw-medium">{{ $p->nama_project }}</td>
                        <td class="py-3" style="font-size:14px;">{{ $p->tim ?? '—' }}</td>
                        <td class="py-3">
                            <span class="px-2 py-1 rounded"
                                  style="font-size:13px;background:{{ $sc }};color:white;white-space:nowrap;">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td class="py-3" style="font-size:14px;">
                            {{ Carbon::parse($p->estimasi_selesai)->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-3">
                            <span class="fw-bold text-danger" style="font-size:14px;">
                                {{ abs($telat) }} hari
                            </span>
                        </td>
                        <td class="py-3 pe-3">
                            <a href="{{ route('ppsmbbyit.show', $p->id) }}"
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
                    @foreach($aktivitasTerbaru as $log)
                    @php
                        $sc = config('status.colors')[$log->status] ?? '#6c757d';
                    @endphp
                    <tr class="border-top">
                        <td class="px-3 py-3 text-muted" style="font-size:13px;white-space:nowrap;">
                            {{ Carbon::parse($log->created_at)->translatedFormat('d M Y, H:i') }}
                        </td>
                        <td class="py-3">
                            <span class="text-muted" style="font-size:13px;">
                                {{ $log->ppsmb->no_ppsmb ?? '—' }}
                            </span>
                        </td>
                        <td class="py-3 fw-medium">{{ $log->ppsmb->nama_project ?? '—' }}</td>
                        <td class="py-3">
                            <span class="px-2 py-1 rounded"
                                  style="font-size:13px;background:{{ $sc }};color:white;white-space:nowrap;">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="py-3 pe-3">
                            @if($log->ppsmb)
                            <a href="{{ route('ppsmbbyit.show', $log->ppsmb->id) }}"
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
    @php
    $statusLabels = $perStatus->keys();
    $statusValues = $perStatus->values();
    $statusColors = $statusLabels->map(fn($s) => config('status.colors')[$s] ?? '#6c757d');
    @endphp

    const labels = @json($statusLabels);
    const values = @json($statusValues);
    const colors = @json($statusColors);

    const ctx = document.getElementById('chartStatus').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.raw} project`
                    }
                }
            },
            scales: {
                x: {
                    ticks: { font: { size: 12 } },
                    grid: { display: false }
                },
                y: {
                    ticks: { stepSize: 1, font: { size: 12 } },
                    grid: { color: '#f0f0f0' }
                }
            }
        }
    });
})();
</script>
@endpush