@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Banner -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge rounded-pill px-3 py-1 bg-primary-subtle text-primary fw-semibold" style="font-size: 11px;">
                    <i class="fa-solid fa-book-bookmark me-1"></i> SI-PERPUS SPENLI
                </span>
                <span class="text-secondary opacity-50">&bull;</span>
                <span class="text-muted small">Koleksi Buku & Bahan Bacaan</span>
            </div>
            <h3 class="fw-bold text-dark mb-0">Katalog Buku Perpustakaan</h3>
            <p class="text-muted small mb-0">Jelajahi koleksi buku menarik sekolah kita dan kumpulkan poin literasimu!</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2 bg-white px-3 py-2 rounded-4 shadow-sm border">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white" 
                     style="width: 38px; height: 38px; background: {{ $badge['color'] ?? '#3b82f6' }};">
                    <i class="fa-solid {{ $badge['icon'] ?? 'fa-award' }} fs-6"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 11px; font-weight: 600;">POIN LITERASIKU</div>
                    <div class="fw-bold text-dark d-flex align-items-center gap-1">
                        <span class="fs-5 text-primary">{{ number_format($totalPoin) }}</span>
                        <span class="badge rounded-pill px-2 py-0 fs-8" style="background: {{ $badge['bg'] ?? '#eff6ff' }}; color: {{ $badge['color'] ?? '#3b82f6' }};">
                            {{ $badge['label'] ?? 'Pembaca Pemula' }}
                        </span>
                    </div>
                </div>
            </div>
            <a href="{{ route('siswa.perpus.pinjaman') }}" class="btn btn-outline-primary rounded-pill px-3 py-2 shadow-sm fs-7">
                <i class="fa-solid fa-book-reader me-1"></i> Pinjamanku
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="card card-custom p-3 bg-white mb-4 shadow-sm">
        <form action="{{ route('siswa.perpus.katalog') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="q" value="{{ $search }}" class="form-control bg-light border-start-0" 
                           placeholder="Cari berdasarkan judul buku, pengarang, penerbit, atau kode...">
                    @if(!empty($search))
                        <a href="{{ route('siswa.perpus.katalog', ['kategori_id' => $kategoriId]) }}" class="input-group-text bg-light border-start-0 text-muted">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="col-md-3">
                <select name="kategori_id" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ $kategoriId == $kat->id ? 'selected' : '' }}>
                            {{ $kat->nama_kategori }} ({{ $kat->bukus_count }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary-custom w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
            </div>
        </form>

        <!-- Category Quick Pills -->
        <div class="d-flex align-items-center gap-2 overflow-auto pt-3 mt-3 border-top" style="white-space: nowrap;">
            <a href="{{ route('siswa.perpus.katalog', ['q' => $search]) }}" 
               class="btn btn-sm rounded-pill px-3 {{ empty($kategoriId) ? 'btn-primary' : 'btn-light border text-secondary' }}">
                Semua Koleksi
            </a>
            @foreach($kategoris as $kat)
                <a href="{{ route('siswa.perpus.katalog', ['kategori_id' => $kat->id, 'q' => $search]) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $kategoriId == $kat->id ? 'btn-primary' : 'btn-light border text-secondary' }}">
                    <i class="fa-solid {{ $kat->icon ?? 'fa-book' }} me-1"></i>
                    {{ $kat->nama_kategori }}
                    <span class="badge rounded-pill bg-white text-dark ms-1">{{ $kat->bukus_count }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Alert Info Cara Meminjam -->
    <div class="alert alert-info border-0 rounded-4 shadow-sm p-3 mb-4 d-flex align-items-center gap-3" style="background: #eff6ff; color: #1e40af;">
        <div class="rounded-circle d-flex align-items-center justify-content-center bg-white text-primary shadow-sm flex-shrink-0" style="width: 42px; height: 42px;">
            <i class="fa-solid fa-bolt fs-5"></i>
        </div>
        <div class="small">
            <strong class="d-block mb-1">Peminjaman Kilat Tanpa Kartu Fisik!</strong>
            Temukan buku yang kamu sukai di rak perpustakaan, lalu sebutkan <strong>Nama</strong> atau <strong>NIS ({{ $siswa->nis }})</strong> kamu kepada Petugas Perpustakaan di meja sirkulasi. Buku langsung tercatat atas namamu!
        </div>
    </div>

    <!-- Book Grid -->
    @if($bukus->count() > 0)
        <div class="row g-4 mb-4">
            @foreach($bukus as $buku)
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <div class="card card-custom h-100 bg-white border-0 shadow-sm d-flex flex-column hover-shadow transition-all">
                        <!-- Book Cover Header Area -->
                        <div class="p-3 text-center rounded-top-4 position-relative" 
                             style="background: linear-gradient(135deg, {{ $buku->kategori->warna ?? '#3b82f6' }}15, {{ $buku->kategori->warna ?? '#3b82f6' }}30); min-height: 140px; display: flex; align-items: center; justify-content: center;">
                            
                            <span class="badge rounded-pill position-absolute top-0 start-0 m-3 px-3 py-1 shadow-sm fs-8"
                                  style="background: {{ $buku->kategori->warna ?? '#4f46e5' }}; color: #ffffff;">
                                <i class="fa-solid {{ $buku->kategori->icon ?? 'fa-book' }} me-1"></i>
                                {{ $buku->kategori->nama_kategori ?? 'Umum' }}
                            </span>

                            <div class="rounded-3 shadow p-3 bg-white d-inline-flex align-items-center justify-content-center" 
                                 style="width: 72px; height: 96px; border-left: 5px solid {{ $buku->kategori->warna ?? '#4f46e5' }};">
                                <i class="fa-solid fa-book-open fs-1" style="color: {{ $buku->kategori->warna ?? '#4f46e5' }};"></i>
                            </div>
                        </div>

                        <!-- Book Content -->
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between text-muted fs-8 mb-1">
                                <span><i class="fa-solid fa-barcode me-1"></i>{{ $buku->kode_buku }}</span>
                                <span><i class="fa-solid fa-layer-group me-1"></i>{{ $buku->lokasi_rak ?? 'Rak Utama' }}</span>
                            </div>

                            <h6 class="fw-bold text-dark mb-1 line-clamp-2" title="{{ $buku->judul }}" style="min-height: 2.4em; line-height: 1.25;">
                                {{ $buku->judul }}
                            </h6>

                            <div class="text-muted small mb-2">
                                <i class="fa-solid fa-pen-nib me-1 text-secondary opacity-75"></i>
                                {{ $buku->pengarang ?? 'Penulis Anonim' }}
                            </div>

                            @if($buku->deskripsi)
                                <p class="text-muted fs-8 mb-3 line-clamp-2" style="font-size: 12px;">
                                    {{ Str::limit($buku->deskripsi, 80) }}
                                </p>
                            @endif

                            <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between">
                                <div>
                                    @if($buku->stok_tersedia > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fs-8">
                                            <i class="fa-solid fa-circle-check me-1"></i> Tersedia ({{ $buku->stok_tersedia }})
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 fs-8">
                                            <i class="fa-solid fa-circle-xmark me-1"></i> Sedang Habis
                                        </span>
                                    @endif
                                </div>
                                <span class="text-muted fs-8">
                                    {{ $buku->tahun_terbit ?? '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center">
            {{ $bukus->links() }}
        </div>
    @else
        <div class="card card-custom p-5 bg-white text-center text-muted shadow-sm">
            <i class="fa-solid fa-book-open-reader fs-1 mb-3 text-secondary opacity-50"></i>
            <h5 class="fw-semibold text-secondary">Tidak ada buku yang sesuai dengan pencarian</h5>
            <p class="small text-muted mb-3">Coba ganti kata kunci pencarian atau pilih kategori lain.</p>
            <div>
                <a href="{{ route('siswa.perpus.katalog') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-arrow-rotate-left me-1"></i> Reset Pencarian
                </a>
            </div>
        </div>
    @endif
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.fs-7 { font-size: 0.875rem !important; }
.fs-8 { font-size: 0.775rem !important; }
.card-custom {
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
}
.hover-shadow {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-shadow:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
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
