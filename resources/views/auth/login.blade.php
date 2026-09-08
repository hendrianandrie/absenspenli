<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SI-GURU SMP</title>
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
            padding: 20px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            border: none;
            overflow: hidden;
            max-width: 440px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            padding: 32px 24px;
            text-align: center;
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

    <div class="login-card">
        <!-- Header Brand -->
        <div class="login-header">
            <div class="mb-2">
                <i class="fa-solid fa-graduation-cap fa-3x text-warning"></i>
            </div>
            <h4 class="fw-bold mb-1">SI-GURU SMP</h4>
            <p class="mb-0 text-white-50 fs-7">Sistem Manajemen Nilai & Absensi Siswa</p>
        </div>

        <!-- Body Form -->
        <div class="p-4 p-sm-5">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show fs-7 border-0 shadow-sm mb-4" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show fs-7 border-0 shadow-sm mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="mb-3">
                    <label for="login" class="form-label fw-semibold fs-7 text-dark">Username atau Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="login" id="login" class="form-control border-start-0 ps-0" placeholder="Masukkan username atau email" value="{{ old('login') }}" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold fs-7 text-dark">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control border-start-0 ps-0" placeholder="Masukkan password" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label fs-7 text-muted" for="remember">Ingat Saya</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 shadow">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> Masuk Aplikasi
                </button>
            </form>
        </div>

        <div class="bg-light py-3 text-center border-top text-muted fs-8">
            &copy; {{ date('Y') }} SI-GURU SMP — Manajemen Nilai & Absensi
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
