<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI-KASEP - Sistem Informasi Rekap Absen Spenli</title>
    <!-- Favicon Logo -->
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Font: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6f9;
            color: #334155;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.15);
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }
        .stat-card {
            border-left: 4px solid #3b82f6;
        }
        .btn-primary-custom {
            background-color: #2563eb;
            border-color: #2563eb;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-primary-custom:hover {
            background-color: #1d4ed8;
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
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SPENLI" width="34" height="34" class="d-inline-block align-text-top bg-white rounded-circle p-1">
                <span>SI-KASEP <small class="fw-normal text-white-50 fs-6">SPENLI</small></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fa-solid fa-chart-line me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('siswa.*') ? 'active fw-semibold' : '' }}" href="{{ route('siswa.index') }}">
                            <i class="fa-solid fa-users me-1"></i> Siswa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('absensi.harian') ? 'active fw-semibold' : '' }}" href="{{ route('absensi.harian') }}">
                            <i class="fa-solid fa-calendar-check me-1"></i> Absensi Cepat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('absensi.rekap') ? 'active fw-semibold' : '' }}" href="{{ route('absensi.rekap') }}">
                            <i class="fa-solid fa-file-invoice me-1"></i> Rekap Absensi
                        </a>
                    </li>
                    @if(Auth::check() && Auth::user()->role !== 'piket')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mapel.*') ? 'active fw-semibold' : '' }}" href="{{ route('mapel.index') }}">
                                <i class="fa-solid fa-book-open me-1"></i> Mata Pelajaran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('nilai.*') ? 'active fw-semibold' : '' }}" href="{{ route('nilai.index') }}">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Manajemen Nilai
                            </a>
                        </li>
                    @endif
                    @auth
                        <li class="nav-item ms-lg-2">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-light px-3 py-1">
                                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout ({{ Auth::user()->name }})
                                </button>
                            </form>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="container pb-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-top py-3 text-center text-muted fs-7 mt-auto">
        <div class="container">
            <small>&copy; {{ date('Y') }} SI-KASEP — Sistem Informasi Rekap Absen Spenli</small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
