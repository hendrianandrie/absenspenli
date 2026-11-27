@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-8 fw-semibold">
                <i class="fa-solid fa-shield-halved me-1"></i> Khusus Administrator
            </span>
            <span class="badge bg-light text-slate-700 border px-2 py-1 fs-8">
                TA {{ $selectedTahunAjaran }} &bull; Semester {{ $selectedSemester }}
            </span>
        </div>
        <h3 class="fw-bold mb-1 text-slate-900">
            <i class="fa-solid fa-chart-pie text-primary me-2"></i> Monitoring Keterisian Penilaian
        </h3>
        <p class="text-muted mb-0 small">
            Pantau kepatuhan dan kelengkapan penginputan nilai mata pelajaran oleh guru per kelas secara terpusat.
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="{{ route('nilai.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 rounded-3 shadow-xs">
            <i class="fa-solid fa-arrow-left me-1"></i> Lembar Nilai Kelas
        </a>
        <button type="button" onclick="window.print()" class="btn btn-light border btn-sm px-3 py-2 rounded-3 shadow-xs">
            <i class="fa-solid fa-print me-1"></i> Cetak Halaman
        </button>
    </div>
</div>

<!-- Navigasi Tab Cepat Admin -->
<ul class="nav nav-pills mb-4 border-bottom pb-2">
    <li class="nav-item">
        <a class="nav-link text-slate-600 rounded-3 py-2 px-3 fw-medium" href="{{ route('nilai.index') }}">
            <i class="fa-solid fa-table-list me-1.5 text-slate-400"></i> Lembar Penilaian Siswa
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active bg-primary text-white rounded-3 py-2 px-3 fw-semibold shadow-xs" href="{{ route('nilai.monitoring') }}">
            <i class="fa-solid fa-chart-pie me-1.5"></i> Monitoring Keterisian Mapel
        </a>
    </li>
</ul>

<!-- STATISTIK RINGKASAN SEKOLAH -->
<div class="row g-3 mb-4">
    <!-- Total Mapel -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-medium">Total Mata Pelajaran</span>
                <div class="rounded-3 p-2 bg-slate-100 text-slate-700">
                    <i class="fa-solid fa-book-open"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-slate-900 mb-0">{{ $stats['total_mapel'] }}</h3>
                <span class="text-muted small">Mata Pelajaran</span>
            </div>
            <div class="mt-2 text-muted" style="font-size: 11.5px;">
                <i class="fa-solid fa-layer-group me-1"></i> Evaluasi per tingkat kelas
            </div>
        </div>
    </div>

    <!-- Sudah Lengkap -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100" style="border-left: 4px solid #10b981 !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-medium">Sudah Lengkap (100%)</span>
                <div class="rounded-3 p-2 bg-success-subtle text-success">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-success mb-0">{{ $stats['lengkap_count'] }}</h3>
                <span class="text-muted small">Mapel</span>
            </div>
            <div class="mt-2 text-success" style="font-size: 11.5px;">
                Semua kelas target sudah terisi
            </div>
        </div>
    </div>

    <!-- Sebagian Mengisi -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100" style="border-left: 4px solid #f59e0b !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-medium">Sebagian Kelas Terisi</span>
                <div class="rounded-3 p-2 bg-warning-subtle text-warning-emphasis">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-warning-emphasis mb-0">{{ $stats['sebagian_count'] }}</h3>
                <span class="text-muted small">Mapel</span>
            </div>
            <div class="mt-2 text-warning-emphasis" style="font-size: 11.5px;">
                Masih ada kelas yang belum diisi
            </div>
        </div>
    </div>

    <!-- Belum Mengisi -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100" style="border-left: 4px solid #ef4444 !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-medium">Belum Mengisi (0%)</span>
                <div class="rounded-3 p-2 bg-danger-subtle text-danger">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-danger mb-0">{{ $stats['belum_count'] }}</h3>
                <span class="text-muted small">Mapel</span>
            </div>
            <div class="mt-2 text-danger" style="font-size: 11.5px;">
                Belum ada kegiatan/nilai diinput
            </div>
        </div>
    </div>
</div>

<!-- Progress Bar Keterisian Global Sekolah -->
<div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold text-slate-800 small">Progres Keterisian Nilai Sekolah:</span>
            <span class="badge bg-primary text-white">{{ $stats['overall_pct'] }}%</span>
            <span class="text-muted small">({{ $stats['total_kelas_terisi'] }} dari {{ $stats['total_kelas_target'] }} rombel terisi)</span>
        </div>
        <div class="text-muted small d-none d-md-block">
            <span class="me-3"><i class="fa-solid fa-list-check text-primary me-1"></i> {{ $stats['total_kegiatan'] }} Kegiatan</span>
            <span><i class="fa-solid fa-user-check text-success me-1"></i> {{ number_format($stats['total_nilai'], 0, ',', '.') }} Entri Nilai</span>
        </div>
    </div>
    <div class="progress" style="height: 10px; border-radius: 6px; background: #f1f5f9;">
        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $stats['overall_pct'] }}%;" aria-valuenow="{{ $stats['overall_pct'] }}" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
</div>

<!-- FILTER BAR -->
<div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
    <form method="GET" action="{{ route('nilai.monitoring') }}" class="row g-2 align-items-end" id="filterMonitoringForm">
        <!-- Semester -->
        <div class="col-md-2 col-6">
            <label class="form-label text-muted small mb-1 fw-semibold">Semester</label>
            <select name="semester" class="form-select form-select-sm rounded-2" onchange="this.form.submit()">
                @foreach ($daftarSemester as $val => $lbl)
                    <option value="{{ $val }}" {{ $selectedSemester == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>

        <!-- Tahun Ajaran -->
        <div class="col-md-2 col-6">
            <label class="form-label text-muted small mb-1 fw-semibold">Tahun Ajaran</label>
            <select name="tahun_ajaran" class="form-select form-select-sm rounded-2" onchange="this.form.submit()">
                @foreach ($daftarTahunAjaran as $ta)
                    <option value="{{ $ta }}" {{ $selectedTahunAjaran == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                @endforeach
            </select>
        </div>

        <!-- Tingkat Kelas -->
        <div class="col-md-2 col-6">
            <label class="form-label text-muted small mb-1 fw-semibold">Tingkat</label>
            <select name="tingkat" class="form-select form-select-sm rounded-2" onchange="this.form.submit()">
                <option value="Semua" {{ $selectedTingkat === 'Semua' ? 'selected' : '' }}>Semua Tingkat</option>
                <option value="7" {{ $selectedTingkat === '7' ? 'selected' : '' }}>Kelas 7 (VII)</option>
                <option value="8" {{ $selectedTingkat === '8' ? 'selected' : '' }}>Kelas 8 (VIII)</option>
                <option value="9" {{ $selectedTingkat === '9' ? 'selected' : '' }}>Kelas 9 (IX)</option>
            </select>
        </div>

        <!-- Status Keterisian -->
        <div class="col-md-2 col-6">
            <label class="form-label text-muted small mb-1 fw-semibold">Status Keterisian</label>
            <select name="status" class="form-select form-select-sm rounded-2" onchange="this.form.submit()">
                <option value="semua" {{ $selectedStatus === 'semua' ? 'selected' : '' }}>Semua Status</option>
                <option value="lengkap" {{ $selectedStatus === 'lengkap' ? 'selected' : '' }}>Lengkap (100%)</option>
                <option value="sebagian" {{ $selectedStatus === 'sebagian' ? 'selected' : '' }}>Sebagian Terisi</option>
                <option value="belum" {{ $selectedStatus === 'belum' ? 'selected' : '' }}>Belum Mengisi (0%)</option>
            </select>
        </div>

        <!-- Pencarian Mapel / Guru -->
        <div class="col-md-3 col-8">
            <label class="form-label text-muted small mb-1 fw-semibold">Cari Mapel / Guru</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="q" value="{{ $searchQuery }}" class="form-control rounded-end-2" placeholder="Nama mapel atau guru...">
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="col-md-1 col-4 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm rounded-2 w-100" title="Terapkan Filter">
                <i class="fa-solid fa-filter"></i>
            </button>
            @if($searchQuery !== '' || $selectedTingkat !== 'Semua' || $selectedStatus !== 'semua')
                <a href="{{ route('nilai.monitoring', ['semester' => $selectedSemester, 'tahun_ajaran' => $selectedTahunAjaran]) }}" class="btn btn-outline-secondary btn-sm rounded-2" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- DUAL VIEW: TABEL DETAIL & MATRIKS GRID -->
<div class="card border-0 shadow-sm rounded-4 p-0 bg-white mb-4 overflow-hidden">
    <!-- Header Card dengan Tab Mode Tampilan -->
    <div class="p-3 bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <ul class="nav nav-tabs card-header-tabs border-bottom-0" id="viewModeTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold text-slate-800" id="table-tab" data-bs-toggle="tab" data-bs-target="#tab-table" type="button" role="tab">
                    <i class="fa-solid fa-list-check me-1.5 text-primary"></i> Daftar Progres per Mapel
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold text-slate-800" id="matrix-tab" data-bs-toggle="tab" data-bs-target="#tab-matrix" type="button" role="tab">
                    <i class="fa-solid fa-table-cells me-1.5 text-indigo-500"></i> Matriks Keterisian (Mapel &times; Kelas)
                </button>
            </li>
        </ul>

        <div class="text-muted small">
            Menampilkan <strong>{{ count($monitoringData) }}</strong> mata pelajaran
        </div>
    </div>

    <div class="tab-content" id="viewModeTabContent">
        <!-- TAB 1: TABEL PROGRES PER MAPEL -->
        <div class="tab-pane fade show active p-0" id="tab-table" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr class="text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                            <th class="ps-4" style="width: 50px;">No</th>
                            <th style="min-width: 220px;">Mata Pelajaran</th>
                            <th style="min-width: 180px;">Guru Pengampu</th>
                            <th style="min-width: 160px;">Progres Kelas</th>
                            <th style="min-width: 120px;">Status</th>
                            <th style="min-width: 240px;">Rincian Kelas (Terisi / Belum)</th>
                            <th class="text-center" style="width: 120px;">Kegiatan</th>
                            <th class="text-end pe-4" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($monitoringData as $index => $row)
                            @php
                                $m = $row['mapel'];
                                $badgeClass = match($row['status']) {
                                    'lengkap' => 'bg-success text-white',
                                    'sebagian' => 'bg-warning text-dark',
                                    'belum' => 'bg-danger text-white',
                                    default => 'bg-secondary text-white'
                                };
                                $statusLabel = match($row['status']) {
                                    'lengkap' => 'Lengkap',
                                    'sebagian' => 'Sebagian',
                                    'belum' => 'Belum Mengisi',
                                    default => '-'
                                };
                            @endphp
                            <tr>
                                <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-slate-900">{{ $m->nama_mapel }}</div>
                                    <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                        <span class="badge bg-light text-slate-600 border" style="font-size: 10px;">{{ $m->kode_mapel }}</span>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 10px;">
                                            Tingkat {{ $m->tingkat ?? 'Semua' }}
                                        </span>
                                        <span class="text-muted" style="font-size: 10.5px;">KKM: {{ $m->kkm }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if(!empty($row['guru_list']))
                                        @foreach($row['guru_list'] as $gName)
                                            <div class="d-flex align-items-center gap-1.5 py-0.5">
                                                <i class="fa-solid fa-chalkboard-user text-muted" style="font-size: 11px;"></i>
                                                <span class="text-slate-800 fw-medium">{{ $gName }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-muted fst-italic" style="font-size: 11.5px;">(Belum ditugaskan)</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-slate-800" style="font-size: 12px;">{{ $row['filled_count'] }} / {{ $row['target_count'] }} Kelas</span>
                                        <span class="text-muted small fw-semibold">{{ $row['pct_keterisian'] }}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px; border-radius: 4px; background: #e2e8f0;">
                                        <div class="progress-bar {{ $row['pct_keterisian'] == 100 ? 'bg-success' : ($row['pct_keterisian'] > 0 ? 'bg-warning' : 'bg-danger') }}" 
                                             role="progressbar" style="width: {{ $row['pct_keterisian'] }}%;"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }} px-2.5 py-1 rounded-pill fw-semibold shadow-2xs">
                                        @if($row['status'] === 'lengkap')
                                            <i class="fa-solid fa-check me-1"></i>
                                        @elseif($row['status'] === 'sebagian')
                                            <i class="fa-solid fa-clock-rotate-left me-1"></i>
                                        @else
                                            <i class="fa-solid fa-xmark me-1"></i>
                                        @endif
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>
                                    <!-- Daftar Pill Kelas -->
                                    <div class="d-flex flex-wrap gap-1">
                                        <!-- Kelas yang sudah terisi -->
                                        @foreach($row['filled_kelas'] as $kName => $kData)
                                            <a href="{{ route('nilai.index', ['mata_pelajaran_id' => $m->id, 'kelas' => $kName, 'semester' => $selectedSemester, 'tahun_ajaran' => $selectedTahunAjaran]) }}" 
                                               class="badge bg-success-subtle text-success-emphasis border border-success-subtle text-decoration-none px-2 py-1 rounded-2"
                                               title="{{ $kName }}: {{ $kData['kegiatan_count'] }} Kegiatan, {{ $kData['nilai_count'] }} Entri Nilai (Klik untuk buka)"
                                               data-bs-toggle="tooltip">
                                                <i class="fa-solid fa-check me-0.5" style="font-size: 9px;"></i> {{ $kName }}
                                            </a>
                                        @endforeach

                                        <!-- Kelas yang belum terisi -->
                                        @foreach($row['empty_kelas'] as $kName)
                                            <a href="{{ route('nilai.index', ['mata_pelajaran_id' => $m->id, 'kelas' => $kName, 'semester' => $selectedSemester, 'tahun_ajaran' => $selectedTahunAjaran]) }}" 
                                               class="badge bg-danger-subtle text-danger border border-danger-subtle text-decoration-none px-2 py-1 rounded-2"
                                               title="{{ $kName }}: Belum ada kegiatan/nilai (Klik untuk buka)"
                                               data-bs-toggle="tooltip">
                                                <i class="fa-solid fa-xmark me-0.5" style="font-size: 9px;"></i> {{ $kName }}
                                            </a>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-slate-900">{{ $row['total_kegiatan'] }}</span>
                                    <div class="text-muted" style="font-size: 10.5px;">
                                        {{ $row['total_nilai'] }} Nilai
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle rounded-2 py-1 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 11.5px;">
                                            Buka Lembar
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                            <li class="dropdown-header text-uppercase small" style="font-size: 10px;">Pilih Kelas:</li>
                                            @foreach($row['target_kelas'] as $targetK)
                                                @php $isFilled = isset($row['filled_kelas'][$targetK]); @endphp
                                                <li>
                                                    <a class="dropdown-item d-flex justify-content-between align-items-center small py-1.5" 
                                                       href="{{ route('nilai.index', ['mata_pelajaran_id' => $m->id, 'kelas' => $targetK, 'semester' => $selectedSemester, 'tahun_ajaran' => $selectedTahunAjaran]) }}">
                                                        <span>Kelas {{ $targetK }}</span>
                                                        @if($isFilled)
                                                            <span class="badge bg-success-subtle text-success" style="font-size: 10px;">Terisi</span>
                                                        @else
                                                            <span class="badge bg-danger-subtle text-danger" style="font-size: 10px;">Belum</span>
                                                        @endif
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fa-3x text-slate-300 mb-3 d-block"></i>
                                    <span class="fw-semibold">Tidak ada data mata pelajaran yang cocok dengan filter.</span>
                                    <p class="small text-muted mb-0">Coba ubah opsi pencarian atau reset filter di atas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: MATRIKS KETERISIAN GRID (MAPEL x KELAS) -->
        <div class="tab-pane fade p-0" id="tab-matrix" role="tabpanel">
            <div class="p-3 bg-light-subtle border-bottom d-flex align-items-center justify-content-between" style="font-size: 12px;">
                <div class="text-muted">
                    <i class="fa-solid fa-circle-info text-primary me-1"></i> Klik pada kotak kelas untuk langsung menuju lembar penilaian kelas tersebut.
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="d-flex align-items-center gap-1"><span class="badge bg-success text-white px-2 py-0.5">✓</span> Terisi</span>
                    <span class="d-flex align-items-center gap-1"><span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5">✕</span> Belum Terisi</span>
                    <span class="d-flex align-items-center gap-1"><span class="badge bg-light text-muted border px-2 py-0.5">-</span> Bukan Target</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 text-center" style="font-size: 12px;">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th class="text-start ps-3" style="min-width: 200px; position: sticky; left: 0; background: #f8fafc; z-index: 2;">Mata Pelajaran</th>
                            <th class="text-start ps-3" style="min-width: 150px;">Guru Pengampu</th>
                            @foreach ($displayKelas as $k)
                                <th style="min-width: 75px; font-size: 11px;" class="fw-bold text-slate-800">
                                    {{ $k }}
                                </th>
                            @endforeach
                            <th style="min-width: 90px; background: #f8fafc;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($monitoringData as $row)
                            @php
                                $m = $row['mapel'];
                            @endphp
                            <tr>
                                <td class="text-start ps-3 fw-bold text-slate-900" style="position: sticky; left: 0; background: #ffffff; z-index: 1;">
                                    <div>{{ $m->nama_mapel }}</div>
                                    <small class="text-muted fw-normal" style="font-size: 10px;">{{ $m->kode_mapel }} &bull; Tingkat {{ $m->tingkat ?? 'Semua' }}</small>
                                </td>
                                <td class="text-start ps-3 small text-slate-700">
                                    {{ implode(', ', $row['guru_list']) ?: '-' }}
                                </td>
                                @foreach ($displayKelas as $k)
                                    @php
                                        $isTarget = $row['target_kelas']->contains($k);
                                        $filledInfo = $row['filled_kelas'][$k] ?? null;
                                    @endphp
                                    @if(!$isTarget)
                                        <td class="bg-light text-muted" style="background-color: #f8fafc !important;" title="Bukan kelas target mapel ini">
                                            <span class="text-slate-300">-</span>
                                        </td>
                                    @elseif($filledInfo)
                                        <td class="bg-success-subtle text-success p-1" style="background-color: #ecfdf5 !important;">
                                            <a href="{{ route('nilai.index', ['mata_pelajaran_id' => $m->id, 'kelas' => $k, 'semester' => $selectedSemester, 'tahun_ajaran' => $selectedTahunAjaran]) }}" 
                                               class="d-block text-decoration-none text-success py-1" 
                                               title="{{ $m->nama_mapel }} - {{ $k }}: {{ $filledInfo['kegiatan_count'] }} Kegiatan, {{ $filledInfo['nilai_count'] }} Nilai">
                                                <div class="fw-bold" style="font-size: 13px;"><i class="fa-solid fa-check"></i></div>
                                                <div style="font-size: 9.5px;" class="text-success-emphasis fw-medium">{{ $filledInfo['kegiatan_count'] }} keg</div>
                                            </a>
                                        </td>
                                    @else
                                        <td class="bg-danger-subtle p-1" style="background-color: #fef2f2 !important;">
                                            <a href="{{ route('nilai.index', ['mata_pelajaran_id' => $m->id, 'kelas' => $k, 'semester' => $selectedSemester, 'tahun_ajaran' => $selectedTahunAjaran]) }}" 
                                               class="d-block text-decoration-none text-danger py-1" 
                                               title="{{ $m->nama_mapel }} - {{ $k }}: Belum ada kegiatan/nilai diinput">
                                                <div class="fw-bold" style="font-size: 12px;"><i class="fa-solid fa-xmark"></i></div>
                                                <div style="font-size: 9.5px;" class="text-danger-emphasis">Belum</div>
                                            </a>
                                        </td>
                                    @endif
                                @endforeach
                                <td class="fw-bold text-slate-800 bg-light" style="background-color: #f8fafc !important;">
                                    {{ $row['filled_count'] }} / {{ $row['target_count'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($displayKelas) + 3 }}" class="text-center py-4 text-muted">
                                    Tidak ada data untuk ditampilkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Inisialisasi Tooltip Bootstrap
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
