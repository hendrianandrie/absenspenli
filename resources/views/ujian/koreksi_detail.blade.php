@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <a href="{{ route('ujian.koreksi.index', $ujian->id) }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Siswa
            </a>
            <h4 class="fw-bold text-slate-800 mb-1">
                <i class="fa-solid fa-pen-fancy text-primary me-2"></i>Koreksi Lembar Siswa: {{ optional($ujianSiswa->siswa)->nama }}
            </h4>
            <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
                <span>Kelas {{ optional($ujianSiswa->siswa)->kelas }}</span>
                <span>•</span>
                <span>NISN: {{ optional($ujianSiswa->siswa)->nisn ?: '-' }}</span>
                <span>•</span>
                <span>Nilai Akhir Saat Ini: <strong class="fs-6 text-primary">{{ $ujianSiswa->nilai_akhir }}</strong></span>
            </div>
        </div>

        <div>
            <span class="badge {{ $ujianSiswa->status_koreksi === 'menunggu_koreksi' ? 'bg-warning text-dark' : 'bg-success text-white' }} px-3 py-2 fs-6 rounded-pill shadow-xs">
                <i class="fa-solid {{ $ujianSiswa->status_koreksi === 'menunggu_koreksi' ? 'fa-clock' : 'fa-check-circle' }} me-1"></i>
                {{ $ujianSiswa->status_koreksi === 'menunggu_koreksi' ? 'Menunggu Koreksi' : 'Koreksi Selesai' }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('ujian.koreksi.simpan', [$ujian->id, $ujianSiswa->id]) }}">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8 col-xl-9">
                @foreach($essaySoals as $idx => $s)
                    @php
                        $jawabanObj = $jawabans->get($s->id);
                        $studentAnswer = $jawabanObj ? $jawabanObj->jawaban : null;
                        $currentScore = $jawabanObj ? $jawabanObj->skor_diperoleh : null;
                        $catatan = $jawabanObj ? $jawabanObj->catatan_koreksi : null;
                    @endphp

                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                        <div class="card-header bg-slate-50 border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary rounded-pill px-3 py-1.5 fs-6">
                                    Soal #{{ $s->nomor_urut }} (Essay)
                                </span>
                                <span class="badge bg-secondary-subtle text-secondary border">Bobot Maks: {{ $s->bobot }} Poin</span>
                            </div>
                            <div>
                                @if($currentScore !== null)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                        <i class="fa-solid fa-check me-1"></i>Telah Dinilai: {{ $currentScore }} / {{ $s->bobot }}
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1">
                                        <i class="fa-solid fa-hourglass-half me-1"></i>Belum Dinilai
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <!-- Pertanyaan -->
                            <div class="fw-semibold text-slate-800 mb-3 fs-6" style="line-height: 1.6;">
                                {!! nl2br(e($s->pertanyaan)) !!}
                            </div>

                            @if($s->gambar)
                                <div class="mb-3">
                                    <img src="{{ asset('storage/' . $s->gambar) }}" alt="Gambar Soal #{{ $s->nomor_urut }}" class="img-thumbnail rounded" style="max-height: 240px;">
                                </div>
                            @endif

                            @if(!empty($s->kunci_jawaban))
                                <div class="p-3 rounded-3 bg-light border border-slate-200 mb-3">
                                    <div class="small fw-bold text-slate-600 mb-1">
                                        <i class="fa-solid fa-lightbulb text-warning me-1"></i>Pedoman Penskoran / Kunci Jawaban Guru:
                                    </div>
                                    <div class="small text-slate-700" style="white-space: pre-wrap;">{{ $s->kunci_jawaban }}</div>
                                </div>
                            @endif

                            <!-- Jawaban Siswa -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-slate-700 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-comment-dots text-primary"></i>Jawaban Siswa (Teks):
                                </label>
                                <div class="p-3.5 rounded-3 bg-slate-50 border border-slate-200 text-slate-800 mb-3" style="min-height: 70px; line-height: 1.7; font-size: 15px; white-space: pre-wrap;">
                                    @if(!empty($studentAnswer))
                                        {{ $studentAnswer }}
                                    @else
                                        <em class="text-muted">(Siswa tidak mengisi jawaban teks pada butir ini)</em>
                                    @endif
                                </div>

                                @if($jawabanObj && $jawabanObj->gambar_jawaban)
                                    <div class="p-3 rounded-3 bg-white border border-slate-200">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-slate-800 small">
                                                <i class="fa-solid fa-camera text-primary me-1"></i>Lampiran Foto / Gambar Jawaban Siswa:
                                            </span>
                                            <a href="{{ asset('storage/' . $jawabanObj->gambar_jawaban) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2.5">
                                                <i class="fa-solid fa-up-right-from-square me-1"></i>Buka Ukuran Asli
                                            </a>
                                        </div>
                                        <div class="text-center p-2 bg-slate-50 rounded border">
                                            <a href="{{ asset('storage/' . $jawabanObj->gambar_jawaban) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $jawabanObj->gambar_jawaban) }}" alt="Lampiran Jawaban Siswa" class="img-fluid rounded border shadow-sm" style="max-height: 420px; object-fit: contain;">
                                            </a>
                                            <div class="form-text small mt-1">Klik gambar untuk melihat dalam resolusi penuh di tab baru.</div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Input Nilai & Catatan Guru -->
                            <div class="row g-3 p-3 rounded-3 bg-indigo-50 border border-indigo-100" style="background: #f8fafc;">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-slate-800">
                                        <i class="fa-solid fa-award text-success me-1"></i>Beri Nilai Skor (0 - {{ $s->bobot }}) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" 
                                               name="skor[{{ $s->id }}]" 
                                               step="0.1" 
                                               min="0" 
                                               max="{{ $s->bobot }}" 
                                               class="form-control form-control-lg fw-bold text-center text-primary" 
                                               value="{{ old('skor.' . $s->id, $currentScore !== null ? $currentScore : '') }}" 
                                               placeholder="0 - {{ $s->bobot }}" 
                                               required>
                                        <span class="input-group-text bg-white fw-bold text-muted">/ {{ $s->bobot }}</span>
                                    </div>
                                    <div class="form-text small">Masukkan poin yang diperoleh siswa.</div>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold text-slate-800">
                                        <i class="fa-solid fa-pencil text-muted me-1"></i>Catatan / Feedback Guru (Opsional)
                                    </label>
                                    <input type="text" 
                                           name="catatan[{{ $s->id }}]" 
                                           class="form-control" 
                                           value="{{ old('catatan.' . $s->id, $catatan) }}" 
                                           placeholder="Tuliskan catatan apresiasi atau evaluasi untuk siswa...">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Sticky Sidebar: Summary & Save Action -->
            <div class="col-lg-4 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 sticky-top p-4" style="top: 80px;">
                    <h5 class="fw-bold text-slate-800 mb-3">
                        <i class="fa-solid fa-floppy-disk text-primary me-2"></i>Simpan Penilaian
                    </h5>
                    <p class="small text-muted mb-4">
                        Pastikan seluruh skor butir essay telah terisi. Menekan tombol simpan akan mengakumulasi seluruh nilai (PG, Menjodohkan, dan Essay) menjadi Skor Akhir (0-100) secara otomatis.
                    </p>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-lg shadow fw-bold py-2.5">
                            <i class="fa-solid fa-check-double me-2"></i>Simpan Nilai Siswa
                        </button>
                        <a href="{{ route('ujian.koreksi.index', $ujian->id) }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

