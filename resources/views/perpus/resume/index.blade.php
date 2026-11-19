@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Banner -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 fs-7">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Verifikasi Resume Literasi</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-feather-pointed text-primary me-2"></i>Verifikasi Resume & Ulasan Buku
            </h3>
            <p class="text-muted small mb-0">Periksa ringkasan buku yang dikirimkan siswa untuk memberikan apresiasi dan poin literasi resmi.</p>
        </div>
        <div>
            <a href="{{ route('perpus.leaderboard.index') }}" class="btn btn-outline-primary rounded-3 shadow-sm">
                <i class="fa-solid fa-trophy me-1"></i> Buka Leaderboard Literasi
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-info me-2"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Kartu Statistik Resume -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white border-start border-warning border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Menunggu Verifikasi</span>
                        <h2 class="fw-bold mb-0 text-warning">{{ number_format($stats['total_menunggu']) }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Perlu persetujuan petugas</small>
                    </div>
                    <div class="bg-warning-subtle p-3 rounded-circle text-warning">
                        <i class="fa-solid fa-clock fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white border-start border-success border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Disetujui (+Poin)</span>
                        <h2 class="fw-bold mb-0 text-success">{{ number_format($stats['total_disetujui']) }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Ulasan berkualitas</small>
                    </div>
                    <div class="bg-success-subtle p-3 rounded-circle text-success">
                        <i class="fa-solid fa-circle-check fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white border-start border-danger border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Minta Perbaiki</span>
                        <h2 class="fw-bold mb-0 text-danger">{{ number_format($stats['total_ditolak']) }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Perlu revisi siswa</small>
                    </div>
                    <div class="bg-danger-subtle p-3 rounded-circle text-danger">
                        <i class="fa-solid fa-rotate-left fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white border-start border-info border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Total Ulasan Masuk</span>
                        <h2 class="fw-bold mb-0 text-info">{{ number_format($stats['total_all']) }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Karya literasi siswa</small>
                    </div>
                    <div class="bg-info-subtle p-3 rounded-circle text-info">
                        <i class="fa-solid fa-book-open-reader fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Bar -->
    <div class="card card-custom p-3 bg-white mb-4 shadow-sm">
        <form method="GET" action="{{ route('perpus.resume.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold fs-7 mb-1"><i class="fa-solid fa-magnifying-glass text-primary me-1"></i> Cari Judul Buku / Nama Siswa</label>
                <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Ketik nama siswa, NIS, atau judul buku...">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7 mb-1"><i class="fa-solid fa-filter text-primary me-1"></i> Status Verifikasi</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="menunggu" {{ $status === 'menunggu' ? 'selected' : '' }}>Menunggu Verifikasi (Antrean)</option>
                    <option value="disetujui" {{ $status === 'disetujui' ? 'selected' : '' }}>Disetujui (+Poin Cair)</option>
                    <option value="ditolak" {{ $status === 'ditolak' ? 'selected' : '' }}>Minta Perbaiki</option>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold fs-7 mb-1"><i class="fa-solid fa-chalkboard-user text-primary me-1"></i> Kelas</label>
                <select name="kelas" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    @foreach($daftarKelas as $k)
                        <option value="{{ $k }}" {{ $kelas === $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary-custom w-100 py-2">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                @if($search || $status !== 'menunggu' || $kelas)
                    <a href="{{ route('perpus.resume.index') }}" class="btn btn-outline-secondary py-2" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Resume -->
    <div class="card card-custom p-4 bg-white shadow-sm">
        @if($resumes->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-feather-pointed fs-1 mb-3 text-secondary opacity-50"></i>
                <h5 class="fw-semibold text-secondary">Tidak ada resume buku pada kategori ini</h5>
                <p class="small text-muted mb-0">Ulasan yang dikirimkan siswa melalui Portal Siswa akan muncul di halaman ini untuk diverifikasi.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45px;">No</th>
                            <th style="min-width: 180px;">Siswa Pengirim</th>
                            <th style="min-width: 220px;">Buku & Penilaian Bintang</th>
                            <th style="min-width: 280px;">Intisari / Rangkuman Bacaan</th>
                            <th style="width: 140px;" class="text-center">Status & Poin</th>
                            <th style="width: 130px;" class="text-center">Tindakan Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resumes as $idx => $r)
                            <tr>
                                <td class="text-muted text-center">{{ $resumes->firstItem() + $idx }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $r->siswa->nama }}</div>
                                    <div class="small text-muted font-monospace">
                                        NIS: {{ $r->siswa->nis }} &bull; <span class="badge bg-secondary-subtle text-secondary">Kelas {{ $r->siswa->kelas }}</span>
                                    </div>
                                    <div class="fs-8 text-muted mt-1" style="font-size: 11px;">
                                        Dikirim: {{ $r->created_at->translatedFormat('d M Y, H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark mb-1">{{ $r->judul_buku }}</div>
                                    <div class="d-flex align-items-center gap-1 mb-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa-solid fa-star fs-8 {{ $i <= $r->rating ? 'text-warning' : 'text-slate-300' }}"></i>
                                        @endfor
                                        <span class="small fw-semibold text-muted ms-1">({{ $r->rating }}/5)</span>
                                    </div>
                                    @if($r->pesan_moral)
                                        <div class="small fst-italic text-secondary" style="font-size: 11.5px;">
                                            <i class="fa-solid fa-quote-left me-1 text-primary opacity-50"></i>{{ Str::limit($r->pesan_moral, 80) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-slate-700 small" style="line-height: 1.5;">
                                        {{ Str::limit($r->ringkasan, 160) }}
                                    </div>
                                    @if(strlen($r->ringkasan) > 160)
                                        <a href="javascript:void(0)" class="small text-primary text-decoration-none fw-semibold" data-bs-toggle="modal" data-bs-target="#modalDetailResume{{ $r->id }}">
                                            Baca Selengkapnya...
                                        </a>
                                    @endif
                                    @if($r->catatan_petugas)
                                        <div class="mt-2 p-2 rounded-2 bg-light border small text-muted">
                                            <i class="fa-solid fa-comment-dots text-secondary me-1"></i> Catatan: {{ $r->catatan_petugas }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($r->status === 'disetujui')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> Disetujui
                                        </span>
                                        <div class="fw-bold text-success fs-7 mt-1">+{{ $r->poin_diberikan }} Poin</div>
                                    @elseif($r->status === 'ditolak')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Perlu Revisi
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1">
                                            <i class="fa-solid fa-clock me-1"></i> Menunggu
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-2 p-1 px-2" title="Baca Lengkap" data-bs-toggle="modal" data-bs-target="#modalDetailResume{{ $r->id }}">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        @if($r->status !== 'disetujui')
                                            <button type="button" class="btn btn-sm btn-success rounded-2 p-1 px-2" title="Setujui (+Poin)" data-bs-toggle="modal" data-bs-target="#modalApproveResume{{ $r->id }}">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-2 p-1 px-2" title="Minta Perbaiki" data-bs-toggle="modal" data-bs-target="#modalRejectResume{{ $r->id }}">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $resumes->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ================= MODALS (Ditempatkan di luar tabel dan card agar stabil dan tidak kelap-kelip) ================= -->
@if(!$resumes->isEmpty())
    @foreach($resumes as $r)
        <!-- Modal Detail Lengkap Resume -->
        <div class="modal fade" id="modalDetailResume{{ $r->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 text-start">
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-book-open text-primary me-2"></i>Karya Ulasan Bacaan Siswa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="p-3 rounded-3 bg-light border mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h5 class="fw-bold text-dark mb-0">{{ $r->judul_buku }}</h5>
                                <div>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star {{ $i <= $r->rating ? 'text-warning' : 'text-slate-300' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <div class="small text-muted font-monospace">
                                Ditulis oleh: <strong>{{ $r->siswa->nama }}</strong> &bull; Kelas: {{ $r->siswa->kelas }} &bull; NIS: {{ $r->siswa->nis }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold text-dark fs-7"><i class="fa-solid fa-align-left text-primary me-1"></i> Intisari & Rangkuman Buku:</h6>
                            <div class="p-3 bg-white border rounded-3 text-slate-800" style="line-height: 1.6; white-space: pre-line;">
                                {{ $r->ringkasan }}
                            </div>
                        </div>

                        @if($r->pesan_moral)
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark fs-7"><i class="fa-solid fa-heart text-danger me-1"></i> Pesan Moral / Hal yang Dipelajari:</h6>
                                <div class="p-3 bg-white border rounded-3 fst-italic text-secondary">
                                    "{{ $r->pesan_moral }}"
                                </div>
                            </div>
                        @endif

                        @if($r->status === 'disetujui')
                            <div class="alert alert-success py-2 px-3 small border-0 d-flex align-items-center gap-2 rounded-3 mb-0">
                                <i class="fa-solid fa-circle-check fs-5"></i>
                                <div>
                                    Telah disetujui oleh <strong>{{ optional($r->verifikator)->name ?? 'Petugas Perpustakaan' }}</strong> pada {{ optional($r->tanggal_verifikasi)->translatedFormat('d F Y, H:i') }} (+{{ $r->poin_diberikan }} Poin Literasi).
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Approve Resume -->
        <div class="modal fade" id="modalApproveResume{{ $r->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 text-start">
                    <form action="{{ route('perpus.resume.approve', $r->id) }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-circle-check text-success me-2"></i>Setujui Resume & Berikan Poin</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="small text-muted mb-3">
                                Ulasan dari <strong>{{ $r->siswa->nama }}</strong> untuk buku <em>{{ $r->judul_buku }}</em> akan disetujui dan poin literasi akan otomatis dicatat ke akun siswa.
                            </p>
                            <div class="mb-3">
                                <label class="form-label fw-semibold fs-7">Jumlah Poin Literasi yang Diberikan</label>
                                <select name="poin" class="form-select">
                                    <option value="15" selected>15 Poin (Standar Ulasan Lengkap & Baik)</option>
                                    <option value="20">20 Poin (Ulasan Sangat Baik / Kritis)</option>
                                    <option value="25">25 Poin (Karya Istimewa / Sangat Mendalam)</option>
                                    <option value="10">10 Poin (Ulasan Singkat Cukup)</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-semibold fs-7">Catatan Apresiasi Petugas (Opsional)</label>
                                <input type="text" name="catatan" value="Ulasan buku disetujui. Sangat baik!" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                            <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success rounded-3 px-4">
                                <i class="fa-solid fa-award me-1"></i> Setujui & Berikan Poin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Reject Resume -->
        <div class="modal fade" id="modalRejectResume{{ $r->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 text-start">
                    <form action="{{ route('perpus.resume.reject', $r->id) }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Minta Perbaikan Resume</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="small text-muted mb-3">
                                Tuliskan catatan perbaikan agar siswa <strong>{{ $r->siswa->nama }}</strong> mengetahui bagian apa yang perlu disempurnakan.
                            </p>
                            <div class="mb-2">
                                <label class="form-label fw-semibold fs-7">Catatan Alasan / Permintaan Revisi <span class="text-danger">*</span></label>
                                <textarea name="catatan" rows="3" class="form-control" placeholder="Contoh: Mohon tuliskan ringkasan cerita minimal 1 paragraf, jangan hanya satu kalimat..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                            <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger rounded-3 px-4">
                                <i class="fa-solid fa-rotate-left me-1"></i> Kembalikan untuk Revisi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif
@endsection
