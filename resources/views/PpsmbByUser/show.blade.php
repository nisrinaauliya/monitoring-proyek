@extends('layouts.app')

@section('title', 'Rincian PPSMB - Sistem Helpdesk')
@section('page_title', 'Rincian PPSMB')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Rincian PPSMB</h6>
        <a href="{{ route('ppsmbbyuser') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="card-body">

        {{-- Personal Info --}}
        <h6 class="fw-semibold mb-3 border-bottom pb-2">
            <i class="bi bi-person me-1"></i>Personal Info
        </h6>
        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label text-muted" style="font-size:13px;">Departemen</label>
                <input type="text" class="form-control" value="{{ $ppsmb->dept }}" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted" style="font-size:13px;">User</label>
                <input type="text" class="form-control" value="{{ $ppsmb->user->name }}" disabled>
            </div>
        </div>

        {{-- Requirements --}}
        <h6 class="fw-semibold mb-3 border-bottom pb-2">
            <i class="bi bi-file-earmark me-1"></i>Requirements
        </h6>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:13px;">No PPSMB</label>
                <input type="text" class="form-control" value="{{ $ppsmb->no_ppsmb ?? '-' }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:13px;">Model Aplikasi</label>
                <input type="text" class="form-control" value="{{ $ppsmb->model_aplikasi }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:13px;">Nama Project</label>
                <input type="text" class="form-control" value="{{ $ppsmb->nama_project }}" disabled>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:13px;">Tahun</label>
                <input type="text" class="form-control" value="{{ $ppsmb->tahun }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:13px;">Quartal</label>
                <input type="text" class="form-control" value="{{ $ppsmb->quartal }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:13px;">File</label>
                <div class="input-group">
                    <input type="text" class="form-control" value="{{ basename($ppsmb->file) }}" disabled>
                    <a href="{{ asset('storage/' . $ppsmb->file) }}" target="_blank" class="btn btn-outline-secondary">
                        <i class="bi bi-download"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label text-muted" style="font-size:13px;">Jenis Permintaan</label>
                <input type="text" class="form-control" value="{{ $ppsmb->jenis_permintaan }}" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted" style="font-size:13px;">Uraian Permintaan</label>
                <textarea class="form-control" rows="3" disabled>{{ $ppsmb->uraian_permintaan }}</textarea>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label text-muted" style="font-size:13px;">Project Leader</label>
                <input type="text" class="form-control" value="{{ $ppsmb->project_leader ?? '-' }}" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted" style="font-size:13px;">PIC BA</label>
                <input type="text" class="form-control" value="{{ $ppsmb->pic_ba ?? '-' }}" disabled>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label text-muted" style="font-size:13px;">Tangible Benefit</label>
                <input type="text" class="form-control" value="{{ number_format($ppsmb->tangible_benefit, 0, ',', '.') ?? '-' }}" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted" style="font-size:13px;">Intangible Benefit</label>
                <input type="text" class="form-control" value="{{ $ppsmb->intangible_benefit ?? '-' }}" disabled>
            </div>
        </div>

        {{-- Progress --}}
        <h6 class="fw-semibold mb-3 border-bottom pb-2">
            <i class="bi bi-bar-chart me-1"></i>Progress
        </h6>
        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label text-muted" style="font-size:13px;">Status Pengerjaan</label>
                <input type="text" class="form-control" value="{{ $ppsmb->status }}" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted" style="font-size:13px;">Presentase Pengerjaan</label>
                <input type="text" class="form-control" value="{{ $ppsmb->progress }}%" disabled>
            </div>
        </div>

        {{-- Detail Pengerjaan --}}
        <h6 class="fw-semibold mb-3 border-bottom pb-2">
            <i class="bi bi-list-check me-1"></i>Detail Pengerjaan
        </h6>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Menu</th>
                        <th>Penilaian</th>
                        <th>Mandays</th>
                        <th>Adjustment Mandays</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ppsmb->detailPengerjaan as $detail)
                    <tr>
                        <td>{{ $detail->menu }}</td>
                        <td>{{ $detail->penilaian ?? '-' }}</td>
                        <td>{{ $detail->mandays ?? '-' }}</td>
                        <td>{{ $detail->adjustment_mandays ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Belum ada detail pengerjaan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- History --}}
        <h6 class="fw-semibold mb-3 border-bottom pb-2">
            <i class="bi bi-clock-history me-1"></i>History
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Pemeriksa</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ppsmb->histories as $history)
                    <tr>
                        <td>{{ $history->created_at->format('d M Y H:i:s') }}</td>
                        <td>{{ $history->pemeriksa }}</td>
                        <td>{{ $history->status }}</td>
                        <td>{{ $history->catatan ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Belum ada history</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection