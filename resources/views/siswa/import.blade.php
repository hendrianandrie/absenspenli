@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-file-excel text-success me-2"></i> Import Data Siswa dari Excel</h3>
        <p class="text-muted mb-0">Unggah berkas spreadsheet Excel (.xlsx, .xls) atau CSV untuk mengimpor data siswa secara masif.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
        <a href="{{ route('siswa.index') }}" class="btn btn-outline-primary shadow-sm">
            <i class="fa-solid fa-users me-1"></i> Ke Data Siswa
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Form Upload File -->
    <div class="col-lg-6">
        <div class="card card-custom p-4 bg-white shadow-sm h-100">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i> Unggah Berkas Spreadsheet</h5>
            
            <form action="{{ route('siswa.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="file" class="form-label fw-semibold">Pilih Berkas Excel / CSV</label>
                    <input type="file" name="file" class="form-control form-control-lg" accept=".xlsx,.xls,.csv" required>
                    <small class="text-muted mt-2 d-block">Format yang didukung: <strong>.xlsx, .xls, .csv</strong> (Ukuran maks: 10MB)</small>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success px-4 py-2 shadow-sm">
                        <i class="fa-solid fa-upload me-1"></i> Unggah & Impor Siswa
                    </button>
                    <a href="{{ route('siswa.downloadTemplate') }}" class="btn btn-outline-success px-3 py-2">
                        <i class="fa-solid fa-download me-1"></i> Unduh Template Contoh
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Panduan Format Kolom -->
    <div class="col-lg-6">
        <div class="card card-custom p-4 bg-white shadow-sm h-100">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-circle-info text-info me-2"></i> Panduan Format Header Excel</h5>
            <p class="text-muted fs-7 mb-2">Pastikan baris pertama (*heading row*) spreadsheet Anda memiliki nama kolom sebagai berikut:</p>
            
            <div class="table-responsive">
                <table class="table table-bordered align-middle fs-7 mb-3">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Kolom</th>
                            <th>Status</th>
                            <th>Keterangan / Variasi Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>nis</code></td>
                            <td><span class="badge bg-danger">Wajib</span></td>
                            <td>NIS unik siswa (opsional: <code>no_induk</code>)</td>
                        </tr>
                        <tr>
                            <td><code>nama</code></td>
                            <td><span class="badge bg-danger">Wajib</span></td>
                            <td>Nama lengkap siswa (opsional: <code>nama_siswa</code>)</td>
                        </tr>
                        <tr>
                            <td><code>kelas</code></td>
                            <td><span class="badge bg-danger">Wajib</span></td>
                            <td>Nama kelas, contoh: <code>7A</code>, <code>8B</code>, <code>9C</code></td>
                        </tr>
                        <tr>
                            <td><code>jenis_kelamin</code></td>
                            <td><span class="badge bg-secondary">Opsional</span></td>
                            <td>Opsi: <code>L</code> (Laki-laki) atau <code>P</code> (Perempuan)</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info py-2 px-3 mb-0 fs-7">
                <i class="fa-solid fa-lightbulb me-1"></i> <strong>Catatan:</strong> Jika NIS siswa sudah ada di database, sistem akan memperbarui data secara otomatis (*auto-update*) tanpa menduplikasi data.
            </div>
        </div>
    </div>
</div>
@endsection
