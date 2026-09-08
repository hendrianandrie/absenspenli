@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-user-graduate text-primary me-2"></i> Data Siswa</h3>
        <p class="text-muted mb-0">Kelola daftar siswa per kelas, tambah siswa baru, import Excel, atau hapus data kelas.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
        <a href="{{ route('siswa.importForm') }}" class="btn btn-outline-success shadow-sm">
            <i class="fa-solid fa-file-excel me-1"></i> Import Excel Siswa
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Form Tambah Siswa Baru -->
    <div class="col-lg-8">
        <div class="card card-custom p-4 bg-white shadow-sm h-100">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-user-plus text-primary me-2"></i> Tambah Siswa Baru</h5>
            <form method="POST" action="{{ route('siswa.store') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label fw-semibold fs-7">NIS</label>
                    <input type="text" name="nis" class="form-control" placeholder="Contoh: 8001" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold fs-7">Nama Lengkap Siswa</label>
                    <input type="text" name="nama" class="form-control" placeholder="Nama siswa" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold fs-7">Kelas</label>
                    <input type="text" name="kelas" class="form-control" placeholder="7A" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold fs-7">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select" required>
                        <option value="">Pilih JK</option>
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary-custom px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Data Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Fasilitas Hapus Seluruh Data Kelas -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 bg-white shadow-sm border-start border-danger border-4 h-100">
            <h5 class="fw-bold mb-2 text-danger"><i class="fa-solid fa-trash-can me-2"></i> Hapus Seluruh Kelas</h5>
            <p class="text-muted fs-7 mb-3">Pilih kelas untuk menghapus seluruh data siswa di kelas tersebut sekaligus.</p>
            
            <form action="{{ route('siswa.destroyKelas') }}" method="POST" onsubmit="return confirm('PERINGATAN! Apakah Anda yakin ingin menghapus SELURUH data siswa pada kelas ini?')">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Pilih Kelas yang Akan Dihapus</label>
                    <select name="kelas" class="form-select" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($daftarKelas as $k)
                            <option value="{{ $k }}">Kelas {{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-outline-danger w-100 py-2">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Hapus Semua Siswa Kelas Ini
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Tabel & Filter Daftar Siswa -->
<div class="card card-custom p-4 bg-white shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h5 class="fw-bold mb-0"><i class="fa-solid fa-list-check text-primary me-2"></i> Daftar Siswa</h5>
        
        <!-- Filter Kelas -->
        <form method="GET" action="{{ route('siswa.index') }}" class="d-flex align-items-center gap-2">
            <label class="fw-semibold fs-7 text-nowrap">Filter Kelas:</label>
            <select name="kelas" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Kelas</option>
                @foreach($daftarKelas as $k)
                    <option value="{{ $k }}" {{ $selectedKelas == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if($siswas->isEmpty())
        <div class="text-center py-4 text-muted">
            <i class="fa-solid fa-user-slash fa-2x mb-2 d-block opacity-50"></i>
            Belum ada data siswa terdaftar {{ $selectedKelas ? 'di Kelas ' . $selectedKelas : '' }}.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th class="text-center">Kelas</th>
                        <th class="text-center">Jenis Kelamin</th>
                        <th class="text-end" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswas as $no => $s)
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td><span class="badge bg-light text-dark border font-monospace fs-6">{{ $s->nis }}</span></td>
                            <td class="fw-semibold">{{ $s->nama }}</td>
                            <td class="text-center"><span class="badge bg-primary fs-6">Kelas {{ $s->kelas }}</span></td>
                            <td class="text-center">
                                @if($s->jenis_kelamin == 'L')
                                    <span class="badge bg-info-subtle text-info px-3">Laki-laki</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-3">Perempuan</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <!-- Form Hapus Per Siswa -->
                                <form action="{{ route('siswa.destroy', $s->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus siswa {{ $s->nama }} (NIS: {{ $s->nis }})?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Siswa">
                                        <i class="fa-solid fa-trash me-1"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
