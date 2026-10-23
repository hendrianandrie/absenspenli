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
            transform: scaleX(-1);
            z-index: 1;
            opacity: 1;
        }

        #cameraCanvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            pointer-events: none;
            z-index: 2;
        }

        #scannerCanvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 3;
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
            z-index: 4;
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
            z-index: 5;
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

        /* Live Diagnostics Bar */
        .diagnostics-bar {
            position: absolute;
            top: 56px;
            left: 14px;
            right: 14px;
            background: rgba(15, 23, 42, 0.92);
            border: 1px solid rgba(56, 189, 248, 0.35);
            border-radius: 12px;
            padding: 7px 12px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace;
            font-size: 11px;
            color: #bae6fd;
            z-index: 12;
            line-height: 1.35;
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
        }

        .diag-title {
            font-weight: 700;
            color: #38bdf8;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .diag-desc {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Center Tap-to-Start Button (User gesture fallback) */
        .tap-start-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #0284c7;
            color: #ffffff;
            border: 2px solid #ffffff;
            border-radius: 24px;
            padding: 12px 22px;
            font-weight: 800;
            font-size: 13.5px;
            z-index: 20;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.7);
            cursor: pointer;
            display: none;
            text-align: center;
        }

        .tap-start-btn:active {
            background: #0369a1;
            transform: translate(-50%, -50%) scale(0.97);
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
        <!-- Video Source Element -->
        <video id="scannerVideo" autoplay playsinline webkit-playsinline muted></video>
        <!-- 2D Canvas Fallback Renderer (Ensures video frames render even on buggy Android WebView GPU layers) -->
        <canvas id="cameraCanvas"></canvas>
        <!-- Detection Canvas for Face Landmarks & Highlights -->
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

        <!-- Diagnostics Status Bar -->
        <div class="diagnostics-bar" id="diagBar">
            <div class="diag-title">
                <span id="diagTitle">Menyiapkan Kamera...</span>
                <span id="diagFps" style="font-size: 9.5px; opacity: 0.8;"></span>
            </div>
            <div class="diag-desc" id="diagDesc">Memeriksa izin & perangkat kamera...</div>
        </div>

        <!-- User Gesture Tap-to-Start Button -->
        <button class="tap-start-btn" id="tapStartBtn">
            📸 Ketuk untuk Buka Kamera
        </button>

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

        const urlParams = new URLSearchParams(window.location.search);
        const authToken = urlParams.get('token') || '';
        const tokenQuery = authToken ? ('?token=' + encodeURIComponent(authToken)) : '';

        const STUDENTS_API_URL = '{{ route("face.mobile.enrolledStudents") }}' + tokenQuery;
        const RECORD_ATTENDANCE_URL = '{{ route("face.mobile.recordAttendance") }}' + tokenQuery;

        const viewport = document.getElementById('viewport');
        const video = document.getElementById('scannerVideo');
        const cameraCanvas = document.getElementById('cameraCanvas');
        const scannerCanvas = document.getElementById('scannerCanvas');
        const statusDot = document.getElementById('statusDot');
        const statusText = document.getElementById('statusText');
        const faceGuide = document.getElementById('faceGuide');
        const guideText = document.getElementById('guideText');
        const diagTitle = document.getElementById('diagTitle');
        const diagDesc = document.getElementById('diagDesc');
        const diagFps = document.getElementById('diagFps');
        const tapStartBtn = document.getElementById('tapStartBtn');
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
        let lastScanned = {};
        let popupTimer = null;
        let hadirCount = {{ $hadirTodayCount }};
        const currentThreshold = 0.44;

        function updateDiag(title, desc, color) {
            if (diagTitle) {
                diagTitle.innerText = title;
                if (color) diagTitle.style.color = color;
            }
            if (diagDesc) diagDesc.innerText = desc;
        }

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
                console.warn('Audio error:', e);
            }
        }

        // 2. Start Camera dengan fallback bertingkat
        async function startCamera() {
            updateDiag('Meminta Izin Kamera...', 'Menghubungkan ke sensor kamera...', '#f59e0b');
            statusText.innerText = 'Buka Kamera...';

            if (currentStream) {
                try {
                    currentStream.getTracks().forEach(t => t.stop());
                } catch (e) {}
                currentStream = null;
            }

            video.setAttribute('autoplay', '');
            video.setAttribute('muted', '');
            video.setAttribute('playsinline', '');
            video.setAttribute('webkit-playsinline', '');
            video.muted = true;
            video.defaultMuted = true;
            video.playsInline = true;

            let stream = null;

            // Percobaan 1: facingMode dasar (paling kompatibel di Android WebView)
            const c1 = {
                video: {
                    facingMode: isFrontCamera ? 'user' : 'environment'
                },
                audio: false
            };

            // Percobaan 2: generic video: true
            const c2 = { video: true, audio: false };

            // Percobaan 3: ideal 640x480
            const c3 = {
                video: {
                    facingMode: isFrontCamera ? 'user' : 'environment',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            };

            try {
                stream = await navigator.mediaDevices.getUserMedia(c1);
            } catch (e1) {
                console.warn('c1 failed:', e1);
                try {
                    stream = await navigator.mediaDevices.getUserMedia(c2);
                } catch (e2) {
                    console.warn('c2 failed:', e2);
                    try {
                        stream = await navigator.mediaDevices.getUserMedia(c3);
                    } catch (e3) {
                        console.error('All constraints failed:', e3);
                        updateDiag('Kamera Gagal Dibuka', e3.name + ': ' + e3.message, '#ef4444');
                        statusText.innerText = 'Kamera Gagal';
                        tapStartBtn.style.display = 'block';
                        return;
                    }
                }
            }

            if (!stream) {
                updateDiag('Kamera Tidak Ditemukan', 'Tidak ada aliran video dari sensor.', '#ef4444');
                tapStartBtn.style.display = 'block';
                return;
            }

            currentStream = stream;
            video.srcObject = stream;
            video.style.transform = isFrontCamera ? 'scaleX(-1)' : 'none';

            const track = stream.getVideoTracks()[0];
            const trackInfo = track ? (track.label || 'Kamera Aktif') : 'Kamera Aktif';
            updateDiag('Kamera Terhubung', trackInfo + ' • Memulai pemutaran...', '#38bdf8');

            try {
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    await playPromise;
                }
            } catch (pErr) {
                console.warn('Initial play promise rejected:', pErr);
                tapStartBtn.style.display = 'block';
            }

            video.onloadedmetadata = () => {
                video.play().catch(e => console.warn(e));
                tapStartBtn.style.display = 'none';
                updateCanvasDimensions();
                checkAndStartDetection();
            };

            video.onplaying = () => {
                tapStartBtn.style.display = 'none';
                updateCanvasDimensions();
                updateDiag('Kamera Aktif (' + video.videoWidth + 'x' + video.videoHeight + ')', isModelsReady ? 'AI siap memindai wajah' : 'Sedang memuat model AI...', '#10b981');
                if (isModelsReady) {
                    statusDot.classList.add('ready');
                    statusText.innerText = 'Kamera Siap';
                } else {
                    statusText.innerText = 'Kamera Aktif';
                }
                setTimeout(() => {
                    const db = document.getElementById('diagBar');
                    if (db) db.style.opacity = '0';
                }, 3000);
            };
        }

        function updateCanvasDimensions() {
            if (!video.videoWidth || !video.videoHeight) return;
            const w = viewport.clientWidth || 360;
            const h = viewport.clientHeight || 640;

            cameraCanvas.width = video.videoWidth;
            cameraCanvas.height = video.videoHeight;
            scannerCanvas.width = w;
            scannerCanvas.height = h;

            faceapi.matchDimensions(scannerCanvas, { width: w, height: h });
        }

        // 3. Fallback Canvas Rendering Pipeline (Loop 60FPS yang menjamin feed tampil di Android)
        function startCanvasRenderLoop() {
            const ctx = cameraCanvas.getContext('2d');
            function drawFrame() {
                if (video && !video.paused && video.videoWidth > 0) {
                    if (cameraCanvas.width !== video.videoWidth) {
                        cameraCanvas.width = video.videoWidth;
                        cameraCanvas.height = video.videoHeight;
                    }
                    if (isFrontCamera) {
                        ctx.save();
                        ctx.translate(cameraCanvas.width, 0);
                        ctx.scale(-1, 1);
                        ctx.drawImage(video, 0, 0, cameraCanvas.width, cameraCanvas.height);
                        ctx.restore();
                    } else {
                        ctx.drawImage(video, 0, 0, cameraCanvas.width, cameraCanvas.height);
                    }
                    tapStartBtn.style.display = 'none';
                }
                requestAnimationFrame(drawFrame);
            }
            requestAnimationFrame(drawFrame);
        }

        // User Gesture handlers (mengatasi pembatasan autoplay / policy gesture MIUI)
        tapStartBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            tapStartBtn.style.display = 'none';
            startCamera();
        });

        viewport.addEventListener('click', () => {
            if (video && video.paused && video.srcObject) {
                video.play().catch(e => console.warn(e));
            }
        });
        viewport.addEventListener('touchstart', () => {
            if (video && video.paused && video.srcObject) {
                video.play().catch(e => console.warn(e));
            }
        }, { passive: true });

        // 4. Load Models & Enrolled Students
        async function loadModels() {
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

                await loadEnrolledStudents();

                isModelsReady = true;

                if (video && video.videoWidth > 0) {
                    statusDot.classList.add('ready');
                    statusText.innerText = 'Kamera Siap';
                    updateDiag('Kamera & AI Siap', 'Posisikan wajah di dalam oval', '#10b981');
                }

                checkAndStartDetection();
            } catch (err) {
                console.error('Gagal memuat model:', err);
                updateDiag('AI Terkendala', err.message || 'Gagal memuat model', '#ef4444');
                statusText.innerText = 'AI Error';
            }
        }

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
                console.warn('Gagal memuat data siswa:', err);
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

        // 7. Controls
        flipCameraBtn.addEventListener('click', () => {
            isFrontCamera = !isFrontCamera;
            startCamera();
        });

        audioToggleBtn.addEventListener('click', () => {
            isAudioEnabled = !isAudioEnabled;
            audioIcon.innerText = isAudioEnabled ? '🔔' : '🔕';
            audioToggleBtn.innerHTML = `<span>${audioIcon.innerText}</span> Suara ${isAudioEnabled ? 'ON' : 'OFF'}`;
        });

        // 8. Boot
        window.addEventListener('DOMContentLoaded', () => {
            startCamera();
            startCanvasRenderLoop();
            loadModels();

            // Jika dalam 2 detik videoWidth masih 0, tampilkan tombol sentuh agar memicu gesture
            setTimeout(() => {
                if (!video.videoWidth || video.paused) {
                    tapStartBtn.style.display = 'block';
                }
            }, 2000);
        });
    </script>
</body>
</html>
