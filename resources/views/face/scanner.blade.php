@extends('layouts.app')

@push('styles')
<style>
    .scanner-viewport {
        position: relative;
        width: 100%;
        background: #090d16;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.4);
        aspect-ratio: 4 / 3;
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
        transform: scaleX(-1);
        pointer-events: none;
    }
    .scan-line {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, transparent, #38bdf8, #6366f1, transparent);
        box-shadow: 0 0 15px #38bdf8;
        animation: scanMove 2.5s infinite linear;
        pointer-events: none;
        z-index: 5;
    }
    @keyframes scanMove {
        0% { top: 0%; opacity: 0; }
        15% { opacity: 1; }
        85% { opacity: 1; }
        100% { top: 100%; opacity: 0; }
    }
    .scan-status-overlay {
        position: absolute;
        bottom: 16px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(15, 23, 42, 0.88);
        color: #fff;
        backdrop-filter: blur(8px);
        padding: 8px 20px;
        border-radius: 999px;
        font-size: 12.5px;
        font-weight: 500;
        white-space: nowrap;
        border: 1px solid rgba(255, 255, 255, 0.15);
        z-index: 10;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    .success-popup {
        position: absolute;
        top: 20px;
        left: 20px;
        right: 20px;
        background: rgba(16, 185, 129, 0.95);
        color: #fff;
        backdrop-filter: blur(8px);
        border-radius: 14px;
        padding: 14px 18px;
        z-index: 20;
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
        display: none;
        animation: slideDown 0.3s ease;
    }
    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* Fullscreen Mode Styling */
    .scanner-viewport:fullscreen,
    .scanner-viewport:-webkit-full-screen {
        width: 100vw !important;
        height: 100vh !important;
        border-radius: 0 !important;
        max-width: 100vw !important;
        background: #000 !important;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .scanner-viewport:fullscreen #scannerVideo,
    .scanner-viewport:-webkit-full-screen #scannerVideo {
        width: 100vw !important;
        height: 100vh !important;
        object-fit: cover !important;
    }
    .scanner-viewport:fullscreen #scannerCanvas,
    .scanner-viewport:-webkit-full-screen #scannerCanvas {
        width: 100vw !important;
        height: 100vh !important;
    }
    .scanner-viewport:fullscreen .success-popup,
    .scanner-viewport:-webkit-full-screen .success-popup {
        top: 40px;
        left: 50%;
        transform: translateX(-50%);
        width: 90%;
        max-width: 680px;
        font-size: 1.15rem;
        padding: 20px 24px;
        box-shadow: 0 20px 40px rgba(16, 185, 129, 0.5);
    }
    .scanner-viewport:fullscreen .scan-status-overlay,
    .scanner-viewport:-webkit-full-screen .scan-status-overlay {
        bottom: 40px;
        font-size: 14.5px;
        padding: 10px 28px;
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-column gap-3">
    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('face.index', ['kelas' => $selectedKelas]) }}" class="text-decoration-none small text-slate-500">
                    <i class="fa-solid fa-arrow-left me-1"></i> Dashboard Biometrik
                </a>
                <span class="text-slate-300">•</span>
                <span class="badge rounded-pill px-2.5 py-1" style="background: #e0f2fe; color: #0284c7; font-size: 11px;">
                    <i class="fa-solid fa-expand me-1"></i> Scanner Kamera (Kiosk Mode)
                </span>
            </div>
            <h2 class="fw-bold mb-1 text-slate-800" style="font-size: 1.4rem; letter-spacing: -0.02em;">
                Mesin Pemindai Presensi Wajah — Kelas {{ $selectedKelas }}
            </h2>
            <p class="text-slate-500 mb-0" style="font-size: 13px;">
                Arahkan kamera ke siswa yang datang. Sistem akan mengenali wajah secara instan dan mencatat kehadiran otomatis.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" id="fullscreenToggleBtn" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 12px;">
                <i class="fa-solid fa-expand me-1.5"></i> Layar Penuh (Full Screen)
            </button>
            <a href="{{ route('face.enroll', ['kelas' => $selectedKelas]) }}" class="btn btn-light btn-sm text-slate-700 border rounded-pill px-3 py-1.5 shadow-xs" style="font-size: 12px;">
                <i class="fa-solid fa-user-plus me-1.5 text-primary"></i> Rekam Siswa Baru
            </a>
            <a href="{{ route('face.unattended', ['kelas' => $selectedKelas]) }}" class="btn btn-outline-warning btn-sm text-dark rounded-pill px-3 py-1.5 shadow-xs" style="font-size: 12px;">
                <i class="fa-solid fa-clipboard-question me-1.5"></i> Cek Belum Absen
            </a>
        </div>
    </div>

    <!-- Layout Dua Kolom -->
    <div class="row g-3">
        <!-- Kolom Kiri: Layar Scanner Kamera -->
        <div class="col-lg-7 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-1" style="font-size: 11px;">
                            <span class="spinner-grow spinner-grow-sm me-1" style="width: 8px; height: 8px;" role="status"></span> Scanner Aktif
                        </span>
                        <span class="small text-slate-500" id="loadedCountText">Memuat data wajah...</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <select id="selectScannerKelas" class="form-select form-select-sm rounded-3 border-slate-300 py-1" style="font-size: 12px;" onchange="changeScannerKelas(this.value)">
                            @foreach($daftarKelas as $k)
                                <option value="{{ $k }}" {{ $selectedKelas == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                            @endforeach
                            <option value="Semua" {{ $selectedKelas == 'Semua' ? 'selected' : '' }}>Semua Kelas</option>
                        </select>
                    </div>
                </div>

                <!-- Viewport Kamera Scanner -->
                <div class="scanner-viewport">
                    <!-- Overlay Top Bar (Live Clock & Viewport Fullscreen Toggle) -->
                    <div class="d-flex align-items-center justify-content-between p-3 position-absolute top-0 start-0 end-0" style="z-index: 15; pointer-events: none;">
                        <div class="badge bg-dark bg-opacity-75 text-white border border-white border-opacity-25 rounded-pill px-3 py-1.5 shadow-sm" style="pointer-events: auto; backdrop-filter: blur(4px);">
                            <i class="fa-solid fa-school me-1.5 text-info"></i> SMPN 5 Ciamis &bull; <span id="kioskLiveClock">--:--:--</span>
                        </div>
                        <button type="button" id="viewportFullscreenToggleBtn" class="btn btn-sm btn-dark bg-opacity-75 text-white border border-white border-opacity-25 rounded-pill px-3 py-1.5 shadow-sm" style="pointer-events: auto; font-size: 11.5px; backdrop-filter: blur(4px);">
                            <i class="fa-solid fa-expand me-1"></i> <span id="vpFsLabel">Layar Penuh</span>
                        </button>
                    </div>

                    <video id="scannerVideo" autoplay playsinline muted></video>
                    <canvas id="scannerCanvas"></canvas>
                    <div class="scan-line"></div>
                    <div class="scan-status-overlay" id="scannerStatus">
                        <i class="fa-solid fa-spinner fa-spin me-1.5"></i> Memuat Model AI & Database Wajah...
                    </div>

                    <!-- Pop-up Notifikasi Sukses -->
                    <div class="success-popup" id="successPopup">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-white text-success" 
                                 style="width: 44px; height: 44px;">
                                <i class="fa-solid fa-check fs-5"></i>
                            </div>
                            <div class="flex-grow-1 text-white">
                                <div class="fw-bold fs-6 lh-sm" id="popupNamaSiswa">Nama Siswa</div>
                                <small class="text-white-50" id="popupDetailSiswa">NIS: 12345 &bull; Kelas IX E &bull; Pukul 06:45:10</small>
                            </div>
                            <span class="badge bg-white text-success rounded-pill px-2.5 py-1 fw-bold shadow-xs">
                                HADIR
                            </span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mt-3 text-slate-500" style="font-size: 11.5px;">
                    <span><i class="fa-solid fa-volume-high text-primary me-1"></i> Suara audio notifikasi aktif</span>
                    <span><i class="fa-solid fa-shield-halved text-success me-1"></i> Anti-spam: jeda 10 detik per siswa</span>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Riwayat Presensi Wajah Hari Ini -->
        <div class="col-lg-5 col-xl-5">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-3.5 h-100 d-flex flex-column">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" 
                             style="width: 32px; height: 32px; background: #e0e7ff;">
                            <i class="fa-solid fa-clock-rotate-left" style="font-size: 13px;"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-slate-800 mb-0" style="font-size: 13.5px;">Riwayat Scan Hari Ini</h6>
                            <small class="text-slate-400" style="font-size: 11px;">Update real-time saat siswa scan</small>
                        </div>
                    </div>
                    <span class="badge bg-primary rounded-pill px-2 py-0.5" id="historyCounter" style="font-size: 11px;">
                        {{ $recentScans->count() }} Tercatat
                    </span>
                </div>

                <!-- List Riwayat Scan -->
                <div class="flex-grow-1 overflow-auto pe-1" id="scansHistoryContainer" style="max-height: 440px;">
                    @forelse($recentScans as $sc)
                        <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-2 bg-slate-50 border border-slate-200 scan-item">
                            <div class="d-flex align-items-center gap-2.5">
                                @if(optional($sc->siswa)->foto_wajah)
                                    <img src="{{ asset('storage/' . $sc->siswa->foto_wajah) }}" alt="Foto" 
                                         class="rounded-circle object-fit-cover border" width="34" height="34"
                                         onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                    <div class="rounded-circle text-white fw-bold align-items-center justify-content-center"
                                         style="display: none; width: 34px; height: 34px; font-size: 12px; background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                                        {{ strtoupper(substr(optional($sc->siswa)->nama ?? 'S', 0, 1)) }}
                                    </div>
                                @else
                                    <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center"
                                         style="width: 34px; height: 34px; font-size: 12px; background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                                        {{ strtoupper(substr(optional($sc->siswa)->nama ?? 'S', 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-semibold text-slate-800 lh-sm" style="font-size: 12.5px;">
                                        {{ optional($sc->siswa)->nama ?? 'Siswa' }}
                                    </div>
                                    <small class="text-slate-400 font-monospace" style="font-size: 10.5px;">
                                        {{ optional($sc->siswa)->nis }} &bull; Kelas {{ optional($sc->siswa)->kelas }}
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 mb-1" style="font-size: 10px;">
                                    Hadir (Wajah)
                                </span>
                                <div class="text-slate-400" style="font-size: 10px;">
                                    {{ \Carbon\Carbon::parse($sc->updated_at)->format('H:i:s') }} WIB
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-slate-400" id="emptyHistoryNotice">
                            <i class="fa-regular fa-id-card fs-1 mb-2 text-slate-300"></i>
                            <h6 class="fw-semibold text-slate-600 mb-1" style="font-size: 13px;">Belum Ada Scan Hari Ini</h6>
                            <p class="small text-slate-400 mb-0" style="font-size: 11px;">Arahkan wajah siswa ke kamera untuk memulai pencatatan otomatis.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script>
    const MODEL_URL = '{{ asset("models/face") }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const STUDENTS_API_URL = '{{ route("face.enrolledStudents") }}';
    const RECORD_ATTENDANCE_URL = '{{ route("face.recordAttendance") }}';

    const video = document.getElementById('scannerVideo');
    const canvas = document.getElementById('scannerCanvas');
    const scannerStatus = document.getElementById('scannerStatus');
    const loadedCountText = document.getElementById('loadedCountText');
    const successPopup = document.getElementById('successPopup');
    const popupNama = document.getElementById('popupNamaSiswa');
    const popupDetail = document.getElementById('popupDetailSiswa');
    const scansContainer = document.getElementById('scansHistoryContainer');

    let currentKelas = '{{ $selectedKelas }}';
    let faceMatcher = null;
    let enrolledStudentsMap = {};
    let isModelsReady = false;
    let isProcessing = false;
    let lastScannedStudents = {}; // Cooldown map: siswaId => timestamp

    // Synthesize audio chime (Ding-dong!) using Web Audio API
    function playSuccessChime() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();

            // Tone 1: 659.25 Hz (E5)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, ctx.currentTime);
            gain1.gain.setValueAtTime(0.2, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start();
            osc1.stop(ctx.currentTime + 0.3);

            // Tone 2: 880 Hz (A5)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, ctx.currentTime + 0.12);
            gain2.gain.setValueAtTime(0.25, ctx.currentTime + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(ctx.currentTime + 0.12);
            osc2.stop(ctx.currentTime + 0.5);
        } catch (e) {
            console.log('Audio error:', e);
        }
    }

    // Load Models & Database
    async function initScanner() {
        try {
            scannerStatus.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5"></i> Memuat Model AI Biometrik...';
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            isModelsReady = true;

            await loadEnrolledStudents();
            startScannerVideo();
        } catch (err) {
            console.error('Inisialisasi gagal:', err);
            scannerStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger me-1.5"></i> Gagal Memuat Model AI';
        }
    }

    // Ambil data vektor wajah dari server
    async function loadEnrolledStudents() {
        try {
            scannerStatus.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5"></i> Memuat Database Wajah Siswa...';
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
                    faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.55); // Jarak threshold max 0.55 (akurasi tinggi)
                    loadedCountText.textContent = `${labeledDescriptors.length} siswa siap dicocokkan`;
                    scannerStatus.innerHTML = '<i class="fa-solid fa-face-smile text-success me-1.5"></i> Scanner Siap! Arahkan wajah ke kamera.';
                } else {
                    faceMatcher = null;
                    loadedCountText.textContent = '0 siswa terdaftar';
                    scannerStatus.innerHTML = '<i class="fa-solid fa-info-circle text-warning me-1.5"></i> Belum ada siswa yang direkam wajahnya di kelas ini.';
                }
            } else {
                faceMatcher = null;
                loadedCountText.textContent = '0 siswa terdaftar';
                scannerStatus.innerHTML = '<i class="fa-solid fa-info-circle text-warning me-1.5"></i> Belum ada siswa yang direkam wajahnya di kelas ini.';
            }
        } catch (e) {
            console.error('Gagal memuat siswa:', e);
            loadedCountText.textContent = 'Gagal memuat data siswa';
        }
    }

    function startScannerVideo() {
        navigator.mediaDevices.getUserMedia({
            video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
            audio: false
        })
        .then(stream => {
            video.srcObject = stream;
        })
        .catch(err => {
            console.error('Gagal buka kamera:', err);
            scannerStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger me-1.5"></i> Akses Kamera Gagal';
        });
    }

    // Loop pendeteksian wajah real-time
    video.addEventListener('play', () => {
        const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
        faceapi.matchDimensions(canvas, displaySize);

        setInterval(async () => {
            if (!isModelsReady || isProcessing) return;

            const detections = await faceapi.detectAllFaces(
                video, 
                new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })
            ).withFaceLandmarks().withFaceDescriptors();

            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height);

            if (!detections || detections.length === 0) {
                return;
            }

            const resizedDetections = faceapi.resizeResults(detections, displaySize);

            // Jika ada faceMatcher, cocokkan
            if (faceMatcher) {
                for (const detection of resizedDetections) {
                    const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                    const box = detection.detection.box;

                    if (bestMatch.label !== 'unknown') {
                        const siswaId = parseInt(bestMatch.label);
                        const siswa = enrolledStudentsMap[siswaId];
                        const confidence = Math.round((1 - bestMatch.distance) * 100);

                        // Gambar box hijau
                        const drawBox = new faceapi.draw.DrawBox(box, {
                            label: `${siswa ? siswa.nama : 'Siswa'} (${confidence}%)`,
                            boxColor: '#10b981'
                        });
                        drawBox.draw(canvas);

                        // Cek cooldown (agar tidak spam jika masih di depan kamera dalam 10 detik)
                        const now = Date.now();
                        const lastTime = lastScannedStudents[siswaId] || 0;
                        if (now - lastTime > 10000) {
                            lastScannedStudents[siswaId] = now;
                            triggerAttendance(siswa, bestMatch.distance);
                        }
                    } else {
                        // Wajah tidak dikenal
                        const drawBox = new faceapi.draw.DrawBox(box, {
                            label: 'Wajah Tidak Dikenali',
                            boxColor: '#f59e0b'
                        });
                        drawBox.draw(canvas);
                    }
                }
            } else {
                faceapi.draw.drawDetections(canvas, resizedDetections);
            }
        }, 250);
    });

    // Kirim presensi ke server
    async function triggerAttendance(siswa, distance) {
        if (!siswa) return;

        isProcessing = true;
        playSuccessChime();

        showSuccessPopup(siswa);

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
                })
            });

            const res = await response.json();
            if (res.success) {
                addHistoryItem(res.siswa);
            }
        } catch (e) {
            console.error('Gagal mencatat presensi:', e);
        } finally {
            setTimeout(() => { isProcessing = false; }, 800);
        }
    }

    function showSuccessPopup(siswa) {
        popupNama.textContent = siswa.nama;
        const nowTime = new Date().toLocaleTimeString('id-ID');
        popupDetail.textContent = `NIS: ${siswa.nis} • Kelas ${siswa.kelas} • Pukul ${nowTime} WIB`;

        successPopup.style.display = 'block';
        setTimeout(() => {
            successPopup.style.display = 'none';
        }, 4000);
    }

    function addHistoryItem(siswa) {
        const emptyNotice = document.getElementById('emptyHistoryNotice');
        if (emptyNotice) emptyNotice.remove();

        const itemHtml = `
            <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-2 bg-slate-50 border border-slate-200 scan-item" style="animation: slideDown 0.3s ease;">
                <div class="d-flex align-items-center gap-2.5">
                    ${siswa.foto_url 
                        ? `<img src="${siswa.foto_url}" alt="Foto" class="rounded-circle object-fit-cover border" width="34" height="34" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"><div class="rounded-circle text-white fw-bold align-items-center justify-content-center" style="display: none; width: 34px; height: 34px; font-size: 12px; background: linear-gradient(135deg, #3b82f6, #1d4ed8);">${siswa.nama.charAt(0).toUpperCase()}</div>`
                        : `<div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 12px; background: linear-gradient(135deg, #3b82f6, #1d4ed8);">${siswa.nama.charAt(0).toUpperCase()}</div>`
                    }
                    <div>
                        <div class="fw-semibold text-slate-800 lh-sm" style="font-size: 12.5px;">${siswa.nama}</div>
                        <small class="text-slate-400 font-monospace" style="font-size: 10.5px;">${siswa.nis} &bull; Kelas ${siswa.kelas}</small>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 mb-1" style="font-size: 10px;">
                        Hadir (Wajah)
                    </span>
                    <div class="text-slate-400" style="font-size: 10px;">${siswa.waktu} WIB</div>
                </div>
            </div>
        `;

        scansContainer.insertAdjacentHTML('afterbegin', itemHtml);
    }

    function changeScannerKelas(kelas) {
        window.location.href = `{{ route('face.scanner') }}?kelas=${encodeURIComponent(kelas)}`;
    }

    // Fullscreen Mode Controls
    const fsToggleBtn = document.getElementById('fullscreenToggleBtn');
    const vpFsToggleBtn = document.getElementById('viewportFullscreenToggleBtn');
    const vpFsLabel = document.getElementById('vpFsLabel');
    const scannerViewport = document.querySelector('.scanner-viewport');

    function toggleFullScreen() {
        if (!document.fullscreenElement && !document.webkitFullscreenElement) {
            if (scannerViewport.requestFullscreen) {
                scannerViewport.requestFullscreen();
            } else if (scannerViewport.webkitRequestFullscreen) {
                scannerViewport.webkitRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
        }
    }

    if (fsToggleBtn) fsToggleBtn.addEventListener('click', toggleFullScreen);
    if (vpFsToggleBtn) vpFsToggleBtn.addEventListener('click', toggleFullScreen);

    function handleFullscreenChange() {
        const isFs = !!(document.fullscreenElement || document.webkitFullscreenElement);
        if (fsToggleBtn) {
            fsToggleBtn.innerHTML = isFs 
                ? '<i class="fa-solid fa-compress me-1.5"></i> Keluar Full Screen' 
                : '<i class="fa-solid fa-expand me-1.5"></i> Layar Penuh (Full Screen)';
        }
        if (vpFsLabel) {
            vpFsLabel.textContent = isFs ? 'Keluar Full Screen' : 'Layar Penuh';
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

    // Live Digital Clock
    function startKioskClock() {
        const clockElem = document.getElementById('kioskLiveClock');
        if (!clockElem) return;
        const update = () => {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            clockElem.textContent = `${h}:${m}:${s} WIB`;
        };
        update();
        setInterval(update, 1000);
    }

    window.addEventListener('load', () => {
        startKioskClock();
        initScanner();
    });
</script>
@endpush
@endsection
