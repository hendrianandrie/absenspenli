@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Hero / Welcome Banner -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" 
         style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 45%, #4338ca 100%); color: #ffffff;">
        <div class="card-body p-4 p-md-5 position-relative">
            <!-- Decorative Glow Background -->
            <div style="position: absolute; right: -40px; top: -40px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(99,102,241,0.35) 0%, rgba(99,102,241,0) 70%); border-radius: 50%; pointer-events: none;"></div>

            <div class="row align-items-center g-4 position-relative">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="badge rounded-pill px-3 py-1-5 text-uppercase fw-semibold" 
                              style="background: rgba(255,255,255,0.15); backdrop-filter: blur(8px); font-size: 11px; letter-spacing: 0.05em;">
                            <i class="fa-solid fa-graduation-cap me-1"></i> Portal Siswa SPENLI
                        </span>
                        <span class="text-white-50 small d-none d-sm-inline">&bull;</span>
                        <span class="text-white-50 small d-none d-sm-inline">Tahun Ajaran {{ date('Y') }}/{{ date('Y')+1 }}</span>
                    </div>

                    <h2 class="fw-bold mb-2 text-white display-6" style="font-size: calc(1.375rem + 1vw);">
                        Halo, <span style="color: #a5b4fc;">{{ $siswa->nama }}</span>! 👋
                    </h2>
                    <p class="text-white-50 mb-4 small" style="max-width: 600px; line-height: 1.6;">
                        Selamat datang di portal akademik siswa SMP Negeri 5 Ciamis. Pantau seluruh aktivitas penilaian tugas harian, ulangan, dan riwayat presensi kehadiranmu secara transparan dan berkala.
                    </p>

                    <!-- Student Metadata Badges -->
                    <div class="d-flex flex-wrap gap-2 pt-1">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(6px);">
                            <i class="fa-solid fa-id-card text-indigo-300" style="color: #c7d2fe;"></i>
                            <span class="small font-monospace">NIS: <strong class="text-white">{{ $siswa->nis }}</strong></span>
                        </div>
                        @if($siswa->nisn)
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(6px);">
                            <i class="fa-solid fa-fingerprint" style="color: #c7d2fe;"></i>
                            <span class="small font-monospace">NISN: <strong class="text-white">{{ $siswa->nisn }}</strong></span>
                        </div>
                        @endif
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(6px);">
                            <i class="fa-solid fa-chalkboard-user" style="color: #c7d2fe;"></i>
                            <span class="small">Kelas: <strong class="text-white">{{ $siswa->kelas }}</strong></span>
                        </div>
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(6px);">
                            <i class="fa-solid fa-user-tie" style="color: #fcd34d;"></i>
                            <span class="small">Wali Kelas: <strong class="text-white">{{ optional(optional($waliKelas)->user)->name ?? 'Belum Ditentukan' }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 text-lg-end d-none d-lg-block">
                    <div class="d-inline-flex flex-column align-items-center p-4 rounded-4" 
                         style="background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15);">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold mb-3 shadow"
                             style="width: 80px; height: 80px; background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); font-size: 32px; border: 3px solid rgba(255,255,255,0.3);">
                            {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                        </div>
                        <div class="fw-bold text-white fs-6 text-center text-truncate" style="max-width: 200px;">
                            {{ $siswa->nama }}
                        </div>
                        <span class="badge rounded-pill mt-1" style="background: #38bdf8; color: #082f49; font-weight: 700; font-size: 11px;">
                            Siswa Aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation & Highlights Grid -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Kehadiran Bulan Ini -->
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge rounded-pill px-3 py-1-5" style="background: #e0e7ff; color: #3730a3; font-weight: 600; font-size: 11px;">
                                <i class="fa-solid fa-calendar-check me-1"></i> Kehadiran {{ $statsAbsen['bulan'] }}
                            </span>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px; background: #e0f2fe; color: #0284c7;">
                                <i class="fa-solid fa-user-check fs-5"></i>
                            </div>
                        </div>

                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <h3 class="fw-bold text-slate-800 mb-0 display-6" style="font-size: 2rem;">
                                {{ $statsAbsen['hadir_pct'] }}%
                            </h3>
                            <span class="text-slate-500 small">Tingkat Hadir</span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="progress mb-3" style="height: 8px; border-radius: 999px; background: #e2e8f0;">
                            <div class="progress-bar rounded-pill" role="progressbar" 
                                 style="width: {{ $statsAbsen['hadir_pct'] }}%; background: linear-gradient(90deg, #10b981 0%, #059669 100%);" 
                                 aria-valuenow="{{ $statsAbsen['hadir_pct'] }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>

                        <!-- Mini Counters -->
                        <div class="row g-2 text-center pt-2">
                            <div class="col-3">
                                <div class="p-2 rounded-3 bg-slate-50 border border-slate-100">
                                    <div class="fw-bold text-success fs-6">{{ $statsAbsen['hadir'] }}</div>
                                    <div class="text-slate-400" style="font-size: 10px; font-weight: 600;">Hadir</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-2 rounded-3 bg-slate-50 border border-slate-100">
                                    <div class="fw-bold text-warning fs-6">{{ $statsAbsen['sakit'] }}</div>
                                    <div class="text-slate-400" style="font-size: 10px; font-weight: 600;">Sakit</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-2 rounded-3 bg-slate-50 border border-slate-100">
                                    <div class="fw-bold text-primary fs-6">{{ $statsAbsen['izin'] }}</div>
                                    <div class="text-slate-400" style="font-size: 10px; font-weight: 600;">Izin</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-2 rounded-3 bg-slate-50 border border-slate-100">
                                    <div class="fw-bold text-danger fs-6">{{ $statsAbsen['alpha'] }}</div>
                                    <div class="text-slate-400" style="font-size: 10px; font-weight: 600;">Alpa</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 mt-2 border-top">
                        <a href="{{ route('siswa.absen') }}" class="btn btn-outline-primary btn-sm w-100 rounded-3 fw-semibold py-2">
                            Buka Rekap Absen Lengkap <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Penilaian & Tugas -->
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge rounded-pill px-3 py-1-5" style="background: #fef3c7; color: #92400e; font-weight: 600; font-size: 11px;">
                                <i class="fa-solid fa-list-check me-1"></i> Aktivitas Penilaian
                            </span>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px; background: #fef3c7; color: #d97706;">
                                <i class="fa-solid fa-star fs-5"></i>
                            </div>
                        </div>

                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <h3 class="fw-bold text-slate-800 mb-0 display-6" style="font-size: 2rem;">
                                {{ $totalDinilai }}
                            </h3>
                            <span class="text-slate-500 small">dari {{ $totalKegiatan }} Kegiatan Dinilai</span>
                        </div>

                        @php
                            $nilaiPct = $totalKegiatan > 0 ? round(($totalDinilai / $totalKegiatan) * 100, 1) : 0;
                        @endphp
                        <div class="progress mb-3" style="height: 8px; border-radius: 999px; background: #e2e8f0;">
                            <div class="progress-bar rounded-pill" role="progressbar" 
                                 style="width: {{ $nilaiPct }}%; background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);" 
                                 aria-valuenow="{{ $nilaiPct }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>

                        <div class="p-3 rounded-3 bg-slate-50 border border-slate-100 mt-2">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-slate-600 small">Kegiatan Diikuti:</span>
                                <span class="fw-bold text-slate-800 small">{{ $totalDinilai }} / {{ $totalKegiatan }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-slate-600 small">Kelengkapan Nilai:</span>
                                <span class="badge {{ $nilaiPct >= 80 ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill" style="font-size: 11px;">
                                    {{ $nilaiPct }}% Lengkap
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 mt-2 border-top">
                        <a href="{{ route('siswa.nilai') }}" class="btn btn-outline-warning btn-sm w-100 rounded-3 fw-semibold py-2 text-dark" style="border-color: #f59e0b;">
                            Lihat Rincian Nilai Per Mapel <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Informasi Akun & Bantuan -->
        <div class="col-md-12 col-xl-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge rounded-pill px-3 py-1-5" style="background: #ecfdf5; color: #065f46; font-weight: 600; font-size: 11px;">
                                <i class="fa-solid fa-circle-info me-1"></i> Petunjuk Siswa
                            </span>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px; background: #ecfdf5; color: #059669;">
                                <i class="fa-solid fa-shield-halved fs-5"></i>
                            </div>
                        </div>

                        <h6 class="fw-bold text-slate-800 mb-2">Login Mandiri Terproteksi</h6>
                        <p class="text-slate-500 small mb-3" style="line-height: 1.6;">
                            Akun Anda terkunci secara otomatis menggunakan <strong>NIS (Nomor Induk Siswa)</strong> untuk Username dan Password. Jika ada ketidaksesuaian data nilai atau absensi, harap segera menghubungi Wali Kelas atau Guru Pengampu.
                        </p>

                        <div class="p-3 rounded-3 bg-light border border-slate-100">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-headset text-primary"></i>
                                <span class="fw-semibold text-slate-700 small">Kontak Wali Kelas:</span>
                            </div>
                            <div class="text-slate-600 small ps-4">
                                {{ optional(optional($waliKelas)->user)->name ?? 'Belum Ditentukan' }}
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 mt-2 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('siswa.nilai') }}" class="btn btn-primary btn-sm flex-fill rounded-3 fw-semibold py-2">
                                <i class="fa-solid fa-star me-1"></i> Rekap Nilai
                            </a>
                            <a href="{{ route('siswa.absen') }}" class="btn btn-light btn-sm flex-fill rounded-3 fw-semibold py-2 border">
                                <i class="fa-solid fa-calendar me-1"></i> Rekap Absen
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table: 5 Penilaian Terbaru yang Sudah Dinilai -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-slate-800 mb-0 fs-6">
                    <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Penilaian Terbaru Diikuti
                </h5>
                <small class="text-slate-400">Daftar 5 aktivitas tugas / ulangan terakhir yang telah dinilai guru</small>
            </div>
            <a href="{{ route('siswa.nilai') }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-medium">
                Lihat Semua Nilai <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 text-slate-500 font-semibold" style="font-size: 12px; width: 60px;">NO</th>
                        <th class="text-slate-500 font-semibold" style="font-size: 12px;">TANGGAL</th>
                        <th class="text-slate-500 font-semibold" style="font-size: 12px;">MATA PELAJARAN</th>
                        <th class="text-slate-500 font-semibold" style="font-size: 12px;">KEGIATAN PENILAIAN</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 12px;">JENIS</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 12px;">NILAI</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 12px;">KKM</th>
                        <th class="pe-4 text-slate-500 font-semibold text-center" style="font-size: 12px;">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nilaiTerbaru as $index => $item)
                        <tr>
                            <td class="ps-4 fw-semibold text-slate-400">{{ $index + 1 }}</td>
                            <td class="text-slate-600 font-monospace small">{{ $item['tanggal'] }}</td>
                            <td>
                                <strong class="text-slate-800">{{ $item['mapel'] }}</strong>
                            </td>
                            <td>
                                <span class="text-slate-700">{{ $item['nama_kegiatan'] }}</span>
                            </td>
                            <td class="text-center">
                                @if($item['jenis'] === 'Tugas')
                                    <span class="badge rounded-pill" style="background: #e0f2fe; color: #0369a1; font-size: 11px;">Tugas</span>
                                @elseif($item['jenis'] === 'UH')
                                    <span class="badge rounded-pill" style="background: #fef3c7; color: #92400e; font-size: 11px;">Ulangan Harian</span>
                                @elseif($item['jenis'] === 'UTS')
                                    <span class="badge rounded-pill" style="background: #ede9fe; color: #6d28d9; font-size: 11px;">UTS</span>
                                @else
                                    <span class="badge rounded-pill" style="background: #fce7f3; color: #9d174d; font-size: 11px;">{{ $item['jenis'] }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="fw-bold fs-6 {{ $item['is_tuntas'] ? 'text-success' : 'text-danger' }}">
                                    {{ $item['nilai'] }}
                                </span>
                            </td>
                            <td class="text-center text-slate-500 font-monospace small">
                                {{ $item['kkm'] }}
                            </td>
                            <td class="pe-4 text-center">
                                @if($item['is_tuntas'])
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 11px;">
                                        <i class="fa-solid fa-check me-1"></i> Tuntas
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 11px;">
                                        <i class="fa-solid fa-rotate-right me-1"></i> Remedial
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-slate-400">
                                <div class="py-3">
                                    <i class="fa-regular fa-clipboard fs-1 mb-2 text-slate-300"></i>
                                    <p class="mb-0">Belum ada data kegiatan penilaian yang diinput untuk kelasmu.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
