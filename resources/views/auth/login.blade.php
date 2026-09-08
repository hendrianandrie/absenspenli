<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SI-KASEP (Sistem Informasi Rekap Absen Spenli)</title>
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
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #3b82f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 24px 16px;
        }
        .login-wrapper {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            border: none;
            overflow: hidden;
            max-width: 980px;
            width: 100%;
        }
        .stats-side {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: #ffffff;
            padding: 40px 32px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .stats-card-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 16px;
        }
        .login-side {
            padding: 40px 36px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #ffffff;
        }
        .btn-login {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.2s ease;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="row g-0">
            <!-- SEBELAH KIRI: Persentase Kehadiran, Izin, Sakit & Alpa Siswa Hari Ini -->
            <div class="col-lg-6 stats-side">
                <div>
                    <!-- Header Logo & Brand -->
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo SPENLI" width="60" height="60" class="bg-white rounded-circle p-1 shadow-sm">
                        <div>
                            <h4 class="fw-bold mb-0 text-white">SI-KASEP</h4>
                            <small class="text-white-50 fw-semibold">SMP NEGERI 5 CIAMIS</small>
                        </div>
                    </div>

                    <!-- Judul Rekap Hari Ini -->
                    <div class="mb-4">
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold mb-2">
                            <i class="fa-solid fa-calendar-day me-1"></i> {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
                        </span>
                        <h5 class="fw-bold text-white mb-1">
                            <i class="fa-solid fa-chart-pie me-2"></i> Rekap Absensi Siswa Hari Ini
                        </h5>
                        <p class="text-white-50 small mb-0">
                            Persentase kehadiran siswa terdaftar di SPENLI (Total {{ $totalSiswa ?? 562 }} Siswa)
                        </p>
                    </div>

                    <!-- Progress & Breakdown Stats -->
                    <div class="stats-card-box mb-3">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="fw-semibold text-white"><i class="fa-solid fa-circle-check text-success me-1"></i> Hadir</span>
                                <span class="fw-bold text-success">{{ $statsHariIni['hadir_pct'] }}% <small class="text-white-50">({{ $statsHariIni['hadir'] }} Siswa)</small></span>
                            </div>
                            <div class="progress" style="height: 8px; background-color: rgba(255,255,255,0.2);">
                                <div class="progress-bar bg-success" style="width: {{ $statsHariIni['hadir_pct'] }}%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="fw-semibold text-white"><i class="fa-solid fa-envelope-open-text text-info me-1"></i> Izin</span>
                                <span class="fw-bold text-info">{{ $statsHariIni['izin_pct'] }}% <small class="text-white-50">({{ $statsHariIni['izin'] }} Siswa)</small></span>
                            </div>
                            <div class="progress" style="height: 8px; background-color: rgba(255,255,255,0.2);">
                                <div class="progress-bar bg-info" style="width: {{ $statsHariIni['izin_pct'] }}%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="fw-semibold text-white"><i class="fa-solid fa-notes-medical text-warning me-1"></i> Sakit</span>
                                <span class="fw-bold text-warning">{{ $statsHariIni['sakit_pct'] }}% <small class="text-white-50">({{ $statsHariIni['sakit'] }} Siswa)</small></span>
                            </div>
                            <div class="progress" style="height: 8px; background-color: rgba(255,255,255,0.2);">
                                <div class="progress-bar bg-warning" style="width: {{ $statsHariIni['sakit_pct'] }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="fw-semibold text-white"><i class="fa-solid fa-circle-xmark text-danger me-1"></i> Alpa</span>
                                <span class="fw-bold text-danger">{{ $statsHariIni['alpha_pct'] }}% <small class="text-white-50">({{ $statsHariIni['alpha'] }} Siswa)</small></span>
                            </div>
                            <div class="progress" style="height: 8px; background-color: rgba(255,255,255,0.2);">
                                <div class="progress-bar bg-danger" style="width: {{ $statsHariIni['alpha_pct'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-top border-white-10 text-white-50 small">
                    <i class="fa-solid fa-circle-info me-1"></i> Data diupdate real-time dari Sistem Informasi Rekap Absen Spenli.
                </div>
            </div>

            <!-- SEBELAH KANAN: Form Login -->
            <div class="col-lg-6 login-side">
                <div>
                    <div class="mb-4 text-center text-lg-start">
                        <h4 class="fw-bold text-dark mb-1">Login Pengguna</h4>
                        <p class="text-muted small mb-0">Masukkan akun Anda untuk masuk ke sistem SI-KASEP</p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show small border-0 shadow-sm mb-4" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show small border-0 shadow-sm mb-4" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="login" class="form-label fw-semibold small text-dark">Username atau Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                                <input type="text" name="login" id="login" class="form-control border-start-0 ps-0" placeholder="Masukkan username atau email" value="{{ old('login') }}" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold small text-dark">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" id="password" class="form-control border-start-0 ps-0" placeholder="Masukkan password" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label small text-muted" for="remember">Ingat Saya</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-login w-100 shadow">
                            <i class="fa-solid fa-right-to-bracket me-2"></i> Masuk Aplikasi
                        </button>
                    </form>
                </div>

                <div class="pt-4 mt-4 border-top text-center text-muted small">
                    &copy; {{ date('Y') }} SI-KASEP — Sistem Informasi Rekap Absen Spenli
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
