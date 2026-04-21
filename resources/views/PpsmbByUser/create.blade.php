@extends('layouts.app')

@section('title', 'Tambah PPSMB - Sistem Helpdesk')
@section('page_title', 'Tambah PPSMB')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-semibold">Form Pengajuan PPSMB</h6>
    </div>

    <div class="card-body">
        <form id="formPpsmb" action="{{ route('ppsmbbyuser.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Personal Info --}}
            <h6 class="fw-semibold mb-3 border-bottom pb-2">
                <i class="bi bi-person me-1"></i>Personal Info
            </h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Departemen</label>
                    <input type="text" class="form-control" value="{{ Auth::user()->dept }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">User</label>
                    <input type="text" class="form-control" value="{{ Auth::user()->name }}" disabled>
                </div>
            </div>

            {{-- Requirements --}}
            <h6 class="fw-semibold mb-3 border-bottom pb-2 mt-4">
                <i class="bi bi-file-earmark me-1"></i>Requirements
            </h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    @if(Auth::user()->dept === 'IT' && Auth::user()->role !== 'admin')
                        <input type="hidden" name="model_aplikasi" value="Improvement IT System">
                        <label class="form-label">Model Aplikasi</label>
                        <input type="text" class="form-control" value="Improvement IT System" disabled>
                    @elseif(Auth::user()->role === 'admin')
                    <label class="form-label">Model Aplikasi <span class="text-danger">*</span></label>
                        <select name="model_aplikasi" class="form-select @error('model_aplikasi') is-invalid @enderror">
                            <option value="">-- Pilih Model Aplikasi --</option>
                            <option value="Aplikasi Internal MD" {{ old('model_aplikasi') == 'Aplikasi Internal MD' ? 'selected' : '' }}>Aplikasi Internal MD</option>
                            <option value="Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot" {{ old('model_aplikasi') == 'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot' ? 'selected' : '' }}>Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot</option>
                            <option value="Improvement IT System" {{ old('model_aplikasi') == 'Improvement IT System' ? 'selected' : '' }}>Improvement IT System</option>
                        </select>
                        @error('model_aplikasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @else
                    <label class="form-label">Model Aplikasi <span class="text-danger">*</span></label>
                        <select name="model_aplikasi" class="form-select @error('model_aplikasi') is-invalid @enderror">
                            <option value="">-- Pilih Model Aplikasi --</option>
                            <option value="Aplikasi Internal MD" {{ old('model_aplikasi') == 'Aplikasi Internal MD' ? 'selected' : '' }}>Aplikasi Internal MD</option>
                            <option value="Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot" {{ old('model_aplikasi') == 'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot' ? 'selected' : '' }}>Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot</option>
                        </select>
                        @error('model_aplikasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @endif

                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Project <span class="text-danger">*</span></label>
                    <input type="text" name="nama_project" class="form-control @error('nama_project') is-invalid @enderror" value="{{ old('nama_project') }}">
                    @error('nama_project')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Dibutuhkan Tahun <span class="text-danger">*</span></label>
                    <select name="tahun" class="form-select @error('tahun') is-invalid @enderror">
                        <option value="">-- Pilih Tahun --</option>
                        @for($y = date('Y'); $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ old('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    @error('tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Quartal <span class="text-danger">*</span></label>
                    <select name="quartal" class="form-select @error('quartal') is-invalid @enderror">
                        <option value="">-- Pilih Quartal --</option>
                        <option value="Q1" {{ old('quartal') == 'Q1' ? 'selected' : '' }}>Q1</option>
                        <option value="Q2" {{ old('quartal') == 'Q2' ? 'selected' : '' }}>Q2</option>
                        <option value="Q3" {{ old('quartal') == 'Q3' ? 'selected' : '' }}>Q3</option>
                        <option value="Q4" {{ old('quartal') == 'Q4' ? 'selected' : '' }}>Q4</option>
                    </select>
                    @error('quartal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Jenis Permintaan <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3 mt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="jenis_permintaan[]" value="Sistem Baru" {{ in_array('Sistem Baru', old('jenis_permintaan', [])) ? 'checked' : '' }}>
                            <label class="form-check-label">Sistem Baru</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="jenis_permintaan[]" value="Modul Baru" {{ in_array('Modul Baru', old('jenis_permintaan', [])) ? 'checked' : '' }}>
                            <label class="form-check-label">Modul Baru</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="jenis_permintaan[]" value="Modifikasi Baru" {{ in_array('Modifikasi Baru', old('jenis_permintaan', [])) ? 'checked' : '' }}>
                            <label class="form-check-label">Modifikasi Baru</label>
                        </div>
                    </div>
                    @error('jenis_permintaan')<div class="text-danger" style="font-size:13px;">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Upload File SRS <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.doc,.docx">
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Uraian Permintaan <span class="text-danger">*</span></label>
                <textarea name="uraian_permintaan" rows="4" class="form-control @error('uraian_permintaan') is-invalid @enderror">{{ old('uraian_permintaan') }}</textarea>
                @error('uraian_permintaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Total Tangible Benefit (Per Tahun)</label>
                    <input type="number" name="tangible_benefit" class="form-control" value="{{ old('tangible_benefit') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Intangible Benefit</label>
                    <input type="text" name="intangible_benefit" class="form-control" value="{{ old('intangible_benefit') }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('ppsmbbyuser') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="button" onclick="cekSebelumSubmit()" class="btn text-white" style="background-color: #af2027;">Submit</button>
            </div>

        </form>
    </div>
</div>

<!-- Modal Blokir — taruh di luar card/form -->
<div class="modal fade" id="modalBlokir" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                        style="width: 36px; height: 36px; background-color: #f8d7da;">
                        <i class="bi bi-exclamation-triangle" style="color: #af2027; font-size: 16px;"></i>
                    </div>
                    <h6 class="modal-title fw-semibold mb-0" style="color: #af2027;">Pengajuan Ditolak</h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3 pb-2">
                <p class="text-muted mb-0" style="font-size: 14px;">
                    Departemen Anda memiliki <strong>3 atau lebih proyek dengan status UAT</strong> dan salah satunya sudah melebihi <strong>10 hari</strong>. Selesaikan terlebih dahulu sebelum mengajukan PPSMB baru.
                </p>
            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-sm text-white" 
                        style="background-color: #af2027; border-color: #af2027;" 
                        data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>

<script>
    function cekSebelumSubmit() {
        fetch('{{ route('ppsmbbyuser.checkuat') }}')
            .then(res => res.json())
            .then(data => {
                if (data.blocked) {
                    new bootstrap.Modal(document.getElementById('modalBlokir')).show();
                } else {
                    document.getElementById('formPpsmb').submit();
                }
            });
    }
</script>

@endsection