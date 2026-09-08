@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-book-open text-primary me-2"></i> Kelola Mata Pelajaran & KKM</h3>
        <p class="text-muted mb-0">Atur daftar mata pelajaran yang Anda ampu dan ubah nilai KKM standar SMP.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Form Tambah Mapel Baru -->
    <div class="col-md-4">
        <div class="card card-custom p-4 bg-white">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-plus-circle text-primary me-2"></i> Tambah Mata Pelajaran</h5>
            <form action="{{ route('mapel.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Kode Mapel</label>
                    <input type="text" name="kode_mapel" class="form-control" placeholder="Contoh: MAT-SMP" required>
                    <small class="text-muted">Singkatan unik mata pelajaran.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Nama Mata Pelajaran</label>
                    <input type="text" name="nama_mapel" class="form-control" placeholder="Contoh: Matematika" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">KKM (Kriteria Ketuntasan Minimal)</label>
                    <input type="number" name="kkm" class="form-control" value="75" min="0" max="100" required>
                </div>
                <button type="submit" class="btn btn-primary-custom w-100 py-2">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Mata Pelajaran
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Mapel & Edit KKM -->
    <div class="col-md-8">
        <div class="card card-custom p-4 bg-white shadow-sm">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-list text-primary me-2"></i> Daftar Mata Pelajaran Saya</h5>
            @if($mapels->isEmpty())
                <div class="text-center py-4 text-muted">
                    Belum ada mata pelajaran terdaftar.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Mata Pelajaran</th>
                                <th class="text-center">Nilai KKM</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mapels as $mapel)
                                <tr>
                                    <td><span class="badge bg-secondary font-monospace fs-6">{{ $mapel->kode_mapel }}</span></td>
                                    <td class="fw-semibold">{{ $mapel->nama_mapel }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-6 px-3 py-2"><i class="fa-solid fa-star text-warning me-1"></i> {{ $mapel->kkm }}</span>
                                    </td>
                                    <td class="text-end">
                                        <!-- Tombol Edit Modal KKM -->
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editMapelModal{{ $mapel->id }}">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit KKM
                                        </button>

                                        <!-- Form Hapus -->
                                        <form action="{{ route('mapel.destroy', $mapel->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus mata pelajaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-trash me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Container (Di Luar Tabel agar Modal Stabil & Tidak Bergeser) -->
@foreach($mapels as $mapel)
    <div class="modal fade" id="editMapelModal{{ $mapel->id }}" tabindex="-1" aria-labelledby="editMapelModalLabel{{ $mapel->id }}" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="editMapelModalLabel{{ $mapel->id }}">
                        <i class="fa-solid fa-pen-to-square me-2"></i> Edit Mata Pelajaran & KKM
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('mapel.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $mapel->id }}">
                    <div class="modal-body p-4 text-start">
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-7">Kode Mapel</label>
                            <input type="text" name="kode_mapel" class="form-control font-monospace" value="{{ $mapel->kode_mapel }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-7">Nama Mata Pelajaran</label>
                            <input type="text" name="nama_mapel" class="form-control fw-semibold" value="{{ $mapel->nama_mapel }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-7 text-primary">Nilai KKM (Kriteria Ketuntasan Minimal)</label>
                            <input type="number" name="kkm" class="form-control form-control-lg fw-bold text-center text-primary" value="{{ $mapel->kkm }}" min="0" max="100" required>
                            <small class="text-muted">Batas standar kelulusan siswa (skala 0 - 100).</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan KKM
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection
