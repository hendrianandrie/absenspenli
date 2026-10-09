@extends('layouts.app')

@push('styles')
<style>
    .camera-container {
        position: relative;
        width: 100%;
        max-width: 480px;
        margin: 0 auto;
        background: #0f172a;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        aspect-ratio: 4 / 3;
    }
    #videoElement {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1); /* Mirror camera */
    }
    #canvasOverlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transform: scaleX(-1); /* Match mirrored video */
        pointer-events: none;
    }
    .oval-guide {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 55%;
        height: 75%;
        border: 2px dashed rgba(255, 255, 255, 0.4);
        border-radius: 50%;
        pointer-events: none;
        transition: border-color 0.3s ease;
    }
    .oval-guide.detected {
        border-color: #10b981;
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
    }
    .status-pill {
        position: absolute;
        bottom: 12px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(15, 23, 42, 0.85);
        color: #fff;
        backdrop-filter: blur(4px);
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 11.5px;
        white-space: nowrap;
        border: 1px solid rgba(255, 255, 255, 0.15);
        z-index: 10;
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
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
                <span class="text-slate-300">•</span>
                <span class="badge rounded-pill px-2.5 py-1" style="background: #e0f2fe; color: #0284c7; font-size: 11px;">
                    <i class="fa-solid fa-user-plus me-1"></i> Laboratorium Rekam Wajah
                </span>
            </div>
            <h2 class="fw-bold mb-1 text-slate-800" style="font-size: 1.4rem; letter-spacing: -0.02em;">
                Perekaman Sampel Wajah Siswa — Kelas {{ $selectedKelas }}
            </h2>
            <p class="text-slate-500 mb-0" style="font-size: 13px;">
                Pilih siswa, posisikan wajah di dalam bingkai kamera, lalu klik tombol simpan sampel biometrik.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('face.scanner', ['kelas' => $selectedKelas]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 shadow-xs" style="font-size: 12px;">
                <i class="fa-solid fa-expand me-1.5"></i> Buka Scanner Kamera
            </a>
        </div>
    </div>

    <div class="row g-3">
        <!-- Panel Kiri: Kamera & Deteksi AI -->
        <div class="col-lg-6 col-xl-5">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" 
                             style="width: 32px; height: 32px; background: #e0e7ff;">
                            <i class="fa-solid fa-video" style="font-size: 13px;"></i>
                        </div>
                        <h6 class="fw-bold text-slate-800 mb-0" style="font-size: 13.5px;">Live Kamera Webcam</h6>
                    </div>
                    <button type="button" id="toggleCameraBtn" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-slate-600" style="font-size: 11px;">
                        <i class="fa-solid fa-rotate me-1"></i> Muat Ulang Kamera
                    </button>
                </div>

                <!-- Frame Kamera -->
                <div class="camera-container mb-3">
                    <video id="videoElement" autoplay playsinline muted></video>
                    <canvas id="canvasOverlay"></canvas>
                    <div class="oval-guide" id="ovalGuide"></div>
                    <div class="status-pill" id="statusPill">
                        <i class="fa-solid fa-spinner fa-spin me-1"></i> Memuat Model AI Browser...
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-grid gap-2">
                    <button type="button" id="captureBtn" class="btn btn-primary rounded-pill py-2.5 fw-semibold shadow-sm" style="font-size: 13px;" disabled>
                        <i class="fa-solid fa-camera me-1.5"></i> Ambil Sampel Wajah & Simpan
                    </button>
                    <small class="text-center text-slate-400" style="font-size: 11px;">
                        Pastikan cahaya cukup terang dan wajah menghadap lurus ke depan.
                    </small>
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Pemilihan Siswa & Profil Biometrik -->
        <div class="col-lg-6 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4 h-100">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-success" 
                         style="width: 32px; height: 32px; background: #dcfce7;">
                        <i class="fa-solid fa-user-check" style="font-size: 13px;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-slate-800 mb-0" style="font-size: 13.5px;">Target Siswa yang Direkam</h6>
                        <small class="text-slate-400" style="font-size: 11.5px;">Pilih data siswa rombel {{ $selectedKelas }}</small>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-sm-5">
                        <label for="selectFilterKelas" class="form-label small fw-semibold text-slate-700 mb-1" style="font-size: 11.5px;">
                            Pilih Rombel Kelas:
                        </label>
                        <select id="selectFilterKelas" class="form-select form-select-sm rounded-3 border-slate-300" onchange="changeKelas(this.value)">
                            @foreach($daftarKelas as $k)
                                <option value="{{ $k }}" {{ $selectedKelas == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-7">
                        <label for="selectTargetSiswa" class="form-label small fw-semibold text-slate-700 mb-1" style="font-size: 11.5px;">
                            Pilih Nama Siswa:
                        </label>
                        <select id="selectTargetSiswa" class="form-select form-select-sm rounded-3 border-slate-300" onchange="changeSiswa(this.value)">
                            @foreach($siswas as $s)
                                <option value="{{ $s->id }}" 
                                        data-nis="{{ $s->nis }}" 
                                        data-nama="{{ $s->nama }}"
                                        data-kelas="{{ $s->kelas }}"
                                        data-foto="{{ $s->foto_wajah ? asset('storage/' . $s->foto_wajah) : '' }}"
                                        data-enrolled="{{ !empty($s->face_descriptor) ? '1' : '0' }}"
                                        {{ optional($currentSiswa)->id == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama }} (NIS: {{ $s->nis }}) {{ !empty($s->face_descriptor) ? '✓' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Kartu Informasi Siswa Terpilih -->
                <div class="p-3.5 rounded-4 bg-slate-50 border border-slate-200 mb-3" id="siswaDetailCard">
                    <div class="d-flex align-items-center gap-3">
                        <div id="siswaAvatarContainer">
                            @if(optional($currentSiswa)->foto_wajah)
                                <img src="{{ asset('storage/' . $currentSiswa->foto_wajah) }}" id="siswaPreviewFoto" 
                                     alt="Foto Wajah" class="rounded-circle object-fit-cover border shadow-xs" width="60" height="60">
                            @else
                                <div id="siswaInitialAvatar" class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-xs"
                                     style="width: 60px; height: 60px; font-size: 20px; background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                                    {{ strtoupper(substr(optional($currentSiswa)->nama ?? 'S', 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold text-slate-800 mb-0" id="cardNamaSiswa">{{ optional($currentSiswa)->nama ?? '-' }}</h5>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge bg-light text-slate-600 border font-monospace" id="cardNisSiswa">NIS: {{ optional($currentSiswa)->nis ?? '-' }}</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" id="cardKelasSiswa">Kelas {{ optional($currentSiswa)->kelas ?? '-' }}</span>
                            </div>
                        </div>
                        <div>
                            <span class="badge rounded-pill px-2.5 py-1.5" id="cardStatusBadge"
                                  style="{{ !empty(optional($currentSiswa)->face_descriptor) ? 'background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;' : 'background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;' }}">
                                <i class="fa-solid {{ !empty(optional($currentSiswa)->face_descriptor) ? 'fa-circle-check' : 'fa-circle-xmark' }} me-1"></i>
                                <span id="cardStatusText">{{ !empty(optional($currentSiswa)->face_descriptor) ? 'Sudah Terekam' : 'Belum Terekam' }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Log Notifikasi Hasil Rekam -->
                <div id="enrollResultAlert" class="alert d-none rounded-3 p-3 mb-0" role="alert"></div>

                <!-- Petunjuk Perekaman Cepat -->
                <div class="mt-auto pt-3 border-top">
                    <h6 class="fw-bold text-slate-700 mb-1" style="font-size: 12px;">
                        <i class="fa-solid fa-lightbulb text-warning me-1"></i> Tips Perekaman Wajah Optimal:
                    </h6>
                    <ul class="text-slate-500 ps-3 mb-0" style="font-size: 11.5px; line-height: 1.6;">
                        <li>Posisikan kepala tepat di tengah bingkai oval hingga garis berubah menjadi <strong>warna hijau</strong>.</li>
                        <li>Jangan memakai kacamata hitam atau masker saat perekaman.</li>
                        <li>Setelah tombol diklik, AI akan mengekstrak 128 titik biometrik dan menyimpannya secara permanen.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Load face-api.js lokal dari public/js/ -->
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script>
    const MODEL_URL = '{{ asset("models/face") }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const SAVE_URL_TEMPLATE = '{{ route("face.enroll.save", ":id") }}';

    const video = document.getElementById('videoElement');
    const canvas = document.getElementById('canvasOverlay');
    const statusPill = document.getElementById('statusPill');
    const ovalGuide = document.getElementById('ovalGuide');
    const captureBtn = document.getElementById('captureBtn');
    const resultAlert = document.getElementById('enrollResultAlert');

    let currentSiswaId = '{{ optional($currentSiswa)->id }}';
    let isModelLoaded = false;
    let isDetecting = false;
    let latestDetection = null;
    let detectionInterval = null;

    // Inisialisasi: Muat model AI
    async function loadModels() {
        try {
            statusPill.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Memuat Model AI Biometrik...';
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            isModelLoaded = true;
            statusPill.innerHTML = '<i class="fa-solid fa-camera me-1"></i> Menyalakan Kamera...';
            startVideo();
        } catch (err) {
            console.error('Gagal memuat model:', err);
            statusPill.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger me-1"></i> Gagal Memuat Model AI';
        }
    }

    // Nyalakan Webcam
    function startVideo() {
        navigator.mediaDevices.getUserMedia({
            video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
            audio: false
        })
        .then(stream => {
            video.srcObject = stream;
        })
        .catch(err => {
            console.error('Gagal akses kamera:', err);
            statusPill.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger me-1"></i> Akses Kamera Ditolak / Tidak Ditemukan';
        });
    }

    // Saat video mulai diputar
    video.addEventListener('play', () => {
        const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
        faceapi.matchDimensions(canvas, displaySize);

        if (detectionInterval) clearInterval(detectionInterval);
        detectionInterval = setInterval(async () => {
            if (!isModelLoaded) return;

            const detection = await faceapi.detectSingleFace(
                video, 
                new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })
            ).withFaceLandmarks().withFaceDescriptor();

            latestDetection = detection;

            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height);

            if (detection) {
                ovalGuide.classList.add('detected');
                statusPill.innerHTML = '<i class="fa-solid fa-check text-success me-1"></i> Wajah Terdeteksi — Siap Rekam!';
                captureBtn.disabled = !currentSiswaId;

                // Gambar bounding box halus
                const resizedDetections = faceapi.resizeResults(detection, displaySize);
                faceapi.draw.drawDetections(canvas, resizedDetections);
            } else {
                ovalGuide.classList.remove('detected');
                statusPill.innerHTML = '<i class="fa-solid fa-arrows-to-circle text-warning me-1"></i> Posisikan Wajah di Tengah Bingkai';
                captureBtn.disabled = true;
            }
        }, 200);
    });

    // Ambil Sampel Wajah & Kirim ke Server
    captureBtn.addEventListener('click', async () => {
        if (!latestDetection || !currentSiswaId) return;

        captureBtn.disabled = true;
        captureBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5"></i> Menyimpan Vektor Biometrik...';

        // Ambil snapshot gambar dari video
        const offscreenCanvas = document.createElement('canvas');
        offscreenCanvas.width = video.videoWidth || 640;
        offscreenCanvas.height = video.videoHeight || 480;
        const ctx = offscreenCanvas.getContext('2d');
        // Mirroring save
        ctx.translate(offscreenCanvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, offscreenCanvas.width, offscreenCanvas.height);
        const dataUrl = offscreenCanvas.toDataURL('image/jpeg', 0.85);

        // Vektor 128 float
        const descriptorArray = Array.from(latestDetection.descriptor);

        try {
            const saveUrl = SAVE_URL_TEMPLATE.replace(':id', currentSiswaId);
            const response = await fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({
                    descriptor: descriptorArray,
                    image: dataUrl,
                })
            });

            const result = await response.json();

            if (result.success) {
                showAlert('success', '<i class="fa-solid fa-circle-check me-1"></i> ' + result.message);
                
                // Update badge & avatar di UI
                document.getElementById('cardStatusBadge').style.background = '#dcfce7';
                document.getElementById('cardStatusBadge').style.color = '#15803d';
                document.getElementById('cardStatusBadge').style.borderColor = '#bbf7d0';
                document.getElementById('cardStatusText').textContent = 'Sudah Terekam';

                if (result.siswa && result.siswa.foto_url) {
                    document.getElementById('siswaAvatarContainer').innerHTML = 
                        `<img src="${result.siswa.foto_url}" alt="Foto Wajah" class="rounded-circle object-fit-cover border shadow-xs" width="60" height="60">`;
                }

                // Update label option dropdown
                const opt = document.querySelector(`#selectTargetSiswa option[value="${currentSiswaId}"]`);
                if (opt && !opt.text.includes('✓')) {
                    opt.text += ' ✓';
                }
            } else {
                showAlert('danger', '<i class="fa-solid fa-triangle-exclamation me-1"></i> ' + (result.message || 'Gagal menyimpan wajah.'));
            }
        } catch (e) {
            console.error('Error saat menyimpan:', e);
            showAlert('danger', '<i class="fa-solid fa-triangle-exclamation me-1"></i> Terjadi kesalahan jaringan saat menyimpan.');
        } finally {
            captureBtn.disabled = false;
            captureBtn.innerHTML = '<i class="fa-solid fa-camera me-1.5"></i> Ambil Sampel Wajah & Simpan';
        }
    });

    function showAlert(type, htmlContent) {
        resultAlert.className = `alert alert-${type} rounded-3 p-3 mb-3 d-block`;
        resultAlert.innerHTML = htmlContent;
        setTimeout(() => {
            resultAlert.classList.add('d-none');
            resultAlert.classList.remove('d-block');
        }, 5000);
    }

    function changeKelas(kelas) {
        window.location.href = `{{ route('face.enroll') }}?kelas=${encodeURIComponent(kelas)}`;
    }

    function changeSiswa(siswaId) {
        currentSiswaId = siswaId;
        const opt = document.querySelector(`#selectTargetSiswa option[value="${siswaId}"]`);
        if (!opt) return;

        document.getElementById('cardNamaSiswa').textContent = opt.dataset.nama;
        document.getElementById('cardNisSiswa').textContent = 'NIS: ' + opt.dataset.nis;
        document.getElementById('cardKelasSiswa').textContent = 'Kelas ' + opt.dataset.kelas;

        const isEnrolled = opt.dataset.enrolled === '1';
        const badge = document.getElementById('cardStatusBadge');
        const badgeText = document.getElementById('cardStatusText');

        if (isEnrolled) {
            badge.style.background = '#dcfce7';
            badge.style.color = '#15803d';
            badge.style.borderColor = '#bbf7d0';
            badgeText.textContent = 'Sudah Terekam';
        } else {
            badge.style.background = '#f1f5f9';
            badge.style.color = '#64748b';
            badge.style.borderColor = '#e2e8f0';
            badgeText.textContent = 'Belum Terekam';
        }

        const avatarContainer = document.getElementById('siswaAvatarContainer');
        if (opt.dataset.foto) {
            avatarContainer.innerHTML = `<img src="${opt.dataset.foto}" alt="Foto" class="rounded-circle object-fit-cover border shadow-xs" width="60" height="60">`;
        } else {
            const initial = opt.dataset.nama ? opt.dataset.nama.charAt(0).toUpperCase() : 'S';
            avatarContainer.innerHTML = `<div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-xs" style="width: 60px; height: 60px; font-size: 20px; background: linear-gradient(135deg, #3b82f6, #1d4ed8);">${initial}</div>`;
        }
    }

    document.getElementById('toggleCameraBtn').addEventListener('click', () => {
        startVideo();
    });

    window.addEventListener('load', loadModels);
</script>
@endpush
@endsection
