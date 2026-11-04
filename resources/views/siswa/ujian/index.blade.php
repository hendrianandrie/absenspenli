@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-slate-800 mb-1">
                <i class="fa-solid fa-file-pen text-primary me-2"></i>Asesmen Online (CBT)
            </h4>
            <p class="text-muted small mb-0">Daftar asesmen dan ujian online aktif untuk <strong>Kelas {{ $siswa->kelas }}</strong>.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Exams Grid -->
    <div class="row g-3">
        @forelse($ujians as $u)
            @php
                $sesi = $sesiMap->get($u->id);
                $isSelesai = $sesi && $sesi->status === 'selesai';
                $isMengerjakan = $sesi && $sesi->status === 'mengerjakan';
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 h-100 {{ $isSelesai ? 'border-top border-success border-4' : ($isMengerjakan ? 'border-top border-info border-4' : 'border-top border-primary border-4') }}">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <span class="badge bg-indigo-subtle text-indigo px-2.5 py-1" style="background:#e0e7ff; color:#3730a3;">
                                    {{ $u->jenis }}
                                </span>
                                @if($isSelesai)
                                    <span class="badge bg-success-subtle text-success border px-2 py-1 fw-bold">
                                        <i class="fa-solid fa-check-circle me-1"></i>Selesai
                                    </span>
                                @elseif($isMengerjakan)
                                    <span class="badge bg-info text-white px-2 py-1 d-inline-flex align-items-center gap-1 shadow-sm">
                                        <span class="pulse-dot"></span> Sedang Berjalan
                                    </span>
                                @else
                                    <span class="badge bg-primary text-white px-2 py-1 shadow-sm">
                                        Siap Dikerjakan
                                    </span>
                                @endif
                            </div>

                            <h5 class="fw-bold text-slate-800 mb-2" style="line-height: 1.4;">
                                {{ $u->judul }}
                            </h5>

                            <div class="text-slate-600 small mb-3">
                                <div><i class="fa-solid fa-book-open text-primary me-2"></i>{{ optional($u->mataPelajaran)->nama_mapel }}</div>
                                @if($u->user)
                                    <div><i class="fa-solid fa-chalkboard-user text-muted me-2"></i>Guru: {{ $u->user->name }}</div>
                                @endif
                            </div>

                            <div class="p-2.5 rounded bg-light border small text-muted mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span><i class="fa-regular fa-clock me-1"></i>Durasi:</span>
                                    <span class="fw-bold text-slate-700">{{ $u->durasi_menit }} Menit</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span><i class="fa-solid fa-list-ol me-1"></i>Jumlah Soal:</span>
                                    <span class="fw-bold text-slate-700">{{ $u->soals_count }} Butir PG</span>
                                </div>
                                @if(!empty($u->token) && !$isSelesai)
                                    <div class="d-flex justify-content-between">
                                        <span><i class="fa-solid fa-key me-1"></i>Token:</span>
                                        <span class="fw-bold text-danger">Memerlukan Token Pengawas</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div>
                            @if($isSelesai)
                                <div class="d-flex align-items-center justify-content-between p-2 rounded bg-success-subtle border border-success-subtle mb-3">
                                    <span class="small fw-semibold text-success">Nilai Asesmen Anda:</span>
                                    <span class="fs-5 fw-bold text-success">{{ $u->tampilkan_nilai ? $sesi->nilai_akhir : 'Disimpan' }}</span>
                                </div>
                                <a href="{{ route('siswa.ujian.hasil', $u->id) }}" class="btn btn-outline-success w-100">
                                    <i class="fa-solid fa-square-poll-vertical me-1"></i> Lihat Rekap Hasil
                                </a>
                            @elseif($isMengerjakan)
                                <a href="{{ route('siswa.ujian.kerjakan', $u->id) }}" class="btn btn-info text-white w-100 shadow-sm fw-semibold">
                                    <i class="fa-solid fa-play me-1"></i> Lanjutkan Mengerjakan
                                </a>
                            @else
                                <a href="{{ route('siswa.ujian.konfirmasi', $u->id) }}" class="btn btn-primary w-100 shadow-sm fw-semibold">
                                    <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Masuk Ruang Asesmen
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 p-5 text-center text-muted">
                    <i class="fa-solid fa-file-circle-check fs-1 text-slate-300 d-block mb-3"></i>
                    <h5 class="fw-bold text-slate-700 mb-1">Tidak Ada Asesmen Aktif Saat Ini</h5>
                    <p class="small text-muted mb-0">Belum ada ujian yang dibuka untuk kelas Anda. Jadwal ujian baru akan muncul secara otomatis ketika guru mengaktifkannya.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
