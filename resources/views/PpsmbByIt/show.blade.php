@extends('layouts.app')

@section('title', 'Rincian PPSMB')
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
        <a href="{{ route('ppsmbbyit') }}" class="btn btn-sm btn-outline-secondary">
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

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label text-muted" style="font-size:13px;">Secondary BA</label>
                <input type="text" class="form-control" value="{{ $ppsmb->secondary_ba ?? '-' }}" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted" style="font-size:13px;">Developer</label>
                <input type="text" class="form-control" value="{{ $ppsmb->developer ?? '-' }}" disabled>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label text-muted" style="font-size:13px;">Tangible Benefit</label>
                <input type="text" class="form-control" value="{{ $ppsmb->tangible_benefit ? number_format($ppsmb->tangible_benefit, 0, ',', '.') : '-' }}" disabled>
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
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:13px;">Status</label>
                <input type="text" class="form-control" value="{{ $ppsmb->status }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:13px;">Estimasi Mulai</label>
                <input type="text" class="form-control" value="{{ $ppsmb->estimasi_mulai ?? '-' }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:13px;">Estimasi Selesai</label>
                <input type="text" class="form-control" value="{{ $ppsmb->estimasi_selesai ?? '-' }}" disabled>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label text-muted" style="font-size:13px;">Progress Pengerjaan</label>
            <div class="d-flex align-items-center gap-3">
                <div class="progress flex-grow-1" style="height: 10px;">
                    <div class="progress-bar" style="width: {{ $ppsmb->progress }}%; background-color: #af2027;"></div>
                </div>
                <span class="fw-semibold" style="font-size:13px;">{{ $ppsmb->progress }}%</span>
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
                        <td>{{ $history->created_at->format('d M Y H:i') }}</td>
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

        {{-- Step 1: Generate No PPSMB - project_leader, status Antrian Analisa BA IT --}}
        @if($isProjectLeader && $ppsmb->status === 'Antrian Analisa BA IT')
        <h6 class="fw-semibold mb-3 border-bottom pb-2">
            <i class="bi bi-gear me-1"></i>Generate No PPSMB
        </h6>
        <div class="card border-0 bg-light mb-4">
            <div class="card-body">
                <form action="{{ route('ppsmbbyit.generate', $ppsmb->id) }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px;">PIC BA <span class="text-danger">*</span></label>
                            <select name="pic_ba" class="form-select" required>
                                <option value="">-- Pilih PIC BA --</option>
                                @foreach($listBa as $ba)
                                    <option value="{{ $ba->name }}">{{ $ba->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px;">Secondary BA <span class="text-muted">(opsional)</span></label>
                            <select name="secondary_ba" class="form-select">
                                <option value="">-- Pilih Secondary BA --</option>
                                @foreach($listBa as $ba)
                                    <option value="{{ $ba->name }}">{{ $ba->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm text-white" style="background-color: #af2027;">
                        <i class="bi bi-lightning me-1"></i>Generate & Mulai Analisa
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Step 2: Input Detail Pengerjaan - business_analyst, status Analisa BA IT --}}
        @if($isBa && $ppsmb->status === 'Analisa BA IT')
        <h6 class="fw-semibold mb-3 border-bottom pb-2">
            <i class="bi bi-table me-1"></i>Input Detail Pengerjaan
        </h6>
        <div class="card border-0 bg-light mb-4">
            <div class="card-body">
                <form action="{{ route('ppsmbbyit.detail', $ppsmb->id) }}" method="POST">
                    @csrf
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle" id="detailTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Menu/Fitur</th>
                                    <th>Penilaian</th>
                                    <th>Mandays</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="detailBody">
                                <tr>
                                    <td><input type="text" name="detail[0][menu]" class="form-control form-control-sm" required></td>
                                    <td>
                                        <select name="detail[0][penilaian]" class="form-select form-select-sm" required>
                                            <option value="">--</option>
                                            <option value="Low">Low</option>
                                            <option value="Medium">Medium</option>
                                            <option value="High">High</option>
                                        </select>
                                    </td>
                                    <td><input type="number" name="detail[0][mandays]" class="form-control form-control-sm" min="0" required></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusBaris(this)"><i class="bi bi-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="tambahBaris()">
                            <i class="bi bi-plus me-1"></i>Tambah Baris
                        </button>
                        <button type="submit" class="btn btn-sm text-white" style="background-color: #af2027;">
                            <i class="bi bi-check me-1"></i>Simpan & Lanjut ke Antrian Development
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Step 3: Assign Developer - project_leader, status Antrian Development --}}
        @if($isProjectLeader && $ppsmb->status === 'Antrian Development')
        <h6 class="fw-semibold mb-3 border-bottom pb-2">
            <i class="bi bi-person-check me-1"></i>Tentukan Developer
        </h6>
        <div class="card border-0 bg-light mb-4">
            <div class="card-body">
                <form action="{{ route('ppsmbbyit.developer', $ppsmb->id) }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px;">Developer <span class="text-danger">*</span></label>
                            <select name="developer" class="form-select" required>
                                <option value="">-- Pilih Developer --</option>
                                @foreach($listDeveloper as $dev)
                                    <option value="{{ $dev->name }}">{{ $dev->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm text-white" style="background-color: #af2027;">
                        <i class="bi bi-person-check me-1"></i>Tentukan & Mulai Development
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Update Estimasi - project_leader, status apapun kecuali Antrian Analisa BA IT dan Done --}}
        @if($isProjectLeader && !in_array($ppsmb->status, ['Antrian Analisa BA IT', 'Done (Live)']))
        <h6 class="fw-semibold mb-3 border-bottom pb-2">
            <i class="bi bi-calendar me-1"></i>Update Estimasi
        </h6>
        <div class="card border-0 bg-light mb-4">
            <div class="card-body">
                <form action="{{ route('ppsmbbyit.estimasi', $ppsmb->id) }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px;">Estimasi Mulai</label>
                            <input type="date" name="estimasi_mulai" class="form-control" value="{{ $ppsmb->estimasi_mulai }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px;">Estimasi Selesai</label>
                            <input type="date" name="estimasi_selesai" class="form-control" value="{{ $ppsmb->estimasi_selesai }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-calendar-check me-1"></i>Update Estimasi
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Step 4: Update Progress - developer, status Proses Development --}}
        @if($isDeveloper && $ppsmb->status === 'Proses Development')
        <h6 class="fw-semibold mb-3 border-bottom pb-2">
            <i class="bi bi-arrow-up-circle me-1"></i>Update Progress
        </h6>
        <div class="card border-0 bg-light mb-4">
            <div class="card-body">
                <form action="{{ route('ppsmbbyit.progress', $ppsmb->id) }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label" style="font-size:13px;">Progress (%) <span class="text-danger">*</span></label>
                            <input type="number" name="progress" class="form-control" min="0" max="100" value="{{ $ppsmb->progress }}" required>
                        </div>
                    </div>
                    @if($ppsmb->detailPengerjaan->count() > 0)
                    <label class="form-label" style="font-size:13px;">Adjustment Mandays <span class="text-muted">(opsional)</span></label>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Menu/Fitur</th>
                                    <th>Mandays Awal</th>
                                    <th>Adjustment Mandays</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ppsmb->detailPengerjaan as $detail)
                                <tr>
                                    <td>{{ $detail->menu }}</td>
                                    <td>{{ $detail->mandays }}</td>
                                    <td>
                                        <input type="number" name="adjustment_mandays[{{ $detail->id }}]" class="form-control form-control-sm" value="{{ $detail->adjustment_mandays }}" min="0">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-up-circle me-1"></i>Update Progress
                        </button>
                    </div>
                </form>
                <form action="{{ route('ppsmbbyit.lanjutuat', $ppsmb->id) }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-sm text-white" style="background-color: #af2027;">
                        <i class="bi bi-arrow-right-circle me-1"></i>Lanjut ke UAT
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Step 5: UAT - developer, status UAT --}}
        @if($isDeveloper && $ppsmb->status === 'UAT')
        <h6 class="fw-semibold mb-3 border-bottom pb-2">
            <i class="bi bi-check2-circle me-1"></i>UAT
        </h6>
        <div class="card border-0 bg-light mb-4">
            <div class="card-body">
                <form action="{{ route('ppsmbbyit.uat', $ppsmb->id) }}" method="POST">
                    @csrf
                    <p class="text-muted mb-3" style="font-size:13px;">Apakah hasil UAT sudah sesuai atau masih ada revisi?</p>
                    <div class="d-flex gap-2">
                        <button type="submit" name="aksi" value="revisi" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Ada Revisi
                        </button>
                        <button type="submit" name="aksi" value="done" class="btn btn-sm text-white" style="background-color: #af2027;">
                            <i class="bi bi-check-circle me-1"></i>Done (Live)
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>

<script>
    let rowIndex = 1;

    function tambahBaris() {
        const tbody = document.getElementById('detailBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="detail[${rowIndex}][menu]" class="form-control form-control-sm" required></td>
            <td>
                <select name="detail[${rowIndex}][penilaian]" class="form-select form-select-sm" required>
                    <option value="">--</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
            </td>
            <td><input type="number" name="detail[${rowIndex}][mandays]" class="form-control form-control-sm" min="0" required></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusBaris(this)"><i class="bi bi-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
        rowIndex++;
    }

    function hapusBaris(btn) {
        const tbody = document.getElementById('detailBody');
        if (tbody.rows.length > 1) {
            btn.closest('tr').remove();
        }
    }
</script>

@endsection