@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-pen-to-square text-primary me-2"></i> Edit Nilai Kegiatan Penilaian</h3>
        <p class="text-muted mb-0">Ubah detail kegiatan dan nilai siswa untuk {{ $kegiatan->nama_kegiatan }}.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('nilai.index', ['kelas' => $kegiatan->kelas, 'mata_pelajaran_id' => $kegiatan->mata_pelajaran_id]) }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Matriks
        </a>
    </div>
</div>

<form action="{{ route('nilai.kegiatan.update', $kegiatan->id) }}" method="POST">
    @csrf
    <div class="card card-custom p-4 bg-white mb-4 shadow-sm">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-file-pen text-primary me-2"></i> Detail Kegiatan Penilaian</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Mata Pelajaran</label>
                <input type="text" class="form-control" value="{{ $kegiatan->mataPelajaran->nama_mapel ?? '' }}" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Kelas</label>
                <input type="text" class="form-control" value="Kelas {{ $kegiatan->kelas }}" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Jenis Penilaian</label>
                <select name="jenis" class="form-select" required>
                    <option value="Tugas" {{ $kegiatan->jenis == 'Tugas' ? 'selected' : '' }}>Tugas</option>
                    <option value="UH" {{ $kegiatan->jenis == 'UH' ? 'selected' : '' }}>Ulangan Harian (UH)</option>
                    <option value="UTS" {{ $kegiatan->jenis == 'UTS' ? 'selected' : '' }}>UTS</option>
                    <option value="UAS" {{ $kegiatan->jenis == 'UAS' ? 'selected' : '' }}>UAS</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Tanggal Pelaksanaan</label>
                <input type="date" name="tanggal" class="form-control" value="{{ $kegiatan->tanggal }}" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold fs-7">Nama / Judul Kegiatan</label>
                <input type="text" name="nama_kegiatan" class="form-control" value="{{ $kegiatan->nama_kegiatan }}" required>
            </div>
        </div>
    </div>

    <!-- Input Nilai Siswa -->
    <div class="card card-custom p-4 bg-white shadow-sm">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-check text-primary me-2"></i> Nilai Siswa Kelas {{ $kegiatan->kelas }}</h5>
        
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
                        @php
                            $val = $nilaiMap[$siswa->id] ?? '';
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><span class="badge bg-light text-dark border font-monospace">{{ $siswa->nis }}</span></td>
                            <td class="fw-semibold">{{ $siswa->nama }}</td>
                            <td>
                                <input type="number" step="0.1" min="0" max="100" name="nilai[{{ $siswa->id }}]" class="form-control fw-bold" value="{{ $val }}" placeholder="0 - 100">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary-custom px-4 py-2 rounded-3 shadow-sm">
                <i class="fa-solid fa-floppy-disk me-1"></i> Perbarui Nilai Kegiatan
            </button>
        </div>
    </div>
</form>
@endsection
