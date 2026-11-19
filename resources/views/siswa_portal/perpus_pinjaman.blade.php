@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Page Title -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge rounded-pill px-3 py-1 bg-primary-subtle text-primary fw-semibold" style="font-size: 11px;">
                    <i class="fa-solid fa-book-reader me-1"></i> Aktivitas Membacaku
                </span>
                <span class="text-secondary opacity-50">&bull;</span>
                <span class="text-muted small">Peminjaman & Ulasan Buku</span>
            </div>
            <h3 class="fw-bold text-dark mb-0">Pinjaman & Resume Buku Siswa</h3>
            <p class="text-muted small mb-0">Pantau buku pinjamanmu, kirimkan ulasan/resume bacaan, dan raih poin literasi!</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('siswa.perpus.katalog') }}" class="btn btn-primary-custom rounded-pill px-3 py-2 shadow-sm fs-7">
                <i class="fa-solid fa-magnifying-glass me-1"></i> Cari Buku di Katalog
            </a>
            <a href="{{ route('siswa.perpus.leaderboard') }}" class="btn btn-outline-warning text-dark rounded-pill px-3 py-2 shadow-sm fs-7">
                <i class="fa-solid fa-trophy text-warning me-1"></i> Lihat Leaderboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-info me-2"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <strong>Mohon periksa kembali:</strong>
            <ul class="mb-0 mt-1 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- KPI Stats Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Poin & Badge -->
        <div class="col-6 col-md-3">
            <div class="card card-custom p-3 bg-white shadow-sm h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" 
                         style="width: 48px; height: 48px; background: {{ $badge['color'] ?? '#3b82f6' }};">
                        <i class="fa-solid {{ $badge['icon'] ?? 'fa-award' }} fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 11px; font-weight: 600;">TOTAL POIN SAYA</div>
                        <h4 class="fw-bold text-dark mb-0">{{ number_format($totalPoin) }}</h4>
                        <span class="badge rounded-pill px-2 py-0 fs-8 mt-1" style="background: {{ $badge['bg'] ?? '#eff6ff' }}; color: {{ $badge['color'] ?? '#3b82f6' }};">
                            {{ $badge['label'] ?? 'Novice' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Sedang Dipinjam -->
        <div class="col-6 col-md-3">
            <div class="card card-custom p-3 bg-white shadow-sm h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" 
                         style="width: 48px; height: 48px; background: #e0e7ff;">
                        <i class="fa-solid fa-book-open-reader fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 11px; font-weight: 600;">SEDANG DIPINJAM</div>
                        <h4 class="fw-bold text-dark mb-0">{{ $activeLoans->count() }} <span class="fs-7 fw-normal text-muted">Buku</span></h4>
                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-0 fs-8 mt-1">Aktif</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Total Selesai Dibaca -->
        <div class="col-6 col-md-3">
            <div class="card card-custom p-3 bg-white shadow-sm h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-success" 
                         style="width: 48px; height: 48px; background: #dcfce7;">
                        <i class="fa-solid fa-circle-check fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 11px; font-weight: 600;">SELESAI DIBACA</div>
                        <h4 class="fw-bold text-dark mb-0">{{ $totalBukuDibaca }} <span class="fs-7 fw-normal text-muted">Buku</span></h4>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0 fs-8 mt-1">+10 pts/buku tepat waktu</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Resume Disetujui -->
        <div class="col-6 col-md-3">
            <div class="card card-custom p-3 bg-white shadow-sm h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-warning" 
                         style="width: 48px; height: 48px; background: #fef3c7;">
                        <i class="fa-solid fa-feather-pointed fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 11px; font-weight: 600;">RESUME DISETUJUI</div>
                        <h4 class="fw-bold text-dark mb-0">{{ $totalResumeDisetujui }} <span class="fs-7 fw-normal text-muted">Ulasan</span></h4>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0 fs-8 mt-1">+15 pts/ulasan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Buku yang Sedang Dipinjam -->
    <div class="card card-custom p-4 bg-white mb-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-book-bookmark text-primary me-2"></i>Buku yang Sedang Dipinjam
                </h5>
                <p class="text-muted small mb-0">Kembalikan tepat waktu sebelum tanggal jatuh tempo agar mendapatkan +10 poin literasi!</p>
            </div>
            <span class="badge bg-primary rounded-pill px-3 py-2 fs-7">{{ $activeLoans->count() }} Sedang Aktif</span>
        </div>

        @if($activeLoans->count() > 0)
            <div class="row g-3">
                @foreach($activeLoans as $loan)
                    @php
                        $isTerlambat = $loan->is_terlambat;
                        $hariTerlambat = $loan->hari_terlambat;
                        $resume = $loan->resume;
                    @endphp
                    <div class="col-12 col-lg-6">
                        <div class="p-3 rounded-4 border {{ $isTerlambat ? 'border-danger bg-danger-subtle bg-opacity-10' : 'border-light-subtle bg-light bg-opacity-50' }} h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <span class="badge rounded-pill px-2 py-1 fs-8" 
                                          style="background: {{ $loan->buku->kategori->warna ?? '#3b82f6' }}20; color: {{ $loan->buku->kategori->warna ?? '#3b82f6' }};">
                                        <i class="fa-solid {{ $loan->buku->kategori->icon ?? 'fa-book' }} me-1"></i>
                                        {{ $loan->buku->kategori->nama_kategori ?? 'Umum' }}
                                    </span>

                                    @if($isTerlambat)
                                        <span class="badge bg-danger text-white rounded-pill px-2 py-1 fs-8">
                                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Terlambat {{ $hariTerlambat }} Hari
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fs-8">
                                            <i class="fa-solid fa-clock me-1"></i> Masih Dalam Masa Pinjam
                                        </span>
                                    @endif
                                </div>

                                <h6 class="fw-bold text-dark mb-1">{{ $loan->buku->judul }}</h6>
                                <p class="text-muted small mb-3">
                                    <i class="fa-solid fa-pen-nib me-1"></i> {{ $loan->buku->pengarang ?? '-' }} &bull;
                                    <span class="text-secondary"><i class="fa-solid fa-barcode me-1"></i> {{ $loan->buku->kode_buku }}</span>
                                </p>

                                <div class="row g-2 mb-3 fs-8 text-secondary">
                                    <div class="col-6">
                                        <div class="p-2 rounded-3 bg-white border">
                                            <div class="text-muted">Tanggal Pinjam:</div>
                                            <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($loan->tanggal_pinjam)->translatedFormat('d M Y') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 rounded-3 bg-white border">
                                            <div class="text-muted">Jatuh Tempo:</div>
                                            <div class="fw-semibold {{ $isTerlambat ? 'text-danger' : 'text-primary' }}">
                                                {{ \Carbon\Carbon::parse($loan->tanggal_jatuh_tempo)->translatedFormat('d M Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resume Status & Action Button -->
                            <div class="pt-2 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    @if(!$resume)
                                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1 fs-8">
                                            <i class="fa-solid fa-pencil me-1"></i> Belum ada ulasan
                                        </span>
                                    @elseif($resume->status === 'menunggu')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fs-8">
                                            <i class="fa-solid fa-hourglass-half me-1"></i> Menunggu Verifikasi Petugas
                                        </span>
                                    @elseif($resume->status === 'disetujui')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fs-8">
                                            <i class="fa-solid fa-circle-check me-1"></i> Disetujui (+{{ $resume->poin_diberikan }} Pts)
                                        </span>
                                    @elseif($resume->status === 'ditolak')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 fs-8">
                                            <i class="fa-solid fa-circle-xmark me-1"></i> Perlu Revisi
                                        </span>
                                    @endif
                                </div>

                                <button type="button" class="btn btn-sm {{ $resume && $resume->status === 'disetujui' ? 'btn-outline-success' : 'btn-primary-custom' }} rounded-pill px-3"
                                        data-bs-toggle="modal" data-bs-target="#modalResume{{ $loan->id }}">
                                    <i class="fa-solid {{ $resume ? 'fa-eye' : 'fa-feather-pointed' }} me-1"></i>
                                    {{ $resume ? 'Lihat / Edit Ulasan' : 'Tulis Ulasan & Resume' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Tulis Resume -->
                    <div class="modal fade" id="modalResume{{ $loan->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content rounded-4 border-0 shadow">
                                <div class="modal-header border-bottom-0 pb-0">
                                    <div>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fs-8 mb-1">
                                            <i class="fa-solid fa-feather me-1"></i> Ulasan & Resume Literasi
                                        </span>
                                        <h5 class="modal-title fw-bold text-dark">{{ $loan->buku->judul }}</h5>
                                        <p class="text-muted small mb-0">Oleh {{ $loan->buku->pengarang ?? 'Anonim' }}</p>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('siswa.perpus.resume.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="peminjaman_id" value="{{ $loan->id }}">
                                    <div class="modal-body py-3">
                                        @if($resume && $resume->status === 'ditolak')
                                            <div class="alert alert-danger border-0 rounded-3 p-3 mb-3 small">
                                                <strong><i class="fa-solid fa-circle-exclamation me-1"></i> Catatan Revisi dari Petugas:</strong>
                                                <p class="mb-0 mt-1">{{ $resume->catatan_petugas ?? 'Mohon perbaiki rangkuman agar lebih mendalam dan mencerminkan isi buku.' }}</p>
                                            </div>
                                        @elseif($resume && $resume->status === 'disetujui')
                                            <div class="alert alert-success border-0 rounded-3 p-3 mb-3 small">
                                                <strong><i class="fa-solid fa-circle-check me-1"></i> Ulasan Ini Telah Disetujui!</strong>
                                                <p class="mb-0 mt-1">Kamu telah mendapatkan <strong>+{{ $resume->poin_diberikan }} poin literasi</strong> untuk ulasan ini. Hebat!</p>
                                                @if($resume->catatan_petugas)
                                                    <div class="mt-2 text-secondary fst-italic">"{{ $resume->catatan_petugas }}"</div>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small mb-1">
                                                Berapa bintang untuk buku ini? (Rating 1 - 5) <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex align-items-center gap-3">
                                                @for($star = 1; $star <= 5; $star++)
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="rating" id="rating_{{ $loan->id }}_{{ $star }}" 
                                                               value="{{ $star }}" {{ ($resume->rating ?? 5) == $star ? 'checked' : '' }}
                                                               {{ $resume && $resume->status === 'disetujui' ? 'disabled' : '' }}>
                                                        <label class="form-check-label text-warning fw-bold" for="rating_{{ $loan->id }}_{{ $star }}">
                                                            {{ $star }} <i class="fa-solid fa-star"></i>
                                                        </label>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small mb-1">
                                                Rangkuman / Inti Cerita Buku <span class="text-danger">*</span>
                                            </label>
                                            <textarea name="ringkasan" class="form-control" rows="4" 
                                                      placeholder="Ceritakan dengan bahasamu sendiri apa isi buku ini, tokoh utama, jalan cerita, atau topik yang dibahas (minimal 30 karakter)..." 
                                                      required {{ $resume && $resume->status === 'disetujui' ? 'readonly' : '' }}>{{ old('ringkasan', $resume->ringkasan ?? '') }}</textarea>
                                            <div class="form-text fs-8 text-muted">Minimal 30 karakter agar dapat dinilai oleh Petugas Perpustakaan.</div>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label fw-semibold text-dark small mb-1">
                                                Pelajaran / Pesan Moral yang Didapat <span class="text-muted fw-normal">(Opsional)</span>
                                            </label>
                                            <input type="text" name="pesan_moral" class="form-control" 
                                                   value="{{ old('pesan_moral', $resume->pesan_moral ?? '') }}" 
                                                   placeholder="Contoh: Kejujuran dan pantang menyerah selalu membuahkan hasil..."
                                                   {{ $resume && $resume->status === 'disetujui' ? 'readonly' : '' }}>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top-0 pt-0">
                                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                                        @if(!$resume || $resume->status !== 'disetujui')
                                            <button type="submit" class="btn btn-primary-custom rounded-pill px-4">
                                                <i class="fa-solid fa-paper-plane me-1"></i> Kirimkan Ulasan
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 bg-light rounded-4">
                <i class="fa-solid fa-book-open text-secondary opacity-50 fs-2 mb-2"></i>
                <h6 class="fw-semibold text-secondary mb-1">Saat ini kamu belum memiliki pinjaman buku aktif</h6>
                <p class="small text-muted mb-3">Yuk mampir ke perpustakaan sekolah dan pinjam buku favoritmu!</p>
                <a href="{{ route('siswa.perpus.katalog') }}" class="btn btn-sm btn-primary-custom rounded-pill px-3">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Buka Katalog Buku
                </a>
            </div>
        @endif
    </div>

    <!-- Section 2: Riwayat Pengembalian & Riwayat Ulasan -->
    <div class="card card-custom p-4 bg-white mb-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-history text-secondary me-2"></i>Riwayat Pengembalian Buku
                </h5>
                <p class="text-muted small mb-0">Buku-buku yang pernah kamu pinjam dan kembalikan sebelumnya.</p>
            </div>
        </div>

        @if($pastLoans->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light fs-8 text-secondary">
                        <tr>
                            <th class="ps-3">KODE & BUKU</th>
                            <th>TANGGAL PINJAM</th>
                            <th>TANGGAL KEMBALI</th>
                            <th>STATUS KEMBALI</th>
                            <th>ULASAN / RESUME</th>
                            <th class="text-end pe-3">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="fs-8">
                        @foreach($pastLoans as $past)
                            @php $pastResume = $past->resume; @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark">{{ $past->buku->judul }}</div>
                                    <div class="text-muted fs-8">{{ $past->buku->kode_buku }} &bull; {{ $past->buku->pengarang }}</div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($past->tanggal_pinjam)->translatedFormat('d M Y') }}</td>
                                <td>{{ $past->tanggal_kembali ? \Carbon\Carbon::parse($past->tanggal_kembali)->translatedFormat('d M Y') : '-' }}</td>
                                <td>
                                    @if($past->status === 'kembali')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> Selesai
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1">
                                            {{ ucfirst($past->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$pastResume)
                                        <span class="badge bg-light text-muted border rounded-pill px-2 py-1">Belum ditulis</span>
                                    @elseif($pastResume->status === 'menunggu')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1">Menunggu</span>
                                    @elseif($pastResume->status === 'disetujui')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">+{{ $pastResume->poin_diberikan }} Pts Disetujui</span>
                                    @elseif($pastResume->status === 'ditolak')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">Perlu Revisi</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fs-8"
                                            data-bs-toggle="modal" data-bs-target="#modalPastResume{{ $past->id }}">
                                        <i class="fa-solid fa-feather me-1"></i> {{ $pastResume ? 'Detail Ulasan' : 'Tulis Ulasan' }}
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Resume Past Loan -->
                            <div class="modal fade" id="modalPastResume{{ $past->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <div>
                                                <h5 class="modal-title fw-bold text-dark">{{ $past->buku->judul }}</h5>
                                                <p class="text-muted small mb-0">{{ $past->buku->pengarang ?? 'Anonim' }}</p>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('siswa.perpus.resume.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="peminjaman_id" value="{{ $past->id }}">
                                            <div class="modal-body py-3">
                                                @if($pastResume && $pastResume->status === 'disetujui')
                                                    <div class="alert alert-success border-0 rounded-3 p-3 mb-3 small">
                                                        <i class="fa-solid fa-circle-check me-1"></i> Disetujui! Kamu meraih <strong>+{{ $pastResume->poin_diberikan }} poin</strong> untuk ulasan ini.
                                                    </div>
                                                @endif

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold text-dark small mb-1">Rating</label>
                                                    <div class="d-flex align-items-center gap-3">
                                                        @for($star = 1; $star <= 5; $star++)
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="rating" id="past_rating_{{ $past->id }}_{{ $star }}" 
                                                                       value="{{ $star }}" {{ ($pastResume->rating ?? 5) == $star ? 'checked' : '' }}
                                                                       {{ $pastResume && $pastResume->status === 'disetujui' ? 'disabled' : '' }}>
                                                                <label class="form-check-label text-warning fw-bold" for="past_rating_{{ $past->id }}_{{ $star }}">
                                                                    {{ $star }} <i class="fa-solid fa-star"></i>
                                                                </label>
                                                            </div>
                                                        @endfor
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold text-dark small mb-1">Rangkuman / Inti Cerita</label>
                                                    <textarea name="ringkasan" class="form-control" rows="4" required {{ $pastResume && $pastResume->status === 'disetujui' ? 'readonly' : '' }}>{{ old('ringkasan', $pastResume->ringkasan ?? '') }}</textarea>
                                                </div>

                                                <div class="mb-2">
                                                    <label class="form-label fw-semibold text-dark small mb-1">Pesan Moral</label>
                                                    <input type="text" name="pesan_moral" class="form-control" 
                                                           value="{{ old('pesan_moral', $pastResume->pesan_moral ?? '') }}" 
                                                           {{ $pastResume && $pastResume->status === 'disetujui' ? 'readonly' : '' }}>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0">
                                                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                                                @if(!$pastResume || $pastResume->status !== 'disetujui')
                                                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">Simpan Ulasan</button>
                                                @endif
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $pastLoans->links() }}
            </div>
        @else
            <div class="text-center py-4 text-muted small">
                Belum ada riwayat pengembalian buku sebelumnya.
            </div>
        @endif
    </div>

    <!-- Section 3: Riwayat Perolehan Poin Literasi -->
    <div class="card card-custom p-4 bg-white shadow-sm">
        <h5 class="fw-bold text-dark mb-1">
            <i class="fa-solid fa-star text-warning me-2"></i>Log Perolehan Poin Literasiku
        </h5>
        <p class="text-muted small mb-3">Catatan perolehan poin dari aktivitas pengembalian tepat waktu dan ulasan buku.</p>

        @if($poinLogs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light fs-8 text-secondary">
                        <tr>
                            <th class="ps-3">WAKTU</th>
                            <th>AKTIVITAS</th>
                            <th>KETERANGAN</th>
                            <th class="text-end pe-3">POIN</th>
                        </tr>
                    </thead>
                    <tbody class="fs-8">
                        @foreach($poinLogs as $log)
                            <tr>
                                <td class="ps-3 text-muted">
                                    {{ $log->created_at->format('d M Y, H:i') }}
                                </td>
                                <td>
                                    @if($log->poin > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                            <i class="fa-solid fa-arrow-trend-up me-1"></i> {{ ucwords(str_replace('_', ' ', $log->aktivitas)) }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                            <i class="fa-solid fa-arrow-trend-down me-1"></i> {{ ucwords(str_replace('_', ' ', $log->aktivitas)) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-secondary">{{ $log->keterangan ?? '-' }}</td>
                                <td class="text-end pe-3">
                                    <span class="fw-bold fs-7 {{ $log->poin >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $log->poin > 0 ? '+' : '' }}{{ $log->poin }} Pts
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-muted small">
                Belum ada log perolehan poin. Mulai pinjam dan baca bukumu sekarang!
            </div>
        @endif
    </div>
</div>

<style>
.fs-7 { font-size: 0.875rem !important; }
.fs-8 { font-size: 0.775rem !important; }
.card-custom {
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
}
.btn-primary-custom {
    background-color: #4f46e5;
    color: white;
    border: none;
    font-weight: 500;
}
.btn-primary-custom:hover {
    background-color: #4338ca;
    color: white;
}
</style>
@endsection
