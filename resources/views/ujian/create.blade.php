@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('ujian.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Ujian
        </a>
        <h4 class="fw-bold text-slate-800 mb-1">
            <i class="fa-solid fa-plus-circle text-primary me-2"></i>Buat Paket Asesmen CBT Baru
        </h4>
        <p class="text-muted small mb-0">Isi pengaturan asesmen berikut. Setelah disimpan, Anda dapat menginput butir soal atau mengimpornya dari Excel.</p>
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
            <form method="POST" action="{{ route('ujian.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold text-slate-700">Judul Asesmen / Ujian <span class="text-danger">*</span></label>
                        <input type="text" name="judul" value="{{ old('judul') }}" class="form-control" placeholder="Contoh: Asesmen Sumatif Lingkup Materi 1 (Bab 1)" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-slate-700">Kategori / Jenis Penilaian <span class="text-danger">*</span></label>
                        <select name="jenis" class="form-select" required>
                            <option value="UH" {{ old('jenis') == 'UH' ? 'selected' : '' }}>Ulangan Harian (UH)</option>
                            <option value="UTS" {{ old('jenis') == 'UTS' ? 'selected' : '' }}>Penilaian Tengah Semester (UTS / STS)</option>
                            <option value="UAS" {{ old('jenis') == 'UAS' ? 'selected' : '' }}>Penilaian Akhir Semester (UAS / SAS)</option>
                            <option value="Sumatif" {{ old('jenis') == 'Sumatif' ? 'selected' : '' }}>Asesmen Sumatif Umum</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-slate-700">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="mata_pelajaran_id" class="form-select" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($mapels as $m)
                                <option value="{{ $m->id }}" {{ old('mata_pelajaran_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->nama_mapel }} (Tingkat: {{ $m->tingkat ?? 'Semua' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-slate-700">Kelas Target <span class="text-danger">*</span></label>
                        <select name="kelas" class="form-select" required>
                            <option value="Semua" {{ old('kelas') == 'Semua' ? 'selected' : '' }}>Semua Kelas (Angkatan)</option>
                            @foreach($daftarKelas as $kls)
                                <option value="{{ $kls }}" {{ old('kelas') == $kls ? 'selected' : '' }}>Kelas {{ $kls }}</option>
                            @endforeach
                        </select>
                        <div class="form-text small">Pilih kelas spesifik atau 'Semua Kelas' jika ujian diadakan serentak.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-slate-700">Durasi Pengerjaan (Menit) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="durasi_menit" value="{{ old('durasi_menit', 60) }}" class="form-control" min="5" max="300" required>
                            <span class="input-group-text">Menit</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-slate-700">Waktu Buka Ujian (Opsional)</label>
                        <input type="datetime-local" name="waktu_mulai" value="{{ old('waktu_mulai') }}" class="form-control">
                        <div class="form-text small">Kosongkan jika ingin dibuka secara manual kapan saja.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-slate-700">Waktu Tutup Ujian (Opsional)</label>
                        <input type="datetime-local" name="waktu_selesai" value="{{ old('waktu_selesai') }}" class="form-control">
                        <div class="form-text small">Batas akhir siswa dapat membuka ujian.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-slate-700">Petunjuk / Tata Tertib Ujian (Opsional)</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Tuliskan petunjuk pengerjaan soal atau aturan ujian...">{{ old('deskripsi') }}</textarea>
                    </div>

                    <!-- Pengaturan Tambahan -->
                    <div class="col-12">
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="fw-semibold text-slate-800 mb-2"><i class="fa-solid fa-sliders me-1"></i> Pengaturan Tambahan CBT:</div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="acak_soal" id="acakSoal" value="1" {{ old('acak_soal', '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold" for="acakSoal">Acak Urutan Soal Tiap Siswa</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="acak_opsi" id="acakOpsi" value="1" {{ old('acak_opsi') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold" for="acakOpsi">Acak Urutan Pilihan Opsi (A/B/C/D)</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="tampilkan_nilai" id="tampilkanNilai" value="1" {{ old('tampilkan_nilai', '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold" for="tampilkanNilai">Tampilkan Skor Langsung ke Siswa</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('ujian.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-arrow-right me-1"></i> Simpan & Lanjut Kelola Soal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
