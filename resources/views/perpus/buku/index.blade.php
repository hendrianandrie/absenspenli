@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Banner -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 fs-7">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Katalog Perpustakaan</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-book-bookmark text-primary me-2"></i>Katalog & Master Buku
            </h3>
            <p class="text-muted small mb-0">Kelola judul buku, kategori, nomor rak penyimpanan, dan stok eksemplar fisik perpustakaan.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('perpus.kategori.index') }}" class="btn btn-outline-secondary rounded-3 shadow-sm">
                <i class="fa-solid fa-tags me-1"></i> Kelola Kategori
            </a>
            <button type="button" class="btn btn-primary-custom rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahBuku">
                <i class="fa-solid fa-plus-circle me-1"></i> Tambah Buku Baru
            </button>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Kartu Statistik Koleksi -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white border-start border-primary border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Total Judul Buku</span>
                        <h2 class="fw-bold mb-0 text-primary">{{ number_format($stats['total_judul']) }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Koleksi judul unik</small>
                    </div>
                    <div class="bg-primary-subtle p-3 rounded-circle text-primary">
                        <i class="fa-solid fa-book-open fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white border-start border-success border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Buku Tersedia</span>
                        <h2 class="fw-bold mb-0 text-success">{{ number_format($stats['total_tersedia']) }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Siap dipinjam di rak</small>
                    </div>
                    <div class="bg-success-subtle p-3 rounded-circle text-success">
                        <i class="fa-solid fa-square-check fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white border-start border-warning border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Total Eksemplar</span>
                        <h2 class="fw-bold mb-0 text-warning">{{ number_format($stats['total_eksemplar']) }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Fisik seluruh buku</small>
                    </div>
                    <div class="bg-warning-subtle p-3 rounded-circle text-warning">
                        <i class="fa-solid fa-layer-group fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white border-start border-info border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Kategori Buku</span>
                        <h2 class="fw-bold mb-0 text-info">{{ $stats['total_kategori'] }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Klasifikasi ilmu</small>
                    </div>
                    <div class="bg-info-subtle p-3 rounded-circle text-info">
                        <i class="fa-solid fa-tags fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Bar -->
    <div class="card card-custom p-3 bg-white mb-4 shadow-sm">
        <form method="GET" action="{{ route('perpus.buku.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold fs-7 mb-1"><i class="fa-solid fa-magnifying-glass text-primary me-1"></i> Cari Judul / Pengarang / Kode</label>
                <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Ketik judul, pengarang, nomor rak, atau ISBN...">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7 mb-1"><i class="fa-solid fa-filter text-primary me-1"></i> Kategori</label>
                <select name="kategori_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $k)
                        <option value="{{ $k->id }}" {{ $kategoriId == $k->id ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold fs-7 mb-1"><i class="fa-solid fa-cubes-stacked text-primary me-1"></i> Stok</label>
                <select name="status_stok" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Stok</option>
                    <option value="tersedia" {{ $statusStok === 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                    <option value="habis" {{ $statusStok === 'habis' ? 'selected' : '' }}>Sedang Habis</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary-custom w-100 py-2">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                @if($search || $kategoriId || $statusStok)
                    <a href="{{ route('perpus.buku.index') }}" class="btn btn-outline-secondary py-2" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Buku -->
    <div class="card card-custom p-4 bg-white shadow-sm">
        @if($bukus->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-book-open fs-1 mb-3 text-secondary opacity-50"></i>
                <h5 class="fw-semibold text-secondary">Belum ada koleksi buku yang sesuai</h5>
                <p class="small text-muted mb-3">Tambahkan buku baru atau sesuaikan kata kunci pencarian Anda.</p>
                <button type="button" class="btn btn-primary-custom btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#modalTambahBuku">
                    <i class="fa-solid fa-plus-circle me-1"></i> Tambah Buku Baru
                </button>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45px;">No</th>
                            <th style="min-width: 250px;">Judul & Info Buku</th>
                            <th style="min-width: 130px;">Kategori</th>
                            <th style="min-width: 110px;">Lokasi Rak</th>
                            <th style="width: 120px;" class="text-center">Ketersediaan</th>
                            <th style="width: 110px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bukus as $idx => $b)
                            <tr>
                                <td class="text-muted text-center">{{ $bukus->firstItem() + $idx }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 44px; height: 58px;">
                                            @if($b->cover_image && file_exists(public_path('storage/' . $b->cover_image)))
                                                <img src="{{ asset('storage/' . $b->cover_image) }}" alt="Cover" class="w-100 h-100 object-fit-cover">
                                            @else
                                                <i class="fa-solid fa-book text-muted fs-4"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6">{{ $b->judul }}</div>
                                            <div class="small text-muted">
                                                <span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $b->kode_buku }}</span>
                                                @if($b->pengarang) &bull; {{ $b->pengarang }} @endif
                                                @if($b->tahun_terbit) ({{ $b->tahun_terbit }}) @endif
                                            </div>
                                            @if($b->penerbit || $b->isbn)
                                                <div class="fs-8 text-secondary mt-0.5" style="font-size: 11px;">
                                                    {{ $b->penerbit }} @if($b->isbn) &bull; ISBN: {{ $b->isbn }} @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">
                                        <i class="fa-solid fa-tag me-1"></i> {{ optional($b->kategori)->nama_kategori ?? 'Umum' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><i class="fa-solid fa-box-archive text-warning me-1"></i> {{ $b->nomor_rak ?: 'Rak Umum' }}</div>
                                </td>
                                <td class="text-center">
                                    @if($b->stok_tersedia > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-7">
                                            <i class="fa-solid fa-circle-check me-1"></i> {{ $b->stok_tersedia }} / {{ $b->stok_total }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-7">
                                            <i class="fa-solid fa-circle-xmark me-1"></i> Habis (0/{{ $b->stok_total }})
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-2 p-1 px-2" title="Edit Buku" data-bs-toggle="modal" data-bs-target="#modalEditBuku{{ $b->id }}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('perpus.buku.destroy', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku \'{{ $b->judul }}\' dari katalog?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-2 p-1 px-2" title="Hapus Buku">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $bukus->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ================= MODAL EDIT BUKU (Di luar tabel & card agar stabil) ================= -->
@if(!$bukus->isEmpty())
    @foreach($bukus as $b)
        <div class="modal fade" id="modalEditBuku{{ $b->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form action="{{ route('perpus.buku.update', $b->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-header border-0 pb-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Data Buku</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold fs-7">Kode Buku <span class="text-danger">*</span></label>
                                    <input type="text" name="kode_buku" value="{{ $b->kode_buku }}" class="form-control" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold fs-7">Judul Buku <span class="text-danger">*</span></label>
                                    <input type="text" name="judul" value="{{ $b->judul }}" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-7">Kategori Buku</label>
                                    <select name="kategori_id" class="form-select">
                                        <option value="">(Pilih Kategori)</option>
                                        @foreach($kategoris as $k)
                                            <option value="{{ $k->id }}" {{ $b->kategori_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-7">Pengarang / Penulis</label>
                                    <input type="text" name="pengarang" value="{{ $b->pengarang }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-7">Penerbit</label>
                                    <input type="text" name="penerbit" value="{{ $b->penerbit }}" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold fs-7">Tahun Terbit</label>
                                    <input type="text" name="tahun_terbit" value="{{ $b->tahun_terbit }}" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold fs-7">ISBN</label>
                                    <input type="text" name="isbn" value="{{ $b->isbn }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-7">Nomor / Posisi Rak</label>
                                    <input type="text" name="nomor_rak" value="{{ $b->nomor_rak }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-7">Jumlah Stok Fisik <span class="text-danger">*</span></label>
                                    <input type="number" name="stok_total" value="{{ $b->stok_total }}" min="1" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold fs-7">Sinopsis / Deskripsi Buku</label>
                                    <textarea name="deskripsi" rows="3" class="form-control">{{ $b->deskripsi }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold fs-7">Ganti Gambar Cover (Opsional)</label>
                                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                            <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary-custom rounded-3 px-4">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif

<!-- Modal Tambah Buku Baru -->
<div class="modal fade" id="modalTambahBuku" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="{{ route('perpus.buku.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Tambah Koleksi Buku Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-7">Kode Buku <span class="text-danger">*</span></label>
                            <input type="text" name="kode_buku" placeholder="Contoh: BK-005" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold fs-7">Judul Buku <span class="text-danger">*</span></label>
                            <input type="text" name="judul" placeholder="Masukkan judul buku lengkap..." class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-7">Kategori Buku</label>
                            <select name="kategori_id" class="form-select">
                                <option value="">(Pilih Kategori)</option>
                                @foreach($kategoris as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-7">Pengarang / Penulis</label>
                            <input type="text" name="pengarang" placeholder="Nama penulis..." class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-7">Penerbit</label>
                            <input type="text" name="penerbit" placeholder="Nama penerbit..." class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold fs-7">Tahun Terbit</label>
                            <input type="text" name="tahun_terbit" placeholder="Tahun..." class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold fs-7">ISBN</label>
                            <input type="text" name="isbn" placeholder="Nomor ISBN..." class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-7">Nomor / Posisi Rak</label>
                            <input type="text" name="nomor_rak" placeholder="Contoh: Rak A-02" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-7">Jumlah Stok Fisik <span class="text-danger">*</span></label>
                            <input type="number" name="stok_total" value="1" min="1" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold fs-7">Sinopsis / Ringkasan Buku</label>
                            <textarea name="deskripsi" rows="3" class="form-control" placeholder="Tuliskan gambaran ringkas buku ini agar menarik minat baca siswa..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold fs-7">Foto Sampul / Cover (Opsional)</label>
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-3 px-4">Simpan Buku</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
