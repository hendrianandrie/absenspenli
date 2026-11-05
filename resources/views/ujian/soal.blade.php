@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <a href="{{ route('ujian.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Ujian
            </a>
            <h4 class="fw-bold text-slate-800 mb-1">
                <i class="fa-solid fa-list-check text-primary me-2"></i>Bank Soal: {{ $ujian->judul }}
            </h4>
            <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
                <span class="badge bg-indigo-subtle text-indigo" style="background:#e0e7ff; color:#3730a3;">{{ $ujian->jenis }}</span>
                <span>•</span>
                <span>{{ optional($ujian->mataPelajaran)->nama_mapel }}</span>
                <span>•</span>
                <span>Kelas {{ $ujian->kelas }}</span>
                <span>•</span>
                <span><i class="fa-regular fa-clock me-1"></i>{{ $ujian->durasi_menit }} Menit</span>
                <span>•</span>
                <span class="fw-bold text-slate-700">Token: <code class="bg-light px-2 py-0.5 rounded border">{{ $ujian->token }}</code></span>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportExcel">
                <i class="fa-solid fa-file-excel me-1"></i> Import dari Excel
            </button>
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSoal">
                <i class="fa-solid fa-plus me-1"></i> Tambah Soal Manual
            </button>
            <a href="{{ route('ujian.monitoring', $ujian->id) }}" class="btn btn-info text-white shadow-sm">
                <i class="fa-solid fa-display me-1"></i> Live Monitor
            </a>
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

    <!-- Summary Box -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 border-start border-primary border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Total Butir Soal</div>
                        <div class="fs-4 fw-bold text-slate-800">{{ $soals->count() }} Soal</div>
                    </div>
                    <div class="mosaic-icon-box bg-primary-subtle text-primary">
                        <i class="fa-solid fa-file-circle-check fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 border-start border-success border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Total Akumulasi Bobot</div>
                        <div class="fs-4 fw-bold text-slate-800">{{ $soals->sum('bobot') ?: 0 }} Poin</div>
                    </div>
                    <div class="mosaic-icon-box bg-success-subtle text-success">
                        <i class="fa-solid fa-scale-balanced fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 border-start border-warning border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Status Ujian Saat Ini</div>
                        <div class="fs-4 fw-bold text-slate-800 text-capitalize">{{ $ujian->status }}</div>
                    </div>
                    <div class="mosaic-icon-box bg-warning-subtle text-warning">
                        <i class="fa-solid fa-signal fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions List -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="fw-bold text-slate-800">
                <i class="fa-solid fa-layer-group text-primary me-2"></i>Daftar Butir Soal Pilihan Ganda
            </div>
            @if($soals->count() > 0)
                <span class="badge bg-slate-100 text-slate-700 border">{{ $soals->count() }} Butir Terdaftar</span>
            @endif
        </div>
        <div class="card-body p-0">
            @forelse($soals as $index => $soal)
                <div class="p-4 border-bottom {{ $loop->even ? 'bg-light' : 'bg-white' }}">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill px-3 py-1 fs-6">#{{ $soal->nomor_urut }}</span>
                            <span class="badge bg-secondary-subtle text-secondary border small">Bobot: {{ $soal->bobot }}</span>
                            <span class="badge bg-success-subtle text-success border small fw-bold">
                                <i class="fa-solid fa-key me-1"></i>Kunci: {{ $soal->kunci_jawaban }}
                            </span>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditSoal{{ $soal->id }}" title="Edit Soal">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <form method="POST" action="{{ route('ujian.soal.destroy', [$ujian->id, $soal->id]) }}" onsubmit="return confirm('Hapus nomor soal #{{ $soal->nomor_urut }} ini?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Hapus Soal">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Question Text & Image -->
                    <div class="text-slate-800 fw-medium mb-3 fs-6" style="line-height: 1.6;">
                        {!! nl2br(e($soal->pertanyaan)) !!}
                    </div>

                    @if($soal->gambar)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $soal->gambar) }}" alt="Gambar Soal #{{ $soal->nomor_urut }}" class="img-thumbnail rounded" style="max-height: 220px;">
                        </div>
                    @endif

                    <!-- Options Grid (A, B, C, D) -->
                    <div class="row g-2">
                        @foreach(['A' => $soal->opsi_a, 'B' => $soal->opsi_b, 'C' => $soal->opsi_c, 'D' => $soal->opsi_d] as $optKey => $optVal)
                            <div class="col-md-6">
                                <div class="p-2.5 px-3 rounded-3 border d-flex align-items-center gap-2 {{ $soal->kunci_jawaban === $optKey ? 'bg-success-subtle border-success text-success fw-bold' : 'bg-white text-slate-700' }}" style="font-size: 14px;">
                                    <span class="badge {{ $soal->kunci_jawaban === $optKey ? 'bg-success text-white' : 'bg-light text-slate-800 border' }}" style="width: 24px; height: 24px; display:inline-flex; align-items:center; justify-content:center;">
                                        {{ $optKey }}
                                    </span>
                                    <span class="flex-grow-1">{{ $optVal }}</span>
                                    @if($soal->kunci_jawaban === $optKey)
                                        <i class="fa-solid fa-circle-check text-success ms-auto" title="Kunci Jawaban"></i>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Modal Edit Soal -->
                <div class="modal fade" id="modalEditSoal{{ $soal->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content border-0 shadow">
                            <form method="POST" action="{{ route('ujian.soal.update', [$ujian->id, $soal->id]) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title fw-bold">Edit Soal Nomor #{{ $soal->nomor_urut }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Teks Pertanyaan <span class="text-danger">*</span></label>
                                        <textarea name="pertanyaan" class="form-control" rows="4" required>{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Gambar Pendukung (Opsional)</label>
                                        <input type="file" name="gambar" class="form-control" accept="image/*">
                                        @if($soal->gambar)
                                            <div class="small text-muted mt-1">Saat ini: <a href="{{ asset('storage/' . $soal->gambar) }}" target="_blank">Lihat Gambar</a> (Unggah baru jika ingin mengganti)</div>
                                        @endif
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Pilihan A <span class="text-danger">*</span></label>
                                            <input type="text" name="opsi_a" value="{{ old('opsi_a', $soal->opsi_a) }}" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Pilihan B <span class="text-danger">*</span></label>
                                            <input type="text" name="opsi_b" value="{{ old('opsi_b', $soal->opsi_b) }}" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Pilihan C <span class="text-danger">*</span></label>
                                            <input type="text" name="opsi_c" value="{{ old('opsi_c', $soal->opsi_c) }}" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Pilihan D <span class="text-danger">*</span></label>
                                            <input type="text" name="opsi_d" value="{{ old('opsi_d', $soal->opsi_d) }}" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Kunci Jawaban <span class="text-danger">*</span></label>
                                            <select name="kunci_jawaban" class="form-select fw-bold text-success" required>
                                                <option value="A" {{ $soal->kunci_jawaban === 'A' ? 'selected' : '' }}>A</option>
                                                <option value="B" {{ $soal->kunci_jawaban === 'B' ? 'selected' : '' }}>B</option>
                                                <option value="C" {{ $soal->kunci_jawaban === 'C' ? 'selected' : '' }}>C</option>
                                                <option value="D" {{ $soal->kunci_jawaban === 'D' ? 'selected' : '' }}>D</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Bobot Soal</label>
                                            <input type="number" step="0.1" min="0.1" name="bobot" value="{{ old('bobot', $soal->bobot) }}" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-file-circle-question fs-1 text-slate-300 d-block mb-3"></i>
                    Belum ada soal pada paket ujian ini.<br>
                    Anda bisa menambahkan soal secara manual atau mengunggah berkas Excel menggunakan tombol di kanan atas.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Tambah Soal Manual -->
<div class="modal fade" id="modalTambahSoal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('ujian.soal.store', $ujian->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Tambah Butir Soal Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Teks Pertanyaan <span class="text-danger">*</span></label>
                        <textarea name="pertanyaan" class="form-control" rows="4" placeholder="Tuliskan butir soal di sini..." required>{{ old('pertanyaan') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Gambar Pendukung (Opsional)</label>
                        <input type="file" name="gambar" class="form-control" accept="image/*">
                        <div class="form-text small">Maksimal 3MB (.jpg, .jpeg, .png).</div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pilihan Opsi A <span class="text-danger">*</span></label>
                            <input type="text" name="opsi_a" class="form-control" placeholder="Jawaban A" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pilihan Opsi B <span class="text-danger">*</span></label>
                            <input type="text" name="opsi_b" class="form-control" placeholder="Jawaban B" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pilihan Opsi C <span class="text-danger">*</span></label>
                            <input type="text" name="opsi_c" class="form-control" placeholder="Jawaban C" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pilihan Opsi D <span class="text-danger">*</span></label>
                            <input type="text" name="opsi_d" class="form-control" placeholder="Jawaban D" required>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kunci Jawaban Benar <span class="text-danger">*</span></label>
                            <select name="kunci_jawaban" class="form-select fw-bold text-success" required>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bobot Nilai Soal</label>
                            <input type="number" step="0.1" min="0.1" name="bobot" value="1.0" class="form-control" required>
                            <div class="form-text small">Default: 1.0</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-save me-1"></i> Simpan Soal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('ujian.importSoal', $ujian->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-excel me-2"></i>Import Soal dari Excel</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3 text-center">
                        <p class="small text-muted mb-2">Gunakan format template Excel resmi agar data soal, opsi A-D, dan kunci jawaban terdeteksi secara akurat.</p>
                        <a href="{{ route('ujian.downloadTemplate', $ujian->id) }}" class="btn btn-outline-success btn-sm">
                            <i class="fa-solid fa-download me-1"></i> Unduh Format Template Excel Soal
                        </a>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Berkas Excel (.xlsx, .xls, .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file_excel" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text small">Maksimal ukuran berkas 5MB.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-success px-4"><i class="fa-solid fa-upload me-1"></i> Unggah & Proses Soal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

