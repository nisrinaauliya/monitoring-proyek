@extends('layouts.app')

@section('title', 'Rincian PPSMB - Sistem Helpdesk')
@section('page_title', 'Rincian PPSMB')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Rincian PPSMB</h6>
        <a href="{{ route('ppsmbbydinov') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="card-body">

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
        <div class="table-responsive mb-4">
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

        @if($ppsmb->status === 'Verifikasi CMD/Dinov' || $ppsmb->status === 'Edit By User - Verifikasi CMD/Dinov')
        <h6 class="fw-semibold mb-3 border-bottom pb-2">
            <i class="bi bi-check-circle me-1"></i>Verifikasi
        </h6>
        <div class="row">
            <div class="col-md-6">
                <div class="card border-success">
                    <div class="card-body">
                        <h6 class="fw-semibold">Approve PPSMB</h6>
                        <form action="{{ route('ppsmbbydinov.approve', $ppsmb->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" style="font-size:13px;">Catatan (opsional)</label>
                                <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan persetujuan..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                Approve
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-danger">
                    <div class="card-body">
                        <h6 class="fw-semibold">Kembalikan untuk Revisi</h6>
                        <form action="{{ route('ppsmbbydinov.revisi', $ppsmb->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" style="font-size:13px;">Catatan Revisi <span class="text-danger">*</span></label>
                                <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="2" placeholder="Tuliskan alasan revisi..."></textarea>
                                @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-danger w-100">
                                Kembalikan ke User
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection