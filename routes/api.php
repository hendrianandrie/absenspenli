<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GuruApiController;
use App\Http\Controllers\Api\SiswaApiController;
use App\Http\Controllers\Api\WaliKelasApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — SI-KASEP (SMPN 5 Ciamis)
| Mendukung akses ber-versi (/api/v1/*) maupun langsung (/api/*)
|--------------------------------------------------------------------------
*/

$registerApiRoutes = function () {
    // 1. PUBLIC ROUTES (AUTENTIKASI)
    Route::post('/login', [AuthController::class, 'login']);

    // 2. PROTECTED ROUTES (SANCTUM TOKEN AUTH)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Siswa Endpoints
        Route::prefix('siswa')->group(function () {
            Route::get('/dashboard', [SiswaApiController::class, 'dashboard']);
            Route::get('/rekap-absen', [SiswaApiController::class, 'rekapAbsen']);
            Route::get('/absen', [SiswaApiController::class, 'rekapAbsen']);
            Route::get('/rekap-nilai', [SiswaApiController::class, 'rekapNilai']);
            Route::get('/nilai', [SiswaApiController::class, 'rekapNilai']);
        });

        // Guru Endpoints
        Route::prefix('guru')->group(function () {
            Route::get('/dashboard', [GuruApiController::class, 'dashboard']);
            Route::get('/kegiatan', [GuruApiController::class, 'daftarKegiatan']);
            Route::post('/nilai', [GuruApiController::class, 'simpanNilai']);
        });

        // Wali Kelas Endpoints
        Route::prefix('walikelas')->group(function () {
            Route::get('/nilai', [WaliKelasApiController::class, 'myClass']);
            Route::get('/kehadiran', [WaliKelasApiController::class, 'kehadiran']);
        });

        // Push Notification Device Token Endpoints
        Route::prefix('device-token')->group(function () {
            Route::post('/register', [\App\Http\Controllers\Api\DeviceTokenController::class, 'register']);
            Route::post('/unregister', [\App\Http\Controllers\Api\DeviceTokenController::class, 'unregister']);
            Route::post('/test', [\App\Http\Controllers\Api\DeviceTokenController::class, 'test']);
            Route::get('/latest', [\App\Http\Controllers\Api\DeviceTokenController::class, 'latest']);
        });
    });
};

// Daftarkan rute dengan prefix v1 dan tanpa prefix
Route::prefix('v1')->group($registerApiRoutes);
Route::group([], $registerApiRoutes);
