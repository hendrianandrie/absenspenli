<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Presensi Wajah — SI-KASEP Mobile</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: #070b13;
            color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            overflow: hidden;
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            user-select: none;
        }

        /* Top Header Bar */
        .mobile-header {
            background: linear-gradient(135deg, #0369a1, #0284c7);
            padding: 12px 16px;
            padding-top: max(12px, env(safe-area-inset-top));
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
            z-index: 20;
            position: relative;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-logo {
            width: 36px;
            height: 36px;
            border-radius: 18px;
            background: #ffffff;
            padding: 2px;
            border: 1.5px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .header-title-box h1 {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.6px;
            line-height: 1.1;
        }

        .header-title-box p {
            font-size: 10.5px;
            color: #bae6fd;
            font-weight: 700;
            letter-spacing: 0.4px;
            margin-top: 2px;
        }

        .header-status-pill {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 4px;
            background: #f59e0b;
        }
        .status-dot.ready {
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
        }

        /* Camera Viewport */
        .camera-viewport {
            flex: 1;
            position: relative;
            background: #000000;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #scannerVideo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* Default selfie mirror */
        }

        #scannerCanvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 5;
        }

        /* Laser Scan Beam */
        .scan-laser {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #38bdf8, #818cf8, transparent);
            box-shadow: 0 0 16px #38bdf8;
            animation: laserScan 2.2s infinite linear;
            pointer-events: none;
            z-index: 6;
        }

        @keyframes laserScan {
            0% { top: 5%; opacity: 0; }
            15% { opacity: 1; }
            85% { opacity: 1; }
            100% { top: 95%; opacity: 0; }
        }

        /* Face Guide Oval */
        .face-guide-frame {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 230px;
            height: 290px;
            border: 2.5px dashed rgba(255, 255, 255, 0.4);
            border-radius: 50% 50% 48% 48% / 60% 60% 40% 40%;
            pointer-events: none;
            z-index: 7;
            transition: all 0.25s ease;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.35);
        }

        .face-guide-frame.matched {
            border: 3.5px solid #10b981;
            box-shadow: 0 0 25px #10b981, 0 0 0 9999px rgba(0, 0, 0, 0.4);
        }

        .guide-text {
            position: absolute;
            bottom: -32px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.85);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            color: #e0f2fe;
            white-space: nowrap;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        /* Floating Camera Controls */
        .controls-pill {
            position: absolute;
            top: 14px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(15, 23, 42, 0.82);
            padding: 6px 12px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            z-index: 10;
            backdrop-filter: blur(8px);
        }

        .control-btn {
            background: rgba(255, 255, 255, 0.12);
            border: none;
            color: #ffffff;
            padding: 6px 10px;
            border-radius: 16px;
            font-size: 11px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }
        .control-btn:active {
            background: rgba(255, 255, 255, 0.25);
        }

        /* Matched Student Hero Popup */
        .match-popup {
            position: absolute;
            bottom: 74px;
            left: 16px;
            right: 16px;
            background: rgba(15, 23, 42, 0.95);
            border: 2px solid #10b981;
            border-radius: 18px;
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.35);
            transform: translateY(120px);
            opacity: 0;
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 15;
            backdrop-filter: blur(12px);
        }

        .match-popup.show {
            transform: translateY(0);
            opacity: 1;
        }

        .match-avatar {
            width: 52px;
            height: 52px;
            border-radius: 26px;
            background: #0284c7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            border: 2px solid #ffffff;
            overflow: hidden;
            flex-shrink: 0;
        }

        .match-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .match-info {
            flex: 1;
            min-width: 0;
        }

        .match-name {
            font-size: 15px;
            font-weight: 800;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .match-meta {
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .match-badge {
            background: #059669;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 8px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            line-height: 1.2;
        }

        .match-time {
            font-size: 9.5px;
            font-weight: 600;
            opacity: 0.9;
        }

        /* Bottom Status Footer */
        .mobile-footer {
            background: #0b1324;
            padding: 10px 16px;
            padding-bottom: max(10px, env(safe-area-inset-bottom));
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 20;
        }

        .stat-item {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }

        .stat-label {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 600;
        }

        .stat-value {
            font-size: 15px;
            font-weight: 800;
            color: #38bdf8;
        }

        .refresh-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 600;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(7, 11, 19, 0.92);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 30;
            padding: 24px;
            text-align: center;
        }

        .spinner {
            width: 44px;
            height: 44px;
            border: 4px solid rgba(56, 189, 248, 0.2);
            border-top-color: #38bdf8;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-bottom: 16px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-title {
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
        }

        .loading-desc {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 6px;
            max-width: 260px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="mobile-header">
        <div class="header-brand">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="header-logo" onerror="this.style.display='none'">
            <div class="header-title-box">
                <h1>PRESENSI WAJAH</h1>
                <p>Petugas Piket • SMPN 5 Ciamis</p>
            </div>
        </div>
        <div class="header-status-pill">
            <span class="status-dot" id="statusDot"></span>
            <span id="statusText">Memuat AI...</span>
        </div>
    </header>

    <!-- Camera Viewport -->
    <main class="camera-viewport">
        <video id="scannerVideo" playsinline autoplay muted></video>
        <canvas id="scannerCanvas"></canvas>

        <div class="scan-laser"></div>

        <!-- Face Silhouette Guide -->
        <div class="face-guide-frame" id="faceGuide">
            <div class="guide-text" id="guideText">Posisikan Wajah di Sini</div>
        </div>

        <!-- Controls Pill -->
        <div class="controls-pill">
            <button class="control-btn" id="flipCameraBtn">
                <span>🔄</span> Ganti Kamera
            </button>
            <button class="control-btn" id="audioToggleBtn">
                <span id="audioIcon">🔔</span> Suara ON
            </button>
        </div>

        <!-- Match Popup -->
        <div class="match-popup" id="matchPopup">
            <div class="match-avatar" id="matchAvatar">
                <span id="matchInitial">S</span>
            </div>
            <div class="match-info">
                <div class="match-name" id="matchName">Nama Siswa</div>
                <div class="match-meta" id="matchMeta">NIS: - • Kelas -</div>
            </div>
            <div class="match-badge">
                <span>HADIR</span>
                <span class="match-time" id="matchTime">--:--</span>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="spinner"></div>
            <div class="loading-title" id="loadingTitle">Memuat Model Pengenalan Wajah</div>
            <div class="loading-desc" id="loadingDesc">Mohon tunggu sebentar, sistem sedang mengunduh neural network dan database wajah siswa...</div>
        </div>
    </main>

    <!-- Bottom Footer Stats -->
    <footer class="mobile-footer">
        <div class="stat-item">
            <span class="stat-label">Hadir Hari Ini:</span>
            <span class="stat-value" id="hadirCount">{{ $hadirTodayCount }}</span>
            <span class="stat-label">Siswa</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Terdaftar:</span>
            <span class="stat-value" id="enrolledCount" style="color: #a7f3d0;">{{ $enrolledCount }}</span>
        </div>
        <button class="refresh-btn" onclick="location.reload()">Muat Ulang</button>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('js/face-api.min.js') }}"></script>
    <script>
        const MODEL_URL = '{{ asset("models/face") }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const STUDENTS_API_URL = '{{ route("face.enrolledStudents") }}';
        const RECORD_ATTENDANCE_URL = '{{ route("face.recordAttendance") }}';

        const video = document.getElementById('scannerVideo');
        const canvas = document.getElementById('scannerCanvas');
        const statusDot = document.getElementById('statusDot');
        const statusText = document.getElementById('statusText');
        const faceGuide = document.getElementById('faceGuide');
        const guideText = document.getElementById('guideText');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const loadingTitle = document.getElementById('loadingTitle');
        const loadingDesc = document.getElementById('loadingDesc');
        const matchPopup = document.getElementById('matchPopup');
        const matchAvatar = document.getElementById('matchAvatar');
        const matchInitial = document.getElementById('matchInitial');
        const matchName = document.getElementById('matchName');
        const matchMeta = document.getElementById('matchMeta');
        const matchTime = document.getElementById('matchTime');
        const hadirCountEl = document.getElementById('hadirCount');
        const flipCameraBtn = document.getElementById('flipCameraBtn');
        const audioToggleBtn = document.getElementById('audioToggleBtn');
        const audioIcon = document.getElementById('audioIcon');

        let isFrontCamera = true;
        let isAudioEnabled = true;
        let isProcessing = false;
        let faceMatcher = null;
        let enrolledStudentsMap = {};
        let currentStream = null;
        let lastScanned = {}; // Cooldown map
        let popupTimer = null;
        let hadirCount = {{ $hadirTodayCount }};

        // WebAudio Chime
        function playSuccessChime() {
            if (!isAudioEnabled) return;
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(659.25, ctx.currentTime);
                gain1.gain.setValueAtTime(0.2, ctx.currentTime);
                gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start();
                osc1.stop(ctx.currentTime + 0.25);

                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, ctx.currentTime + 0.1);
                gain2.gain.setValueAtTime(0.25, ctx.currentTime + 0.1);
                gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.45);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(ctx.currentTime + 0.1);
                osc2.stop(ctx.currentTime + 0.45);
            } catch (e) {
                console.warn('Audio play error:', e);
            }
        }

        // Load Face API Models
        async function loadModels() {
            try {
                loadingTitle.innerText = 'Mengunduh Neural Network...';
                await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

                loadingTitle.innerText = 'Mengunduh Sampel Wajah Siswa...';
                await loadEnrolledStudents();

                loadingOverlay.style.display = 'none';
                statusDot.classList.add('ready');
                statusText.innerText = 'Kamera Aktif';

                startCamera();
            } catch (err) {
                console.error('Gagal memuat model:', err);
                loadingTitle.innerText = 'Koneksi Bermasalah';
                loadingDesc.innerText = 'Gagal mengunduh data pengenalan wajah: ' + err.message;
            }
        }

        // Fetch Enrolled Students
        async function loadEnrolledStudents() {
            const res = await fetch(STUDENTS_API_URL, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (!data.success || !data.students || data.students.length === 0) {
                statusText.innerText = 'Belum Ada Wajah Terdaftar';
                return;
            }

            const labeledDescriptors = [];
            data.students.forEach(s => {
                if (s.descriptor && Array.isArray(s.descriptor)) {
                    enrolledStudentsMap[s.id] = s;
                    const descriptorArray = new Float32Array(s.descriptor);
                    labeledDescriptors.push(new faceapi.LabeledFaceDescriptors(String(s.id), [descriptorArray]));
                }
            });

            if (labeledDescriptors.length > 0) {
                // Ambang batas akurat 0.44
                faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.44);
            }
        }

        // Start Camera
        async function startCamera() {
            if (currentStream) {
                currentStream.getTracks().forEach(t => t.stop());
            }

            const constraints = {
                video: {
                    facingMode: isFrontCamera ? 'user' : 'environment',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            };

            try {
                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                currentStream = stream;
                video.srcObject = stream;
                video.style.transform = isFrontCamera ? 'scaleX(-1)' : 'none';

                video.onloadedmetadata = () => {
                    video.play();
                    startDetectionLoop();
                };
            } catch (err) {
                alert('Izin kamera ditolak atau kamera tidak ditemukan: ' + err.message);
            }
        }

        // Detection Loop
        function startDetectionLoop() {
            setInterval(async () => {
                if (isProcessing || !faceMatcher || video.paused || video.ended) return;

                isProcessing = true;
                try {
                    // Detect single face with landmarks & descriptor
                    const detection = await faceapi
                        .detectSingleFace(video)
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (detection) {
                        const match = faceMatcher.findBestMatch(detection.descriptor);

                        if (match.label !== 'unknown') {
                            const siswaId = parseInt(match.label);
                            const now = Date.now();

                            // 5-detik cooldown per siswa agar tidak double-scan
                            if (!lastScanned[siswaId] || (now - lastScanned[siswaId] > 5000)) {
                                lastScanned[siswaId] = now;
                                onStudentMatched(siswaId, match.distance);
                            }
                        } else {
                            guideText.innerText = 'Wajah Belum Terdaftar';
                            faceGuide.classList.remove('matched');
                        }
                    } else {
                        guideText.innerText = 'Posisikan Wajah di Sini';
                        faceGuide.classList.remove('matched');
                    }
                } catch (e) {
                    console.warn('Detection cycle error:', e);
                } finally {
                    isProcessing = false;
                }
            }, 180);
        }

        // Handle Matched Student
        async function onStudentMatched(siswaId, distance) {
            const student = enrolledStudentsMap[siswaId];
            if (!student) return;

            // Visual indicator
            faceGuide.classList.add('matched');
            guideText.innerText = `Terverifikasi: ${student.nama}`;
            playSuccessChime();

            // Show Popup
            showHeroPopup(student);

            // Capture snapshot frame (low-res jpeg base64)
            let imageBase64 = null;
            try {
                const snapCanvas = document.createElement('canvas');
                snapCanvas.width = 320;
                snapCanvas.height = 240;
                const ctx = snapCanvas.getContext('2d');
                if (isFrontCamera) {
                    ctx.translate(320, 0);
                    ctx.scale(-1, 1);
                }
                ctx.drawImage(video, 0, 0, 320, 240);
                imageBase64 = snapCanvas.toDataURL('image/jpeg', 0.6);
            } catch (e) {}

            // Send record to backend
            try {
                const res = await fetch(RECORD_ATTENDANCE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        siswa_id: student.id,
                        distance: distance,
                        image: imageBase64
                    })
                });
                const resData = await res.json();
                if (resData.success && !resData.already_hadir) {
                    hadirCount++;
                    hadirCountEl.innerText = hadirCount;
                }
            } catch (err) {
                console.error('Gagal mencatat presensi:', err);
            }
        }

        // Show Hero Popup
        function showHeroPopup(student) {
            clearTimeout(popupTimer);

            matchName.innerText = student.nama;
            matchMeta.innerText = `NIS: ${student.nis} • Kelas ${student.kelas}`;

            const now = new Date();
            const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
            matchTime.innerText = `${timeStr} WIB`;

            if (student.foto_url) {
                matchAvatar.innerHTML = `<img src="${student.foto_url}" alt="${student.nama}">`;
            } else {
                matchAvatar.innerHTML = `<span>${student.nama.charAt(0).toUpperCase()}</span>`;
            }

            matchPopup.classList.add('show');

            popupTimer = setTimeout(() => {
                matchPopup.classList.remove('show');
                faceGuide.classList.remove('matched');
            }, 3500);
        }

        // Flip Camera Event
        flipCameraBtn.addEventListener('click', () => {
            isFrontCamera = !isFrontCamera;
            startCamera();
        });

        // Audio Toggle Event
        audioToggleBtn.addEventListener('click', () => {
            isAudioEnabled = !isAudioEnabled;
            audioIcon.innerText = isAudioEnabled ? '🔔' : '🔕';
            audioToggleBtn.innerHTML = `<span>${audioIcon.innerText}</span> Suara ${isAudioEnabled ? 'ON' : 'OFF'}`;
        });

        // Start loading
        window.addEventListener('DOMContentLoaded', loadModels);
    </script>
</body>
</html>
