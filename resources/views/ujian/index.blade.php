@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-slate-800 mb-1">
                <i class="fa-solid fa-file-signature text-primary me-2"></i>Asesmen CBT (Ujian Online)
            </h4>
            <p class="text-muted small mb-0">Kelola paket asesmen, bank soal pilihan ganda, token ujian, dan live monitoring pengerjaan siswa.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ujian.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i>
                <span>Buat Ujian Baru</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('ujian.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Mata Pelajaran</label>
                    <select name="mata_pelajaran_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Mata Pelajaran --</option>
                        @foreach($mapels as $m)
                            <option value="{{ $m->id }}" {{ request('mata_pelajaran_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->nama_mapel }} (Tingkat {{ $m->tingkat ?? 'Semua' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Kelas Target</label>
                    <select name="kelas" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="Semua">-- Semua Kelas --</option>
                        @foreach($daftarKelas as $kls)
                            <option value="{{ $kls }}" {{ request('kelas') == $kls ? 'selected' : '' }}>Kelas {{ $kls }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft (Belum Aktif)</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif (Sedang Berlangsung)</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('ujian.index') }}" class="btn btn-sm btn-outline-secondary w-100 mt-md-4">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Paket Ujian -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive" style="min-height: 320px;">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-slate-700">
                    <tr class="text-uppercase small fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">
                        <th class="py-3 px-3">Informasi Ujian</th>
                        <th class="py-3 px-3">Mapel & Kelas</th>
                        <th class="py-3 px-3 text-center">Durasi & Token</th>
                        <th class="py-3 px-3 text-center">Soal & Peserta</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-end">Aksi Pengelolaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ujians as $u)
                        <tr>
                            <td class="py-3 px-3">
                                <div class="fw-bold text-slate-800 fs-6 mb-1">{{ $u->judul }}</div>
                                <div class="d-flex align-items-center gap-2 small text-muted">
                                    <span class="badge bg-indigo-subtle text-indigo px-2 py-1" style="background:#e0e7ff; color:#3730a3;">
                                        {{ $u->jenis }}
                                    </span>
                                    <span>•</span>
                                    <span>Semester {{ $u->semester }} ({{ $u->tahun_ajaran ?: '2026/2027' }})</span>
                                    @if($u->user)
                                        <span>•</span>
                                        <span><i class="fa-solid fa-chalkboard-user me-1"></i>{{ $u->user->name }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="fw-semibold text-slate-700">{{ optional($u->mataPelajaran)->nama_mapel ?? '-' }}</div>
                                @if($u->kelas === 'Semua')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
                                        <i class="fa-solid fa-users me-1"></i>Semua Kelas
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border small" title="{{ $u->kelas }}">
                                        <i class="fa-solid fa-users me-1"></i>Kelas {{ \Illuminate\Support\Str::limit($u->kelas, 22) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="small fw-semibold text-slate-700 mb-1">
                                    <i class="fa-regular fa-clock text-muted me-1"></i>{{ $u->durasi_menit }} Menit
                                </div>
                                @if($u->token)
                                    <div class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded bg-slate-100 border text-slate-800 fw-mono fw-bold" style="font-size: 12px; letter-spacing: 1px;" title="Token Ujian">
                                        {{ $u->token }}
                                        <form method="POST" action="{{ route('ujian.generateToken', $u->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-link p-0 text-muted ms-1" title="Perbarui Token">
                                                <i class="fa-solid fa-arrows-rotate" style="font-size: 10px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <a href="{{ route('ujian.soal', $u->id) }}" class="badge bg-primary-subtle text-primary border text-decoration-none px-2 py-1 mb-1 d-inline-block" style="background:#dbeafe; color:#1d4ed8; border-color:#bfdbfe !important;">
                                    <i class="fa-solid fa-list-ol me-1"></i>{{ $u->soals_count }} Soal
                                </a>
                                <div>
                                    <a href="{{ route('ujian.monitoring', $u->id) }}" class="small text-muted text-decoration-none">
                                        <i class="fa-solid fa-user-check me-1"></i>{{ $u->ujian_siswas_count }} Peserta
                                    </a>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($u->status === 'aktif')
                                    <span class="badge bg-success text-white px-2 py-1 d-inline-flex align-items-center gap-1 shadow-sm">
                                        <span class="pulse-dot"></span> Aktif
                                    </span>
                                @elseif($u->status === 'selesai')
                                    <span class="badge bg-dark text-white px-2 py-1">Selesai</span>
                                @else
                                    <span class="badge bg-secondary text-white px-2 py-1">Draft</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('ujian.soal', $u->id) }}" class="btn btn-outline-primary" title="Kelola Soal (Input/Import)">
                                        <i class="fa-solid fa-list-check me-1"></i> Soal
                                    </a>
                                    <a href="{{ route('ujian.monitoring', $u->id) }}" class="btn btn-outline-info" title="Live Monitor Pengawas">
                                        <i class="fa-solid fa-display me-1"></i> Monitor
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border rounded-3 py-2" style="z-index: 1060; min-width: 210px;">
                                        <li><h6 class="dropdown-header">Ubah Status Ujian</h6></li>
                                        <li>
                                            <form method="POST" action="{{ route('ujian.toggleStatus', $u->id) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="aktif">
                                                <button type="submit" class="dropdown-item text-success {{ $u->status === 'aktif' ? 'fw-bold active' : '' }}">
                                                    <i class="fa-solid fa-play me-2"></i>Aktifkan Ujian
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('ujian.toggleStatus', $u->id) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="draft">
                                                <button type="submit" class="dropdown-item text-secondary {{ $u->status === 'draft' ? 'fw-bold' : '' }}">
                                                    <i class="fa-solid fa-pause me-2"></i>Kembalikan ke Draft
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('ujian.toggleStatus', $u->id) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="selesai">
                                                <button type="submit" class="dropdown-item text-dark {{ $u->status === 'selesai' ? 'fw-bold' : '' }}">
                                                    <i class="fa-solid fa-flag-checkered me-2"></i>Tandai Selesai
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('ujian.edit', $u->id) }}">
                                                <i class="fa-solid fa-pen-to-square text-warning me-2"></i>Edit Pengaturan
                                            </a>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('ujian.destroy', $u->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ujian ini beserta seluruh soal dan data nilai siswa?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fa-solid fa-trash me-2"></i>Hapus Ujian
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-file-circle-question fs-1 text-slate-300 d-block mb-3"></i>
                                Belum ada paket asesmen CBT yang dibuat. Klik tombol <strong>Buat Ujian Baru</strong> di atas untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($ujians->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $ujians->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<style>
/* Pastikan dropdown menu aksi tidak terpotong oleh overflow tabel */
.table-responsive {
    min-height: 320px;
}
.btn-group .dropdown-menu {
    z-index: 1060 !important;
}
</style>
@endsection

