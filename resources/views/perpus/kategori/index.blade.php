@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Banner -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 fs-7">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('perpus.buku.index') }}" class="text-decoration-none">Perpustakaan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Kategori Buku</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-tags text-primary me-2"></i>Kategori & Klasifikasi Buku
            </h3>
            <p class="text-muted small mb-0">Kelola kelompok jenis bacaan untuk memudahkan pencarian dan pengelompokan rak buku.</p>
        </div>
        <div>
            <a href="{{ route('perpus.buku.index') }}" class="btn btn-outline-secondary rounded-3 shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Katalog Buku
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
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Form Tambah Kategori (Kiri) -->
        <div class="col-lg-4">
            <div class="card card-custom p-4 bg-white shadow-sm border-0">
                <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Tambah Kategori Baru</h5>
                <form action="{{ route('perpus.kategori.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Fiksi, Komik Edukasi..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Kode Singkat (Opsional)</label>
                        <input type="text" name="kode_kategori" class="form-control text-uppercase font-monospace" placeholder="Contoh: FIK, SCI...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Deskripsi Kategori</label>
                        <textarea name="deskripsi" rows="3" class="form-control" placeholder="Penjelasan singkat jenis buku dalam kategori ini..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100 py-2">
                        <i class="fa-solid fa-save me-1"></i> Simpan Kategori
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabel Daftar Kategori (Kanan) -->
        <div class="col-lg-8">
            <div class="card card-custom p-4 bg-white shadow-sm border-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list text-primary me-2"></i>Daftar Kategori Aktif</h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 fs-7">
                        Total: {{ $kategoris->count() }} Kategori
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Kategori</th>
                                <th style="width: 100px;" class="text-center">Kode</th>
                                <th style="width: 130px;" class="text-center">Jumlah Judul</th>
                                <th style="width: 110px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kategoris as $idx => $k)
                                <tr>
                                    <td class="text-muted text-center">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $k->nama_kategori }}</div>
                                        @if($k->deskripsi)
                                            <small class="text-muted">{{ $k->deskripsi }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $k->kode_kategori ?: '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1">
                                            {{ $k->bukus_count }} Judul
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-2 p-1 px-2" title="Edit Kategori" data-bs-toggle="modal" data-bs-target="#modalEditKat{{ $k->id }}">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <form action="{{ route('perpus.kategori.destroy', $k->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kategori ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-2 p-1 px-2" title="Hapus Kategori">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit Kategori -->
                                <div class="modal fade" id="modalEditKat{{ $k->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <form action="{{ route('perpus.kategori.update', $k->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header border-0 pb-0 pt-4 px-4">
                                                    <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Kategori</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold fs-7">Nama Kategori <span class="text-danger">*</span></label>
                                                        <input type="text" name="nama_kategori" value="{{ $k->nama_kategori }}" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold fs-7">Kode Singkat (Opsional)</label>
                                                        <input type="text" name="kode_kategori" value="{{ $k->kode_kategori }}" class="form-control text-uppercase font-monospace">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold fs-7">Deskripsi Kategori</label>
                                                        <textarea name="deskripsi" rows="3" class="form-control">{{ $k->deskripsi }}</textarea>
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
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada kategori yang dibuat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
