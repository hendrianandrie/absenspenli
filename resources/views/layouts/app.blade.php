<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI-KASEP - Sistem Informasi Rekap Absen dan Penilaian SMP Negeri 5 Ciamis</title>
    <!-- Favicon Logo -->
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Font: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --mosaic-sidebar-width: 260px;
            --mosaic-header-height: 64px;
            --mosaic-sidebar-bg: #0f172a;
            --mosaic-sidebar-surface: #1e293b;
            --mosaic-sidebar-hover: rgba(255, 255, 255, 0.07);
            --mosaic-primary: #6366f1;
            --mosaic-primary-hover: #4f46e5;
            --mosaic-text-muted: #94a3b8;
            --mosaic-border: #e2e8f0;
            --mosaic-bg: #f8fafc;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--mosaic-bg);
            color: #334155;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        /* App Layout Container */
        .mosaic-app-layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
            position: relative;
        }

        /* Sidebar Styling (Cruip Mosaic Style) */
        .mosaic-sidebar {
            width: var(--mosaic-sidebar-width);
            background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 60%, #0f172a 100%);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1045;
            transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
        }

        .mosaic-sidebar-brand {
            height: var(--mosaic-header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.15);
        }

        .mosaic-brand-link {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #ffffff;
        }

        .mosaic-brand-link:hover {
            color: #ffffff;
        }

        .mosaic-sidebar-nav {
            flex-grow: 1;
            overflow-y: auto;
            padding: 16px 12px;
        }

        .mosaic-sidebar-nav::-webkit-scrollbar {
            width: 5px;
        }
        .mosaic-sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }

        .mosaic-nav-section {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            padding: 16px 12px 6px;
            user-select: none;
        }

        .mosaic-nav-item {
            margin-bottom: 3px;
        }

        .mosaic-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: var(--mosaic-text-muted);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.18s ease;
        }

        .mosaic-nav-link i {
            font-size: 15px;
            width: 20px;
            text-align: center;
            color: #64748b;
            transition: color 0.18s ease;
        }

        .mosaic-nav-link:hover {
            color: #ffffff;
            background-color: var(--mosaic-sidebar-hover);
        }

        .mosaic-nav-link:hover i {
            color: #818cf8;
        }

        .mosaic-nav-link.active {
            color: #ffffff;
            background: linear-gradient(90deg, #4f46e5 0%, #6366f1 100%);
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
            font-weight: 600;
        }

        .mosaic-nav-link.active i {
            color: #ffffff;
        }

        .mosaic-sidebar-footer {
            padding: 14px 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.25);
        }

        /* Main Content Wrapper */
        .mosaic-main-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin-left: var(--mosaic-sidebar-width);
            transition: margin-left 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 0;
            background-color: var(--mosaic-bg);
        }

        /* Top Header */
        .mosaic-header {
            height: var(--mosaic-header-height);
            background-color: #ffffff;
            border-bottom: 1px solid var(--mosaic-border);
            position: sticky;
            top: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
        }

        .mosaic-main-content {
            flex-grow: 1;
            padding: 20px 24px;
            font-size: 13.5px;
        }

        @media (min-width: 1200px) {
            .mosaic-main-content {
                padding: 24px 28px 36px;
            }
        }

        /* Harmonious content typography to match sidebar */
        .mosaic-main-content h1, .mosaic-main-content .h1 { font-size: 1.6rem; }
        .mosaic-main-content h2, .mosaic-main-content .h2 { font-size: 1.4rem; }
        .mosaic-main-content h3, .mosaic-main-content .h3 { font-size: 1.25rem; }
        .mosaic-main-content h4, .mosaic-main-content .h4 { font-size: 1.15rem; }
        .mosaic-main-content h5, .mosaic-main-content .h5 { font-size: 1.05rem; }
        .mosaic-main-content h6, .mosaic-main-content .h6 { font-size: 0.92rem; }
        .mosaic-main-content .table { font-size: 13px; }

        /* Footer */
        .mosaic-footer {
            background-color: #ffffff;
            border-top: 1px solid var(--mosaic-border);
            padding: 16px 24px;
            font-size: 13px;
            color: #64748b;
        }

        /* Responsive Mobile Drawer */
        @media (max-width: 991.98px) {
            .mosaic-sidebar {
                transform: translateX(-100%);
            }
            .mosaic-sidebar.show {
                transform: translateX(0);
            }
            .mosaic-main-wrapper {
                margin-left: 0 !important;
            }
            .mosaic-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(15, 23, 42, 0.6);
                backdrop-filter: blur(2px);
                z-index: 1040;
            }
            .mosaic-backdrop.show {
                display: block;
            }
        }

        /* Cruip UI Design Tokens & Utility Classes */
        .cruip-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .cruip-card:hover {
            box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
            transform: translateY(-2px);
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

        /* Backward Compatibility Classes */
        .card-custom {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.07);
        }
        .stat-card {
            border-left: 4px solid #3b82f6;
        }
        .btn-primary-custom {
            background-color: #4f46e5;
            border-color: #4f46e5;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-primary-custom:hover {
            background-color: #4338ca;
        }
        .badge-kkm-pass {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .badge-kkm-fail {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Mobile Backdrop -->
    <div id="sidebarBackdrop" class="mosaic-backdrop"></div>

    <div class="mosaic-app-layout">
        <!-- Cruip Mosaic Sidebar -->
        <aside id="mosaicSidebar" class="mosaic-sidebar">
            <!-- Sidebar Header / Brand -->
            <div class="mosaic-sidebar-brand">
                <a href="{{ Auth::check() && Auth::user()->role === 'siswa' ? route('siswa.dashboard') : route('dashboard') }}" class="mosaic-brand-link">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SPENLI" width="34" height="34" class="rounded-circle bg-white p-1 shadow-sm">
                    <div>
                        <div class="fw-bold fs-6 lh-1">SI-KASEP</div>
                        <small class="text-white-50" style="font-size: 11px;">SMPN 5 Ciamis</small>
                    </div>
                </a>
                <button id="sidebarCloseBtn" class="btn btn-sm text-white-50 d-lg-none p-1" type="button" aria-label="Tutup menu">
                    <i class="fa-solid fa-xmark fs-5"></i>
                </button>
            </div>

            <!-- Sidebar Navigation Links -->
            <div class="mosaic-sidebar-nav">
                @if(Auth::check() && Auth::user()->role === 'siswa')
                    <!-- MENU KHUSUS SISWA -->
                    <div class="mosaic-nav-section">Portal Siswa</div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('siswa.dashboard') ? 'active' : '' }}" href="{{ route('siswa.dashboard') }}">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Dashboard Siswa</span>
                        </a>
                    </div>

                    <div class="mosaic-nav-section">Akademik & Presensi</div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('siswa.nilai') ? 'active' : '' }}" href="{{ route('siswa.nilai') }}">
                            <i class="fa-solid fa-star"></i>
                            <span>Rekap Nilai</span>
                        </a>
                    </div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('siswa.absen') ? 'active' : '' }}" href="{{ route('siswa.absen') }}">
                            <i class="fa-solid fa-calendar-check"></i>
                            <span>Rekap Absen</span>
                        </a>
                    </div>

                @elseif(Auth::check() && Auth::user()->role === 'piket')
                    <!-- MENU PIKET -->
                    <div class="mosaic-nav-section">Menu Utama</div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>

                    <div class="mosaic-nav-section">Presensi Siswa</div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('siswa.*') ? 'active' : '' }}" href="{{ route('siswa.index') }}">
                            <i class="fa-solid fa-users"></i>
                            <span>Data Siswa</span>
                        </a>
                    </div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('absensi.harian') ? 'active' : '' }}" href="{{ route('absensi.harian') }}">
                            <i class="fa-solid fa-calendar-check"></i>
                            <span>Absensi Cepat</span>
                        </a>
                    </div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('absensi.rekap') ? 'active' : '' }}" href="{{ route('absensi.rekap') }}">
                            <i class="fa-solid fa-file-invoice"></i>
                            <span>Rekap Absensi</span>
                        </a>
                    </div>

                @elseif(Auth::check() && Auth::user()->role === 'guru')
                    <!-- MENU GURU MAPEL -->
                    <div class="mosaic-nav-section">Menu Utama</div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fa-solid fa-chalkboard-user"></i>
                            <span>Dashboard Guru</span>
                        </a>
                    </div>

                    <div class="mosaic-nav-section">Akademik & Nilai</div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('nilai.index') ? 'active' : '' }}" href="{{ route('nilai.index') }}">
                            <i class="fa-solid fa-star"></i>
                            <span>Manajemen Nilai</span>
                        </a>
                    </div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('nilai.kegiatan.*') ? 'active' : '' }}" href="{{ route('nilai.kegiatan.create') }}">
                            <i class="fa-solid fa-circle-plus"></i>
                            <span>Input Nilai Baru</span>
                        </a>
                    </div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('mapel.*') ? 'active' : '' }}" href="{{ route('mapel.index') }}">
                            <i class="fa-solid fa-book-open"></i>
                            <span>Pengaturan KKM & Bobot</span>
                        </a>
                    </div>

                    @if(Auth::user()->is_wali_kelas && Auth::user()->waliKelas)
                        @php
                            $myWaliKelas = Auth::user()->waliKelas->first() ?? Auth::user()->waliKelas;
                            $myKelasName = optional($myWaliKelas)->kelas;
                        @endphp
                        <div class="mosaic-nav-section">Tugas Tambahan</div>
                        <div class="mosaic-nav-item">
                            <a class="mosaic-nav-link {{ request()->routeIs('walikelas.myClass') ? 'active' : '' }}" href="{{ route('walikelas.myClass') }}">
                                <i class="fa-solid fa-user-tie" style="color: #f59e0b;"></i>
                                <span>Nilai Kelas {{ $myKelasName }}</span>
                            </a>
                        </div>
                        <div class="mosaic-nav-item">
                            <a class="mosaic-nav-link {{ request()->routeIs('walikelas.kehadiran*') ? 'active' : '' }}" href="{{ route('walikelas.kehadiran') }}">
                                <i class="fa-solid fa-clipboard-user" style="color: #38bdf8;"></i>
                                <span>Rekap Kehadiran</span>
                            </a>
                        </div>
                    @endif

                @else
                    <!-- MENU ADMINISTRATOR -->
                    <div class="mosaic-nav-section">Menu Utama</div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>

                    <div class="mosaic-nav-section">Presensi & Siswa</div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('siswa.*') ? 'active' : '' }}" href="{{ route('siswa.index') }}">
                            <i class="fa-solid fa-users"></i>
                            <span>Data Siswa</span>
                        </a>
                    </div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('absensi.harian') ? 'active' : '' }}" href="{{ route('absensi.harian') }}">
                            <i class="fa-solid fa-calendar-check"></i>
                            <span>Absensi Cepat</span>
                        </a>
                    </div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('absensi.rekap') ? 'active' : '' }}" href="{{ route('absensi.rekap') }}">
                            <i class="fa-solid fa-file-invoice"></i>
                            <span>Rekap Absensi</span>
                        </a>
                    </div>

                    <div class="mosaic-nav-section">Akademik & Kurikulum</div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('nilai.*') ? 'active' : '' }}" href="{{ route('nilai.index') }}">
                            <i class="fa-solid fa-star"></i>
                            <span>Penilaian Siswa</span>
                        </a>
                    </div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('walikelas.*') ? 'active' : '' }}" href="{{ route('walikelas.index') }}">
                            <i class="fa-solid fa-user-tie"></i>
                            <span>Wali Kelas</span>
                        </a>
                    </div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('mapel.*') ? 'active' : '' }}" href="{{ route('mapel.index') }}">
                            <i class="fa-solid fa-book-open"></i>
                            <span>Mata Pelajaran</span>
                        </a>
                    </div>

                    <div class="mosaic-nav-section">Pengaturan Sistem</div>
                    <div class="mosaic-nav-item">
                        <a class="mosaic-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <i class="fa-solid fa-users-gear"></i>
                            <span>Kelola Pengguna</span>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Sidebar User Profile Footer -->
            @auth
                <div class="mosaic-sidebar-footer">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
                             style="width: 38px; height: 38px; background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); font-size: 14px; flex-shrink: 0;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="text-white fw-semibold text-truncate small" style="line-height: 1.2;">
                                {{ Auth::user()->name }}
                            </div>
                            <div class="text-slate-400" style="font-size: 11px;">
                                @if(Auth::user()->role === 'admin')
                                    <span style="color: #a5b4fc;"><i class="fa-solid fa-shield-halved me-1"></i> Admin</span>
                                @elseif(Auth::user()->role === 'guru')
                                    <span style="color: #6ee7b7;"><i class="fa-solid fa-chalkboard-user me-1"></i> Guru Mapel</span>
                                @elseif(Auth::user()->role === 'siswa')
                                    <span style="color: #38bdf8;"><i class="fa-solid fa-user-graduate me-1"></i> Siswa {{ optional(Auth::user()->siswa)->kelas ? 'Kelas ' . Auth::user()->siswa->kelas : '' }}</span>
                                @else
                                    <span style="color: #fcd34d;"><i class="fa-solid fa-clipboard-user me-1"></i> Piket</span>
                                @endif
                            </div>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm p-1 text-slate-400" title="Keluar dari sistem" style="background: transparent; border: none;">
                                <i class="fa-solid fa-right-from-bracket fs-6"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endauth
        </aside>

        <!-- Main Content Wrapper (Header + Content + Footer) -->
        <div class="mosaic-main-wrapper">
            <!-- Top Header -->
            <header class="mosaic-header sticky-top">
                <div class="d-flex align-items-center gap-3">
                    <!-- Hamburger Toggle Button for Mobile -->
                    <button id="sidebarToggleBtn" class="btn btn-light btn-sm d-lg-none rounded-3 border px-2 py-1-5 shadow-sm" type="button" aria-label="Buka menu navigasi">
                        <i class="fa-solid fa-bars-staggered fs-5 text-slate-700"></i>
                    </button>

                    <!-- School Identity Info -->
                    <div class="d-none d-sm-flex align-items-center gap-2 text-slate-600 small">
                        <span class="badge rounded-pill px-3 py-1-5" style="background: #eef2ff; color: #4338ca; font-weight: 600; font-size: 12px;">
                            <i class="fa-solid fa-school me-1"></i> SMP Negeri 5 Ciamis
                        </span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="text-slate-500">Tahun Ajaran {{ date('Y') }}/{{ date('Y')+1 }}</span>
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <!-- Today Date Pill -->
                    <div class="d-none d-md-flex align-items-center gap-2 px-3 py-1-5 rounded-pill bg-slate-50 border border-slate-200 text-slate-600 small">
                        <i class="fa-regular fa-calendar-days" style="color: #6366f1;"></i>
                        <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                    </div>

                    @auth
                        <!-- User Profile Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 py-1 px-2 rounded-pill border border-slate-200 shadow-sm" 
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center" 
                                     style="width: 32px; height: 32px; background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); font-size: 13px;">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span class="d-none d-sm-inline fw-semibold text-slate-800 small">{{ Auth::user()->name }}</span>
                                <span class="badge rounded-pill ms-1 d-none d-md-inline" 
                                      style="{{ Auth::user()->role === 'admin' ? 'background:#fee2e2; color:#b91c1c;' : (Auth::user()->role === 'guru' ? 'background:#e0e7ff; color:#3730a3;' : (Auth::user()->role === 'siswa' ? 'background:#e0f2fe; color:#0369a1;' : 'background:#fef3c7; color:#92400e;')) }} font-size: 10px;">
                                    {{ ucfirst(Auth::user()->role) }}
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2 py-2" style="min-width: 230px;">
                                <li class="px-3 py-2 border-bottom">
                                    <div class="fw-bold text-slate-800 small">{{ Auth::user()->name }}</div>
                                    <div class="text-slate-400" style="font-size: 11px;">
                                        <span class="text-primary fw-medium font-monospace">@ {{ Auth::user()->username ?? strtolower(str_replace(' ', '', Auth::user()->name)) }}</span>
                                        • {{ Auth::user()->email }}
                                    </div>
                                </li>
                                @if(Auth::user()->nip)
                                    <li class="px-3 py-1 border-bottom bg-slate-50">
                                        <span class="text-slate-500 font-monospace" style="font-size: 10px;">NIP. {{ Auth::user()->nip }}</span>
                                    </li>
                                @elseif(Auth::user()->role === 'siswa' && Auth::user()->siswa)
                                    <li class="px-3 py-1 border-bottom bg-slate-50">
                                        <span class="text-slate-600 font-monospace" style="font-size: 11px;">NIS: {{ Auth::user()->siswa->nis }} &bull; Kelas {{ Auth::user()->siswa->kelas }}</span>
                                    </li>
                                @endif
                                @if(Auth::user()->role === 'admin')
                                    <li>
                                        <a class="dropdown-item py-2 small" href="{{ route('users.index') }}">
                                            <i class="fa-solid fa-users-gear me-2 text-primary"></i> Kelola Pengguna
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                @else
                                    <li class="my-1"></li>
                                @endif
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger py-2 small">
                                            <i class="fa-solid fa-right-from-bracket me-2"></i> Keluar (Logout)
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endauth
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="mosaic-main-content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert" style="background: #ecfdf5; color: #065f46; border-left: 4px solid #10b981 !important;">
                        <i class="fa-solid fa-circle-check fs-5 text-success"></i>
                        <div class="flex-grow-1 fw-medium">{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert" style="background: #fff1f2; color: #9f1239; border-left: 4px solid #f43f5e !important;">
                        <i class="fa-solid fa-triangle-exclamation fs-5 text-danger"></i>
                        <div class="flex-grow-1 fw-medium">{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="mosaic-footer text-center">
                <div class="container-fluid">
                    <small>&copy; {{ date('Y') }} <strong>SI-KASEP</strong> — Sistem Informasi Rekap Absen dan Penilaian SMP Negeri 5 Ciamis</small>
                </div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Cruip Mosaic Mobile Drawer JS -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('mosaicSidebar');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const closeBtn = document.getElementById('sidebarCloseBtn');
        const backdrop = document.getElementById('sidebarBackdrop');

        function openSidebar() {
            if (sidebar) sidebar.classList.add('show');
            if (backdrop) backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('show');
            if (backdrop) backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', openSidebar);
        }
        if (closeBtn) {
            closeBtn.addEventListener('click', closeSidebar);
        }
        if (backdrop) {
            backdrop.addEventListener('click', closeSidebar);
        }
    });
    </script>
    
    @stack('scripts')
</body>
</html>
