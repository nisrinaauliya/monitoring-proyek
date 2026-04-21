@extends('layouts.app')

@section('title', 'Edit PPSMB - Sistem Helpdesk')
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
                    <input type="hidden" name="model_aplikasi" value="{{ $ppsmb->model_aplikasi }}">
                    <input type="text" class="form-control" value="{{ $ppsmb->model_aplikasi }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Project</label>
                    <input type="hidden" name="nama_project" value="{{ $ppsmb->nama_project }}">
                    <input type="text" class="form-control" value="{{ $ppsmb->nama_project }}" disabled>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tahun</label>
                    <input type="hidden" name="tahun" value="{{ $ppsmb->tahun }}">
                    <input type="text" class="form-control" value="{{ $ppsmb->tahun }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Quartal</label>
                    <input type="hidden" name="quartal" value="{{ $ppsmb->quartal }}">
                    <input type="text" class="form-control" value="{{ $ppsmb->quartal }}" disabled>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Jenis Permintaan</label>
                    <input type="hidden" name="jenis_permintaan[]" value="{{ $ppsmb->jenis_permintaan }}">
                    <input type="text" class="form-control" value="{{ $ppsmb->jenis_permintaan }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Upload File SRS Baru</label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.doc,.docx">
                    <small class="text-muted">
                        File saat ini:
                        <a href="{{ asset('storage/' . $ppsmb->file) }}" target="_blank">{{ basename($ppsmb->file) }}</a>. 
                        Kosongkan jika tidak ingin mengganti file.
                    </small>
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
                <a href="{{ route('ppsmbbyuser') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn text-white" style="background-color: #af2027;">Submit Revisi</button>
            </div>
        </form>
    </div>
</div>

@endsection