@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('ujian.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Ujian
        </a>
        <h4 class="fw-bold text-slate-800 mb-1">
            <i class="fa-solid fa-pen-to-square text-warning me-2"></i>Edit Pengaturan Ujian: {{ $ujian->judul }}
        </h4>
        <p class="text-muted small mb-0">Ubah konfigurasi waktu, target kelas, durasi, atau tata tertib asesmen.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Terjadi kesalahan validasi:</div>
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('ujian.update', $ujian->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold text-slate-700">Judul Asesmen / Ujian <span class="text-danger">*</span></label>
                        <input type="text" name="judul" value="{{ old('judul', $ujian->judul) }}" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-slate-700">Kategori / Jenis Penilaian <span class="text-danger">*</span></label>
                        <select name="jenis" class="form-select" required>
                            <option value="UH" {{ old('jenis', $ujian->jenis) == 'UH' ? 'selected' : '' }}>Ulangan Harian (UH)</option>
                            <option value="UTS" {{ old('jenis', $ujian->jenis) == 'UTS' ? 'selected' : '' }}>Penilaian Tengah Semester (UTS / STS)</option>
                            <option value="UAS" {{ old('jenis', $ujian->jenis) == 'UAS' ? 'selected' : '' }}>Penilaian Akhir Semester (UAS / SAS)</option>
                            <option value="Sumatif" {{ old('jenis', $ujian->jenis) == 'Sumatif' ? 'selected' : '' }}>Asesmen Sumatif Umum</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-slate-700">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="mata_pelajaran_id" class="form-select" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($mapels as $m)
                                <option value="{{ $m->id }}" {{ old('mata_pelajaran_id', $ujian->mata_pelajaran_id) == $m->id ? 'selected' : '' }}>
                                    {{ $m->nama_mapel }} (Tingkat: {{ $m->tingkat ?? 'Semua' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-slate-700">Kelas Target <span class="text-danger">*</span></label>
                        <select name="kelas" class="form-select" required>
                            <option value="Semua" {{ old('kelas', $ujian->kelas) == 'Semua' ? 'selected' : '' }}>Semua Kelas (Angkatan)</option>
                            @foreach($daftarKelas as $kls)
                                <option value="{{ $kls }}" {{ old('kelas', $ujian->kelas) == $kls ? 'selected' : '' }}>Kelas {{ $kls }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-slate-700">Durasi Pengerjaan (Menit) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="durasi_menit" value="{{ old('durasi_menit', $ujian->durasi_menit) }}" class="form-control" min="5" max="300" required>
                            <span class="input-group-text">Menit</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-slate-700">Waktu Buka Ujian</label>
                        <input type="datetime-local" name="waktu_mulai" value="{{ old('waktu_mulai', $ujian->waktu_mulai ? $ujian->waktu_mulai->format('Y-m-d\TH:i') : '') }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-slate-700">Waktu Tutup Ujian</label>
                        <input type="datetime-local" name="waktu_selesai" value="{{ old('waktu_selesai', $ujian->waktu_selesai ? $ujian->waktu_selesai->format('Y-m-d\TH:i') : '') }}" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-slate-700">Petunjuk / Tata Tertib Ujian</label>
                        <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $ujian->deskripsi) }}</textarea>
                    </div>

                    <div class="col-12">
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="fw-semibold text-slate-800 mb-2"><i class="fa-solid fa-sliders me-1"></i> Pengaturan Tambahan CBT:</div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="acak_soal" id="acakSoal" value="1" {{ old('acak_soal', $ujian->acak_soal) ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold" for="acakSoal">Acak Urutan Soal Tiap Siswa</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="acak_opsi" id="acakOpsi" value="1" {{ old('acak_opsi', $ujian->acak_opsi) ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold" for="acakOpsi">Acak Urutan Pilihan Opsi (A/B/C/D)</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="tampilkan_nilai" id="tampilkanNilai" value="1" {{ old('tampilkan_nilai', $ujian->tampilkan_nilai) ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold" for="tampilkanNilai">Tampilkan Skor Langsung ke Siswa</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="{{ route('ujian.soal', $ujian->id) }}" class="btn btn-outline-primary">
                        <i class="fa-solid fa-list-check me-1"></i> Ke Halaman Bank Soal
                    </a>
                    <div class="d-flex gap-2">
                        <a href="{{ route('ujian.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-check me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
