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

        html, body {
            background-color: #000000;
            color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            overflow: hidden;
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            user-select: none;
        }

        /* Camera Viewport */
        .camera-viewport {
            flex: 1;
            position: relative;
            background: #000000;
            overflow: hidden;
            width: 100%;
            height: 100%;
        }

        #scannerVideo {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* Default mirror selfie */
            background: #000000;
            z-index: 1;
        }

        #scannerCanvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 2;
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
            z-index: 3;
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
            border: 2.5px dashed rgba(255, 255, 255, 0.45);
            border-radius: 50% 50% 48% 48% / 60% 60% 40% 40%;
            pointer-events: none;
            z-index: 4;
            transition: all 0.25s ease;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.38);
        }

        .face-guide-frame.matched {
            border: 3.5px solid #10b981;
            box-shadow: 0 0 25px #10b981, 0 0 0 9999px rgba(0, 0, 0, 0.45);
        }

        .guide-text {
            position: absolute;
            bottom: -32px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.88);
            padding: 5px 14px;
            border-radius: 14px;
            font-size: 11.5px;
            font-weight: 600;
            color: #e0f2fe;
            white-space: nowrap;
            border: 1px solid rgba(255, 255, 255, 0.18);
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
            background: rgba(15, 23, 42, 0.85);
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
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 11.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }
        .control-btn:active {
            background: rgba(255, 255, 255, 0.25);
        }

        /* Non-blocking Floating AI Status Badge */
        .ai-status-badge {
            position: absolute;
            top: 60px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(56, 189, 248, 0.35);
            border-radius: 18px;
            padding: 6px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 11;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
        }

        .ai-status-badge.hidden {
            opacity: 0;
            pointer-events: none;
            transform: translate(-50%, -15px);
        }

        .badge-spinner {
            width: 14px;
            height: 14px;
            border: 2px solid rgba(56, 189, 248, 0.25);
            border-top-color: #38bdf8;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .badge-text {
            font-size: 11.5px;
            font-weight: 600;
            color: #e0f2fe;
            white-space: nowrap;
        }

        /* Camera Error Overlay Card */
        .camera-error-card {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(15, 23, 42, 0.96);
            border: 1.5px solid #ef4444;
            border-radius: 20px;
            padding: 22px;
            width: 86%;
            max-width: 320px;
            text-align: center;
            z-index: 25;
            display: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
        }

        .camera-error-card h4 {
            font-size: 15px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .camera-error-card p {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
            margin-bottom: 16px;
        }

        .retry-cam-btn {
            background: #0284c7;
            border: none;
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
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

        .status-pill {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 4px 10px;
            border-radius: 14px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 4px;
            background: #f59e0b;
        }
        .status-dot.ready {
            background: #10b981;
            box-shadow: 0 0 6px #10b981;
        }
    </style>
</head>
<body>

    <!-- Camera Viewport -->
    <main class="camera-viewport" id="viewport">
        <video id="scannerVideo" autoplay playsinline webkit-playsinline muted></video>
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

        <!-- Floating AI Status Badge (Non-blocking) -->
        <div class="ai-status-badge" id="aiStatusBadge">
            <div class="badge-spinner" id="badgeSpinner"></div>
            <div class="badge-text" id="badgeText">Memuat AI Biometrik...</div>
        </div>

        <!-- Camera Error Overlay Card -->
        <div class="camera-error-card" id="cameraErrorCard">
            <div style="font-size: 32px; margin-bottom: 8px;">📷</div>
            <h4 id="errorTitle">Kamera Belum Aktif</h4>
            <p id="errorDesc">Sentuh tombol di bawah untuk mengizinkan dan menghubungkan kamera.</p>
            <button type="button" class="retry-cam-btn" id="retryCamBtn">Hubungkan Kamera</button>
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
    </main>

    <!-- Bottom Footer Stats -->
    <footer class="mobile-footer">
        <div class="stat-item">
            <span class="stat-label">Hadir:</span>
            <span class="stat-value" id="hadirCount">{{ $hadirTodayCount }}</span>
            <span class="stat-label">Siswa</span>
        </div>
        <div class="status-pill">
            <span class="status-dot" id="statusDot"></span>
            <span id="statusText">Memulai...</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Terdaftar:</span>
            <span class="stat-value" id="enrolledCount" style="color: #a7f3d0;">{{ $enrolledCount }}</span>
        </div>
    </footer>

    <!-- Face API Library -->
    <script src="{{ asset('js/face-api.min.js') }}"></script>
    <script>
        const MODEL_URL = '{{ asset("models/face") }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';

        // Ambil token dari query param URL jika dibuka via React Native WebView
        const urlParams = new URLSearchParams(window.location.search);
        const authToken = urlParams.get('token') || '';
        const tokenQuery = authToken ? ('?token=' + encodeURIComponent(authToken)) : '';

        const STUDENTS_API_URL = '{{ route("face.mobile.enrolledStudents") }}' + tokenQuery;
        const RECORD_ATTENDANCE_URL = '{{ route("face.mobile.recordAttendance") }}' + tokenQuery;

        const viewport = document.getElementById('viewport');
        const video = document.getElementById('scannerVideo');
        const canvas = document.getElementById('scannerCanvas');
        const statusDot = document.getElementById('statusDot');
        const statusText = document.getElementById('statusText');
        const faceGuide = document.getElementById('faceGuide');
        const guideText = document.getElementById('guideText');
        const aiStatusBadge = document.getElementById('aiStatusBadge');
        const badgeSpinner = document.getElementById('badgeSpinner');
        const badgeText = document.getElementById('badgeText');
        const cameraErrorCard = document.getElementById('cameraErrorCard');
        const errorTitle = document.getElementById('errorTitle');
        const errorDesc = document.getElementById('errorDesc');
        const retryCamBtn = document.getElementById('retryCamBtn');
        const matchPopup = document.getElementById('matchPopup');
        const matchAvatar = document.getElementById('matchAvatar');
        const matchInitial = document.getElementById('matchInitial');
        const matchName = document.getElementById('matchName');
        const matchMeta = document.getElementById('matchMeta');
        const matchTime = document.getElementById('matchTime');
        const hadirCountEl = document.getElementById('hadirCount');
        const enrolledCountEl = document.getElementById('enrolledCount');
        const flipCameraBtn = document.getElementById('flipCameraBtn');
        const audioToggleBtn = document.getElementById('audioToggleBtn');
        const audioIcon = document.getElementById('audioIcon');

        let isFrontCamera = true;
        let isAudioEnabled = true;
        let isProcessing = false;
        let isModelsReady = false;
        let isDetectionLoopRunning = false;
        let faceMatcher = null;
        let enrolledStudentsMap = {};
        let currentStream = null;
        let lastScanned = {}; // Cooldown map
        let popupTimer = null;
        let hadirCount = {{ $hadirTodayCount }};
        const currentThreshold = 0.44; // Ambang batas ketat & presisi

        // 1. WebAudio Chime
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

        // 2. Start Camera (LANGSUNG DIAKTIFKAN DI AWAL AGAR TIDAK BLANK HITAM)
        async function startCamera() {
            cameraErrorCard.style.display = 'none';
            statusText.innerText = 'Buka Kamera...';

            if (currentStream) {
                try {
                    currentStream.getTracks().forEach(t => t.stop());
                } catch (e) {}
                currentStream = null;
            }

            // Atribut wajib untuk Android WebView / iOS inline video
            video.setAttribute('autoplay', '');
            video.setAttribute('muted', '');
            video.setAttribute('playsinline', '');
            video.setAttribute('webkit-playsinline', '');
            video.muted = true;
            video.defaultMuted = true;
            video.playsInline = true;

            let stream = null;

            // Percobaan 1: Resolusi ideal HD
            const constraints1 = {
                video: {
                    facingMode: isFrontCamera ? 'user' : 'environment',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            };

            // Percobaan 2: FacingMode dasar
            const constraints2 = {
                video: {
                    facingMode: isFrontCamera ? 'user' : 'environment'
                },
                audio: false
            };

            // Percobaan 3: Kamera paling dasar (fallback)
            const constraints3 = {
                video: true,
                audio: false
            };

            try {
                stream = await navigator.mediaDevices.getUserMedia(constraints1);
            } catch (err1) {
                console.warn('Constraints 1 failed, trying constraints 2...', err1);
                try {
                    stream = await navigator.mediaDevices.getUserMedia(constraints2);
                } catch (err2) {
                    console.warn('Constraints 2 failed, trying constraints 3...', err2);
                    try {
                        stream = await navigator.mediaDevices.getUserMedia(constraints3);
                    } catch (err3) {
                        console.error('Semua constraints gagal:', err3);
                        showCameraError(err3);
                        return;
                    }
                }
            }

            if (!stream) {
                showCameraError(new Error('Kamera tidak menghasilkan feed video'));
                return;
            }

            currentStream = stream;
            video.srcObject = stream;
            video.style.transform = isFrontCamera ? 'scaleX(-1)' : 'none';

            // Panggil play() langsung tanpa menunggu onloadedmetadata agar tidak deadlock di WebView
            try {
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    await playPromise;
                }
            } catch (playErr) {
                console.warn('Direct video.play() warning:', playErr);
            }

            video.onloadedmetadata = () => {
                video.play().catch(e => console.warn(e));
                updateCanvasDimensions();
                checkAndStartDetection();
            };

            video.onplaying = () => {
                updateCanvasDimensions();
                if (isModelsReady) {
                    statusDot.classList.add('ready');
                    statusText.innerText = 'Kamera Siap';
                } else {
                    statusText.innerText = 'Kamera Aktif';
                }
            };
        }

        function showCameraError(err) {
            cameraErrorCard.style.display = 'block';
            errorTitle.innerText = 'Akses Kamera Terkendala';
            errorDesc.innerText = 'Silakan izinkan kamera pada aplikasi SI-KASEP (' + (err.message || err.name) + ')';
            statusText.innerText = 'Kamera Gagal';
            statusDot.classList.remove('ready');
        }

        function updateCanvasDimensions() {
            if (!video.videoWidth || !video.videoHeight) return;
            canvas.width = video.clientWidth || 360;
            canvas.height = video.clientHeight || 480;
            faceapi.matchDimensions(canvas, {
                width: video.clientWidth || 360,
                height: video.clientHeight || 480
            });
        }

        // Tap di viewport untuk memicu play jika terpause oleh OS
        viewport.addEventListener('click', () => {
            if (video && video.paused && video.srcObject) {
                video.play().catch(e => console.warn(e));
            }
        });

        // 3. Load Model Face API & Data Wajah Siswa
        async function loadModels() {
            try {
                badgeText.innerText = 'Memuat AI Biometrik...';

                // Hanya model yang tersedia di folder public/models/face (TinyFaceDetector, Landmark68, FaceRecognition)
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

                badgeText.innerText = 'Sinkronisasi Wajah Siswa...';
                await loadEnrolledStudents();

                isModelsReady = true;
                badgeSpinner.style.display = 'none';
                badgeText.innerText = 'AI Siap!';

                // Sembunyikan badge status AI setelah 2 detik
                setTimeout(() => {
                    aiStatusBadge.classList.add('hidden');
                }, 1800);

                statusDot.classList.add('ready');
                statusText.innerText = 'Kamera Siap';

                checkAndStartDetection();
            } catch (err) {
                console.error('Gagal memuat model:', err);
                badgeSpinner.style.display = 'none';
                badgeText.innerText = 'AI Terkendala: ' + (err.message || 'Error');
                statusText.innerText = 'AI Error';
            }
        }

        // 4. Fetch Enrolled Students
        async function loadEnrolledStudents() {
            try {
                const res = await fetch(STUDENTS_API_URL, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                if (!data.success || !data.students || data.students.length === 0) {
                    enrolledCountEl.innerText = '0';
                    return;
                }

                enrolledCountEl.innerText = data.students.length;

                const labeledDescriptors = [];
                data.students.forEach(s => {
                    if (s.descriptor && Array.isArray(s.descriptor) && s.descriptor.length === 128) {
                        enrolledStudentsMap[s.id] = s;
                        const descriptorArray = new Float32Array(s.descriptor);
                        labeledDescriptors.push(new faceapi.LabeledFaceDescriptors(String(s.id), [descriptorArray]));
                    }
                });

                if (labeledDescriptors.length > 0) {
                    faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, currentThreshold);
                }
            } catch (err) {
                console.warn('Gagal memuat data siswa terdaftar:', err);
            }
        }

        // 5. Detection Loop
        function checkAndStartDetection() {
            if (isDetectionLoopRunning || !isModelsReady || !faceMatcher) return;
            isDetectionLoopRunning = true;
            startDetectionLoop();
        }

        function startDetectionLoop() {
            const detectorOptions = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });

            setInterval(async () => {
                if (isProcessing || !faceMatcher || video.paused || video.ended || !video.videoWidth) return;

                isProcessing = true;
                try {
                    const detection = await faceapi
                        .detectSingleFace(video, detectorOptions)
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (detection) {
                        const match = faceMatcher.findBestMatch(detection.descriptor);

                        if (match.label !== 'unknown' && match.distance <= currentThreshold) {
                            const siswaId = parseInt(match.label);
                            const now = Date.now();

                            // 5-detik cooldown per siswa agar tidak spam attendance
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
                    // Ignore transient cycle error
                } finally {
                    isProcessing = false;
                }
            }, 180);
        }

        // 6. Handle Matched Student
        async function onStudentMatched(siswaId, distance) {
            const student = enrolledStudentsMap[siswaId];
            if (!student) return;

            faceGuide.classList.add('matched');
            guideText.innerText = `Terverifikasi: ${student.nama}`;
            playSuccessChime();

            showHeroPopup(student);

            // Snapshot capture (low-res jpeg base64)
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

            try {
                const res = await fetch(RECORD_ATTENDANCE_URL, {
                    method: 'POST',
                    credentials: 'same-origin',
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

        // 7. Show Hero Popup
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

        // 8. Event Listeners
        flipCameraBtn.addEventListener('click', () => {
            isFrontCamera = !isFrontCamera;
            startCamera();
        });

        retryCamBtn.addEventListener('click', () => {
            startCamera();
        });

        audioToggleBtn.addEventListener('click', () => {
            isAudioEnabled = !isAudioEnabled;
            audioIcon.innerText = isAudioEnabled ? '🔔' : '🔕';
            audioToggleBtn.innerHTML = `<span>${audioIcon.innerText}</span> Suara ${isAudioEnabled ? 'ON' : 'OFF'}`;
        });

        // 9. Boot: Nyalakan kamera & download model secara paralel
        window.addEventListener('DOMContentLoaded', () => {
            startCamera();
            loadModels();
        });
    </script>
</body>
</html>
