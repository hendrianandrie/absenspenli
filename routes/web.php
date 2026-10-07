<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MataPelajaranController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SiswaPortalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaliKelasController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Auth Routes (Public)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (Hanya dapat diakses setelah login)
Route::middleware('auth')->group(function () {

    // ==========================================
    // PORTAL SISWA (Khusus Role Siswa)
    // ==========================================
    Route::middleware(\App\Http\Middleware\EnsureRoleSiswa::class)->prefix('portal-siswa')->group(function () {
        Route::get('/dashboard', [SiswaPortalController::class, 'dashboard'])->name('siswa.dashboard');
        Route::get('/nilai', [SiswaPortalController::class, 'rekapNilai'])->name('siswa.nilai');
        Route::get('/absen', [SiswaPortalController::class, 'rekapAbsen'])->name('siswa.absen');
    });

    // ==========================================
    // ROUTES GURU / ADMIN / PIKET (Bukan Siswa)
    // ==========================================
    Route::middleware(\App\Http\Middleware\EnsureNotSiswa::class)->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/cetak-pdf', [DashboardController::class, 'cetakPdf'])->name('dashboard.cetakPdf');

        // Siswa
        Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
        Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
        Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->name('siswa.destroy');
        Route::post('/siswa/hapus-kelas', [SiswaController::class, 'destroyKelas'])->name('siswa.destroyKelas');
        Route::get('/siswa/import', [SiswaController::class, 'importForm'])->name('siswa.importForm');
        Route::post('/siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
        Route::get('/siswa/download-template', [SiswaController::class, 'downloadTemplate'])->name('siswa.downloadTemplate');

        // Absensi Cepat Harian & Rekap
        Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
        Route::post('/absensi', [AbsensiController::class, 'store'])->name('absensi.store');
        Route::get('/absensi/harian', [AbsensiController::class, 'harian'])->name('absensi.harian');
        Route::post('/absensi/harian', [AbsensiController::class, 'simpanHarian'])->name('absensi.harian.simpan');
        Route::get('/rekap', [AbsensiController::class, 'rekap'])->name('absensi.rekap');
        Route::get('/rekap/pdf', [AbsensiController::class, 'cetakPDF'])->name('absensi.cetakPDF');

    // Routes khusus Guru / Admin (Bukan Piket)
    Route::middleware(\App\Http\Middleware\CheckNotPiket::class)->group(function () {
        // Kelola Pengguna (Admin Only)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/sync-siswa', [UserController::class, 'syncSiswaAccounts'])->name('users.syncSiswa');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        // Mata Pelajaran
        Route::get('/mapel', [MataPelajaranController::class, 'index'])->name('mapel.index');
        Route::post('/mapel', [MataPelajaranController::class, 'store'])->name('mapel.store');
        Route::delete('/mapel/{id}', [MataPelajaranController::class, 'destroy'])->name('mapel.destroy');

        // Penilaian (Kegiatan, Rata-Rata Murni, Export PDF & Excel, Cetak Rapor Siswa)
        Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai.index');
        Route::get('/nilai/kegiatan/create', [NilaiController::class, 'createKegiatan'])->name('nilai.kegiatan.create');
        Route::post('/nilai/kegiatan/store', [NilaiController::class, 'storeKegiatan'])->name('nilai.kegiatan.store');
        Route::get('/nilai/kegiatan/{id}/edit', [NilaiController::class, 'editKegiatan'])->name('nilai.kegiatan.edit');
        Route::post('/nilai/kegiatan/{id}/update', [NilaiController::class, 'updateKegiatan'])->name('nilai.kegiatan.update');
        Route::delete('/nilai/kegiatan/{id}', [NilaiController::class, 'destroyKegiatan'])->name('nilai.kegiatan.destroy');
        Route::get('/nilai/rekap-pdf', [NilaiController::class, 'rekapPdf'])->name('nilai.rekapPdf');
        Route::get('/nilai/rekap-excel', [NilaiController::class, 'rekapExcel'])->name('nilai.rekapExcel');
        Route::get('/nilai/lembar-kosong-pdf', [NilaiController::class, 'lembarKosongPdf'])->name('nilai.lembarKosongPdf');
        Route::get('/nilai/rapor-siswa/{id}', [NilaiController::class, 'raporSiswaPdf'])->name('nilai.raporSiswaPdf');

        // Wali Kelas (Pengaturan Admin, Monitoring Nilai & Rekap Kehadiran)
        Route::get('/walikelas', [WaliKelasController::class, 'index'])->name('walikelas.index');
        Route::post('/walikelas/assign', [WaliKelasController::class, 'assign'])->name('walikelas.assign');
        Route::get('/wali-kelas/binaan', [WaliKelasController::class, 'myClass'])->name('walikelas.myClass');
        Route::get('/wali-kelas/export-excel', [WaliKelasController::class, 'exportExcel'])->name('walikelas.exportExcel');
        Route::get('/wali-kelas/kehadiran', [WaliKelasController::class, 'kehadiranKelas'])->name('walikelas.kehadiran');
        Route::get('/wali-kelas/kehadiran/export-excel', [WaliKelasController::class, 'exportKehadiranExcel'])->name('walikelas.kehadiran.exportExcel');

        // Utilitas Migrasi Database via Browser (Khusus Admin / Hosting cPanel / Hostinger)
        Route::get('/run-migrate', function () {
            if (!\Illuminate\Support\Facades\Auth::check() || \Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
                abort(403, 'Akses terbatas untuk Administrator.');
            }
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                $output = \Illuminate\Support\Facades\Artisan::output();
                return "<div style='font-family:sans-serif; max-width:650px; margin:40px auto; padding:24px; border-radius:12px; background:#0f172a; color:#f8fafc; box-shadow:0 10px 25px rgba(0,0,0,0.3);'>
                    <h3 style='color:#10b981; margin-top:0;'>✅ Migrasi Database Berhasil</h3>
                    <pre style='background:#1e293b; padding:15px; border-radius:8px; overflow-x:auto; font-size:13px; color:#e2e8f0; border:1px solid #334155;'>" . e($output ?: 'Database sudah ter-update (Nothing to migrate).') . "</pre>
                    <a href='" . route('dashboard') . "' style='display:inline-block; margin-top:15px; background:#4f46e5; color:#fff; text-decoration:none; padding:10px 20px; border-radius:8px; font-weight:600;'>← Kembali ke Dashboard</a>
                </div>";
            } catch (\Throwable $e) {
                return "<div style='font-family:sans-serif; max-width:650px; margin:40px auto; padding:24px; border-radius:12px; background:#0f172a; color:#f8fafc;'>
                    <h3 style='color:#ef4444; margin-top:0;'>❌ Terjadi Kesalahan Migrasi</h3>
                    <pre style='background:#1e293b; padding:15px; border-radius:8px; overflow-x:auto; font-size:13px; color:#fca5a5; border:1px solid #dc2626;'>" . e($e->getMessage()) . "</pre>
                    <a href='" . route('dashboard') . "' style='display:inline-block; margin-top:15px; background:#475569; color:#fff; text-decoration:none; padding:10px 20px; border-radius:8px; font-weight:600;'>Kembali ke Dashboard</a>
                </div>";
            }
        })->name('run.migrate');
    });
    });
});
