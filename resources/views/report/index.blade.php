@extends('layouts.app')
@php use Carbon\Carbon; @endphp

@section('title', 'Report - Sistem Helpdesk')
@section('page_title', 'Report')

@section('content')

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('report') }}" class="d-flex align-items-end gap-3 flex-wrap">
            <div>
                <label class="form-label text-muted mb-1" style="font-size:16px;">Tahun</label>
                <select name="tahun" class="form-select form-select-sm" style="width:150px;font-size:16px;">
                    @foreach($tahunList as $t)
                    <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label text-muted mb-1" style="font-size:16px;">Bulan (Cutoff)</label>
                <select name="bulan" class="form-select form-select-sm" style="width:150px;font-size:16px;">
                    @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == $bulan ? 'selected' : '' }}>
                        {{ Carbon::create(null, $m)->translatedFormat('F') }}
                    </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label text-muted mb-1" style="font-size:16px;">Dept (Detail)</label>
                <select name="dept" class="form-select form-select-sm" style="width:150px;font-size:16px;">
                    <option value="">-- Semua --</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->code }}" {{ $dept->code === $selectedDeptCode ? 'selected' : '' }}>
                        {{ $dept->code }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-sm text-white" style="background-color:#af2027;font-size:16px;">
                    <i class="bi bi-filter me-1"></i>Filter
                </button>
                <a href="{{ route('report.export', ['tahun' => $tahun, 'bulan' => $bulan]) }}"
                   class="btn btn-sm btn-success" style="font-size:16px">
                    <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Matrix Summary --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 px-3 pt-3 pb-2">
        <div class="fw-semibold" style="font-size:16px;">
            PPSMB Progress – YTD {{ $bulanIniLabel }}
        </div>
        <div class="text-muted" style="font-size:14px;">Matrix progress per departemen</div>
    </div>
    <div class="card-body px-3 pb-3 pt-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle" id="ppsmbTable" style="font-size:14px;">
                <thead class="table-light">
                    <tr>
                        <th class="py-2 px-3" rowspan="2" style="vertical-align:middle;white-space:nowrap;">Dept</th>
                        <th class="text-center py-2 px-2" rowspan="2" style="vertical-align:middle;white-space:nowrap;">Pengajuan PPSMB</th>
                        <th class="text-center py-2 px-2" colspan="{{ count($statusList) }}">
                            Progress PPSMB
                        </th>
                    </tr>
                    <tr>
                        @foreach($statusList as $status)
                        @php $sc = config('status.colors')[$status] ?? '#6c757d'; @endphp
                        <th class="text-center py-2 px-2">
                            <span class="px-2 py-1 rounded" style="font-size:14px;background:{{ $sc }};color:white;white-space:nowrap;">
                                {{ $status }}
                            </span>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($matrix as $matrixDeptCode => $row)
                    <tr>
                        <td class="fw-semibold px-3">{{ $matrixDeptCode }}</td>
                        <td class="text-center fw-bold">{{ $row['pengajuan'] }}</td>
                        @foreach($statusList as $status)
                        <td class="text-center">
                            {{ isset($row[$status]) && $row[$status] > 0 ? $row[$status] : '—' }}
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td class="px-3">Total</td>
                        <td class="text-center">{{ $totalPengajuan }}</td>
                        @foreach($statusList as $status)
                        <td class="text-center">
                            {{ $totalPerStatus[$status] > 0 ? $totalPerStatus[$status] : '—' }}
                        </td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Detail Per Dept --}}
@if($selectedDept && $detailDept)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 px-3 pt-3 pb-2 d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <div class="fw-semibold" style="font-size:16px;">
                PPSMB – {{ $selectedDept->code }}
                <span class="text-muted fw-normal" style="font-size:14px;">
                    ({{ $detailDept->total() }} project)
                </span>
            </div>
            <div class="text-muted" style="font-size:14px;">Progress {{ $bulanIniLabel }}</div>
        </div>

        {{-- Filter status + sort --}}
        <form method="GET" action="{{ route('report') }}" class="d-flex gap-2 flex-wrap align-items-end">
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <input type="hidden" name="bulan" value="{{ $bulan }}">
            <input type="hidden" name="dept" value="{{ $selectedDeptCode }}">

            <div>
                <label class="form-label text-muted mb-1" style="font-size:14px;">Filter Status</label>
                <select name="filter_status" class="form-select form-select-sm" style="width:200px;font-size:14px;">
                    <option value="">-- Semua Status --</option>
                    @foreach($statusList as $s)
                    <option value="{{ $s }}" {{ $filterStatus === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label text-muted mb-1" style="font-size:14px;">Urutkan</label>
                <select name="sort_by" class="form-select form-select-sm" style="width:180px;font-size:14px;">
                    <option value="estimasi_selesai" {{ $sortBy === 'estimasi_selesai' ? 'selected' : '' }}>Estimasi Selesai</option>
                    <option value="status" {{ $sortBy === 'status' ? 'selected' : '' }}>Status</option>
                </select>
            </div>
            <button type="submit" class="btn btn-sm text-white" style="background-color:#af2027;font-size:14px;">
                <i class="bi bi-filter me-1"></i>Apply
            </button>
        </form>
    </div>

    <div class="card-body px-3 pb-3 pt-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle" style="font-size:14px;">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-3" style="min-width:200px;">Nama Project</th>
                        <th class="text-center py-3 px-2" style="min-width:150px;">Model Aplikasi</th>
                        <th class="text-center py-3 px-2" style="min-width:120px;">User</th>
                        <th class="text-center py-3 px-2" style="white-space:nowrap;">Status {{ $bulanLaluLabel }}</th>
                        <th class="text-center py-3 px-2" style="white-space:nowrap;">Status {{ $bulanIniLabel }}</th>
                        <th class="text-center py-3 px-2" style="white-space:nowrap;">Progress {{ $bulanLaluLabel }}</th>
                        <th class="text-center py-3 px-2" style="white-space:nowrap;">Progress {{ $bulanIniLabel }}</th>
                        <th class="text-center py-3 px-2" style="white-space:nowrap;">% {{ $bulanLaluLabel }}</th>
                        <th class="text-center py-3 px-2" style="white-space:nowrap;">% {{ $bulanIniLabel }}</th>
                        <th class="text-center py-3 px-2" style="white-space:nowrap;">Estimasi Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailDept as $item)
                    <tr>
                        <td class="px-3">{{ $item['nama_project'] }}</td>
                        <td class="px-2">{{ $item['model_aplikasi'] }}</td>
                        <td class="text-center px-2">{{ $item['user'] }}</td>
                        <td class="text-center px-2">
                            @if($item['status_bulan_lalu'] !== '-')
                            <span class="px-1 py-1 rounded"
                                  style="font-size:13px;background:{{ config('status.colors')[$item['status_bulan_lalu']] ?? '#6c757d' }};color:white;white-space:nowrap;">
                                {{ $item['status_bulan_lalu'] }}
                            </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center px-2">
                            @if($item['status_bulan_ini'] !== '-')
                            <span class="px-1 py-1 rounded"
                                  style="font-size:13px;background:{{ config('status.colors')[$item['status_bulan_ini']] ?? '#6c757d' }};color:white;white-space:nowrap;">
                                {{ $item['status_bulan_ini'] }}
                            </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center px-2">{{ number_format($item['progress_bulan_lalu'], 1) }}</td>
                        <td class="text-center px-2">{{ number_format($item['progress_bulan_ini'], 1) }}</td>
                        <td class="text-center px-2">{{ number_format($item['progress_bulan_lalu'], 1) }}%</td>
                        <td class="text-center px-2">{{ number_format($item['progress_bulan_ini'], 1) }}%</td>
                        <td class="text-center px-2">{{ $item['estimasi_selesai'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-3">Tidak ada data project</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <small class="text-muted">
                Showing {{ $detailDept->firstItem() }} to {{ $detailDept->lastItem() }} of {{ $detailDept->total() }} results
            </small>
            {{ $detailDept->links() }}
        </div>
    </div>
</div>
@elseif(!$selectedDept)
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-4 text-muted">
        <i class="bi bi-hand-index mb-2 d-block" style="font-size:32px;"></i>
        <div style="font-size:16px;">Pilih departemen untuk melihat detail project</div>
    </div>
</div>
@endif

@endsection