@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Banner -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 fs-7">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Sirkulasi Perpustakaan</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-repeat text-primary me-2"></i>Sirkulasi Peminjaman & Pengembalian
            </h3>
            <p class="text-muted small mb-0">Pantau transaksi pinjaman aktif, batas jatuh tempo pengembalian, dan riwayat buku yang telah kembali.</p>
        </div>
        <div>
            <a href="{{ route('perpus.sirkulasi.create') }}" class="btn btn-primary-custom rounded-3 shadow-sm px-3 py-2">
                <i class="fa-solid fa-bolt me-1"></i> Peminjaman Kilat Baru
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
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-info me-2"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Kartu Statistik Sirkulasi -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white border-start border-primary border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Sedang Dipinjam</span>
                        <h2 class="fw-bold mb-0 text-primary">{{ number_format($stats['total_aktif']) }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Buku ada di tangan siswa</small>
                    </div>
                    <div class="bg-primary-subtle p-3 rounded-circle text-primary">
                        <i class="fa-solid fa-book-reader fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white border-start border-danger border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Terlambat Kembali</span>
                        <h2 class="fw-bold mb-0 text-danger">{{ number_format($stats['total_terlambat']) }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Melebihi batas tenggat</small>
                    </div>
                    <div class="bg-danger-subtle p-3 rounded-circle text-danger">
                        <i class="fa-solid fa-clock-rotate-left fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white border-start border-success border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Kembali Bulan Ini</span>
                        <h2 class="fw-bold mb-0 text-success">{{ number_format($stats['total_kembali_bulan_ini']) }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Transaksi selesai</small>
                    </div>
                    <div class="bg-success-subtle p-3 rounded-circle text-success">
                        <i class="fa-solid fa-circle-check fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white border-start border-info border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Total Peminjaman</span>
                        <h2 class="fw-bold mb-0 text-info">{{ number_format($stats['total_peminjaman_all']) }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Akumulasi sirkulasi</small>
                    </div>
                    <div class="bg-info-subtle p-3 rounded-circle text-info">
                        <i class="fa-solid fa-list-check fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Bar -->
    <div class="card card-custom p-3 bg-white mb-4 shadow-sm">
        <form method="GET" action="{{ route('perpus.sirkulasi.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold fs-7 mb-1"><i class="fa-solid fa-magnifying-glass text-primary me-1"></i> Cari Transaksi / Siswa / Buku</label>
                <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Kode transaksi, nama siswa, NIS, atau judul...">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7 mb-1"><i class="fa-solid fa-filter text-primary me-1"></i> Status Transaksi</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="dipinjam" {{ $status === 'dipinjam' ? 'selected' : '' }}>Sedang Dipinjam (Aktif)</option>
                    <option value="terlambat" {{ $status === 'terlambat' ? 'selected' : '' }}>Terlambat (Overdue)</option>
                    <option value="kembali" {{ $status === 'kembali' ? 'selected' : '' }}>Sudah Kembali</option>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Riwayat</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7 mb-1"><i class="fa-solid fa-chalkboard-user text-primary me-1"></i> Filter Kelas</label>
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
                @if($search || $status !== 'dipinjam' || $kelas)
                    <a href="{{ route('perpus.sirkulasi.index') }}" class="btn btn-outline-secondary py-2" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Sirkulasi -->
    <div class="card card-custom p-4 bg-white shadow-sm">
        @if($peminjamans->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-box-open fs-1 mb-3 text-secondary opacity-50"></i>
                <h5 class="fw-semibold text-secondary">Belum ada transaksi peminjaman pada filter ini</h5>
                <p class="small text-muted mb-3">Klik tombol di bawah untuk mencatat peminjaman buku baru tanpa kartu.</p>
                <a href="{{ route('perpus.sirkulasi.create') }}" class="btn btn-primary-custom btn-sm rounded-3">
                    <i class="fa-solid fa-bolt me-1"></i> Buka Meja Peminjaman Kilat
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45px;">No</th>
                            <th style="min-width: 140px;">No. Transaksi</th>
                            <th style="min-width: 180px;">Peminjam (Siswa)</th>
                            <th style="min-width: 220px;">Buku yang Dipinjam</th>
                            <th style="min-width: 140px;">Tgl Pinjam & Tenggat</th>
                            <th style="width: 130px;" class="text-center">Status</th>
                            <th style="width: 130px;" class="text-center">Aksi Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($peminjamans as $idx => $p)
                            @php
                                $isTerlambat = $p->is_terlambat;
                                $hariTerlambat = $p->hari_terlambat;
                            @endphp
                            <tr class="{{ ($p->status === 'dipinjam' && $isTerlambat) ? 'table-danger-subtle' : '' }}">
                                <td class="text-muted text-center">{{ $peminjamans->firstItem() + $idx }}</td>
                                <td>
                                    <span class="font-monospace fw-bold text-dark fs-7">{{ $p->kode_transaksi }}</span>
                                    <div class="fs-8 text-muted" style="font-size: 11px;">
                                        Oleh: {{ optional($p->petugas)->name ?? 'Petugas' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $p->siswa->nama }}</div>
                                    <div class="small text-muted font-monospace">
                                        NIS: {{ $p->siswa->nis }} &bull; <span class="badge bg-secondary-subtle text-secondary">Kelas {{ $p->siswa->kelas }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $p->buku->judul }}</div>
                                    <div class="small text-muted font-monospace">
                                        {{ $p->buku->kode_buku }} &bull; Rak: {{ $p->buku->nomor_rak ?: 'Umum' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <div><i class="fa-regular fa-calendar text-muted me-1"></i> {{ $p->tanggal_pinjam->format('d/m/Y') }}</div>
                                        <div class="fw-semibold {{ ($p->status === 'dipinjam' && $isTerlambat) ? 'text-danger' : 'text-primary' }}">
                                            <i class="fa-solid fa-hourglass-half me-1"></i> {{ $p->tanggal_jatuh_tempo->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($p->status === 'kembali')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> Kembali
                                        </span>
                                        <div class="fs-8 text-muted mt-1" style="font-size: 10.5px;">
                                            {{ optional($p->tanggal_kembali)->format('d/m/Y') }}
                                        </div>
                                    @elseif($isTerlambat)
                                        <span class="badge bg-danger text-white px-2.5 py-1">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Terlambat {{ $hariTerlambat }} Hari
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1">
                                            <i class="fa-solid fa-clock me-1"></i> Dipinjam
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($p->status === 'dipinjam')
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-success rounded-2 px-2 py-1 fs-7 shadow-sm" title="Kembalikan Buku" data-bs-toggle="modal" data-bs-target="#modalKembalikan{{ $p->id }}">
                                                <i class="fa-solid fa-arrow-down-to-bracket me-1"></i> Kembali
                                            </button>
                                            <form action="{{ route('perpus.sirkulasi.perpanjang', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Perpanjang masa pinjam buku ini selama 7 hari ke depan?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-2 p-1 px-2" title="Perpanjang 7 Hari">
                                                    <i class="fa-solid fa-calendar-plus"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Modal Konfirmasi Pengembalian Buku -->
                                        <div class="modal fade" id="modalKembalikan{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg rounded-4 text-start">
                                                    <form action="{{ route('perpus.sirkulasi.kembalikan', $p->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header border-0 pb-0 pt-4 px-4">
                                                            <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-box-archive text-success me-2"></i>Konfirmasi Pengembalian Buku</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body p-4">
                                                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                                                <div class="fw-bold text-dark fs-6">{{ $p->buku->judul }}</div>
                                                                <div class="small text-muted font-monospace">{{ $p->buku->kode_buku }} &bull; Peminjam: <strong>{{ $p->siswa->nama }}</strong> ({{ $p->siswa->kelas }})</div>
                                                                <div class="small mt-1 text-secondary">Jatuh Tempo: {{ $p->tanggal_jatuh_tempo->translatedFormat('d F Y') }}</div>
                                                            </div>

                                                            @if($isTerlambat)
                                                                <div class="alert alert-danger py-2 px-3 small border-0 mb-3 d-flex align-items-center gap-2 rounded-3">
                                                                    <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                                                                    <div>
                                                                        <strong>Perhatian:</strong> Buku terlambat {{ $hariTerlambat }} hari dari tenggat waktu. Siswa dikenakan penalti poin literasi (-5 poin).
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="alert alert-success py-2 px-3 small border-0 mb-3 d-flex align-items-center gap-2 rounded-3">
                                                                    <i class="fa-solid fa-circle-check fs-5"></i>
                                                                    <div>
                                                                        <strong>Tepat Waktu:</strong> Siswa mengembalikan buku sebelum/pada batas waktu. Siswa otomatis mendapatkan <strong>+10 Poin Literasi</strong>!
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold fs-7">Denda Biaya / Ganti Rugi (Jika Ada)</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light">Rp</span>
                                                                    <input type="number" name="denda" value="0" min="0" step="500" class="form-control">
                                                                </div>
                                                                <small class="text-muted" style="font-size: 11px;">Isi 0 jika tidak ada denda keterlambatan / kerusakan fisik.</small>
                                                            </div>

                                                            <div class="mb-2">
                                                                <label class="form-label fw-semibold fs-7">Catatan Pengembalian (Opsional)</label>
                                                                <input type="text" name="catatan" class="form-control" placeholder="Kondisi buku baik, halaman lengkap...">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                                            <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-success rounded-3 px-4">
                                                                <i class="fa-solid fa-circle-check me-1"></i> Proses Pengembalian
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted fs-8">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $peminjamans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
