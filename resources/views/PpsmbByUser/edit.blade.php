@extends('layouts.app')

@section('title', 'Edit PPSMB')
@section('page_title', 'Edit PPSMB')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Form Revisi PPSMB</h6>
        <a href="{{ route('ppsmbbyuser') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="card-body">

        <form action="{{ route('ppsmbbyuser.update', $ppsmb->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Personal Info --}}
            <h6 class="fw-semibold mb-3 border-bottom pb-2">
                <i class="bi bi-person me-1"></i>Personal Info
            </h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Departemen</label>
                    <input type="text" class="form-control" value="{{ $ppsmb->dept }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">User</label>
                    <input type="text" class="form-control" value="{{ $ppsmb->user->name }}" disabled>
                </div>
            </div>

            {{-- Requirements --}}
            <h6 class="fw-semibold mb-3 border-bottom pb-2 mt-4">
                <i class="bi bi-file-earmark me-1"></i>Requirements
            </h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Model Aplikasi</label>
                    <input type="text" class="form-control" value="{{ $ppsmb->model_aplikasi }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Project <span class="text-danger">*</span></label>
                    <input type="text" name="nama_project" class="form-control @error('nama_project') is-invalid @enderror" value="{{ old('nama_project', $ppsmb->nama_project) }}" disabled>
                    @error('nama_project')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tahun</label>
                    <input type="text" class="form-control" value="{{ $ppsmb->tahun }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Quartal</label>
                    <input type="text" class="form-control" value="{{ $ppsmb->quartal }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Jenis Permintaan</label>
                    <input type="text" class="form-control" value="{{ $ppsmb->jenis_permintaan }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Upload File SRS Baru</label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.doc,.docx">
                    <small class="text-muted">File saat ini: {{ basename($ppsmb->file) }}. Kosongkan jika tidak ingin mengganti file.</small>
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Uraian Permintaan <span class="text-danger">*</span></label>
                <textarea name="uraian_permintaan" rows="4" class="form-control @error('uraian_permintaan') is-invalid @enderror">{{ old('uraian_permintaan', $ppsmb->uraian_permintaan) }}</textarea>
                @error('uraian_permintaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tangible Benefit</label>
                    <input type="number" name="tangible_benefit" class="form-control" value="{{ old('tangible_benefit', $ppsmb->tangible_benefit) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Intangible Benefit</label>
                    <input type="text" name="intangible_benefit" class="form-control" value="{{ old('intangible_benefit', $ppsmb->intangible_benefit) }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('ppsmbbyuser') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn text-white" style="background-color: #af2027;">Submit Revisi</button>
            </div>

        </form>
    </div>
</div>

@endsection