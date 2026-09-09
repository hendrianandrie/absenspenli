@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-book-open text-primary me-2"></i> Kelola Mata Pelajaran, KKM & Bobot Penilaian</h3>
        <p class="text-muted mb-0">Atur KKM dan komposisi bobot penilaian (Tugas, UH, UTS, UAS) untuk kalkulasi nilai akhir Rapor.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Form Tambah Mapel Baru -->
    <div class="col-md-4">
        <div class="card card-custom p-4 bg-white shadow-sm">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-plus-circle text-primary me-2"></i> Tambah Mata Pelajaran</h5>
            <form action="{{ route('mapel.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Kode Mapel</label>
                    <input type="text" name="kode_mapel" class="form-control" placeholder="Contoh: MAT-SMP" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Nama Mata Pelajaran</label>
                    <input type="text" name="nama_mapel" class="form-control" placeholder="Contoh: Matematika" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">KKM Minimal</label>
                    <input type="number" name="kkm" class="form-control" value="75" min="0" max="100" required>
                </div>
                <hr class="my-3">
                <h6 class="fw-bold text-dark mb-2 fs-7"><i class="fa-solid fa-sliders text-primary me-1"></i> Pengaturan Bobot Rapor (%)</h6>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small mb-1">Tugas (%)</label>
                        <input type="number" name="bobot_tugas" class="form-control form-control-sm" value="20" min="0" max="100" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-1">UH (%)</label>
                        <input type="number" name="bobot_uh" class="form-control form-control-sm" value="30" min="0" max="100" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-1">UTS (%)</label>
                        <input type="number" name="bobot_uts" class="form-control form-control-sm" value="25" min="0" max="100" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-1">UAS (%)</label>
                        <input type="number" name="bobot_uas" class="form-control form-control-sm" value="25" min="0" max="100" required>
                    </div>
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
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-list text-primary me-2"></i> Daftar Mata Pelajaran & Bobot</h5>
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
                                <th class="text-center">KKM</th>
                                <th class="text-center">Bobot Penilaian (T/UH/UTS/UAS)</th>
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
                                    <td class="text-center small">
                                        <span class="badge bg-light text-dark border">Tugas: {{ $mapel->bobot_tugas ?? 20 }}%</span>
                                        <span class="badge bg-light text-dark border">UH: {{ $mapel->bobot_uh ?? 30 }}%</span>
                                        <span class="badge bg-light text-dark border">UTS: {{ $mapel->bobot_uts ?? 25 }}%</span>
                                        <span class="badge bg-light text-dark border">UAS: {{ $mapel->bobot_uas ?? 25 }}%</span>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editMapelModal{{ $mapel->id }}">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit KKM & Bobot
                                        </button>
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

<!-- Modal Container -->
@foreach($mapels as $mapel)
    <div class="modal fade" id="editMapelModal{{ $mapel->id }}" tabindex="-1" aria-labelledby="editMapelModalLabel{{ $mapel->id }}" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="editMapelModalLabel{{ $mapel->id }}">
                        <i class="fa-solid fa-pen-to-square me-2"></i> Edit Mapel, KKM & Bobot
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
                        </div>
                        <hr class="my-3">
                        <h6 class="fw-bold text-dark mb-2 fs-7"><i class="fa-solid fa-sliders text-primary me-1"></i> Komposisi Bobot Penilaian Rapor (%)</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small mb-1">Bobot Tugas (%)</label>
                                <input type="number" name="bobot_tugas" class="form-control" value="{{ $mapel->bobot_tugas ?? 20 }}" min="0" max="100" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1">Bobot UH (%)</label>
                                <input type="number" name="bobot_uh" class="form-control" value="{{ $mapel->bobot_uh ?? 30 }}" min="0" max="100" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1">Bobot UTS (%)</label>
                                <input type="number" name="bobot_uts" class="form-control" value="{{ $mapel->bobot_uts ?? 25 }}" min="0" max="100" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1">Bobot UAS (%)</label>
                                <input type="number" name="bobot_uas" class="form-control" value="{{ $mapel->bobot_uas ?? 25 }}" min="0" max="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection
