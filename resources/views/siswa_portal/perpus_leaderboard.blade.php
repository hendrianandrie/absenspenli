@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Banner -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge rounded-pill px-3 py-1 bg-warning-subtle text-warning-emphasis fw-semibold" style="font-size: 11px;">
                    <i class="fa-solid fa-trophy me-1"></i> Hall of Fame Literasi
                </span>
                <span class="text-secondary opacity-50">&bull;</span>
                <span class="text-muted small">Apresiasi Minat Baca Siswa & Kelas</span>
            </div>
            <h3 class="fw-bold text-dark mb-0">Papan Peringkat Literasi (Leaderboard)</h3>
            <p class="text-muted small mb-0">Pantau posisi membacamu dan dukung kelasmu memperebutkan Piala Bergilir Literasi!</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('siswa.perpus.katalog') }}" class="btn btn-primary-custom rounded-pill px-3 py-2 shadow-sm fs-7">
                <i class="fa-solid fa-book-bookmark me-1"></i> Pinjam Buku Baru
            </a>
            <a href="{{ route('siswa.perpus.pinjaman') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 shadow-sm fs-7">
                <i class="fa-solid fa-feather me-1"></i> Tulis Ulasan Buku
            </a>
        </div>
    </div>

    <!-- Filter Periode Bar -->
    <div class="card card-custom p-3 bg-white mb-4 shadow-sm">
        <form method="GET" action="{{ route('siswa.perpus.leaderboard') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-8 text-muted mb-1">Mode Peringkat</label>
                <select name="mode" class="form-select bg-light fs-8" onchange="this.form.submit()">
                    <option value="bulanan" {{ $filterMode === 'bulanan' ? 'selected' : '' }}>Peringkat Bulanan</option>
                    <option value="all" {{ $filterMode === 'all' ? 'selected' : '' }}>Akumulasi Semua Waktu (All-Time)</option>
                </select>
            </div>

            @if($filterMode === 'bulanan')
                <div class="col-md-3">
                    <label class="form-label fw-semibold fs-8 text-muted mb-1">Bulan</label>
                    <select name="bulan" class="form-select bg-light fs-8" onchange="this.form.submit()">
                        @foreach($daftarBulan as $num => $nama)
                            <option value="{{ $num }}" {{ $selectedBulan == $num ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold fs-8 text-muted mb-1">Tahun</label>
                    <select name="tahun" class="form-select bg-light fs-8" onchange="this.form.submit()">
                        @for($y = date('Y'); $y >= date('Y') - 1; $y--)
                            <option value="{{ $y }}" {{ $selectedTahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            @endif

            <div class="col-md-2 d-flex align-items-end pt-3">
                <button type="submit" class="btn btn-primary-custom w-100 fs-8 py-2">
                    <i class="fa-solid fa-filter me-1"></i> Terapkan
                </button>
            </div>

            <div class="col-md-{{ $filterMode === 'bulanan' ? '2' : '7' }} text-md-end text-muted small pt-3">
                <span class="badge bg-light text-secondary border px-3 py-2">
                    Periode: <strong>{{ $filterMode === 'bulanan' ? ($daftarBulan[$selectedBulan] ?? '') . ' ' . $selectedTahun : 'Sepanjang Waktu' }}</strong>
                </span>
            </div>
        </form>
    </div>

    <!-- My Personal Literacy Standing Card -->
    <div class="card card-custom p-4 bg-white mb-4 shadow-sm border-0 position-relative overflow-hidden" 
         style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%) !important; color: white;">
        <div class="position-absolute end-0 bottom-0 opacity-10 p-3 d-none d-md-block">
            <i class="fa-solid fa-trophy" style="font-size: 160px; color: white;"></i>
        </div>

        <div class="row align-items-center g-3 position-relative z-1">
            <div class="col-md-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white text-primary shadow" 
                         style="width: 58px; height: 58px;">
                        <i class="fa-solid {{ $myBadge['icon'] ?? 'fa-award' }} fs-3" style="color: {{ $myBadge['color'] ?? '#4f46e5' }};"></i>
                    </div>
                    <div>
                        <div class="badge rounded-pill bg-white bg-opacity-25 text-white px-3 py-1 fs-8 mb-1">
                            {{ $myBadge['label'] ?? 'Pembaca' }}
                        </div>
                        <h4 class="fw-bold mb-0 text-white">{{ $siswa->nama }}</h4>
                        <div class="text-white-50 small">Kelas {{ $siswa->kelas }} &bull; NIS: {{ $siswa->nis }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="p-3 rounded-4 bg-white bg-opacity-10 backdrop-blur border border-white border-opacity-10">
                            <div class="text-white-50 fs-8 fw-semibold text-uppercase">Poin Periode Ini</div>
                            <div class="fs-2 fw-bold text-warning mb-0">{{ number_format($myPoin) }}</div>
                            <div class="text-white-50 fs-8">Poin Literasi</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-4 bg-white bg-opacity-10 backdrop-blur border border-white border-opacity-10">
                            <div class="text-white-50 fs-8 fw-semibold text-uppercase">Peringkat Sekolah</div>
                            <div class="fs-2 fw-bold text-white mb-0">#{{ $myRankPosition }}</div>
                            <div class="text-white-50 fs-8">Dari Seluruh Siswa</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nav Tabs: Siswa & Kelas -->
    <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3" id="leaderboardTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 py-2 fw-semibold fs-7" id="siswa-tab" data-bs-toggle="pill" data-bs-target="#tab-siswa" type="button" role="tab">
                <i class="fa-solid fa-user-graduate me-1 text-primary"></i> Top 10 Siswa Teraktif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-semibold fs-7" id="kelas-tab" data-bs-toggle="pill" data-bs-target="#tab-kelas" type="button" role="tab">
                <i class="fa-solid fa-chalkboard-user me-1 text-success"></i> Peringkat Kelas (Piala Bergilir)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="leaderboardTabContent">
        <!-- TAB 1: DUTA LITERASI SISWA -->
        <div class="tab-pane fade show active" id="tab-siswa" role="tabpanel">
            @if(empty($topSiswaList))
                <div class="card card-custom p-5 bg-white text-center text-muted shadow-sm">
                    <i class="fa-solid fa-medal fs-1 mb-3 text-secondary opacity-50"></i>
                    <h5 class="fw-semibold text-secondary">Belum ada aktivitas literasi pada periode ini</h5>
                    <p class="small text-muted mb-0">Jadilah siswa pertama yang meraih poin bulan ini dengan meminjam buku di perpustakaan!</p>
                </div>
            @else
                <!-- Top 3 Podium Cards -->
                <div class="row g-3 mb-4 justify-content-center">
                    @foreach(array_slice($topSiswaList, 0, 3) as $top)
                        <div class="col-md-4">
                            <div class="card card-custom p-4 bg-white shadow-sm border-0 position-relative overflow-hidden text-center h-100 {{ $top['is_me'] ? 'border-primary border-2 ring-2 ring-primary' : '' }}"
                                 style="{{ $top['is_me'] ? 'border: 2px solid #4f46e5 !important;' : '' }}">
                                
                                @if($top['rank'] == 1)
                                    <div class="position-absolute top-0 end-0 bg-warning text-dark px-3 py-1 rounded-bottom-start-4 fw-bold fs-8">
                                        <i class="fa-solid fa-crown me-1"></i> Juara 1
                                    </div>
                                    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center text-white shadow"
                                         style="width: 68px; height: 68px; background: linear-gradient(135deg, #f59e0b, #d97706); font-size: 28px;">
                                        <i class="fa-solid fa-trophy"></i>
                                    </div>
                                @elseif($top['rank'] == 2)
                                    <div class="position-absolute top-0 end-0 bg-secondary-subtle text-secondary px-3 py-1 rounded-bottom-start-4 fw-bold fs-8">
                                        <i class="fa-solid fa-medal me-1"></i> Juara 2
                                    </div>
                                    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center text-white shadow"
                                         style="width: 64px; height: 64px; background: linear-gradient(135deg, #94a3b8, #64748b); font-size: 26px;">
                                        <i class="fa-solid fa-medal"></i>
                                    </div>
                                @else
                                    <div class="position-absolute top-0 end-0 bg-danger-subtle text-danger px-3 py-1 rounded-bottom-start-4 fw-bold fs-8">
                                        <i class="fa-solid fa-medal me-1"></i> Juara 3
                                    </div>
                                    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center text-white shadow"
                                         style="width: 60px; height: 60px; background: linear-gradient(135deg, #b45309, #78350f); font-size: 24px;">
                                        <i class="fa-solid fa-award"></i>
                                    </div>
                                @endif

                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center justify-content-center gap-1">
                                    {{ $top['nama'] }}
                                    @if($top['is_me'])
                                        <span class="badge bg-primary rounded-pill px-2 py-0 fs-8">Saya</span>
                                    @endif
                                </h6>
                                <div class="text-muted small mb-2">Kelas {{ $top['kelas'] }}</div>

                                <div class="mb-2">
                                    <span class="badge rounded-pill px-3 py-1 fs-8 {{ $top['badge']['class'] ?? '' }}"
                                          style="background: {{ $top['badge']['bg'] ?? '#f1f5f9' }}; color: {{ $top['badge']['text_color'] ?? $top['badge']['color'] ?? '#475569' }}; font-weight: 600;">
                                        <i class="fa-solid {{ $top['badge']['icon'] ?? 'fa-award' }} me-1"></i> {{ $top['badge']['label'] ?? $top['badge']['nama'] ?? 'Pembaca' }}
                                    </span>
                                </div>

                                <div class="mt-auto pt-2 border-top">
                                    <span class="fs-4 fw-bold text-primary">{{ number_format($top['poin']) }}</span>
                                    <span class="text-muted fs-8">Poin Literasi</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Top 10 Table -->
                <div class="card card-custom bg-white shadow-sm overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light fs-8 text-secondary">
                                <tr>
                                    <th class="text-center" style="width: 70px;">PERINGKAT</th>
                                    <th>SISWA</th>
                                    <th>KELAS</th>
                                    <th>GELAR LITERASI</th>
                                    <th class="text-end pe-4">TOTAL POIN</th>
                                </tr>
                            </thead>
                            <tbody class="fs-8">
                                @foreach($topSiswaList as $s)
                                    <tr class="{{ $s['is_me'] ? 'table-primary fw-bold' : '' }}">
                                        <td class="text-center">
                                            @if($s['rank'] == 1)
                                                <span class="badge bg-warning text-dark rounded-circle p-2" style="width: 28px; height: 28px;">1</span>
                                            @elseif($s['rank'] == 2)
                                                <span class="badge bg-secondary text-white rounded-circle p-2" style="width: 28px; height: 28px;">2</span>
                                            @elseif($s['rank'] == 3)
                                                <span class="badge text-white rounded-circle p-2" style="background: #b45309; width: 28px; height: 28px;">3</span>
                                            @else
                                                <span class="text-muted fw-bold">#{{ $s['rank'] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-dark">{{ $s['nama'] }}</span>
                                                @if($s['is_me'])
                                                    <span class="badge bg-primary rounded-pill px-2 py-0 fs-8">Kamu</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border">{{ $s['kelas'] }}</span></td>
                                        <td>
                                            <span class="badge rounded-pill px-2 py-1 {{ $s['badge']['class'] ?? '' }}"
                                                  style="background: {{ $s['badge']['bg'] ?? '#f1f5f9' }}; color: {{ $s['badge']['text_color'] ?? $s['badge']['color'] ?? '#475569' }};">
                                                <i class="fa-solid {{ $s['badge']['icon'] ?? 'fa-award' }} me-1"></i> {{ $s['badge']['label'] ?? $s['badge']['nama'] ?? 'Pembaca' }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="fw-bold fs-7 text-primary">{{ number_format($s['poin']) }}</span>
                                            <span class="text-muted fs-8">Pts</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- TAB 2: PERINGKAT KELAS -->
        <div class="tab-pane fade" id="tab-kelas" role="tabpanel">
            <!-- Penjelasan Rumus Adil -->
            <div class="alert alert-info border-0 rounded-4 shadow-sm p-3 mb-4 d-flex align-items-center gap-3" style="background: #eff6ff; color: #1e40af;">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-white text-primary shadow-sm flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="fa-solid fa-scale-balanced fs-5"></i>
                </div>
                <div class="small">
                    <strong class="d-block mb-1">Formula Adil Penilaian Kelas (Piala Bergilir):</strong>
                    Peringkat kelas ditentukan berdasarkan <strong>Indeks Literasi Rata-Rata</strong> = 
                    <span class="badge bg-primary text-white">Total Poin Siswa di Kelas ÷ Jumlah Siswa Kelas</span>.
                    Dengan cara ini, kelas dengan jumlah siswa sedikit tetap memiliki kesempatan menang yang setara dan adil dengan kelas yang siswanya banyak!
                </div>
            </div>

            @if(empty($kelasRankings))
                <div class="card card-custom p-5 bg-white text-center text-muted shadow-sm">
                    <i class="fa-solid fa-school fs-1 mb-3 text-secondary opacity-50"></i>
                    <h5 class="fw-semibold text-secondary">Belum ada data perolehan poin kelas</h5>
                </div>
            @else
                <div class="card card-custom bg-white shadow-sm overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light fs-8 text-secondary">
                                <tr>
                                    <th class="text-center" style="width: 80px;">PERINGKAT</th>
                                    <th>NAMA KELAS</th>
                                    <th class="text-center">JUMLAH SISWA</th>
                                    <th class="text-end">TOTAL POIN KELAS</th>
                                    <th class="text-end pe-4">INDEKS RATA-RATA (SKOR)</th>
                                </tr>
                            </thead>
                            <tbody class="fs-8">
                                @foreach($kelasRankings as $kr)
                                    <tr class="{{ $kr['is_my_class'] ? 'table-warning fw-bold' : '' }}">
                                        <td class="text-center">
                                            @if($kr['rank'] == 1)
                                                <span class="badge bg-warning text-dark rounded-circle p-2" style="width: 30px; height: 30px;" title="Pemegang Piala Bergilir">
                                                    <i class="fa-solid fa-trophy"></i>
                                                </span>
                                            @elseif($kr['rank'] == 2)
                                                <span class="badge bg-secondary text-white rounded-circle p-2" style="width: 30px; height: 30px;">2</span>
                                            @elseif($kr['rank'] == 3)
                                                <span class="badge text-white rounded-circle p-2" style="background: #b45309; width: 30px; height: 30px;">3</span>
                                            @else
                                                <span class="text-muted fw-bold">#{{ $kr['rank'] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold text-dark fs-7">Kelas {{ $kr['kelas'] }}</span>
                                                @if($kr['is_my_class'])
                                                    <span class="badge bg-primary text-white rounded-pill px-2 py-0 fs-8">Kelasku</span>
                                                @endif
                                                @if($kr['rank'] == 1)
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0 fs-8">
                                                        <i class="fa-solid fa-star me-1"></i> Piala Bergilir
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center text-muted">
                                            <i class="fa-solid fa-users me-1"></i> {{ $kr['total_siswa'] }} Siswa
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-semibold text-secondary">{{ number_format($kr['total_poin']) }}</span> Pts
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="fs-6 fw-bold {{ $kr['rank'] == 1 ? 'text-warning-emphasis' : 'text-primary' }}">
                                                {{ number_format($kr['indeks_rata_rata'], 1) }}
                                            </span>
                                            <span class="text-muted fs-8">pts/siswa</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.fs-7 { font-size: 0.875rem !important; }
.fs-8 { font-size: 0.775rem !important; }
.card-custom {
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
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
