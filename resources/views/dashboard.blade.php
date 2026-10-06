@extends('layouts.app')

@push('styles')
<style>
    /* Cruip Mosaic Design Tokens & Utilities */
    .cruip-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .cruip-card:hover {
        box-shadow: 0 10px 22px -4px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
        transform: translateY(-2px);
    }
    .mosaic-banner {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 45%, #4338ca 100%);
        color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(49, 46, 129, 0.25);
    }
    .mosaic-icon-box {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        flex-shrink: 0;
    }
    .text-slate-800 { color: #1e293b !important; }
    .text-slate-700 { color: #334155 !important; }
    .text-slate-600 { color: #475569 !important; }
    .text-slate-500 { color: #64748b !important; }
    .text-slate-400 { color: #94a3b8 !important; }
    .bg-slate-50 { background-color: #f8fafc !important; }
    .bg-slate-100 { background-color: #f1f5f9 !important; }
    .border-slate-100 { border-color: #f1f5f9 !important; }
    .border-slate-200 { border-color: #e2e8f0 !important; }

    .pulse-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: cruipPulse 2s infinite;
    }
    @keyframes cruipPulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }
</style>
@endpush

@section('content')
@if(Auth::check() && Auth::user()->role === 'admin')
    <!-- Cruip Mosaic Style Welcome Banner for Admin -->
    <div class="mosaic-banner rounded-4 p-3.5 p-md-4 mb-3.5 position-relative overflow-hidden">
        <!-- SVG Graphic Illustration -->
        <div class="position-absolute top-0 end-0 h-100 d-none d-lg-block pointer-events-none" style="opacity: 0.18; transform: translate(10%, -10%);">
            <svg width="400" height="220" viewBox="0 0 460 260" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="230" cy="130" r="160" fill="url(#cruip-grad-1)" />
                <circle cx="360" cy="70" r="100" fill="url(#cruip-grad-2)" />
                <path d="M140 40C200 80 280 20 370 90C450 160 320 250 230 210C150 170 70 230 40 150C20 60 70 0 140 40Z" fill="url(#cruip-grad-3)" opacity="0.6"/>
                <defs>
                    <linearGradient id="cruip-grad-1" x1="70" y1="0" x2="390" y2="260" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#818cf8"/>
                        <stop offset="1" stop-color="#c084fc"/>
                    </linearGradient>
                    <linearGradient id="cruip-grad-2" x1="260" y1="0" x2="440" y2="160" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#38bdf8"/>
                        <stop offset="1" stop-color="#818cf8"/>
                    </linearGradient>
                    <linearGradient id="cruip-grad-3" x1="40" y1="20" x2="370" y2="260" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#a855f7"/>
                        <stop offset="1" stop-color="#ec4899"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <div class="position-relative z-1">
            <div class="d-inline-flex align-items-center gap-2 px-2.5 py-1 rounded-pill bg-white bg-opacity-10 text-white border border-white border-opacity-20 mb-2" style="font-size: 11.5px;">
                <span class="pulse-dot" style="width: 7px; height: 7px;"></span>
                <i class="fa-regular fa-calendar-days me-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </div>
            <h3 class="fw-bold mb-1 text-white" style="font-size: 1.35rem; letter-spacing: -0.02em;">Selamat Datang di SI-KASEP, {{ Auth::user()->name }}! 👋</h3>
            <p class="text-white-50 mb-3" style="max-width: 650px; font-size: 12.5px; line-height: 1.5;">
                Sistem Informasi Rekapitulasi Absensi dan Penilaian SMP Negeri 5 Ciamis. Pantau kehadiran rombongan belajar dan progres kegiatan akademik secara real-time.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('absensi.harian') }}" class="btn btn-light btn-sm text-primary fw-semibold px-3 py-1.5 rounded-pill shadow-sm" style="font-size: 12px;">
                    <i class="fa-solid fa-calendar-check me-1 text-primary"></i> Input Absensi Hari Ini
                </a>
                <a href="{{ route('nilai.index') }}" class="btn btn-outline-light btn-sm fw-semibold px-3 py-1.5 rounded-pill" style="font-size: 12px;">
                    <i class="fa-solid fa-graduation-cap me-1"></i> Manajemen Nilai
                </a>
                <a href="{{ route('siswa.index') }}" class="btn btn-outline-light btn-sm fw-semibold px-3 py-1.5 rounded-pill" style="font-size: 12px;">
                    <i class="fa-solid fa-users me-1"></i> Kelola Siswa
                </a>
                <a href="{{ route('dashboard.cetakPdf') }}" class="btn btn-outline-light btn-sm fw-semibold px-3 py-1.5 rounded-pill" style="font-size: 12px;">
                    <i class="fa-solid fa-file-pdf me-1 text-warning"></i> Cetak Rekap PDF
                </a>
            </div>
        </div>
    </div>
@else
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-gauge-high text-primary me-2"></i> Dashboard SI-KASEP</h3>
            <p class="text-muted mb-0">Selamat datang di Sistem Informasi Rekap Absen dan Nilai SMP Negeri 5 Ciamis.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <span class="badge bg-white text-dark border px-3 py-2 fs-6 rounded-pill shadow-sm">
                <i class="fa-regular fa-calendar-days text-primary me-1"></i> Hari ini: {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </div>
@endif

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    @if(isset($userWaliKelas) && $userWaliKelas)
        <div class="col-12 mb-1">
            <div class="card p-3 p-md-3.5 rounded-4 border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 50%, #14b8a6 100%);">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="text-white-50 mb-1 fw-medium" style="font-size: 11.5px; letter-spacing: 0.3px;">
                            <i class="fa-solid fa-award text-warning me-1"></i> Tugas Tambahan
                        </div>
                        <h4 class="fw-bold mb-1 text-white" style="font-size: 1.25rem;">
                            <i class="fa-solid fa-user-tie me-1.5"></i> Wali Kelas {{ $userWaliKelas->kelas }}
                        </h4>
                        <p class="text-white-50 mb-0 small" style="font-size: 12px;">
                            Total Siswa: <strong>{{ $waliKelasStats['total_siswa'] ?? 0 }} siswa</strong> • Kehadiran Hari Ini: <strong>{{ $waliKelasStats['hadir'] ?? 0 }} hadir ({{ $waliKelasStats['hadir_pct'] ?? 0 }}%)</strong>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('walikelas.myClass') }}" class="btn btn-light btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm" style="color: #0f766e; font-size: 12px;">
                            <i class="fa-solid fa-graduation-cap me-1"></i> Lihat Data Nilai Siswa Kelas {{ $userWaliKelas->kelas }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                        @if(isset($guruMapels) && $guruMapels->count() > 1)
                            <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
                                <span class="small text-white-50 fw-semibold"><i class="fa-solid fa-layer-group me-1"></i> Ganti Mapel:</span>
                                <div class="btn-group btn-group-sm">
                                    @foreach($guruMapels as $gm)
                                        <a href="{{ route('dashboard', ['mapel_id' => $gm->id]) }}" 
                                           class="btn {{ ($guruMapel && $guruMapel->id == $gm->id) ? 'btn-light text-primary fw-bold' : 'btn-outline-light' }}">
                                            {{ $gm->nama_mapel }} <span class="badge bg-primary text-white ms-1">{{ $gm->tingkat ?? 'Semua' }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
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
            <div class="card card-custom p-3 bg-white stat-card border-start border-primary border-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Total Siswa Diampu</span>
                        <div class="fw-bold text-primary mt-1" style="font-size: 1.45rem; line-height: 1.2;">{{ $guruStats['total_siswa'] ?? 0 }}</div>
                    </div>
                    <div class="bg-primary-subtle p-2.5 rounded-circle text-primary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-info border-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Kegiatan Penilaian</span>
                        <div class="fw-bold text-info mt-1" style="font-size: 1.45rem; line-height: 1.2;">{{ $guruStats['total_kegiatan'] ?? 0 }}</div>
                    </div>
                    <div class="bg-info-subtle p-2.5 rounded-circle text-info d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-warning border-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Rata-Rata Nilai</span>
                        <div class="fw-bold text-warning mt-1" style="font-size: 1.45rem; line-height: 1.2;">{{ $guruStats['avg_nilai'] ?? 0 }}</div>
                    </div>
                    <div class="bg-warning-subtle p-2.5 rounded-circle text-warning d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="fa-solid fa-star"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-success border-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Ketuntasan KKM</span>
                        <div class="fw-bold text-success mt-1" style="font-size: 1.45rem; line-height: 1.2;">{{ $guruStats['pct_tuntas'] ?? 0 }}%</div>
                        <small class="text-slate-400" style="font-size: 10.5px;">{{ $guruStats['tuntas_count'] ?? 0 }} Tuntas / {{ $guruStats['belum_tuntas_count'] ?? 0 }} Remedial</small>
                    </div>
                    <div class="bg-success-subtle p-2.5 rounded-circle text-success d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>

    @elseif(Auth::check() && Auth::user()->role === 'piket')
        <div class="col-sm-6 col-xl-6">
            <div class="card card-custom p-3 bg-white stat-card border-start border-primary border-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Total Siswa Terdaftar</span>
                        <div class="fw-bold text-primary mt-1" style="font-size: 1.45rem; line-height: 1.2;">{{ $totalSiswa ?? 0 }}</div>
                    </div>
                    <div class="bg-primary-subtle p-2.5 rounded-circle text-primary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-6">
            <div class="card card-custom p-3 bg-white stat-card border-start border-warning border-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Hak Akses Sistem</span>
                        <div class="fw-bold text-warning mt-1" style="font-size: 1.2rem; line-height: 1.2;"><i class="fa-solid fa-clipboard-user me-1"></i> Petugas Piket</div>
                    </div>
                    <div class="bg-warning-subtle p-2.5 rounded-circle text-warning d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Cruip Mosaic KPI Stat Cards for Admin -->
        <div class="col-sm-6 col-xl-3">
            <div class="cruip-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="mosaic-icon-box" style="width: 36px; height: 36px; background: #eef2ff; color: #4f46e5; border-radius: 10px;">
                        <i class="fa-solid fa-user-graduate" style="font-size: 13.5px;"></i>
                    </div>
                    <span class="badge rounded-pill fw-semibold px-2 py-0.5" style="background: #eef2ff; color: #4338ca; font-size: 10.5px;">
                        <i class="fa-solid fa-chalkboard me-1"></i> {{ count($dataKelas) }} Kelas
                    </span>
                </div>
                <div class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Total Siswa Terdaftar</div>
                <div class="d-flex align-items-baseline gap-1.5 mt-1">
                    <div class="fw-bold text-slate-800" style="font-size: 1.45rem; line-height: 1.2;">{{ number_format($totalSiswa ?? 0) }}</div>
                    <span class="text-slate-400" style="font-size: 11px;">siswa</span>
                </div>
                <div class="mt-1 text-slate-400" style="font-size: 11px;">
                    Tersebar di seluruh rombel kelas
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="cruip-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="mosaic-icon-box" style="width: 36px; height: 36px; background: #ecfdf5; color: #059669; border-radius: 10px;">
                        <i class="fa-solid fa-circle-check" style="font-size: 13.5px;"></i>
                    </div>
                    <span class="badge rounded-pill fw-semibold px-2 py-0.5" style="background: #ecfdf5; color: #047857; font-size: 10.5px;">
                        {{ $statsHariIni['hadir_pct'] }}% Hadir
                    </span>
                </div>
                <div class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Kehadiran Hari Ini</div>
                <div class="d-flex align-items-baseline gap-1.5 mt-1">
                    <div class="fw-bold text-slate-800" style="font-size: 1.45rem; line-height: 1.2;">{{ $statsHariIni['hadir'] }}</div>
                    <span class="text-slate-400" style="font-size: 11px;">/ {{ $statsHariIni['total'] ?: $totalSiswa }} tercatat</span>
                </div>
                <div class="mt-1 text-slate-400" style="font-size: 11px;">
                    Tingkat kehadiran siswa hari ini
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="cruip-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="mosaic-icon-box" style="width: 36px; height: 36px; background: #fff1f2; color: #e11d48; border-radius: 10px;">
                        <i class="fa-solid fa-user-xmark" style="font-size: 13.5px;"></i>
                    </div>
                    <span class="badge rounded-pill fw-semibold px-2 py-0.5" style="background: #fff1f2; color: #be123c; font-size: 10.5px;">
                        S:{{ $statsHariIni['sakit'] }} • I:{{ $statsHariIni['izin'] }} • A:{{ $statsHariIni['alpha'] }}
                    </span>
                </div>
                <div class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Tidak Hadir (S / I / A)</div>
                <div class="d-flex align-items-baseline gap-1.5 mt-1">
                    <div class="fw-bold text-danger" style="font-size: 1.45rem; line-height: 1.2;">{{ $statsHariIni['sakit'] + $statsHariIni['izin'] + $statsHariIni['alpha'] }}</div>
                    <span class="text-slate-400" style="font-size: 11px;">siswa</span>
                </div>
                <div class="mt-1 text-slate-400" style="font-size: 11px;">
                    Sakit, Izin, atau Tanpa Keterangan
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="cruip-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="mosaic-icon-box" style="width: 36px; height: 36px; background: #faf5ff; color: #7c3aed; border-radius: 10px;">
                        <i class="fa-solid fa-award" style="font-size: 13.5px;"></i>
                    </div>
                    <span class="badge rounded-pill fw-semibold px-2 py-0.5" style="background: #faf5ff; color: #6d28d9; font-size: 10.5px;">
                        {{ $totalMapel }} Mapel
                    </span>
                </div>
                <div class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Rata-Rata Nilai Global</div>
                <div class="d-flex align-items-baseline gap-1.5 mt-1">
                    <div class="fw-bold text-slate-800" style="font-size: 1.45rem; line-height: 1.2;">{{ $avgNilaiGlobal ? number_format($avgNilaiGlobal, 1) : '-' }}</div>
                    <span class="text-slate-400" style="font-size: 11px;">poin KKM</span>
                </div>
                <div class="mt-1 text-slate-400" style="font-size: 11px;">
                    Dari {{ $totalKegiatan }} kegiatan penilaian terdata
                </div>
            </div>
        </div>
    @endif
</div>

@if(Auth::check() && Auth::user()->role === 'admin')
    <!-- Cruip Mosaic 2-Column Analytics Section -->
    <div class="row g-3 mb-3.5">
        <!-- Chart 1: Bar Chart Kehadiran Siswa Per Rombel Kelas -->
        <div class="col-lg-8">
            <div class="cruip-card p-3 p-md-3.5 h-100">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2.5 pb-2 border-bottom border-slate-100">
                    <div>
                        <div class="fw-bold text-slate-800" style="font-size: 13.5px;">
                            <i class="fa-solid fa-chart-column me-1.5" style="color: #6366f1;"></i> Grafik Kehadiran Siswa Per Rombel Kelas
                        </div>
                        <span class="text-slate-400" style="font-size: 11px;">Perbandingan siswa Hadir vs Tidak Hadir hari ini ({{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }})</span>
                    </div>
                    <div class="d-flex align-items-center gap-1.5">
                        <span class="badge px-2 py-1 rounded-pill" style="background: #eef2ff; color: #4338ca; font-size: 10.5px;">
                            <span class="d-inline-block rounded-circle me-1" style="width: 7px; height: 7px; background: #6366f1;"></span> Hadir
                        </span>
                        <span class="badge px-2 py-1 rounded-pill" style="background: #fff1f2; color: #e11d48; font-size: 10.5px;">
                            <span class="d-inline-block rounded-circle me-1" style="width: 7px; height: 7px; background: #f43f5e;"></span> Tidak Hadir
                        </span>
                    </div>
                </div>

                @if(empty($dataKelas))
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-chart-column fa-2x mb-2 text-slate-400 opacity-50"></i>
                        <p class="mb-0 small">Belum ada data rombel kelas atau siswa.</p>
                    </div>
                @else
                    <div style="position: relative; height: 240px; width: 100%;">
                        <canvas id="cruipBarChart"></canvas>
                    </div>
                @endif
            </div>
        </div>

        <!-- Chart 2: Donut Chart Distribusi Status Absensi Hari Ini -->
        <div class="col-lg-4">
            <div class="cruip-card p-3 p-md-3.5 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2 border-bottom border-slate-100">
                        <div>
                            <div class="fw-bold text-slate-800" style="font-size: 13.5px;">
                                <i class="fa-solid fa-chart-pie me-1.5" style="color: #6366f1;"></i> Komposisi Status Hari Ini
                            </div>
                            <span class="text-slate-400" style="font-size: 11px;">Distribusi presensi siswa</span>
                        </div>
                        <span class="badge rounded-pill px-2 py-0.5" style="background: #f1f5f9; color: #475569; font-size: 10.5px;">
                            {{ $statsHariIni['total'] }} Record
                        </span>
                    </div>

                    @if($statsHariIni['total'] == 0)
                        <div class="text-center py-4 text-muted">
                            <i class="fa-solid fa-chart-pie fa-2x mb-2 text-slate-400 opacity-50"></i>
                            <p class="text-slate-500 mb-2" style="font-size: 11.5px;">Belum ada absensi yang dicatat pada hari ini.</p>
                            <a href="{{ route('absensi.harian') }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Mulai Absen Sekarang
                            </a>
                        </div>
                    @else
                        <div style="position: relative; height: 160px; width: 100%;" class="mb-2.5">
                            <canvas id="cruipDonutChart"></canvas>
                        </div>

                        <div class="row g-1.5">
                            <div class="col-6">
                                <div class="p-1.5 rounded-3 border border-slate-100 bg-slate-50">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-slate-600" style="font-size: 10.5px;"><span class="d-inline-block rounded-circle me-1" style="width: 7px; height: 7px; background: #10b981;"></span> Hadir</span>
                                        <span class="fw-bold text-success" style="font-size: 10.5px;">{{ $statsHariIni['hadir_pct'] }}%</span>
                                    </div>
                                    <div class="fw-bold text-slate-800 mt-0.5" style="font-size: 12.5px;">{{ $statsHariIni['hadir'] }} <span class="text-slate-400 fw-normal" style="font-size: 10px;">siswa</span></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-1.5 rounded-3 border border-slate-100 bg-slate-50">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-slate-600" style="font-size: 10.5px;"><span class="d-inline-block rounded-circle me-1" style="width: 7px; height: 7px; background: #0ea5e9;"></span> Izin</span>
                                        <span class="fw-bold text-info" style="font-size: 10.5px;">{{ $statsHariIni['izin_pct'] }}%</span>
                                    </div>
                                    <div class="fw-bold text-slate-800 mt-0.5" style="font-size: 12.5px;">{{ $statsHariIni['izin'] }} <span class="text-slate-400 fw-normal" style="font-size: 10px;">siswa</span></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-1.5 rounded-3 border border-slate-100 bg-slate-50">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-slate-600" style="font-size: 10.5px;"><span class="d-inline-block rounded-circle me-1" style="width: 7px; height: 7px; background: #f59e0b;"></span> Sakit</span>
                                        <span class="fw-bold text-warning" style="font-size: 10.5px;">{{ $statsHariIni['sakit_pct'] }}%</span>
                                    </div>
                                    <div class="fw-bold text-slate-800 mt-0.5" style="font-size: 12.5px;">{{ $statsHariIni['sakit'] }} <span class="text-slate-400 fw-normal" style="font-size: 10px;">siswa</span></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-1.5 rounded-3 border border-slate-100 bg-slate-50">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-slate-600" style="font-size: 10.5px;"><span class="d-inline-block rounded-circle me-1" style="width: 7px; height: 7px; background: #f43f5e;"></span> Alpa</span>
                                        <span class="fw-bold text-danger" style="font-size: 10.5px;">{{ $statsHariIni['alpha_pct'] }}%</span>
                                    </div>
                                    <div class="fw-bold text-slate-800 mt-0.5" style="font-size: 12.5px;">{{ $statsHariIni['alpha'] }} <span class="text-slate-400 fw-normal" style="font-size: 10px;">siswa</span></div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@if(empty($isGuru) || !$isGuru)
    <!-- Cruip Mosaic Class Attendance Table -->
    <div class="cruip-card p-3 p-md-3.5 mb-3.5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2.5 pb-2 border-bottom border-slate-100 gap-2">
            <div>
                <div class="fw-bold text-slate-800" style="font-size: 13.5px;">
                    <i class="fa-solid fa-list-check me-1.5" style="color: #6366f1;"></i> Monitoring Kehadiran Rombongan Belajar
                </div>
                <span class="text-slate-400" style="font-size: 11px;">Presensi siswa per rombel pada tanggal {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</span>
            </div>
            <a href="{{ route('dashboard.cetakPdf') }}" class="btn btn-outline-danger btn-sm px-2.5 py-1 rounded-pill shadow-sm" style="font-size: 11.5px;">
                <i class="fa-solid fa-file-pdf me-1"></i> Cetak Rekap Kehadiran PDF
            </a>
        </div>

        @if(empty($dataKelas))
            <div class="text-center py-4 text-muted">
                <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                <small>Belum ada data siswa atau kelas yang terdaftar. <a href="{{ route('siswa.index') }}">Tambah Data Siswa</a></small>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                    <thead class="bg-slate-50">
                        <tr class="text-slate-500 text-uppercase" style="font-size: 10.5px; letter-spacing: 0.5px;">
                            <th class="ps-3 py-2 border-0">Kelas</th>
                            <th class="py-2 border-0">Total Siswa</th>
                            <th class="py-2 border-0">Hadir</th>
                            <th class="py-2 border-0">Tidak Hadir (S/I/A)</th>
                            <th class="py-2 border-0">Persentase Kehadiran</th>
                            <th class="text-end pe-3 py-2 border-0">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dataKelas as $item)
                            @php
                                $persen = $item['total'] > 0 ? round(($item['hadir'] / $item['total']) * 100) : 0;
                            @endphp
                            <tr>
                                <td class="ps-3 py-2">
                                    <span class="badge px-2.5 py-1 rounded-2 fw-bold" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 11.5px;">
                                        {{ $item['kelas'] }}
                                    </span>
                                </td>
                                <td class="py-2 fw-medium text-slate-700">{{ $item['total'] }} Siswa</td>
                                <td class="py-2"><span class="badge px-2 py-0.5 rounded-pill" style="background: #ecfdf5; color: #047857; font-size: 11px;"><i class="fa-solid fa-check me-1"></i> {{ $item['hadir'] }}</span></td>
                                <td class="py-2">
                                    @if($item['tidak_hadir'] > 0)
                                        <span class="badge px-2 py-0.5 rounded-pill" style="background: #fff1f2; color: #be123c; font-size: 11px;"><i class="fa-solid fa-xmark me-1"></i> {{ $item['tidak_hadir'] }}</span>
                                    @else
                                        <span class="text-slate-400 small">0</span>
                                    @endif
                                </td>
                                <td class="py-2" style="width: 25%;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; background: #f1f5f9; border-radius: 999px;">
                                            <div class="progress-bar rounded-pill {{ $persen >= 85 ? 'bg-success' : ($persen >= 70 ? 'bg-warning' : 'bg-danger') }}" 
                                                 style="width: {{ $persen }}%;"></div>
                                        </div>
                                        <span class="fw-bold text-slate-700" style="min-width: 32px; font-size: 11.5px;">{{ $persen }}%</span>
                                    </div>
                                </td>
                                <td class="text-end pe-3 py-2">
                                    <a href="{{ route('absensi.harian', ['kelas' => $item['kelas'], 'tanggal' => $tanggal]) }}" 
                                       class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Absen
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Cruip Mosaic Periodical Attendance Analytics -->
    <div class="cruip-card p-3 p-md-3.5 shadow-sm mb-3.5">
        <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom border-slate-100 flex-wrap gap-2">
            <div>
                <div class="fw-bold text-slate-800" style="font-size: 13.5px;">
                    <i class="fa-solid fa-chart-pie me-1.5" style="color: #6366f1;"></i> Rekapitulasi Persentase Kehadiran Periodik
                </div>
                <span class="text-slate-400" style="font-size: 11px;">Monitoring tren presensi siswa berdasarkan rentang waktu</span>
            </div>
            <span class="badge rounded-pill px-2.5 py-1 text-slate-600" style="background: #f1f5f9; font-size: 10.5px;">
                <i class="fa-solid fa-circle-info me-1 text-info"></i> Rekapitulasi Otomatis SI-KASEP
            </span>
        </div>

        <div class="row g-2.5">
            <!-- Card 1: Hari Ini -->
            <div class="col-sm-6 col-xl-3">
                <div class="p-2.5 rounded-3 border border-slate-100 bg-slate-50 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-warning" style="font-size: 12px;">
                            <i class="fa-solid fa-calendar-day me-1"></i> Hari Ini
                        </span>
                        <span class="badge bg-warning-subtle text-warning fw-semibold px-2 py-0.5 rounded-pill" style="font-size: 10px;">
                            {{ $statsHariIni['total'] }} Record
                        </span>
                    </div>
                    
                    <div class="mb-1.5">
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-success"><i class="fa-solid fa-circle-check me-1"></i> Hadir</span>
                            <span class="fw-bold text-success">{{ $statsHariIni['hadir_pct'] }}% <small class="text-muted">({{ $statsHariIni['hadir'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-success rounded-pill" style="width: {{ $statsHariIni['hadir_pct'] }}%"></div>
                        </div>
                    </div>

                    <div class="mb-1.5">
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-info"><i class="fa-solid fa-envelope-open-text me-1"></i> Izin</span>
                            <span class="fw-bold text-info">{{ $statsHariIni['izin_pct'] }}% <small class="text-muted">({{ $statsHariIni['izin'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-info rounded-pill" style="width: {{ $statsHariIni['izin_pct'] }}%"></div>
                        </div>
                    </div>

                    <div class="mb-1.5">
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-warning"><i class="fa-solid fa-notes-medical me-1"></i> Sakit</span>
                            <span class="fw-bold text-warning">{{ $statsHariIni['sakit_pct'] }}% <small class="text-muted">({{ $statsHariIni['sakit'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-warning rounded-pill" style="width: {{ $statsHariIni['sakit_pct'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Alpa</span>
                            <span class="fw-bold text-danger">{{ $statsHariIni['alpha_pct'] }}% <small class="text-muted">({{ $statsHariIni['alpha'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-danger rounded-pill" style="width: {{ $statsHariIni['alpha_pct'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Minggu Ini -->
            <div class="col-sm-6 col-xl-3">
                <div class="p-2.5 rounded-3 border border-slate-100 bg-slate-50 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold" style="color: #4f46e5; font-size: 12px;">
                            <i class="fa-solid fa-calendar-week me-1"></i> Minggu Ini
                        </span>
                        <span class="badge rounded-pill fw-semibold px-2 py-0.5" style="background: #eef2ff; color: #4338ca; font-size: 10px;">
                            {{ $statsMingguIni['total'] }} Record
                        </span>
                    </div>
                    
                    <div class="mb-1.5">
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-success"><i class="fa-solid fa-circle-check me-1"></i> Hadir</span>
                            <span class="fw-bold text-success">{{ $statsMingguIni['hadir_pct'] }}% <small class="text-muted">({{ $statsMingguIni['hadir'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-success rounded-pill" style="width: {{ $statsMingguIni['hadir_pct'] }}%"></div>
                        </div>
                    </div>

                    <div class="mb-1.5">
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-info"><i class="fa-solid fa-envelope-open-text me-1"></i> Izin</span>
                            <span class="fw-bold text-info">{{ $statsMingguIni['izin_pct'] }}% <small class="text-muted">({{ $statsMingguIni['izin'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-info rounded-pill" style="width: {{ $statsMingguIni['izin_pct'] }}%"></div>
                        </div>
                    </div>

                    <div class="mb-1.5">
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-warning"><i class="fa-solid fa-notes-medical me-1"></i> Sakit</span>
                            <span class="fw-bold text-warning">{{ $statsMingguIni['sakit_pct'] }}% <small class="text-muted">({{ $statsMingguIni['sakit'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-warning rounded-pill" style="width: {{ $statsMingguIni['sakit_pct'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Alpa</span>
                            <span class="fw-bold text-danger">{{ $statsMingguIni['alpha_pct'] }}% <small class="text-muted">({{ $statsMingguIni['alpha'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-danger rounded-pill" style="width: {{ $statsMingguIni['alpha_pct'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Bulan Ini -->
            <div class="col-sm-6 col-xl-3">
                <div class="p-2.5 rounded-3 border border-slate-100 bg-slate-50 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-info" style="font-size: 12px;">
                            <i class="fa-solid fa-calendar-days me-1"></i> Bulan Ini
                        </span>
                        <span class="badge bg-info-subtle text-info fw-semibold px-2 py-0.5 rounded-pill" style="font-size: 10px;">
                            {{ $statsBulanIni['total'] }} Record
                        </span>
                    </div>
                    
                    <div class="mb-1.5">
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-success"><i class="fa-solid fa-circle-check me-1"></i> Hadir</span>
                            <span class="fw-bold text-success">{{ $statsBulanIni['hadir_pct'] }}% <small class="text-muted">({{ $statsBulanIni['hadir'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-success rounded-pill" style="width: {{ $statsBulanIni['hadir_pct'] }}%"></div>
                        </div>
                    </div>

                    <div class="mb-1.5">
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-info"><i class="fa-solid fa-envelope-open-text me-1"></i> Izin</span>
                            <span class="fw-bold text-info">{{ $statsBulanIni['izin_pct'] }}% <small class="text-muted">({{ $statsBulanIni['izin'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-info rounded-pill" style="width: {{ $statsBulanIni['izin_pct'] }}%"></div>
                        </div>
                    </div>

                    <div class="mb-1.5">
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-warning"><i class="fa-solid fa-notes-medical me-1"></i> Sakit</span>
                            <span class="fw-bold text-warning">{{ $statsBulanIni['sakit_pct'] }}% <small class="text-muted">({{ $statsBulanIni['sakit'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-warning rounded-pill" style="width: {{ $statsBulanIni['sakit_pct'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Alpa</span>
                            <span class="fw-bold text-danger">{{ $statsBulanIni['alpha_pct'] }}% <small class="text-muted">({{ $statsBulanIni['alpha'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-danger rounded-pill" style="width: {{ $statsBulanIni['alpha_pct'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Keseluruhan -->
            <div class="col-sm-6 col-xl-3">
                <div class="p-2.5 rounded-3 border border-slate-100 bg-slate-50 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-success" style="font-size: 12px;">
                            <i class="fa-solid fa-globe me-1"></i> Keseluruhan
                        </span>
                        <span class="badge bg-success-subtle text-success fw-semibold px-2 py-0.5 rounded-pill" style="font-size: 10px;">
                            {{ $statsKeseluruhan['total'] }} Record
                        </span>
                    </div>
                    
                    <div class="mb-1.5">
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-success"><i class="fa-solid fa-circle-check me-1"></i> Hadir</span>
                            <span class="fw-bold text-success">{{ $statsKeseluruhan['hadir_pct'] }}% <small class="text-muted">({{ $statsKeseluruhan['hadir'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-success rounded-pill" style="width: {{ $statsKeseluruhan['hadir_pct'] }}%"></div>
                        </div>
                    </div>

                    <div class="mb-1.5">
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-info"><i class="fa-solid fa-envelope-open-text me-1"></i> Izin</span>
                            <span class="fw-bold text-info">{{ $statsKeseluruhan['izin_pct'] }}% <small class="text-muted">({{ $statsKeseluruhan['izin'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-info rounded-pill" style="width: {{ $statsKeseluruhan['izin_pct'] }}%"></div>
                        </div>
                    </div>

                    <div class="mb-1.5">
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-warning"><i class="fa-solid fa-notes-medical me-1"></i> Sakit</span>
                            <span class="fw-bold text-warning">{{ $statsKeseluruhan['sakit_pct'] }}% <small class="text-muted">({{ $statsKeseluruhan['sakit'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-warning rounded-pill" style="width: {{ $statsKeseluruhan['sakit_pct'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-0.5" style="font-size: 11px;">
                            <span class="fw-semibold text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Alpa</span>
                            <span class="fw-bold text-danger">{{ $statsKeseluruhan['alpha_pct'] }}% <small class="text-muted">({{ $statsKeseluruhan['alpha'] }})</small></span>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 999px;">
                            <div class="progress-bar bg-danger rounded-pill" style="width: {{ $statsKeseluruhan['alpha_pct'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matriks Ringkasan Tabel -->
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-sm align-middle text-center mb-0 bg-white" style="border-color: #e2e8f0; font-size: 12px;">
                <thead class="bg-slate-50">
                    <tr class="text-slate-600" style="font-size: 11px;">
                        <th class="text-start ps-3 py-1.5">Kategori Status</th>
                        <th class="py-1.5"><i class="fa-solid fa-calendar-day text-warning me-1"></i> Hari Ini</th>
                        <th class="py-1.5"><i class="fa-solid fa-calendar-week text-primary me-1"></i> Minggu Ini</th>
                        <th class="py-1.5"><i class="fa-solid fa-calendar-days text-info me-1"></i> Bulan Ini</th>
                        <th class="py-1.5"><i class="fa-solid fa-globe text-success me-1"></i> Keseluruhan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-start ps-3 py-1.5 fw-semibold text-success"><i class="fa-solid fa-circle-check me-2"></i> Hadir</td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 11px;">{{ $statsHariIni['hadir_pct'] }}%</span></td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 11px;">{{ $statsMingguIni['hadir_pct'] }}%</span></td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 11px;">{{ $statsBulanIni['hadir_pct'] }}%</span></td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 11px;">{{ $statsKeseluruhan['hadir_pct'] }}%</span></td>
                    </tr>
                    <tr>
                        <td class="text-start ps-3 py-1.5 fw-semibold text-info"><i class="fa-solid fa-envelope-open-text me-2"></i> Izin</td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; font-size: 11px;">{{ $statsHariIni['izin_pct'] }}%</span></td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; font-size: 11px;">{{ $statsMingguIni['izin_pct'] }}%</span></td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; font-size: 11px;">{{ $statsBulanIni['izin_pct'] }}%</span></td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; font-size: 11px;">{{ $statsKeseluruhan['izin_pct'] }}%</span></td>
                    </tr>
                    <tr>
                        <td class="text-start ps-3 py-1.5 fw-semibold text-warning"><i class="fa-solid fa-notes-medical me-2"></i> Sakit</td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 11px;">{{ $statsHariIni['sakit_pct'] }}%</span></td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 11px;">{{ $statsMingguIni['sakit_pct'] }}%</span></td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 11px;">{{ $statsBulanIni['sakit_pct'] }}%</span></td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 11px;">{{ $statsKeseluruhan['sakit_pct'] }}%</span></td>
                    </tr>
                    <tr>
                        <td class="text-start ps-3 py-1.5 fw-semibold text-danger"><i class="fa-solid fa-circle-xmark me-2"></i> Alpa</td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; font-size: 11px;">{{ $statsHariIni['alpha_pct'] }}%</span></td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; font-size: 11px;">{{ $statsMingguIni['alpha_pct'] }}%</span></td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; font-size: 11px;">{{ $statsBulanIni['alpha_pct'] }}%</span></td>
                        <td class="py-1.5"><span class="badge rounded-pill px-2.5 py-0.5" style="background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; font-size: 11px;">{{ $statsKeseluruhan['alpha_pct'] }}%</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if(Auth::check() && Auth::user()->role === 'admin')
        // 1. Cruip Mosaic Bar Chart (Kehadiran Per Rombel Kelas)
        const barCanvas = document.getElementById('cruipBarChart');
        if (barCanvas) {
            const kelasLabels = {!! json_encode($kelasLabels) !!};
            const hadirData = {!! json_encode($hadirData) !!};
            const tidakHadirData = {!! json_encode($tidakHadirData) !!};

            new Chart(barCanvas, {
                type: 'bar',
                data: {
                    labels: kelasLabels,
                    datasets: [
                        {
                            label: 'Hadir',
                            data: hadirData,
                            backgroundColor: '#6366f1',
                            hoverBackgroundColor: '#4f46e5',
                            borderRadius: 6,
                            barPercentage: 0.65,
                            categoryPercentage: 0.7
                        },
                        {
                            label: 'Tidak Hadir',
                            data: tidakHadirData,
                            backgroundColor: '#f43f5e',
                            hoverBackgroundColor: '#e11d48',
                            borderRadius: 6,
                            barPercentage: 0.65,
                            categoryPercentage: 0.7
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#f8fafc',
                            bodyColor: '#f1f5f9',
                            padding: 10,
                            cornerRadius: 8,
                            usePointStyle: true,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ' + context.raw + ' siswa';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 11,
                                    weight: '500'
                                },
                                color: '#64748b'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            },
                            ticks: {
                                precision: 0,
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 11
                                },
                                color: '#94a3b8'
                            }
                        }
                    }
                }
            });
        }

        // 2. Cruip Mosaic Donut Chart (Komposisi Status Absensi Hari Ini)
        const donutCanvas = document.getElementById('cruipDonutChart');
        if (donutCanvas) {
            const stats = {
                hadir: {{ $statsHariIni['hadir'] }},
                izin: {{ $statsHariIni['izin'] }},
                sakit: {{ $statsHariIni['sakit'] }},
                alpha: {{ $statsHariIni['alpha'] }}
            };
            const total = stats.hadir + stats.izin + stats.sakit + stats.alpha;

            if (total > 0) {
                new Chart(donutCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Hadir', 'Izin', 'Sakit', 'Alpa'],
                        datasets: [{
                            data: [stats.hadir, stats.izin, stats.sakit, stats.alpha],
                            backgroundColor: ['#10b981', '#0ea5e9', '#f59e0b', '#f43f5e'],
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleColor: '#f8fafc',
                                bodyColor: '#f1f5f9',
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        const val = context.raw;
                                        const pct = ((val / total) * 100).toFixed(1);
                                        return ` ${context.label}: ${val} siswa (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    @endif
});
</script>
@endpush
