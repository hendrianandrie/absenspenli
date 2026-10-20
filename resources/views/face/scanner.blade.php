@extends('layouts.app')

@push('styles')
<style>
    /* ========================================================
       KIOSK & SCANNER MASTER STYLING (SMPN 5 CIAMIS)
       ======================================================== */
    :root {
        --kiosk-bg: #070b13;
        --kiosk-card: rgba(15, 23, 42, 0.75);
        --kiosk-border: rgba(255, 255, 255, 0.12);
        --kiosk-accent: #38bdf8;
        --kiosk-success: #10b981;
        --kiosk-warning: #f59e0b;
    }

    .kiosk-container {
        position: relative;
        width: 100%;
        transition: all 0.3s ease;
    }

    /* Standard Viewport */
    .scanner-viewport {
        position: relative;
        width: 100%;
        background: #060911;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.45);
        aspect-ratio: 4 / 3;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #scannerVideo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1);
    }

    #scannerCanvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transform: none; /* Jangan mirror canvas agar teks dan persentase tidak terbalik */
        pointer-events: none;
        z-index: 4;
    }

    /* Laser Scan Beam */
    .scan-line {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, transparent, #38bdf8, #818cf8, transparent);
        box-shadow: 0 0 18px #38bdf8;
        animation: scanMove 2.4s infinite linear;
        pointer-events: none;
        z-index: 5;
    }

    @keyframes scanMove {
        0% { top: 0%; opacity: 0; }
        15% { opacity: 1; }
        85% { opacity: 1; }
        100% { top: 100%; opacity: 0; }
    }

    /* Face Silhouette Guide Frame */
    .face-guide-frame {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 260px;
        height: 330px;
        border: 2px dashed rgba(255, 255, 255, 0.35);
        border-radius: 50% 50% 48% 48% / 60% 60% 40% 40%;
        pointer-events: none;
        z-index: 6;
        transition: all 0.3s ease;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.22);
    }

    .face-guide-frame.matched {
        border: 3.5px solid var(--kiosk-success);
        box-shadow: 0 0 25px var(--kiosk-success), 0 0 0 9999px rgba(0, 0, 0, 0.28);
    }

    .face-guide-corner {
        position: absolute;
        width: 24px;
        height: 24px;
        border-color: #38bdf8;
        border-style: solid;
        pointer-events: none;
    }
    .guide-tl { top: -2px; left: -2px; border-width: 3px 0 0 3px; border-top-left-radius: 8px; }
    .guide-tr { top: -2px; right: -2px; border-width: 3px 3px 0 0; border-top-right-radius: 8px; }
    .guide-bl { bottom: -2px; left: -2px; border-width: 0 0 3px 3px; border-bottom-left-radius: 8px; }
    .guide-br { bottom: -2px; right: -2px; border-width: 0 3px 3px 0; border-bottom-right-radius: 8px; }

    .face-guide-label {
        position: absolute;
        bottom: -34px;
        left: 50%;
        transform: translateX(-50%);
        color: rgba(255, 255, 255, 0.85);
        font-size: 11.5px;
        font-weight: 500;
        white-space: nowrap;
        background: rgba(15, 23, 42, 0.75);
        padding: 3px 12px;
        border-radius: 999px;
        backdrop-filter: blur(6px);
        letter-spacing: 0.3px;
    }

    /* Live Status Pill */
    .scan-status-overlay {
        position: absolute;
        bottom: 18px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(15, 23, 42, 0.88);
        color: #fff;
        backdrop-filter: blur(10px);
        padding: 8px 22px;
        border-radius: 999px;
        font-size: 12.5px;
        font-weight: 500;
        white-space: nowrap;
        border: 1px solid rgba(255, 255, 255, 0.16);
        z-index: 10;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
    }

    /* Floating Hero Verification Popup */
    .kiosk-hero-popup {
        position: absolute;
        top: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(-20px);
        width: 92%;
        max-width: 580px;
        background: rgba(15, 23, 42, 0.94);
        border: 1.5px solid rgba(16, 185, 129, 0.6);
        color: #fff;
        backdrop-filter: blur(14px);
        border-radius: 20px;
        padding: 16px 22px;
        z-index: 25;
        box-shadow: 0 20px 45px rgba(16, 185, 129, 0.35);
        display: none;
        opacity: 0;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .kiosk-hero-popup.show {
        display: block;
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    .kiosk-hero-popup.warning-state {
        border-color: rgba(245, 158, 11, 0.7);
        box-shadow: 0 20px 45px rgba(245, 158, 11, 0.3);
    }

    .student-avatar-wrap {
        position: relative;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #10b981;
        box-shadow: 0 0 15px rgba(16, 185, 129, 0.5);
        flex-shrink: 0;
    }

    .warning-state .student-avatar-wrap {
        border-color: #f59e0b;
        box-shadow: 0 0 15px rgba(245, 158, 11, 0.5);
    }

    .student-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* ========================================================
       FULLSCREEN / KIOSK MODE DEDICATED THEME
       ======================================================== */
    .kiosk-container:fullscreen,
    .kiosk-container:-webkit-full-screen {
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        background: var(--kiosk-bg) !important;
        color: #f8fafc !important;
        padding: 18px 24px !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }

    /* Kiosk Top Bar */
    .kiosk-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 10px 18px;
        background: var(--kiosk-card);
        border: 1px solid var(--kiosk-border);
        border-radius: 16px;
        backdrop-filter: blur(12px);
        margin-bottom: 14px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }

    /* Fullscreen Main Grid */
    .kiosk-container:fullscreen .kiosk-grid,
    .kiosk-container:-webkit-full-screen .kiosk-grid {
        flex: 1 1 auto;
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 16px;
        min-height: 0;
        overflow: hidden;
    }

    .kiosk-container:fullscreen .scanner-viewport,
    .kiosk-container:-webkit-full-screen .scanner-viewport {
        border-radius: 20px;
        height: 100% !important;
        aspect-ratio: auto !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
        border: 1px solid var(--kiosk-border);
    }

    .kiosk-container:fullscreen .face-guide-frame,
    .kiosk-container:-webkit-full-screen .face-guide-frame {
        width: 320px;
        height: 420px;
    }

    .kiosk-container:fullscreen .kiosk-side-panel,
    .kiosk-container:-webkit-full-screen .kiosk-side-panel {
        background: var(--kiosk-card);
        border: 1px solid var(--kiosk-border);
        border-radius: 20px;
        backdrop-filter: blur(12px);
        display: flex;
        flex-direction: column;
        min-height: 0;
        padding: 16px;
        overflow: hidden;
    }

    .kiosk-container:fullscreen .kiosk-hero-popup,
    .kiosk-container:-webkit-full-screen .kiosk-hero-popup {
        top: 36px;
        max-width: 680px;
        padding: 20px 28px;
    }

    .kiosk-container:fullscreen .student-avatar-wrap,
    .kiosk-container:-webkit-full-screen .student-avatar-wrap {
        width: 76px;
        height: 76px;
    }

    /* Pulse animation for active camera dot */
    .pulse-dot {
        width: 9px;
        height: 9px;
        background-color: #10b981;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseDot 1.8s infinite;
    }

    @keyframes pulseDot {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    /* Custom Scrollbar for Recent Scans */
    .recent-scans-scroll::-webkit-scrollbar {
        width: 5px;
    }
    .recent-scans-scroll::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.03);
    }
    .recent-scans-scroll::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }
</style>
@endpush

@section('content')
<!-- WRAPPER MASTER KIOSK CONTAINER -->
<div id="kioskContainer" class="kiosk-container">

    <!-- TOP BAR KIOSK (Brand, Clock, Live Stats & Controls) -->
    <div class="kiosk-top-bar">
        <!-- Brand & Title -->
        <div class="d-flex align-items-center gap-2.5">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0"
                 style="width: 38px; height: 38px; background: linear-gradient(135deg, #0284c7, #4f46e5);">
                <i class="fa-solid fa-school fs-6"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-uppercase" style="font-size: 13px; letter-spacing: 0.5px;">SMPN 5 CIAMIS</span>
                    <span class="badge rounded-pill bg-primary bg-opacity-20 text-info border border-info border-opacity-30 px-2 py-0.5" style="font-size: 10px;">
                        <span class="pulse-dot me-1"></span> POS PIKET & GERBANG
                    </span>
                </div>
                <div class="small text-muted" style="font-size: 11.5px;" id="kioskFullDateTime">
                    Memuat waktu...
                </div>
            </div>
        </div>

        <!-- Real-Time Attendance Counters -->
        <div class="d-none d-md-flex align-items-center gap-2">
            <div class="px-3 py-1.5 rounded-3 d-flex align-items-center gap-2 border" 
                 style="background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.25) !important;">
                <i class="fa-solid fa-user-check text-success fs-6"></i>
                <div class="lh-1">
                    <div class="small text-muted" style="font-size: 10px;">HADIR HARI INI</div>
                    <div class="fw-bold text-success" style="font-size: 14px;" id="hudHadirCount">{{ $hadirTodayCount }}</div>
                </div>
            </div>

            <div class="px-3 py-1.5 rounded-3 d-flex align-items-center gap-2 border" 
                 style="background: rgba(245, 158, 11, 0.08); border-color: rgba(245, 158, 11, 0.25) !important;">
                <i class="fa-solid fa-user-clock text-warning fs-6"></i>
                <div class="lh-1">
                    <div class="small text-muted" style="font-size: 10px;">BELUM HADIR</div>
                    <div class="fw-bold text-warning" style="font-size: 14px;" id="hudBelumCount">{{ $belumHadirCount }}</div>
                </div>
            </div>

            <div class="px-3 py-1.5 rounded-3 d-flex align-items-center gap-2 border" 
                 style="background: rgba(99, 102, 241, 0.08); border-color: rgba(99, 102, 241, 0.25) !important;">
                <i class="fa-solid fa-users text-primary fs-6"></i>
                <div class="lh-1">
                    <div class="small text-muted" style="font-size: 10px;">TERDAFTAR WAJAH</div>
                    <div class="fw-bold text-primary" style="font-size: 14px;" id="hudEnrolledCount">{{ $enrolledCount }} / {{ $totalSiswa }}</div>
                </div>
            </div>
        </div>

        <!-- Controls: Kelas, Kamera, Suara & Fullscreen -->
        <div class="d-flex align-items-center gap-2">
            <!-- Pilihan Kelas -->
            <select id="kioskSelectKelas" class="form-select form-select-sm rounded-pill border-slate-300 py-1" 
                    style="font-size: 12px; max-width: 145px;" onchange="changeScannerKelas(this.value)">
                <option value="Semua" {{ $selectedKelas == 'Semua' ? 'selected' : '' }}>Semua Kelas (Seluruh Siswa)</option>
                @foreach($daftarKelas as $k)
                    <option value="{{ $k }}" {{ $selectedKelas == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                @endforeach
            <!-- Pilihan Sensitivitas Akurasi AI -->
            <select id="kioskSelectAccuracy" class="form-select form-select-sm rounded-pill border-slate-300 py-1 d-none d-md-inline-block" 
                    style="font-size: 11.5px; max-width: 165px;" onchange="changeAccuracyThreshold(this.value)" title="Tingkat Akurasi AI">
                <option value="0.42">🛡️ Ketat (Akurasi Tinggi)</option>
                <option value="0.44" selected>⚖️ Standar (Rekomendasi)</option>
                <option value="0.48">⚡ Fleksibel (Cahaya Redup)</option>
            </select>

            <!-- Pilihan Kamera (Jika ada kamera eksternal USB) -->
            <select id="kioskSelectCamera" class="form-select form-select-sm rounded-pill border-slate-300 py-1 d-none d-lg-inline-block" 
                    style="font-size: 12px; max-width: 140px;" onchange="changeCameraDevice(this.value)">
                <option value="">Kamera Default</option>
            </select>

            <!-- Audio Voice TTS Toggle Button -->
            <button type="button" id="toggleTtsBtn" class="btn btn-sm btn-light border rounded-circle shadow-xs" 
                    style="width: 32px; height: 32px; padding: 0;" title="Toggle Suara Panggilan Nama">
                <i class="fa-solid fa-volume-high text-primary" id="ttsIcon"></i>
            </button>

            <!-- Tombol Fullscreen Master -->
            <button type="button" id="kioskFullscreenBtn" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 12px;">
                <i class="fa-solid fa-expand me-1.5" id="fsIcon"></i> <span id="fsLabelText">Layar Penuh (Kiosk)</span>
            </button>
        </div>
    </div>

    <!-- MAIN GRID KIOSK -->
    <div class="row g-3 kiosk-grid">
        <!-- Kolom Kiri / Utama: Viewport Kamera Biometrik -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-2.5 p-md-3 h-100 d-flex flex-column" style="background: rgba(255,255,255,0.95);">
                <!-- Viewport Scanner -->
                <div class="scanner-viewport flex-grow-1">
                    <!-- Scanner Video Stream -->
                    <video id="scannerVideo" autoplay playsinline muted></video>
                    
                    <!-- Bounding Box Canvas -->
                    <canvas id="scannerCanvas"></canvas>

                    <!-- Laser Animated Scan Beam -->
                    <div class="scan-line"></div>

                    <!-- Face Guide Silhouette (Oval frame for alignment) -->
                    <div class="face-guide-frame" id="faceGuideFrame">
                        <div class="face-guide-corner guide-tl"></div>
                        <div class="face-guide-corner guide-tr"></div>
                        <div class="face-guide-corner guide-bl"></div>
                        <div class="face-guide-corner guide-br"></div>
                        <div class="face-guide-label" id="guideLabel">
                            <i class="fa-solid fa-arrows-to-dot me-1 text-info"></i> Posisikan Wajah di Dalam Bingkai
                        </div>
                    </div>

                    <!-- Camera Diagnostic / Troubleshooting Overlay -->
                    <div id="cameraTroubleshootOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column align-items-center justify-content-center p-4 text-center" 
                         style="z-index: 30; background: rgba(10, 15, 29, 0.94); backdrop-filter: blur(8px);">
                        <div id="troubleshootContent" class="text-white" style="max-width: 440px;"></div>
                    </div>

                    <!-- Bottom Status Overlay Pill -->
                    <div class="scan-status-overlay" id="scannerStatus">
                        <i class="fa-solid fa-spinner fa-spin me-1.5"></i> Memuat Model AI Biometrik...
                    </div>

                    <!-- HERO POPUP: SUCCESS / DUPLICATE NOTIFICATION -->
                    <div class="kiosk-hero-popup" id="kioskHeroPopup">
                        <div class="d-flex align-items-center gap-3">
                            <!-- Student Avatar -->
                            <div class="student-avatar-wrap">
                                <img src="" alt="Foto" id="heroStudentPhoto" class="student-avatar-img" 
                                     onerror="this.style.display='none'; document.getElementById('heroStudentInitial').style.display='flex';">
                                <div id="heroStudentInitial" class="w-100 h-100 text-white fw-bold align-items-center justify-content-center"
                                     style="display: none; font-size: 26px; background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                                    S
                                </div>
                            </div>

                            <!-- Student Info -->
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge rounded-pill px-2.5 py-1 fw-bold" id="heroStatusBadge" 
                                          style="background: #10b981; color: #fff; font-size: 11px;">
                                        <i class="fa-solid fa-check me-1"></i> VERIFIKASI BERHASIL
                                    </span>
                                    <small class="text-white-50 font-monospace" id="heroScanTime">06:45:10 WIB</small>
                                </div>
                                <h4 class="fw-bold text-white mb-0 text-truncate" id="heroStudentName" style="font-size: 1.25rem;">
                                    Nama Siswa
                                </h4>
                                <div class="text-white-50 small mt-0.5" id="heroStudentDetails" style="font-size: 12px;">
                                    NIS: 12345 • Kelas IX E
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Tips Bar -->
                <div class="d-flex align-items-center justify-content-between mt-2.5 px-1 text-slate-500" style="font-size: 11.5px;">
                    <div class="d-flex align-items-center gap-2">
                        <span id="activeCameraLabel"><i class="fa-solid fa-camera text-primary me-1"></i> Sensor Kamera Aktif</span>
                        <span>•</span>
                        <span id="screenWakeLockStatus"><i class="fa-solid fa-sun text-warning me-1"></i> Layar Terjaga (Anti-Sleep)</span>
                    </div>
                    <div>
                        <span><i class="fa-solid fa-shield-halved text-success me-1"></i> Jeda deteksi 8 detik per siswa</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Panel Riwayat Presensi Realtime -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-3.5 h-100 d-flex flex-column kiosk-side-panel">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-slate-200">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" 
                             style="width: 32px; height: 32px; background: #e0e7ff;">
                            <i class="fa-solid fa-clock-rotate-left" style="font-size: 13px;"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" style="font-size: 13.5px;">Presensi Wajah Terkini</h6>
                            <small class="text-muted" style="font-size: 11px;">Otomatis update saat siswa lewat</small>
                        </div>
                    </div>
                    <span class="badge bg-primary rounded-pill px-2.5 py-1" id="historyCounterBadge" style="font-size: 11px;">
                        {{ $recentScans->count() }} Tercatat
                    </span>
                </div>

                <!-- List Riwayat Scan -->
                <div class="flex-grow-1 overflow-auto pe-1 recent-scans-scroll" id="scansHistoryContainer" style="max-height: 480px;">
                    @forelse($recentScans as $sc)
                        <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-2 bg-slate-50 border border-slate-200 scan-item">
                            <div class="d-flex align-items-center gap-2.5 min-w-0">
                                @if(optional($sc->siswa)->foto_wajah)
                                    <img src="{{ asset('storage/' . $sc->siswa->foto_wajah) }}" alt="Foto" 
                                         class="rounded-circle object-fit-cover border flex-shrink-0" width="34" height="34"
                                         onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                    <div class="rounded-circle text-white fw-bold align-items-center justify-content-center flex-shrink-0"
                                         style="display: none; width: 34px; height: 34px; font-size: 12px; background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                                        {{ strtoupper(substr(optional($sc->siswa)->nama ?? 'S', 0, 1)) }}
                                    </div>
                                @else
                                    <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width: 34px; height: 34px; font-size: 12px; background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                                        {{ strtoupper(substr(optional($sc->siswa)->nama ?? 'S', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate lh-sm" style="font-size: 12.5px;">
                                        {{ optional($sc->siswa)->nama ?? 'Siswa' }}
                                    </div>
                                    <small class="text-muted font-monospace" style="font-size: 10.5px;">
                                        {{ optional($sc->siswa)->nis }} &bull; Kelas {{ optional($sc->siswa)->kelas }}
                                    </small>
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0 ms-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 mb-1" style="font-size: 10px;">
                                    Hadir
                                </span>
                                <div class="text-muted font-monospace" style="font-size: 10px;">
                                    {{ \Carbon\Carbon::parse($sc->updated_at)->format('H:i:s') }} WIB
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted" id="emptyHistoryNotice">
                            <i class="fa-regular fa-id-card fs-1 mb-2 text-slate-300"></i>
                            <h6 class="fw-semibold mb-1" style="font-size: 13px;">Belum Ada Scan Hari Ini</h6>
                            <p class="small text-muted mb-0" style="font-size: 11px;">Siswa yang lewat di depan kamera akan otomatis tercatat di sini.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Footer Shortcut Actions -->
                <div class="pt-3 border-top border-slate-200 mt-2 d-flex align-items-center justify-content-between">
                    <a href="{{ route('face.index', ['kelas' => $selectedKelas]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1" style="font-size: 11.5px;">
                        <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
                    </a>
                    <a href="{{ route('face.unattended', ['kelas' => $selectedKelas]) }}" class="btn btn-outline-warning btn-sm rounded-pill px-3 py-1" style="font-size: 11.5px;">
                        <i class="fa-solid fa-clipboard-question me-1"></i> Belum Hadir
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script>
    // ========================================================
    // KONFIGURASI & VARIABEL STATE
    // ========================================================
    const MODEL_URL = '{{ asset("models/face") }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const STUDENTS_API_URL = '{{ route("face.enrolledStudents") }}';
    const RECORD_ATTENDANCE_URL = '{{ route("face.recordAttendance") }}';

    const video = document.getElementById('scannerVideo');
    const canvas = document.getElementById('scannerCanvas');
    const scannerStatus = document.getElementById('scannerStatus');
    const faceGuideFrame = document.getElementById('faceGuideFrame');
    const guideLabel = document.getElementById('guideLabel');

    // Hero Popup Elements
    const heroPopup = document.getElementById('kioskHeroPopup');
    const heroPhoto = document.getElementById('heroStudentPhoto');
    const heroInitial = document.getElementById('heroStudentInitial');
    const heroName = document.getElementById('heroStudentName');
    const heroDetails = document.getElementById('heroStudentDetails');
    const heroBadge = document.getElementById('heroStatusBadge');
    const heroTime = document.getElementById('heroScanTime');

    // Real-Time Counters
    const hudHadirCount = document.getElementById('hudHadirCount');
    const hudBelumCount = document.getElementById('hudBelumCount');
    const hudEnrolledCount = document.getElementById('hudEnrolledCount');
    const scansContainer = document.getElementById('scansHistoryContainer');
    const historyCounterBadge = document.getElementById('historyCounterBadge');

    let currentKelas = '{{ $selectedKelas }}';
    let faceMatcher = null;
    let enrolledStudentsMap = {};
    let labeledDescriptorsCache = [];
    let currentThreshold = 0.44; // Default ketat & akurat: 0.44 (sebelumnya 0.54 rawan tertukar)
    let isModelsReady = false;
    let isProcessing = false;
    let lastScannedStudents = {}; // Cooldown map: siswaId => timestamp
    let isTtsEnabled = true; // Voice speech synthesis aktif secara default
    let wakeLock = null;
    let popupTimer = null;
    let currentStream = null;
    let currentDeviceId = null;

    // ========================================================
    // WEBAUDIO CHIMES (SUCCESS & WARNING)
    // ========================================================
    function playSuccessChime() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();

            // Tone 1: E5 (659.25 Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, ctx.currentTime);
            gain1.gain.setValueAtTime(0.25, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.28);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start();
            osc1.stop(ctx.currentTime + 0.28);

            // Tone 2: A5 (880 Hz)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, ctx.currentTime + 0.12);
            gain2.gain.setValueAtTime(0.3, ctx.currentTime + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(ctx.currentTime + 0.12);
            osc2.stop(ctx.currentTime + 0.5);
        } catch (e) {
            console.log('Audio error:', e);
        }
    }

    function playDuplicateChime() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();

            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(440, ctx.currentTime);
            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.25);
        } catch (e) {}
    }

    // ========================================================
    // TEXT-TO-SPEECH (TTS) VOICE GREETING
    // ========================================================
    function speakGreeting(nama, isDuplicate) {
        if (!isTtsEnabled || !('speechSynthesis' in window)) return;

        window.speechSynthesis.cancel(); // Hentikan ucapan sebelumnya jika ada

        const hour = new Date().getHours();
        let sapaan = 'Selamat pagi';
        if (hour >= 11 && hour < 15) sapaan = 'Selamat siang';
        else if (hour >= 15) sapaan = 'Selamat sore';

        let text = `${sapaan} ${nama}, presensi berhasil.`;
        if (isDuplicate) {
            text = `${nama}, kamu sudah presensi sebelumnya.`;
        }

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'id-ID';
        utterance.rate = 1.05; // Sedikit lebih cepat agar tangkas
        utterance.pitch = 1.0;

        window.speechSynthesis.speak(utterance);
    }

    // Toggle Voice TTS Button
    const toggleTtsBtn = document.getElementById('toggleTtsBtn');
    const ttsIcon = document.getElementById('ttsIcon');
    if (toggleTtsBtn) {
        toggleTtsBtn.addEventListener('click', () => {
            isTtsEnabled = !isTtsEnabled;
            if (isTtsEnabled) {
                ttsIcon.className = 'fa-solid fa-volume-high text-primary';
                toggleTtsBtn.classList.remove('btn-light');
                toggleTtsBtn.classList.add('btn-primary-subtle');
            } else {
                ttsIcon.className = 'fa-solid fa-volume-xmark text-muted';
                toggleTtsBtn.classList.remove('btn-primary-subtle');
                toggleTtsBtn.classList.add('btn-light');
                if ('speechSynthesis' in window) window.speechSynthesis.cancel();
            }
        });
    }

    // ========================================================
    // INISIALISASI MODEL AI & DATABASE
    // ========================================================
    async function initScanner() {
        try {
            scannerStatus.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5 text-info"></i> Memuat Model AI Biometrik...';
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            isModelsReady = true;

            await loadEnrolledStudents();
            await populateCameraDevices();
            await startScannerVideo();
            requestScreenWakeLock();
        } catch (err) {
            console.error('Inisialisasi gagal:', err);
            scannerStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger me-1.5"></i> Gagal Memuat Model AI';
        }
    }

    async function loadEnrolledStudents() {
        try {
            scannerStatus.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5 text-info"></i> Memuat Data Wajah Siswa...';
            const res = await fetch(`${STUDENTS_API_URL}?kelas=${encodeURIComponent(currentKelas)}`);
            const data = await res.json();

            if (data.success && data.students.length > 0) {
                const labeledDescriptors = [];
                enrolledStudentsMap = {};

                data.students.forEach(s => {
                    enrolledStudentsMap[s.id] = s;
                    if (s.descriptor && s.descriptor.length === 128) {
                        const floatArray = new Float32Array(s.descriptor);
                        labeledDescriptors.push(new faceapi.LabeledFaceDescriptors(s.id.toString(), [floatArray]));
                    }
                });

                if (labeledDescriptors.length > 0) {
                    labeledDescriptorsCache = labeledDescriptors;
                    // Ambang batas ketat: 0.44 (hanya wajah dengan akurasi tinggi yang cocok)
                    faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, currentThreshold);
                    scannerStatus.innerHTML = `<i class="fa-solid fa-face-smile text-success me-1.5"></i> Scanner Siap (${labeledDescriptors.length} siswa siap dicocokkan)`;
                } else {
                    labeledDescriptorsCache = [];
                    faceMatcher = null;
                    scannerStatus.innerHTML = '<i class="fa-solid fa-info-circle text-warning me-1.5"></i> Belum ada siswa yang direkam wajahnya di kelas ini.';
                }
            } else {
                faceMatcher = null;
                scannerStatus.innerHTML = '<i class="fa-solid fa-info-circle text-warning me-1.5"></i> Belum ada data wajah siswa untuk kelas ini.';
            }
        } catch (e) {
            console.error('Gagal memuat siswa:', e);
            scannerStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger me-1.5"></i> Gagal Memuat Data Siswa';
        }
    }

    // ========================================================
    // KAMERA: MULTI-DEVICE SUPPORT & STREAM
    // ========================================================
    async function populateCameraDevices() {
        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return;
            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(d => d.kind === 'videoinput');
            const selectElem = document.getElementById('kioskSelectCamera');

            if (videoDevices.length > 1 && selectElem) {
                selectElem.innerHTML = '';
                videoDevices.forEach((device, index) => {
                    const opt = document.createElement('option');
                    opt.value = device.deviceId;
                    opt.text = device.label || `Kamera ${index + 1}`;
                    selectElem.appendChild(opt);
                });
                selectElem.classList.remove('d-none');
            }
        } catch (e) {
            console.log('Device enumerate error:', e);
        }
    }

    function changeAccuracyThreshold(val) {
        currentThreshold = parseFloat(val);
        if (labeledDescriptorsCache.length > 0) {
            faceMatcher = new faceapi.FaceMatcher(labeledDescriptorsCache, currentThreshold);
            console.log('Ambang batas jarak biometrik disetel ke:', currentThreshold);
        }
    }

    async function changeCameraDevice(deviceId) {
        currentDeviceId = deviceId;
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
        }
        await startScannerVideo();
    }

    async function startScannerVideo() {
        const overlay = document.getElementById('cameraTroubleshootOverlay');
        const content = document.getElementById('troubleshootContent');

        // 1. Validasi HTTPS (Kamera di browser modern wajib HTTPS jika online)
        const isLocalhost = location.hostname === 'localhost' || location.hostname === '127.0.0.1';
        if (location.protocol !== 'https:' && !isLocalhost) {
            const httpsUrl = 'https://' + location.host + location.pathname + location.search;
            if (overlay && content) {
                content.innerHTML = `
                    <div class="mb-3">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: rgba(245, 158, 11, 0.15); border: 2px solid #f59e0b;">
                            <i class="fa-solid fa-lock text-warning fs-2"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-white mb-2">Kamera Wajib Menggunakan HTTPS</h5>
                    <p class="text-white-50 small mb-3">
                        Browser melarang akses kamera jika situs dibuka melalui <strong>http://</strong> biasa.<br>
                        Silakan akses web menggunakan koneksi aman <strong>https://</strong>
                    </p>
                    <a href="${httpsUrl}" class="btn btn-warning rounded-pill px-4 py-2 fw-semibold text-dark shadow-sm" style="font-size: 13px;">
                        <i class="fa-solid fa-shield-halved me-1.5"></i> Buka via HTTPS Sekarang
                    </a>
                `;
                overlay.classList.remove('d-none');
                overlay.classList.add('d-flex');
            }
            scannerStatus.innerHTML = '<i class="fa-solid fa-lock text-warning me-1.5"></i> Akses Kamera Wajib HTTPS';
            return;
        }

        // 2. Cek ketersediaan navigator.mediaDevices
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (overlay && content) {
                content.innerHTML = `
                    <div class="mb-3">
                        <i class="fa-solid fa-triangle-exclamation text-danger fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-2">Fitur Kamera Tidak Didukung</h5>
                    <p class="text-white-50 small mb-0">
                        Browser Anda menolak API kamera karena situs belum menggunakan sertifikat SSL/HTTPS yang valid.
                    </p>
                `;
                overlay.classList.remove('d-none');
                overlay.classList.add('d-flex');
            }
            scannerStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger me-1.5"></i> MediaDevices Tidak Didukung';
            return;
        }

        // 3. Coba aktifkan kamera dengan fallback
        const constraints = {
            video: currentDeviceId 
                ? { deviceId: { exact: currentDeviceId } }
                : { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' },
            audio: false
        };

        try {
            currentStream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = currentStream;
            if (overlay) {
                overlay.classList.add('d-none');
                overlay.classList.remove('d-flex');
            }
            scannerStatus.innerHTML = '<i class="fa-solid fa-face-smile text-success me-1.5"></i> Scanner Siap! Arahkan wajah ke kamera.';
        } catch (err) {
            console.warn('Gagal dengan constraint ideal, mencoba fallback dasar...', err);
            try {
                // Fallback kedua: tanpa batasan resolusi/facingMode
                currentStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                video.srcObject = currentStream;
                if (overlay) {
                    overlay.classList.add('d-none');
                    overlay.classList.remove('d-flex');
                }
                scannerStatus.innerHTML = '<i class="fa-solid fa-face-smile text-success me-1.5"></i> Scanner Siap (Kamera Dasar)!';
            } catch (err2) {
                console.error('Gagal total buka kamera:', err2);
                let judul = 'Akses Kamera Ditolak';
                let penjelasan = 'Browser memblokir perizinan kamera untuk situs ini.';
                let instruksi = `
                    1. Klik ikon <strong>gembok / perizinan situs</strong> di sebelah kiri alamat URL browser.<br>
                    2. Pada baris <strong>Kamera (Camera)</strong>, ubah dari <em>Blokir</em> menjadi <strong>Izinkan (Allow)</strong>.<br>
                    3. Klik tombol di bawah untuk mencoba kembali.
                `;

                if (err2.name === 'NotFoundError' || err2.name === 'DevicesNotFoundError') {
                    judul = 'Perangkat Kamera Tidak Ditemukan';
                    penjelasan = 'Tidak ada webcam atau modul kamera fisik yang terhubung pada perangkat ini.';
                    instruksi = 'Pastikan webcam USB sudah tercolok dengan baik ke port komputer Anda.';
                } else if (err2.name === 'NotReadableError' || err2.name === 'TrackStartError') {
                    judul = 'Kamera Sedang Dipakai Aplikasi Lain';
                    penjelasan = 'Sensor kamera sedang digunakan oleh aplikasi lain.';
                    instruksi = 'Tutup aplikasi seperti Zoom, OBS Studio, Google Meet, Skype, atau tab browser lain yang sedang memakai webcam.';
                }

                if (overlay && content) {
                    content.innerHTML = `
                        <div class="mb-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: rgba(239, 68, 68, 0.15); border: 2px solid #ef4444;">
                                <i class="fa-solid fa-video-slash text-danger fs-2"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold text-white mb-2">${judul}</h5>
                        <p class="text-white-50 small mb-2">${penjelasan}</p>
                        <div class="alert text-start small mb-3 p-2.5 rounded-3" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); font-size: 11.5px; color: #cbd5e1;">
                            ${instruksi}
                        </div>
                        <button type="button" onclick="startScannerVideo()" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm" style="font-size: 12.5px;">
                            <i class="fa-solid fa-rotate-right me-1.5"></i> Coba Hubungkan Kamera Lagi
                        </button>
                    `;
                    overlay.classList.remove('d-none');
                    overlay.classList.add('d-flex');
                }
                scannerStatus.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-danger me-1.5"></i> ${judul}`;
            }
        }
    }

    // ========================================================
    // LOOP DETEKSI WAJAH REAL-TIME
    // ========================================================
    video.addEventListener('play', () => {
        const displaySize = { 
            width: video.videoWidth || video.clientWidth || 640, 
            height: video.videoHeight || video.clientHeight || 480 
        };
        faceapi.matchDimensions(canvas, displaySize);

        setInterval(async () => {
            if (!isModelsReady || isProcessing) return;

            // Gunakan TinyFaceDetector inputSize 320 untuk resolusi landmark wajah lebih tajam & presisi
            const detections = await faceapi.detectAllFaces(
                video, 
                new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.55 })
            ).withFaceLandmarks().withFaceDescriptors();

            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height);

            if (!detections || detections.length === 0) {
                faceGuideFrame.classList.remove('matched');
                guideLabel.innerHTML = '<i class="fa-solid fa-arrows-to-dot me-1 text-info"></i> Posisikan Wajah di Dalam Bingkai';
                return;
            }

            const resizedDetections = faceapi.resizeResults(detections, displaySize);

            if (faceMatcher) {
                for (const detection of resizedDetections) {
                    const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                    const box = detection.detection.box;

                    // Hitung koordinat horizontal mirrored agar teks tegak normal & box presisi
                    const mirroredX = displaySize.width - box.x - box.width;
                    const mirroredBox = new faceapi.Rect(mirroredX, box.y, box.width, box.height);

                    // Validasi ketat: hanya cocok jika label terdaftar DAN jarak <= currentThreshold
                    if (bestMatch.label !== 'unknown' && bestMatch.distance <= currentThreshold) {
                        const siswaId = parseInt(bestMatch.label);
                        const siswa = enrolledStudentsMap[siswaId];
                        
                        // Hitung skor kemiripan proporsional (0.0 distance = 100%, 0.44 = 80%)
                        const confidence = Math.round(Math.max(0, Math.min(100, (1 - (bestMatch.distance / 0.55)) * 100)));

                        // Highlight Guide Frame
                        faceGuideFrame.classList.add('matched');
                        guideLabel.innerHTML = `<i class="fa-solid fa-circle-check text-success me-1"></i> Terverifikasi: <strong>${siswa ? siswa.nama : 'Siswa'}</strong> (${confidence}%)`;

                        // Box hijau pada canvas dengan teks tegak normal (tidak terbalik)
                        const drawBox = new faceapi.draw.DrawBox(mirroredBox, {
                            label: `${siswa ? siswa.nama : 'Siswa'} (${confidence}%)`,
                            boxColor: '#10b981',
                            lineWidth: 2.5
                        });
                        drawBox.draw(canvas);

                        // Cooldown 8 detik per siswa
                        const now = Date.now();
                        const lastTime = lastScannedStudents[siswaId] || 0;
                        if (now - lastTime > 8000) {
                            lastScannedStudents[siswaId] = now;
                            triggerAttendance(siswa, bestMatch.distance);
                        }
                    } else {
                        // Wajah tidak dikenal atau di bawah ambang batas akurasi (mencegah salah kenal)
                        faceGuideFrame.classList.remove('matched');
                        guideLabel.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> Wajah Tidak Cocok / Belum Terdaftar';

                        const drawBox = new faceapi.draw.DrawBox(mirroredBox, {
                            label: 'Wajah Tidak Dikenali',
                            boxColor: '#f59e0b',
                            lineWidth: 2
                        });
                        drawBox.draw(canvas);
                    }
                }
            } else {
                faceapi.draw.drawDetections(canvas, resizedDetections);
            }
        }, 220); // Deteksi setiap 220ms (sangat mulus)
    });

    // ========================================================
    // PENCATATAN PRESENSI & SNAPSHOT KAMERA
    // ========================================================
    async function triggerAttendance(siswa, distance) {
        if (!siswa) return;

        isProcessing = true;

        // Ambil snapshot foto kilat dari video stream
        let snapshotData = null;
        try {
            const snapCanvas = document.createElement('canvas');
            snapCanvas.width = video.videoWidth || 640;
            snapCanvas.height = video.videoHeight || 480;
            const snapCtx = snapCanvas.getContext('2d');
            // Cerminkan kembali foto jika video di-flip
            snapCtx.translate(snapCanvas.width, 0);
            snapCtx.scale(-1, 1);
            snapCtx.drawImage(video, 0, 0, snapCanvas.width, snapCanvas.height);
            snapshotData = snapCanvas.toDataURL('image/jpeg', 0.82);
        } catch (e) {
            console.log('Snapshot error:', e);
        }

        try {
            const response = await fetch(RECORD_ATTENDANCE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({
                    siswa_id: siswa.id,
                    distance: distance,
                    image: snapshotData
                })
            });

            const res = await response.json();

            if (res.success) {
                if (res.is_duplicate) {
                    playDuplicateChime();
                    showHeroPopup(res.siswa, true);
                    speakGreeting(res.siswa.nama, true);
                } else {
                    playSuccessChime();
                    showHeroPopup(res.siswa, false);
                    speakGreeting(res.siswa.nama, false);
                    addHistoryItem(res.siswa);
                    updateCountersOnSuccess();
                }
            }
        } catch (e) {
            console.error('Gagal mencatat presensi:', e);
        } finally {
            setTimeout(() => { isProcessing = false; }, 800);
        }
    }

    // ========================================================
    // TAMPILKAN HERO POPUP
    // ========================================================
    function showHeroPopup(siswa, isDuplicate) {
        if (popupTimer) clearTimeout(popupTimer);

        heroName.textContent = siswa.nama;
        heroDetails.textContent = `NIS: ${siswa.nis} • Kelas ${siswa.kelas}`;
        heroTime.textContent = (siswa.waktu || new Date().toLocaleTimeString('id-ID')) + ' WIB';

        if (siswa.foto_url) {
            heroPhoto.src = siswa.foto_url;
            heroPhoto.style.display = 'block';
            heroInitial.style.display = 'none';
        } else {
            heroPhoto.style.display = 'none';
            heroInitial.style.display = 'flex';
            heroInitial.textContent = siswa.nama.charAt(0).toUpperCase();
        }

        if (isDuplicate) {
            heroPopup.classList.add('warning-state');
            heroBadge.style.background = '#f59e0b';
            heroBadge.innerHTML = '<i class="fa-solid fa-clock-rotate-left me-1"></i> SUDAH HADIR SEBELUMNYA';
        } else {
            heroPopup.classList.remove('warning-state');
            heroBadge.style.background = '#10b981';
            heroBadge.innerHTML = '<i class="fa-solid fa-check me-1"></i> VERIFIKASI BERHASIL • HADIR';
        }

        heroPopup.classList.add('show');

        popupTimer = setTimeout(() => {
            heroPopup.classList.remove('show');
        }, 3600);
    }

    // ========================================================
    // UPDATE COUNTER & RIWAYAT SCAN SECARA REALTIME
    // ========================================================
    function updateCountersOnSuccess() {
        if (hudHadirCount) {
            const curHadir = parseInt(hudHadirCount.textContent) || 0;
            hudHadirCount.textContent = curHadir + 1;
        }
        if (hudBelumCount) {
            const curBelum = parseInt(hudBelumCount.textContent) || 0;
            hudBelumCount.textContent = Math.max(0, curBelum - 1);
        }
    }

    function addHistoryItem(siswa) {
        const emptyNotice = document.getElementById('emptyHistoryNotice');
        if (emptyNotice) emptyNotice.remove();

        const itemHtml = `
            <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-2 bg-slate-50 border border-slate-200 scan-item" style="animation: slideDown 0.3s ease;">
                <div class="d-flex align-items-center gap-2.5 min-w-0">
                    ${siswa.foto_url 
                        ? `<img src="${siswa.foto_url}" alt="Foto" class="rounded-circle object-fit-cover border flex-shrink-0" width="34" height="34" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"><div class="rounded-circle text-white fw-bold align-items-center justify-content-center flex-shrink-0" style="display: none; width: 34px; height: 34px; font-size: 12px; background: linear-gradient(135deg, #3b82f6, #1d4ed8);">${siswa.nama.charAt(0).toUpperCase()}</div>`
                        : `<div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; font-size: 12px; background: linear-gradient(135deg, #3b82f6, #1d4ed8);">${siswa.nama.charAt(0).toUpperCase()}</div>`
                    }
                    <div class="min-w-0">
                        <div class="fw-semibold text-truncate lh-sm" style="font-size: 12.5px;">${siswa.nama}</div>
                        <small class="text-muted font-monospace" style="font-size: 10.5px;">${siswa.nis} &bull; Kelas ${siswa.kelas}</small>
                    </div>
                </div>
                <div class="text-end flex-shrink-0 ms-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 mb-1" style="font-size: 10px;">
                        Hadir
                    </span>
                    <div class="text-muted font-monospace" style="font-size: 10px;">${siswa.waktu || 'Sekarang'} WIB</div>
                </div>
            </div>
        `;

        scansContainer.insertAdjacentHTML('afterbegin', itemHtml);

        if (historyCounterBadge) {
            const currentTotal = parseInt(historyCounterBadge.textContent) || 0;
            historyCounterBadge.textContent = `${currentTotal + 1} Tercatat`;
        }
    }

    function changeScannerKelas(kelas) {
        window.location.href = `{{ route('face.scanner') }}?kelas=${encodeURIComponent(kelas)}`;
    }

    // ========================================================
    // FULLSCREEN KIOSK MODE CONTROLLER & WAKE LOCK
    // ========================================================
    const kioskContainer = document.getElementById('kioskContainer');
    const kioskFsBtn = document.getElementById('kioskFullscreenBtn');
    const fsIcon = document.getElementById('fsIcon');
    const fsLabelText = document.getElementById('fsLabelText');

    function toggleKioskFullscreen() {
        if (!document.fullscreenElement && !document.webkitFullscreenElement) {
            if (kioskContainer.requestFullscreen) {
                kioskContainer.requestFullscreen();
            } else if (kioskContainer.webkitRequestFullscreen) {
                kioskContainer.webkitRequestFullscreen();
            }
            requestScreenWakeLock();
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
            releaseScreenWakeLock();
        }
    }

    if (kioskFsBtn) kioskFsBtn.addEventListener('click', toggleKioskFullscreen);

    function handleFullscreenChange() {
        const isFs = !!(document.fullscreenElement || document.webkitFullscreenElement);
        if (fsIcon && fsLabelText) {
            if (isFs) {
                fsIcon.className = 'fa-solid fa-compress me-1.5';
                fsLabelText.textContent = 'Keluar Kiosk (ESC)';
                kioskFsBtn.classList.remove('btn-primary');
                kioskFsBtn.classList.add('btn-light');
            } else {
                fsIcon.className = 'fa-solid fa-expand me-1.5';
                fsLabelText.textContent = 'Layar Penuh (Kiosk)';
                kioskFsBtn.classList.remove('btn-light');
                kioskFsBtn.classList.add('btn-primary');
            }
        }

        setTimeout(() => {
            const displaySize = { 
                width: video.videoWidth || video.clientWidth, 
                height: video.videoHeight || video.clientHeight 
            };
            faceapi.matchDimensions(canvas, displaySize);
        }, 300);
    }

    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);

    // Prevent Sleep / Screen Lock
    async function requestScreenWakeLock() {
        if ('wakeLock' in navigator) {
            try {
                wakeLock = await navigator.wakeLock.request('screen');
                const wlStatus = document.getElementById('screenWakeLockStatus');
                if (wlStatus) wlStatus.innerHTML = '<i class="fa-solid fa-sun text-warning me-1"></i> Layar Terjaga (Anti-Sleep: Aktif)';
            } catch (err) {
                console.log('WakeLock error:', err);
            }
        }
    }

    function releaseScreenWakeLock() {
        if (wakeLock) {
            wakeLock.release().then(() => { wakeLock = null; });
        }
    }

    // ========================================================
    // JAM DIGITAL REALTIME KIOSK (WIB)
    // ========================================================
    function startKioskClock() {
        const dateTimeElem = document.getElementById('kioskFullDateTime');
        if (!dateTimeElem) return;

        const hariList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const bulanList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        const updateClock = () => {
            const now = new Date();
            const hari = hariList[now.getDay()];
            const tanggal = now.getDate();
            const bulan = bulanList[now.getMonth()];
            const tahun = now.getFullYear();

            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');

            dateTimeElem.textContent = `${hari}, ${tanggal} ${bulan} ${tahun} • ${h}:${m}:${s} WIB`;
        };

        updateClock();
        setInterval(updateClock, 1000);
    }

    // Jalankan saat halaman siap
    window.addEventListener('load', () => {
        startKioskClock();
        initScanner();
    });
</script>
@endpush
@endsection
