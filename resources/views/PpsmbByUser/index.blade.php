@extends('layouts.app')

@section('title', 'PPSMB by User - Sistem Helpdesk')
@section('page_title', 'PPSMB by User')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->has('uat'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ $errors->first('uat') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <h6 class="mb-0 fw-semibold">Monitoring Pengajuan PPSMB</h6>
        <div class="d-flex gap-2">
            <a href="{{ asset('template/SRS_Template.docx') }}" 
                class="btn btn-sm btn-outline-secondary" download>
                <i class="bi bi-download me-1"></i>Download Template SRS
            </a>
            <a href="{{ route('ppsmbbyuser.create') }}" class="btn btn-sm text-white" style="background-color: #af2027;">
                <i class="bi bi-plus me-1"></i>Tambah PPSMB
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            <input type="text" id="searchInput" class="form-control w-auto" placeholder="Search...">
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="ppsmbTable">
                <thead class="table-light">
                    <tr>
                        <th>No PPSMB</th>
                        <th>Nama Project</th>
                        <th>Dept</th>
                        <th>User</th>
                        <th>Model Aplikasi</th>
                        <th>Tahun</th>
                        <th>Quartal</th>
                        <th>Status Pengajuan</th>
                        <th>Estimasi Mulai</th>
                        <th>Estimasi Selesai</th>
                        <th>Progress</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ppsmbs as $ppsmb)
                    <tr>
                        <td>{{ $ppsmb->no_ppsmb ?? '-' }}</td>
                        <td>{{ $ppsmb->nama_project }}</td>
                        <td>{{ $ppsmb->dept }}</td>
                        <td>{{ $ppsmb->user->name }}</td>
                        <td>{{ $ppsmb->model_aplikasi }}</td>
                        <td>{{ $ppsmb->tahun }}</td>
                        <td>{{ $ppsmb->quartal }}</td>
                        <td>
                            @php
                                $badge = match($ppsmb->status) {
                                    'Verifikasi CMD/Dinov'  => 'warning',
                                    'Revisi User'           => 'danger',
                                    'Antrian Analisa BA IT' => 'info',
                                    'Analisa BA IT'         => 'primary',
                                    'Antrian Development'   => 'secondary',
                                    'Proses Development'    => 'primary',
                                    'UAT'                   => 'warning',
                                    'Done'                  => 'success',
                                    default                 => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ $ppsmb->status }}</span>
                        </td>
                        <td>{{ $ppsmb->estimasi_mulai ?? '-' }}</td>
                        <td>{{ $ppsmb->estimasi_selesai ?? '-' }}</td>
                        <td>{{ $ppsmb->progress }}%</td>
                        <td>
                            <a href="{{ route('ppsmbbyuser.show', $ppsmb->id) }}" 
                               class="btn btn-sm btn-info text-white">Rincian</a>
                            @if($ppsmb->status === 'Revisi User' && $ppsmb->user_id === Auth::id())
                                <a href="{{ route('ppsmbbyuser.edit', $ppsmb->id) }}" 
                                   class="btn btn-sm btn-warning text-white">Edit</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted">Belum ada data PPSMB</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const keyword = this.value.toLowerCase();
        document.querySelectorAll('#ppsmbTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(keyword) ? '' : 'none';
        });
    });
</script>

@endsection