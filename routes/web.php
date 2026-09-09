<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MataPelajaranController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\UserController;
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
    });
});
