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
                            <option value="Ulangan Harian" {{ old('jenis', $ujian->jenis) == 'Ulangan Harian' || old('jenis', $ujian->jenis) == 'UH' ? 'selected' : '' }}>Ulangan Harian</option>
                            <option value="Sumatif Tengah Semester" {{ old('jenis', $ujian->jenis) == 'Sumatif Tengah Semester' || old('jenis', $ujian->jenis) == 'UTS' ? 'selected' : '' }}>Sumatif Tengah Semester</option>
                            <option value="Sumatif Akhir Semester" {{ old('jenis', $ujian->jenis) == 'Sumatif Akhir Semester' || old('jenis', $ujian->jenis) == 'UAS' ? 'selected' : '' }}>Sumatif Akhir Semester</option>
                            <option value="Try Out" {{ old('jenis', $ujian->jenis) == 'Try Out' ? 'selected' : '' }}>Try Out</option>
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
                        <label class="form-label fw-semibold text-slate-700">Durasi Pengerjaan (Menit) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="durasi_menit" value="{{ old('durasi_menit', $ujian->durasi_menit) }}" class="form-control" min="5" max="300" required>
                            <span class="input-group-text">Menit</span>
                        </div>
                    </div>

                    <!-- Kelas Target (Multiple Kelas) -->
                    <div class="col-12">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <div>
                                <label class="form-label fw-semibold text-slate-700 mb-0">
                                    <i class="fa-solid fa-users text-primary me-1"></i>Kelas Target <span class="text-danger">*</span>
                                </label>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-2" id="kelasCounterBadge">
                                    0 Kelas Dipilih
                                </span>
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary py-0.5 px-2" id="btnPilihSemuaKelas">
                                    <i class="fa-solid fa-check-double me-1"></i>Pilih Semua Kelas
                                </button>
                                @foreach($groupedKelas as $tingkat => $items)
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2 btn-tingkat-quick" data-tingkat="{{ $tingkat }}">
                                        Kelas {{ $tingkat }}
                                    </button>
                                @endforeach
                                <button type="button" class="btn btn-sm btn-outline-danger py-0.5 px-2" id="btnResetKelas">
                                    <i class="fa-solid fa-rotate-left me-1"></i>Reset
                                </button>
                            </div>
                        </div>

                        <div class="card border bg-light-subtle rounded-3 p-3 mb-1">
                            <div class="row g-3">
                                @foreach($groupedKelas as $tingkat => $kelasList)
                                    <div class="col-md-4">
                                        <div class="p-3 bg-white rounded-3 border h-100 shadow-2xs">
                                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                                <span class="small fw-bold text-slate-700">
                                                    <i class="fa-solid fa-graduation-cap text-primary me-1"></i>Tingkat {{ $tingkat }}
                                                </span>
                                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-primary fw-semibold btn-toggle-group" data-tingkat="{{ $tingkat }}" style="font-size: 11px;">
                                                    Pilih Tingkat
                                                </button>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1.5">
                                                @foreach($kelasList as $kls)
                                                    @php
                                                        if (old('kelas')) {
                                                            $isChecked = in_array($kls, (array) old('kelas'));
                                                        } else {
                                                            $isChecked = $ujian->kelas === 'Semua' || $ujian->matchesKelas($kls);
                                                        }
                                                        $inputSlug = 'kls_' . \Illuminate\Support\Str::slug($kls);
                                                    @endphp
                                                    <div class="me-1 mb-1">
                                                        <input type="checkbox"
                                                               class="btn-check kls-checkbox"
                                                               data-tingkat="{{ $tingkat }}"
                                                               name="kelas[]"
                                                               id="{{ $inputSlug }}"
                                                               value="{{ $kls }}"
                                                               autocomplete="off"
                                                               {{ $isChecked ? 'checked' : '' }}>
                                                        <label class="btn btn-sm btn-outline-secondary rounded-2 py-1 px-2.5 fw-semibold kls-pill"
                                                               for="{{ $inputSlug }}">
                                                            {{ $kls }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-text small text-muted">
                            <i class="fa-solid fa-circle-info me-1"></i>Pilih satu atau beberapa kelas target. Anda dapat mencentang kelas tertentu atau memilih seluruh angkatan menggunakan tombol cepat di atas.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-slate-700">Waktu Buka Ujian</label>
                        <input type="datetime-local" name="waktu_mulai" value="{{ old('waktu_mulai', $ujian->waktu_mulai ? $ujian->waktu_mulai->format('Y-m-d\TH:i') : '') }}" class="form-control">
                        <div class="form-text small">Kosongkan jika ingin dibuka secara manual kapan saja.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-slate-700">Waktu Tutup Ujian</label>
                        <input type="datetime-local" name="waktu_selesai" value="{{ old('waktu_selesai', $ujian->waktu_selesai ? $ujian->waktu_selesai->format('Y-m-d\TH:i') : '') }}" class="form-control">
                        <div class="form-text small">Batas akhir siswa dapat membuka ujian.</div>
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

<style>
.kls-pill {
    font-size: 13px;
    border-color: #cbd5e1;
    color: #475569;
    background-color: #f8fafc;
    transition: all 0.15s ease-in-out;
}
.kls-pill:hover {
    border-color: #2563eb;
    color: #2563eb;
}
.btn-check:checked + .kls-pill {
    background-color: #2563eb !important;
    border-color: #2563eb !important;
    color: #ffffff !important;
    font-weight: 700;
    box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
}
.btn-check:focus + .kls-pill {
    box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.kls-checkbox');
    const badge = document.getElementById('kelasCounterBadge');
    const btnPilihSemua = document.getElementById('btnPilihSemuaKelas');
    const btnReset = document.getElementById('btnResetKelas');
    const btnTingkatList = document.querySelectorAll('.btn-tingkat-quick, .btn-toggle-group');

    function updateCounter() {
        const checked = document.querySelectorAll('.kls-checkbox:checked');
        const count = checked.length;
        const total = checkboxes.length;
        if (count === 0) {
            badge.textContent = '0 Kelas Dipilih';
            badge.className = 'badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-2';
        } else if (count === total) {
            badge.textContent = `Semua Kelas Dipilih (${count})`;
            badge.className = 'badge bg-success-subtle text-success border border-success-subtle ms-2';
        } else {
            badge.textContent = `${count} Kelas Dipilih`;
            badge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle ms-2';
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateCounter);
    });

    if (btnPilihSemua) {
        btnPilihSemua.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = true);
            updateCounter();
        });
    }

    if (btnReset) {
        btnReset.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = false);
            updateCounter();
        });
    }

    btnTingkatList.forEach(btn => {
        btn.addEventListener('click', function() {
            const tingkat = this.getAttribute('data-tingkat');
            const targetCheckboxes = document.querySelectorAll(`.kls-checkbox[data-tingkat="${tingkat}"]`);
            const allChecked = Array.from(targetCheckboxes).every(cb => cb.checked);
            targetCheckboxes.forEach(cb => cb.checked = !allChecked);
            updateCounter();
        });
    });

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('.kls-checkbox:checked');
            if (checked.length === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal 1 kelas target asesmen.');
                badge.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

    updateCounter();
});
</script>
@endsection
