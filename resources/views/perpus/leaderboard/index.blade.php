@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Banner -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 fs-7">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Leaderboard Literasi</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-trophy text-warning me-2"></i>Papan Peringkat Literasi (Leaderboard)
            </h3>
            <p class="text-muted small mb-0">Apresiasi dan pemeringkatan bagi siswa dan kelas paling aktif meminjam serta mengulas buku.</p>
        </div>
        <div>
            <a href="{{ route('perpus.sirkulasi.create') }}" class="btn btn-primary-custom rounded-3 shadow-sm">
                <i class="fa-solid fa-bolt me-1"></i> Sirkulasi Kilat Baru
            </a>
        </div>
    </div>

    <!-- Filter Periode Bulan & Tahun Bar -->
    <div class="card card-custom p-3 bg-white mb-4 shadow-sm">
        <form method="GET" action="{{ route('perpus.leaderboard.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7 mb-1"><i class="fa-solid fa-calendar text-primary me-1"></i> Mode Peringkat</label>
                <select name="mode" class="form-select" onchange="this.form.submit()">
                    <option value="bulanan" {{ $filterMode === 'bulanan' ? 'selected' : '' }}>Peringkat Bulanan (Per Periode)</option>
                    <option value="all" {{ $filterMode === 'all' ? 'selected' : '' }}>Akumulasi Keseluruhan (All-Time)</option>
                </select>
            </div>
            @if($filterMode === 'bulanan')
                <div class="col-md-3">
                    <label class="form-label fw-semibold fs-7 mb-1"><i class="fa-solid fa-calendar-day text-primary me-1"></i> Pilih Bulan</label>
                    <select name="bulan" class="form-select" onchange="this.form.submit()">
                        @foreach($daftarBulan as $num => $nama)
                            <option value="{{ $num }}" {{ $selectedBulan == $num ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold fs-7 mb-1"><i class="fa-solid fa-calendar-week text-primary me-1"></i> Tahun</label>
                    <select name="tahun" class="form-select" onchange="this.form.submit()">
                        @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                            <option value="{{ $y }}" {{ $selectedTahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            @endif
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary-custom w-100 py-2">
                    <i class="fa-solid fa-filter me-1"></i> Terapkan
                </button>
            </div>
            <div class="col-md-{{ $filterMode === 'bulanan' ? '2' : '7' }} text-md-end text-muted small">
                <span class="badge bg-light text-secondary border px-3 py-2">
                    Periode: <strong>{{ $filterMode === 'bulanan' ? ($daftarBulan[$selectedBulan] ?? '') . ' ' . $selectedTahun : 'Akumulasi Semua Data' }}</strong>
                </span>
            </div>
        </form>
    </div>

    <!-- Nav Tabs: Siswa, Kelas, dan Hall of Fame -->
    <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3" id="leaderboardTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 py-2 fw-semibold fs-7" id="siswa-tab" data-bs-toggle="pill" data-bs-target="#tab-siswa" type="button" role="tab">
                <i class="fa-solid fa-user-graduate me-1 text-primary"></i> Duta Literasi Siswa (Top 20)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-semibold fs-7" id="kelas-tab" data-bs-toggle="pill" data-bs-target="#tab-kelas" type="button" role="tab">
                <i class="fa-solid fa-chalkboard-user me-1 text-success"></i> Kelas Teraktif (Piala Bergilir)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-semibold fs-7" id="fame-tab" data-bs-toggle="pill" data-bs-target="#tab-fame" type="button" role="tab">
                <i class="fa-solid fa-award me-1 text-warning"></i> Riwayat Juara Bulanan (Hall of Fame)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="leaderboardTabContent">
        <!-- TAB 1: DUTA LITERASI SISWA -->
        <div class="tab-pane fade show active" id="tab-siswa" role="tabpanel">
            @if(empty($leaderboardSiswa))
                <div class="card card-custom p-5 bg-white text-center text-muted shadow-sm">
                    <i class="fa-solid fa-medal fs-1 mb-3 text-secondary opacity-50"></i>
                    <h5 class="fw-semibold text-secondary">Belum ada aktivitas literasi pada periode ini</h5>
                    <p class="small text-muted mb-0">Poin literasi akan otomatis bertambah ketika siswa meminjam buku dan mengirimkan ulasan.</p>
                </div>
            @else
                <!-- Top 3 Podium Siswa -->
                <div class="row g-3 mb-4 justify-content-center">
                    @foreach(array_slice($leaderboardSiswa, 0, 3) as $top)
                        <div class="col-md-4">
                            <div class="card card-custom p-4 bg-white shadow-sm border-0 position-relative overflow-hidden text-center h-100"
                                 style="border-top: 4px solid {{ $top['rank'] === 1 ? '#f59e0b' : ($top['rank'] === 2 ? '#94a3b8' : '#cd7f32') }} !important;">
                                <div class="position-absolute top-0 end-0 p-3">
                                    @if($top['rank'] === 1)
                                        <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill"><i class="fa-solid fa-crown me-1"></i> Juara 1</span>
                                    @elseif($top['rank'] === 2)
                                        <span class="badge bg-secondary text-white px-2.5 py-1 rounded-pill"><i class="fa-solid fa-medal me-1"></i> Juara 2</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border px-2.5 py-1 rounded-pill"><i class="fa-solid fa-award me-1"></i> Juara 3</span>
                                    @endif
                                </div>
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold mx-auto mb-3 shadow"
                                     style="width: 64px; height: 64px; font-size: 24px; background: {{ $top['rank'] === 1 ? 'linear-gradient(135deg, #f59e0b, #d97706)' : ($top['rank'] === 2 ? 'linear-gradient(135deg, #94a3b8, #64748b)' : 'linear-gradient(135deg, #d97706, #b45309)') }};">
                                    {{ strtoupper(substr($top['siswa']->nama, 0, 1)) }}
                                </div>
                                <h5 class="fw-bold text-dark mb-0 text-truncate">{{ $top['siswa']->nama }}</h5>
                                <div class="small text-muted font-monospace mb-2">NIS: {{ $top['siswa']->nis }} &bull; Kelas {{ $top['siswa']->kelas }}</div>
                                
                                <div class="my-2">
                                    <span class="badge {{ $top['badge']['class'] }} border px-3 py-1.5 rounded-pill fs-7">
                                        <i class="fa-solid {{ $top['badge']['icon'] }} me-1"></i> {{ $top['badge']['nama'] }}
                                    </span>
                                </div>

                                <div class="display-6 fw-bold text-primary mt-2">{{ number_format($top['total_poin']) }} <span class="fs-6 text-muted fw-normal">Poin</span></div>
                                <div class="d-flex justify-content-center gap-3 small text-muted mt-2 border-top pt-2">
                                    <div><i class="fa-solid fa-book text-secondary me-1"></i> {{ $top['total_buku'] }} Buku</div>
                                    <div><i class="fa-solid fa-feather text-success me-1"></i> {{ $top['total_resume'] }} Ulasan</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Tabel Peringkat Siswa Lengkap -->
                <div class="card card-custom p-4 bg-white shadow-sm border-0">
                    <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-list-ol text-primary me-2"></i>Daftar Lengkap Duta Literasi Siswa</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;" class="text-center">Rank</th>
                                    <th>Nama Siswa & NIS</th>
                                    <th style="width: 100px;" class="text-center">Kelas</th>
                                    <th style="width: 130px;" class="text-center">Buku Dibaca</th>
                                    <th style="width: 130px;" class="text-center">Resume Sah</th>
                                    <th style="width: 160px;" class="text-center">Lencana Gelar</th>
                                    <th style="width: 130px;" class="text-center">Total Poin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaderboardSiswa as $row)
                                    <tr class="{{ $row['rank'] <= 3 ? 'table-warning-subtle' : '' }}">
                                        <td class="text-center fw-bold">
                                            @if($row['rank'] === 1)
                                                <i class="fa-solid fa-crown text-warning fs-5"></i>
                                            @elseif($row['rank'] === 2)
                                                <i class="fa-solid fa-medal text-secondary fs-5"></i>
                                            @elseif($row['rank'] === 3)
                                                <i class="fa-solid fa-award text-warning fs-5" style="color:#cd7f32;"></i>
                                            @else
                                                <span class="badge bg-light text-dark rounded-circle border">{{ $row['rank'] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $row['siswa']->nama }}</div>
                                            <div class="small text-muted font-monospace">NIS: {{ $row['siswa']->nis }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-secondary">{{ $row['siswa']->kelas }}</span>
                                        </td>
                                        <td class="text-center fw-semibold text-dark">{{ $row['total_buku'] }} Buku</td>
                                        <td class="text-center fw-semibold text-success">{{ $row['total_resume'] }} Ulasan</td>
                                        <td class="text-center">
                                            <span class="badge {{ $row['badge']['class'] }} border px-2 py-1 fs-8">
                                                <i class="fa-solid {{ $row['badge']['icon'] }} me-1"></i> {{ $row['badge']['nama'] }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="fw-bold text-primary fs-6">{{ number_format($row['total_poin']) }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- TAB 2: PERINGKAT KELAS TERAKTIF -->
        <div class="tab-pane fade" id="tab-kelas" role="tabpanel">
            <div class="card card-custom p-4 bg-white shadow-sm border-0 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-trophy text-warning me-2"></i>Papan Peringkat Kelas Terliterat (Piala Bergilir)</h5>
                        <small class="text-muted">Diurutkan secara adil berdasarkan <strong>Indeks Rata-Rata Poin per Siswa</strong> (Total Poin / Jumlah Siswa di Kelas).</small>
                    </div>
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold">
                        <i class="fa-solid fa-award me-1"></i> Juara 1: Berhak Memajang Piala Bergilir
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;" class="text-center">Peringkat</th>
                                <th style="min-width: 140px;">Kelas</th>
                                <th style="min-width: 180px;">Wali Kelas</th>
                                <th style="width: 120px;" class="text-center">Partisipasi</th>
                                <th style="width: 120px;" class="text-center">Buku Dipinjam</th>
                                <th style="width: 120px;" class="text-center">Total Poin</th>
                                <th style="width: 150px;" class="text-center">Indeks Poin / Siswa</th>
                                <th style="width: 140px;" class="text-center">Status Apresiasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kelasStats as $ks)
                                <tr class="{{ $ks['rank'] === 1 ? 'table-warning-subtle' : '' }}">
                                    <td class="text-center fw-bold">
                                        @if($ks['rank'] === 1)
                                            <i class="fa-solid fa-trophy text-warning fs-5"></i>
                                        @elseif($ks['rank'] === 2)
                                            <span class="badge bg-secondary text-white rounded-circle">2</span>
                                        @elseif($ks['rank'] === 3)
                                            <span class="badge bg-warning text-dark rounded-circle">3</span>
                                        @else
                                            <span class="text-muted">{{ $ks['rank'] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6">Kelas {{ $ks['kelas'] }}</div>
                                        <div class="small text-muted">{{ $ks['total_siswa'] }} Siswa</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-slate-700"><i class="fa-solid fa-user-tie text-primary me-1"></i> {{ $ks['wali_kelas'] }}</div>
                                    </td>
                                    <td class="text-center">
                                        <div class="fw-bold text-dark">{{ $ks['partisipasi_pct'] }}%</div>
                                        <div class="small text-muted">{{ $ks['active_siswa'] }}/{{ $ks['total_siswa'] }} Siswa</div>
                                    </td>
                                    <td class="text-center fw-semibold text-dark">{{ $ks['total_buku'] }} Buku</td>
                                    <td class="text-center fw-semibold text-primary">{{ number_format($ks['total_poin']) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 fs-7 fw-bold">
                                            {{ $ks['indeks_rata_rata'] }} Pts
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($ks['rank'] === 1)
                                            <span class="badge bg-warning text-dark px-2.5 py-1.5 rounded-pill shadow-sm">
                                                <i class="fa-solid fa-crown me-1"></i> Juara 1 (Trofi)
                                            </span>
                                        @elseif($ks['rank'] === 2)
                                            <span class="badge bg-secondary text-white px-2 py-1 rounded-pill">
                                                Runner-Up 1
                                            </span>
                                        @elseif($ks['rank'] === 3)
                                            <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                                Runner-Up 2
                                            </span>
                                        @else
                                            <span class="text-muted fs-8">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Belum ada data keaktifan kelas pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 3: HALL OF FAME RIWAYAT BULANAN -->
        <div class="tab-pane fade" id="tab-fame" role="tabpanel">
            <div class="card card-custom p-4 bg-white shadow-sm border-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-award text-warning me-2"></i>Hall of Fame: Arsip Juara Bulanan Tahun {{ $selectedTahun }}</h5>
                        <small class="text-muted">Rekam jejak juara Duta Literasi Siswa dan Kelas Pemegang Piala Bergilir dari bulan ke bulan.</small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 140px;">Periode Bulan</th>
                                <th style="min-width: 220px;">Duta Literasi Siswa (Juara 1)</th>
                                <th style="min-width: 220px;">Kelas Terbaik (Piala Bergilir)</th>
                                <th style="width: 160px;" class="text-center">Status Periode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hallOfFameBulanan as $fame)
                                <tr class="{{ $fame['is_current'] ? 'table-primary-subtle' : '' }}">
                                    <td>
                                        <div class="fw-bold text-dark">{{ $fame['nama_bulan'] }} {{ $selectedTahun }}</div>
                                    </td>
                                    <td>
                                        @if($fame['top_siswa'])
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-crown text-warning fs-5"></i>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $fame['top_siswa']['nama'] }}</div>
                                                    <div class="small text-muted">Kelas {{ $fame['top_siswa']['kelas'] }} &bull; <strong class="text-primary">{{ $fame['top_siswa']['poin'] }} Poin</strong></div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted small fst-italic">Belum ada pemenang</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($fame['top_kelas'])
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-trophy text-warning fs-5"></i>
                                                <div>
                                                    <div class="fw-bold text-dark">Kelas {{ $fame['top_kelas']['kelas'] }}</div>
                                                    <div class="small text-muted">Indeks: <strong class="text-primary">{{ $fame['top_kelas']['indeks'] }} Pts/Siswa</strong> &bull; ({{ $fame['top_kelas']['total_poin'] }} Total Poin)</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted small fst-italic">Belum ada pemenang</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($fame['is_current'])
                                            <span class="badge bg-primary text-white px-2.5 py-1 rounded-pill">
                                                <i class="fa-solid fa-spinner fa-spin me-1"></i> Bulan Berjalan
                                            </span>
                                        @elseif($fame['top_siswa'] || $fame['top_kelas'])
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                                <i class="fa-solid fa-check me-1"></i> Tuntas
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill">
                                                Akan Datang
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
