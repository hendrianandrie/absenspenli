@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-gauge-high text-primary me-2"></i> Dashboard SI-KASEP</h3>
        <p class="text-muted mb-0">Selamat datang di Sistem Informasi Rekap Absen Spenli (SMP Negeri 5).</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <span class="badge bg-white text-dark border px-3 py-2 fs-6 rounded-pill shadow-sm">
            <i class="fa-regular fa-calendar-days text-primary me-1"></i> Hari ini: {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
        </span>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    @if(isset($isGuru) && $isGuru)
        <!-- DASHBOARD KHUSUS GURU MATA PELAJARAN -->
        <div class="col-12 mb-2">
            <div class="card card-custom p-4 bg-primary text-white shadow-sm border-0 rounded-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <span class="badge bg-white text-primary rounded-pill px-3 py-1 mb-2 fw-semibold">
                            <i class="fa-solid fa-chalkboard-user me-1"></i> Dashboard Guru Mata Pelajaran
                        </span>
                        <h3 class="fw-bold mb-1 text-white">Guru {{ $guruMapel->nama_mapel ?? 'Mata Pelajaran' }}</h3>
                        <p class="text-white-50 mb-0 small">
                            <i class="fa-solid fa-id-card me-1"></i> pengampu mata pelajaran {{ $guruMapel->nama_mapel ?? '-' }} (KKM: {{ $guruMapel->kkm ?? 75 }}) 
                            • Kelas Diampu: <strong>{{ !empty($guruKelas) ? implode(', ', $guruKelas) : 'Semua Kelas' }}</strong>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('nilai.kegiatan.create') }}" class="btn btn-warning text-dark fw-bold px-3 py-2 rounded-3 shadow-sm">
                            <i class="fa-solid fa-plus-circle me-1"></i> Input Nilai Baru
                        </a>
                        <a href="{{ route('nilai.index') }}" class="btn btn-light text-primary fw-bold px-3 py-2 rounded-3 shadow-sm">
                            <i class="fa-solid fa-list-check me-1"></i> Kelola Nilai
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-primary border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Total Siswa Diampu</span>
                        <h2 class="fw-bold mb-0 text-primary">{{ $guruStats['total_siswa'] ?? 0 }}</h2>
                    </div>
                    <div class="bg-primary-subtle p-3 rounded-circle text-primary">
                        <i class="fa-solid fa-users fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-info border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Kegiatan Penilaian</span>
                        <h2 class="fw-bold mb-0 text-info">{{ $guruStats['total_kegiatan'] ?? 0 }}</h2>
                    </div>
                    <div class="bg-info-subtle p-3 rounded-circle text-info">
                        <i class="fa-solid fa-clipboard-list fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-warning border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Rata-Rata Nilai</span>
                        <h2 class="fw-bold mb-0 text-warning">{{ $guruStats['avg_nilai'] ?? 0 }}</h2>
                    </div>
                    <div class="bg-warning-subtle p-3 rounded-circle text-warning">
                        <i class="fa-solid fa-star fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-success border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Ketuntasan KKM</span>
                        <h2 class="fw-bold mb-0 text-success">{{ $guruStats['pct_tuntas'] ?? 0 }}%</h2>
                        <small class="text-muted" style="font-size: 11px;">{{ $guruStats['tuntas_count'] ?? 0 }} Tuntas / {{ $guruStats['belum_tuntas_count'] ?? 0 }} Remedial</small>
                    </div>
                    <div class="bg-success-subtle p-3 rounded-circle text-success">
                        <i class="fa-solid fa-circle-check fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>

    @elseif(Auth::check() && Auth::user()->role === 'piket')
        <div class="col-sm-6 col-xl-6">
            <div class="card card-custom p-3 bg-white stat-card border-start border-primary border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Total Siswa Terdaftar</span>
                        <h2 class="fw-bold mb-0 text-primary">{{ $totalSiswa ?? 0 }}</h2>
                    </div>
                    <div class="bg-primary-subtle p-3 rounded-circle text-primary">
                        <i class="fa-solid fa-user-graduate fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-6">
            <div class="card card-custom p-3 bg-white stat-card border-start border-warning border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Hak Akses Sistem</span>
                        <h4 class="fw-bold mb-0 text-warning"><i class="fa-solid fa-clipboard-user me-1"></i> Petugas Piket (Absensi)</h4>
                    </div>
                    <div class="bg-warning-subtle p-3 rounded-circle text-warning">
                        <i class="fa-solid fa-user-shield fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-primary border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Total Siswa</span>
                        <h2 class="fw-bold mb-0 text-primary">{{ $totalSiswa ?? 0 }}</h2>
                    </div>
                    <div class="bg-primary-subtle p-3 rounded-circle text-primary">
                        <i class="fa-solid fa-user-graduate fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-info border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Mata Pelajaran</span>
                        <h2 class="fw-bold mb-0 text-info">{{ $totalMapel ?? 0 }}</h2>
                    </div>
                    <div class="bg-info-subtle p-3 rounded-circle text-info">
                        <i class="fa-solid fa-book fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-warning border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Kegiatan Penilaian</span>
                        <h2 class="fw-bold mb-0 text-warning">{{ $totalKegiatan ?? 0 }}</h2>
                    </div>
                    <div class="bg-warning-subtle p-3 rounded-circle text-warning">
                        <i class="fa-solid fa-clipboard-list fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-success border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Rata-Rata Nilai</span>
                        <h2 class="fw-bold mb-0 text-success">{{ $avgNilaiGlobal ? number_format($avgNilaiGlobal, 1) : '-' }}</h2>
                    </div>
                    <div class="bg-success-subtle p-3 rounded-circle text-success">
                        <i class="fa-solid fa-star fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Kehadiran Kelas Chart/Table & Posisi Tombol Cetak PDF -->
<div class="card card-custom p-4 bg-white mb-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h5 class="fw-bold mb-0">
            <i class="fa-solid fa-list-check text-primary me-2"></i> Kehadiran Siswa Per Kelas ({{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }})
        </h5>
        <!-- Tombol Cetak PDF Diletakkan Presisi di Header Tabel Kehadiran -->
        <a href="{{ route('dashboard.cetakPdf') }}" class="btn btn-outline-danger btn-sm px-3 py-2 rounded-3 shadow-sm">
            <i class="fa-solid fa-file-pdf me-1"></i> Cetak Rekap Kehadiran PDF
        </a>
    </div>

    @if(empty($dataKelas))
        <div class="text-center py-4 text-muted">
            <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
            Belum ada data siswa atau kelas yang terdaftar. <a href="{{ route('siswa.index') }}">Tambah Data Siswa</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kelas</th>
                        <th>Total Siswa</th>
                        <th>Hadir</th>
                        <th>Tidak Hadir (S/I/A)</th>
                        <th>Persentase Kehadiran</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dataKelas as $item)
                        @php
                            $persen = $item['total'] > 0 ? round(($item['hadir'] / $item['total']) * 100) : 0;
                        @endphp
                        <tr>
                            <td><span class="badge bg-primary fs-6">{{ $item['kelas'] }}</span></td>
                            <td>{{ $item['total'] }} Siswa</td>
                            <td class="text-success fw-bold">{{ $item['hadir'] }}</td>
                            <td class="text-danger fw-bold">{{ $item['tidak_hadir'] }}</td>
                            <td style="width: 25%;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar {{ $persen >= 85 ? 'bg-success' : ($persen >= 70 ? 'bg-warning' : 'bg-danger') }}" 
                                             style="width: {{ $persen }}%;"></div>
                                    </div>
                                    <span class="small fw-semibold">{{ $persen }}%</span>
                                </div>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('absensi.harian', ['kelas' => $item['kelas'], 'tanggal' => $tanggal]) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Absen Kelas Ini
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Rekap Persentase Kehadiran (Hari Ini, Mingguan, Bulanan, Keseluruhan) -->
<div class="card card-custom p-4 bg-white shadow-sm">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">
            <i class="fa-solid fa-chart-pie text-primary me-2"></i> Persentase Kehadiran, Izin, Sakit & Alpa Siswa
        </h5>
        <span class="badge bg-light text-secondary border px-3 py-1">
            <i class="fa-solid fa-circle-info me-1 text-info"></i> Berdasarkan rekapitulasi data absensi siswa
        </span>
    </div>

    <div class="row g-3">
        <!-- Card 1: Hari Ini -->
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 rounded-3 border bg-light h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-warning">
                        <i class="fa-solid fa-calendar-day me-1"></i> Hari Ini
                    </h6>
                    <span class="badge bg-warning-subtle text-warning fw-semibold px-2 py-1">
                        {{ $statsHariIni['total'] }} Record
                    </span>
                </div>
                
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-success"><i class="fa-solid fa-circle-check me-1"></i> Hadir</span>
                        <span class="fw-bold text-success">{{ $statsHariIni['hadir_pct'] }}% <small class="text-muted">({{ $statsHariIni['hadir'] }})</small></span>
                    </div>
                    <div class="progress mb-2" style="height: 7px;">
                        <div class="progress-bar bg-success" style="width: {{ $statsHariIni['hadir_pct'] }}%"></div>
                    </div>
                </div>

                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-info"><i class="fa-solid fa-envelope-open-text me-1"></i> Izin</span>
                        <span class="fw-bold text-info">{{ $statsHariIni['izin_pct'] }}% <small class="text-muted">({{ $statsHariIni['izin'] }})</small></span>
                    </div>
                    <div class="progress mb-2" style="height: 7px;">
                        <div class="progress-bar bg-info" style="width: {{ $statsHariIni['izin_pct'] }}%"></div>
                    </div>
                </div>

                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-warning"><i class="fa-solid fa-notes-medical me-1"></i> Sakit</span>
                        <span class="fw-bold text-warning">{{ $statsHariIni['sakit_pct'] }}% <small class="text-muted">({{ $statsHariIni['sakit'] }})</small></span>
                    </div>
                    <div class="progress mb-2" style="height: 7px;">
                        <div class="progress-bar bg-warning" style="width: {{ $statsHariIni['sakit_pct'] }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Alpa</span>
                        <span class="fw-bold text-danger">{{ $statsHariIni['alpha_pct'] }}% <small class="text-muted">({{ $statsHariIni['alpha'] }})</small></span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar bg-danger" style="width: {{ $statsHariIni['alpha_pct'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Minggu Ini -->
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 rounded-3 border bg-light h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="fa-solid fa-calendar-week me-1"></i> Minggu Ini
                    </h6>
                    <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1">
                        {{ $statsMingguIni['total'] }} Record
                    </span>
                </div>
                
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-success"><i class="fa-solid fa-circle-check me-1"></i> Hadir</span>
                        <span class="fw-bold text-success">{{ $statsMingguIni['hadir_pct'] }}% <small class="text-muted">({{ $statsMingguIni['hadir'] }})</small></span>
                    </div>
                    <div class="progress mb-2" style="height: 7px;">
                        <div class="progress-bar bg-success" style="width: {{ $statsMingguIni['hadir_pct'] }}%"></div>
                    </div>
                </div>

                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-info"><i class="fa-solid fa-envelope-open-text me-1"></i> Izin</span>
                        <span class="fw-bold text-info">{{ $statsMingguIni['izin_pct'] }}% <small class="text-muted">({{ $statsMingguIni['izin'] }})</small></span>
                    </div>
                    <div class="progress mb-2" style="height: 7px;">
                        <div class="progress-bar bg-info" style="width: {{ $statsMingguIni['izin_pct'] }}%"></div>
                    </div>
                </div>

                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-warning"><i class="fa-solid fa-notes-medical me-1"></i> Sakit</span>
                        <span class="fw-bold text-warning">{{ $statsMingguIni['sakit_pct'] }}% <small class="text-muted">({{ $statsMingguIni['sakit'] }})</small></span>
                    </div>
                    <div class="progress mb-2" style="height: 7px;">
                        <div class="progress-bar bg-warning" style="width: {{ $statsMingguIni['sakit_pct'] }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Alpa</span>
                        <span class="fw-bold text-danger">{{ $statsMingguIni['alpha_pct'] }}% <small class="text-muted">({{ $statsMingguIni['alpha'] }})</small></span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar bg-danger" style="width: {{ $statsMingguIni['alpha_pct'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Bulan Ini -->
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 rounded-3 border bg-light h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-info">
                        <i class="fa-solid fa-calendar-days me-1"></i> Bulan Ini
                    </h6>
                    <span class="badge bg-info-subtle text-info fw-semibold px-2 py-1">
                        {{ $statsBulanIni['total'] }} Record
                    </span>
                </div>
                
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-success"><i class="fa-solid fa-circle-check me-1"></i> Hadir</span>
                        <span class="fw-bold text-success">{{ $statsBulanIni['hadir_pct'] }}% <small class="text-muted">({{ $statsBulanIni['hadir'] }})</small></span>
                    </div>
                    <div class="progress mb-2" style="height: 7px;">
                        <div class="progress-bar bg-success" style="width: {{ $statsBulanIni['hadir_pct'] }}%"></div>
                    </div>
                </div>

                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-info"><i class="fa-solid fa-envelope-open-text me-1"></i> Izin</span>
                        <span class="fw-bold text-info">{{ $statsBulanIni['izin_pct'] }}% <small class="text-muted">({{ $statsBulanIni['izin'] }})</small></span>
                    </div>
                    <div class="progress mb-2" style="height: 7px;">
                        <div class="progress-bar bg-info" style="width: {{ $statsBulanIni['izin_pct'] }}%"></div>
                    </div>
                </div>

                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-warning"><i class="fa-solid fa-notes-medical me-1"></i> Sakit</span>
                        <span class="fw-bold text-warning">{{ $statsBulanIni['sakit_pct'] }}% <small class="text-muted">({{ $statsBulanIni['sakit'] }})</small></span>
                    </div>
                    <div class="progress mb-2" style="height: 7px;">
                        <div class="progress-bar bg-warning" style="width: {{ $statsBulanIni['sakit_pct'] }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Alpa</span>
                        <span class="fw-bold text-danger">{{ $statsBulanIni['alpha_pct'] }}% <small class="text-muted">({{ $statsBulanIni['alpha'] }})</small></span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar bg-danger" style="width: {{ $statsBulanIni['alpha_pct'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Keseluruhan -->
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 rounded-3 border bg-light h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-success">
                        <i class="fa-solid fa-globe me-1"></i> Keseluruhan
                    </h6>
                    <span class="badge bg-success-subtle text-success fw-semibold px-2 py-1">
                        {{ $statsKeseluruhan['total'] }} Record
                    </span>
                </div>
                
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-success"><i class="fa-solid fa-circle-check me-1"></i> Hadir</span>
                        <span class="fw-bold text-success">{{ $statsKeseluruhan['hadir_pct'] }}% <small class="text-muted">({{ $statsKeseluruhan['hadir'] }})</small></span>
                    </div>
                    <div class="progress mb-2" style="height: 7px;">
                        <div class="progress-bar bg-success" style="width: {{ $statsKeseluruhan['hadir_pct'] }}%"></div>
                    </div>
                </div>

                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-info"><i class="fa-solid fa-envelope-open-text me-1"></i> Izin</span>
                        <span class="fw-bold text-info">{{ $statsKeseluruhan['izin_pct'] }}% <small class="text-muted">({{ $statsKeseluruhan['izin'] }})</small></span>
                    </div>
                    <div class="progress mb-2" style="height: 7px;">
                        <div class="progress-bar bg-info" style="width: {{ $statsKeseluruhan['izin_pct'] }}%"></div>
                    </div>
                </div>

                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-warning"><i class="fa-solid fa-notes-medical me-1"></i> Sakit</span>
                        <span class="fw-bold text-warning">{{ $statsKeseluruhan['sakit_pct'] }}% <small class="text-muted">({{ $statsKeseluruhan['sakit'] }})</small></span>
                    </div>
                    <div class="progress mb-2" style="height: 7px;">
                        <div class="progress-bar bg-warning" style="width: {{ $statsKeseluruhan['sakit_pct'] }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Alpa</span>
                        <span class="fw-bold text-danger">{{ $statsKeseluruhan['alpha_pct'] }}% <small class="text-muted">({{ $statsKeseluruhan['alpha'] }})</small></span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar bg-danger" style="width: {{ $statsKeseluruhan['alpha_pct'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Matriks Ringkasan Tabel -->
    <div class="table-responsive mt-4">
        <table class="table table-bordered table-sm align-middle text-center mb-0 bg-white">
            <thead class="table-light">
                <tr>
                    <th class="text-start ps-3">Kategori Status</th>
                    <th><i class="fa-solid fa-calendar-day text-warning me-1"></i> Hari Ini</th>
                    <th><i class="fa-solid fa-calendar-week text-primary me-1"></i> Minggu Ini</th>
                    <th><i class="fa-solid fa-calendar-days text-info me-1"></i> Bulan Ini</th>
                    <th><i class="fa-solid fa-globe text-success me-1"></i> Keseluruhan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-start ps-3 fw-semibold text-success"><i class="fa-solid fa-circle-check me-2"></i> Hadir</td>
                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fs-6">{{ $statsHariIni['hadir_pct'] }}%</span></td>
                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fs-6">{{ $statsMingguIni['hadir_pct'] }}%</span></td>
                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fs-6">{{ $statsBulanIni['hadir_pct'] }}%</span></td>
                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fs-6">{{ $statsKeseluruhan['hadir_pct'] }}%</span></td>
                </tr>
                <tr>
                    <td class="text-start ps-3 fw-semibold text-info"><i class="fa-solid fa-envelope-open-text me-2"></i> Izin</td>
                    <td><span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 fs-6">{{ $statsHariIni['izin_pct'] }}%</span></td>
                    <td><span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 fs-6">{{ $statsMingguIni['izin_pct'] }}%</span></td>
                    <td><span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 fs-6">{{ $statsBulanIni['izin_pct'] }}%</span></td>
                    <td><span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 fs-6">{{ $statsKeseluruhan['izin_pct'] }}%</span></td>
                </tr>
                <tr>
                    <td class="text-start ps-3 fw-semibold text-warning"><i class="fa-solid fa-notes-medical me-2"></i> Sakit</td>
                    <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 fs-6">{{ $statsHariIni['sakit_pct'] }}%</span></td>
                    <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 fs-6">{{ $statsMingguIni['sakit_pct'] }}%</span></td>
                    <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 fs-6">{{ $statsBulanIni['sakit_pct'] }}%</span></td>
                    <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 fs-6">{{ $statsKeseluruhan['sakit_pct'] }}%</span></td>
                </tr>
                <tr>
                    <td class="text-start ps-3 fw-semibold text-danger"><i class="fa-solid fa-circle-xmark me-2"></i> Alpa</td>
                    <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 fs-6">{{ $statsHariIni['alpha_pct'] }}%</span></td>
                    <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 fs-6">{{ $statsMingguIni['alpha_pct'] }}%</span></td>
                    <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 fs-6">{{ $statsBulanIni['alpha_pct'] }}%</span></td>
                    <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 fs-6">{{ $statsKeseluruhan['alpha_pct'] }}%</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
