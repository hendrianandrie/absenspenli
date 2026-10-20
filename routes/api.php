<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GuruApiController;
use App\Http\Controllers\Api\SiswaApiController;
use App\Http\Controllers\Api\WaliKelasApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — SI-KASEP (SMPN 5 Ciamis)
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. PUBLIC ROUTES (AUTENTIKASI)
// ==========================================
Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// ==========================================
// 2. PROTECTED ROUTES (SANCTUM TOKEN AUTH)
// ==========================================
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth & Profil Pengguna
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ------------------------------------------
    // A. ENDPOINT SISWA
    // ------------------------------------------
    Route::prefix('siswa')->group(function () {
        Route::get('/dashboard', [SiswaApiController::class, 'dashboard']);
        Route::get('/rekap-absen', [SiswaApiController::class, 'rekapAbsen']);
        Route::get('/rekap-nilai', [SiswaApiController::class, 'rekapNilai']);
    });

    // ------------------------------------------
    // B. ENDPOINT GURU
    // ------------------------------------------
    Route::prefix('guru')->group(function () {
        Route::get('/dashboard', [GuruApiController::class, 'dashboard']);
        Route::get('/kegiatan', [GuruApiController::class, 'daftarKegiatan']);
        Route::post('/nilai', [GuruApiController::class, 'simpanNilai']);
    });

    // ------------------------------------------
    // C. ENDPOINT WALI KELAS (TUGAS KHUSUS)
    // ------------------------------------------
    Route::prefix('walikelas')->group(function () {
        Route::get('/nilai', [WaliKelasApiController::class, 'myClass']);
        Route::get('/kehadiran', [WaliKelasApiController::class, 'kehadiran']);
    });
});
