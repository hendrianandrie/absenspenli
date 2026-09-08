@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-plus-circle text-success me-2"></i> Tambah Kegiatan Penilaian Baru</h3>
        <p class="text-muted mb-0">Buat judul kegiatan (Tugas / Ulangan / UTS / UAS) dan langsung input nilai siswa.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('nilai.index', ['kelas' => $selectedKelas, 'mata_pelajaran_id' => $selectedMapelId]) }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Matriks
        </a>
    </div>
</div>

<form action="{{ route('nilai.kegiatan.store') }}" method="POST">
    @csrf
    <div class="card card-custom p-4 bg-white mb-4 shadow-sm">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-file-pen text-primary me-2"></i> Detail Kegiatan Penilaian</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Mata Pelajaran</label>
                <select name="mata_pelajaran_id" class="form-select" required>
                    @foreach($mapels as $m)
                        <option value="{{ $m->id }}" {{ $selectedMapelId == $m->id ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Kelas</label>
                <select name="kelas" class="form-select" required>
                    @foreach($daftarKelas as $k)
                        <option value="{{ $k }}" {{ $selectedKelas == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Jenis Penilaian</label>
                <select name="jenis" class="form-select" required>
                    <option value="Tugas">Tugas</option>
                    <option value="UH">Ulangan Harian (UH)</option>
                    <option value="UTS">UTS</option>
                    <option value="UAS">UAS</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Tanggal Pelaksanaan</label>
                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold fs-7">Nama / Judul Kegiatan</label>
                <input type="text" name="nama_kegiatan" class="form-control" placeholder="Contoh: UH 1 Bab 1 Aljabar" required>
            </div>
        </div>
    </div>

    <!-- Input Nilai Siswa -->
    <div class="card card-custom p-4 bg-white shadow-sm">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-check text-primary me-2"></i> Input Nilai Siswa Kelas {{ $selectedKelas }}</h5>
        
        @if($siswas->isEmpty())
            <div class="text-center py-4 text-muted">Belum ada siswa terdaftar di kelas {{ $selectedKelas }}.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th style="width: 200px;">Nilai (0 - 100)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siswas as $idx => $siswa)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td><span class="badge bg-light text-dark border font-monospace">{{ $siswa->nis }}</span></td>
                                <td class="fw-semibold">{{ $siswa->nama }}</td>
                                <td>
                                    <input type="number" step="0.1" min="0" max="100" name="nilai[{{ $siswa->id }}]" class="form-control fw-bold" placeholder="0 - 100">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-success px-4 py-2 rounded-3 shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Kegiatan & Nilai Siswa
                </button>
            </div>
        @endif
    </div>
</form>
@endsection
