@extends('layouts.app')

@section('content')
<div class="cbt-workspace-wrapper" style="background: #f1f5f9; min-height: calc(100vh - 64px); padding-bottom: 80px;">
    <!-- Sticky Header Bar -->
    <div class="sticky-top bg-white border-bottom shadow-sm py-2 px-3 px-md-4" style="z-index: 1020;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-indigo-subtle text-indigo me-2" style="background:#e0e7ff; color:#3730a3;">{{ $ujian->jenis }}</span>
                <span class="fw-bold text-slate-800 d-none d-md-inline">{{ $ujian->judul }}</span>
                <span class="text-muted small d-block d-md-inline ms-md-2">{{ optional($ujian->mataPelajaran)->nama_mapel }}</span>
            </div>

            <div class="d-flex align-items-center gap-2 gap-md-3">
                <!-- Save Status Indicator -->
                <div id="saveStatusIndicator" class="small text-muted d-none d-sm-flex align-items-center gap-1">
                    <i class="fa-solid fa-cloud-arrow-up text-success"></i>
                    <span id="saveStatusText">Tersimpan</span>
                </div>

                <!-- Timer Box -->
                <div class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-pill border bg-slate-50 shadow-sm" id="timerBox">
                    <i class="fa-regular fa-clock text-primary fs-5" id="timerIcon"></i>
                    <span id="timerDisplay" class="fw-bold font-monospace fs-5 text-slate-800">
                        @php
                            $sTotal = (int) $sisaDetik;
                            $initH = floor($sTotal / 3600);
                            $initM = floor(($sTotal % 3600) / 60);
                            $initS = $sTotal % 60;
                            if ($initH > 0) {
                                echo sprintf('%02d:%02d:%02d', $initH, $initM, $initS);
                            } else {
                                echo sprintf('%02d:%02d', $initM, $initS);
                            }
                        @endphp
                    </span>
                </div>

                <!-- Number Grid Drawer Trigger (Mobile) -->
                <button type="button" class="btn btn-outline-primary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavigasi">
                    <i class="fa-solid fa-list-ol"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="container-fluid px-3 px-md-4 py-3">
        <div class="row g-3">
            <!-- Left Area: Main Question Box -->
            <div class="col-lg-8 col-xl-9">
                @foreach($soals as $idx => $s)
                    @php
                        $displayNum = $idx + 1;
                        $jawabanObj = $jawabans->get($s->id);
                        $currentVal = $jawabanObj ? $jawabanObj->jawaban : null;
                        $isRagu = $jawabanObj && $jawabanObj->ragu_ragu;
                    @endphp
                    <div class="card border-0 shadow-sm rounded-4 question-slide p-3 p-md-4 mb-3" 
                         id="questionSlide{{ $idx }}" 
                         data-index="{{ $idx }}"
                         data-soal-id="{{ $s->id }}"
                         style="{{ $idx === 0 ? '' : 'display: none;' }}">

                        <!-- Question Header -->
                        <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary fs-6 px-3 py-1.5 rounded-pill">
                                    Soal #{{ $displayNum }}
                                </span>
                                <span class="text-muted small">dari {{ count($soals) }} Soal</span>
                            </div>
                            <div>
                                <span class="badge bg-secondary-subtle text-secondary border small">Bobot: {{ $s->bobot }}</span>
                            </div>
                        </div>

                        <!-- Question Text & Image -->
                        <div class="question-body mb-4 text-slate-800" style="font-size: 16px; line-height: 1.7;">
                            {!! nl2br(e($s->pertanyaan)) !!}
                        </div>

                        @if($s->gambar)
                            <div class="mb-4 text-center text-md-start">
                                <img src="{{ asset('storage/' . $s->gambar) }}" alt="Gambar Soal #{{ $displayNum }}" class="img-fluid rounded border shadow-sm" style="max-height: 320px;">
                            </div>
                        @endif

                        @if($s->isEssay())
                            <!-- Input Lembar Jawaban Essay / Uraian -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-slate-700 mb-2">
                                    <i class="fa-solid fa-pen-nib text-primary me-1"></i> Tuliskan Jawaban Uraian Anda:
                                </label>
                                <textarea class="form-control rounded-3 p-3 text-slate-800" 
                                          id="essay_{{ $s->id }}" 
                                          rows="6" 
                                          placeholder="Ketikkan uraian jawaban Anda di sini..."
                                          oninput="onEssayChange({{ $idx }}, {{ $s->id }}, this.value)"
                                          style="font-size: 15px; line-height: 1.7; resize: vertical;">{{ $currentVal }}</textarea>
                                <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                                    <span id="essayWordCount_{{ $s->id }}">
                                        {{ !empty($currentVal) ? count(array_filter(preg_split('/\s+/', trim($currentVal)))) : 0 }} kata
                                    </span>
                                    <span class="text-success small"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Otomatis tersimpan</span>
                                </div>

                                <!-- Fasilitas Upload Foto / Gambar Jawaban Essay -->
                                <div class="mt-3 p-3 rounded-3 bg-white border border-slate-200">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-bold text-slate-800 small mb-0">
                                            <i class="fa-solid fa-camera text-primary me-1"></i> Lampirkan Foto Jawaban (Opsional):
                                        </label>
                                        <span class="text-muted" style="font-size: 12px;">Maksimal 5MB (.jpg, .png)</span>
                                    </div>
                                    <p class="small text-muted mb-3" style="font-size: 13px;">
                                        Jika Anda mengerjakan rumus, hitungan, atau uraian tulisan tangan di lembar kertas, Anda dapat memfoto dan mengunggahnya di sini:
                                    </p>

                                    <!-- Preview Foto yang Sudah Ada -->
                                    <div id="imgPreviewWrapper_{{ $s->id }}" class="mb-3 {{ $jawabanObj && $jawabanObj->gambar_jawaban ? '' : 'd-none' }}">
                                        <div class="d-inline-flex flex-wrap align-items-center gap-3 p-2.5 border rounded-3 bg-slate-50">
                                            <img id="imgPreview_{{ $s->id }}" 
                                                 src="{{ $jawabanObj && $jawabanObj->gambar_jawaban ? asset('storage/' . $jawabanObj->gambar_jawaban) : '' }}" 
                                                 alt="Foto Jawaban Soal #{{ $displayNum }}" 
                                                 class="rounded border bg-white shadow-2xs" 
                                                 style="max-height: 140px; max-width: 220px; object-fit: contain;">
                                            <div class="d-flex flex-column gap-1.5">
                                                <a id="imgLink_{{ $s->id }}" 
                                                   href="{{ $jawabanObj && $jawabanObj->gambar_jawaban ? asset('storage/' . $jawabanObj->gambar_jawaban) : '#' }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary py-1 px-2.5">
                                                    <i class="fa-solid fa-magnifying-glass-plus me-1"></i> Lihat Foto Penuh
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger py-1 px-2.5" 
                                                        onclick="hapusGambarJawaban({{ $idx }}, {{ $s->id }})">
                                                    <i class="fa-solid fa-trash me-1"></i> Hapus Foto
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tombol Upload File / Ambil Foto -->
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <input type="file" 
                                               id="essayImgInput_{{ $s->id }}" 
                                               accept="image/*" 
                                               class="d-none" 
                                               onchange="uploadGambarJawaban({{ $idx }}, {{ $s->id }}, this)">
                                        <button type="button" 
                                                class="btn btn-outline-primary btn-sm px-3 shadow-2xs" 
                                                onclick="document.getElementById('essayImgInput_{{ $s->id }}').click()"
                                                id="btnUploadImg_{{ $s->id }}">
                                            <i class="fa-solid fa-camera me-1"></i>
                                            <span>{{ $jawabanObj && $jawabanObj->gambar_jawaban ? 'Ganti Foto Jawaban' : 'Ambil Foto / Unggah Gambar' }}</span>
                                        </button>
                                        <span id="imgUploadStatus_{{ $s->id }}" class="small"></span>
                                    </div>
                                </div>
                            </div>
                        @elseif($s->isMenjodohkan())
                            <!-- Input Menjodohkan (Matching Pairs) -->
                            @php
                                $pairs = $s->data_tambahan['pairs'] ?? [];
                                $userPairs = !empty($currentVal) ? (json_decode($currentVal, true) ?: []) : [];
                                // Kumpulkan semua item pasangan kanan unik
                                $rightOptions = collect($pairs)->pluck('kanan')->filter()->unique()->values()->all();
                                if ($ujian->acak_opsi) {
                                    shuffle($rightOptions);
                                }
                            @endphp
                            <div class="mb-4">
                                <div class="p-3 rounded-3 bg-slate-50 border border-slate-200 mb-3">
                                    <div class="small fw-bold text-slate-800 mb-1">
                                        <i class="fa-solid fa-arrows-split-up-and-left text-primary me-1"></i>Petunjuk Menjodohkan:
                                    </div>
                                    <div class="small text-slate-600">Pilihlah pasangan yang paling tepat pada kolom kanan untuk setiap pernyataan di sebelah kiri.</div>
                                </div>

                                <div class="d-flex flex-column gap-2.5" id="matchContainer_{{ $s->id }}">
                                    @foreach($pairs as $pIdx => $p)
                                        @php
                                            $pid = (string) ($p['id'] ?? ($pIdx + 1));
                                            $selectedKanan = $userPairs[$pid] ?? '';
                                        @endphp
                                        <div class="p-3 rounded-3 border bg-white shadow-2xs">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                                                            {{ $pIdx + 1 }}
                                                        </span>
                                                        <span class="fw-semibold text-slate-800" style="font-size: 15px;">
                                                            {{ $p['kiri'] ?? '-' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <select class="form-select match-select-{{ $s->id }} border-2" 
                                                            data-pid="{{ $pid }}" 
                                                            onchange="onMatchChange({{ $idx }}, {{ $s->id }})">
                                                        <option value="">-- Pilih Pasangan --</option>
                                                        @foreach($rightOptions as $ro)
                                                            <option value="{{ $ro }}" {{ $selectedKanan === $ro ? 'selected' : '' }}>
                                                                {{ $ro }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <!-- Options Choices (Pilihan Ganda) -->
                            <div class="options-container d-flex flex-column gap-2.5 mb-4">
                                @foreach(['A' => [$s->opsi_a, $s->gambar_a], 'B' => [$s->opsi_b, $s->gambar_b], 'C' => [$s->opsi_c, $s->gambar_c], 'D' => [$s->opsi_d, $s->gambar_d]] as $optKey => [$optVal, $optImg])
                                    <label class="option-card p-3 rounded-3 border d-flex align-items-start gap-3 cursor-pointer transition-all {{ $currentVal === $optKey ? 'selected-option' : '' }}" 
                                           id="label_{{ $s->id }}_{{ $optKey }}"
                                           onclick="selectOption({{ $idx }}, {{ $s->id }}, '{{ $optKey }}')">
                                        <div class="option-badge rounded-circle border fw-bold d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" 
                                             style="width: 32px; height: 32px; font-size: 14px;">
                                            {{ $optKey }}
                                        </div>
                                        <div class="flex-grow-1 text-slate-700 fw-medium" style="font-size: 15px;">
                                            @if($optVal)
                                                <div class="text-slate-800">{{ $optVal }}</div>
                                            @endif
                                            @if($optImg)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . $optImg) }}" alt="Pilihan {{ $optKey }}" class="img-fluid rounded border bg-white p-1 shadow-2xs" style="max-height: 200px; max-width: 100%; object-fit: contain;">
                                                </div>
                                            @endif
                                        </div>
                                        <input type="radio" name="jawaban_{{ $s->id }}" value="{{ $optKey }}" class="d-none" {{ $currentVal === $optKey ? 'checked' : '' }}>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <!-- Bottom Navigation Buttons -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <div>
                                <button type="button" class="btn btn-outline-secondary px-3" onclick="prevQuestion()" {{ $idx === 0 ? 'disabled' : '' }}>
                                    <i class="fa-solid fa-chevron-left me-1"></i> Sebelumnya
                                </button>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" 
                                        class="btn {{ $isRagu ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }} px-3" 
                                        id="btnRagu{{ $idx }}" 
                                        onclick="toggleRagu({{ $idx }}, {{ $s->id }})">
                                    <i class="fa-solid fa-flag me-1"></i> Ragu-Ragu
                                </button>

                                @if($idx < count($soals) - 1)
                                    <button type="button" class="btn btn-primary px-4" onclick="nextQuestion()">
                                        Berikutnya <i class="fa-solid fa-chevron-right ms-1"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-success px-4 fw-bold shadow-sm" onclick="showFinishModal()">
                                        <i class="fa-solid fa-check-double me-1"></i> Kumpulkan Ujian
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Right Area: Question Navigation Grid (Desktop Sticky Sidebar) -->
            <div class="col-lg-4 col-xl-3 d-none d-lg-block">
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 80px;">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-slate-800">
                            <i class="fa-solid fa-table-cells text-primary me-2"></i>Nomor Soal
                        </span>
                        <span class="badge bg-slate-100 text-slate-700 border" id="answeredCountBadge">0 / {{ count($soals) }}</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-grid" style="grid-template-columns: repeat(5, 1fr); gap: 8px;" id="desktopNavGrid">
                            @foreach($soals as $idx => $s)
                                @php
                                    $displayNum = $idx + 1;
                                    $jawabanObj = $jawabans->get($s->id);
                                    $currentVal = $jawabanObj ? $jawabanObj->jawaban : null;
                                    $isRagu = $jawabanObj && $jawabanObj->ragu_ragu;
                                    $btnClass = 'btn-nav-empty';
                                    if ($isRagu) {
                                        $btnClass = 'btn-nav-ragu';
                                    } elseif ($currentVal) {
                                        $btnClass = 'btn-nav-answered';
                                    }
                                @endphp
                                <button type="button" 
                                        class="btn btn-sm nav-number-btn {{ $btnClass }} {{ $idx === 0 ? 'active-number' : '' }}" 
                                        id="navBtn_{{ $idx }}" 
                                        onclick="goToQuestion({{ $idx }})">
                                    {{ $displayNum }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Legend -->
                        <div class="mt-4 pt-3 border-top small text-muted">
                            <div class="d-flex align-items-center gap-2 mb-1.5">
                                <span class="nav-legend-dot bg-success"></span>
                                <span>Sudah Dijawab</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-1.5">
                                <span class="nav-legend-dot bg-warning"></span>
                                <span>Ragu-Ragu</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="nav-legend-dot bg-secondary-subtle border"></span>
                                <span>Belum Dijawab</span>
                            </div>
                        </div>

                        <div class="mt-4 d-grid">
                            <button type="button" class="btn btn-success fw-bold py-2.5 shadow-sm" onclick="showFinishModal()">
                                <i class="fa-solid fa-flag-checkered me-1"></i> Selesaikan Ujian
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas Navigasi Mobile -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavigasi">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold">Nomor Soal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        <div class="d-grid" style="grid-template-columns: repeat(5, 1fr); gap: 8px;" id="mobileNavGrid">
            @foreach($soals as $idx => $s)
                @php
                    $displayNum = $idx + 1;
                    $jawabanObj = $jawabans->get($s->id);
                    $currentVal = $jawabanObj ? $jawabanObj->jawaban : null;
                    $isRagu = $jawabanObj && $jawabanObj->ragu_ragu;
                    $btnClass = 'btn-nav-empty';
                    if ($isRagu) {
                        $btnClass = 'btn-nav-ragu';
                    } elseif ($currentVal) {
                        $btnClass = 'btn-nav-answered';
                    }
                @endphp
                <button type="button" 
                        class="btn btn-sm nav-number-btn {{ $btnClass }} {{ $idx === 0 ? 'active-number' : '' }}" 
                        id="mNavBtn_{{ $idx }}" 
                        onclick="goToQuestion({{ $idx }}); bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasNavigasi')).hide();">
                    {{ $displayNum }}
                </button>
            @endforeach
        </div>

        <div class="mt-4 pt-3 border-top small text-muted">
            <div class="d-flex align-items-center gap-2 mb-1.5"><span class="nav-legend-dot bg-success"></span> Sudah Dijawab</div>
            <div class="d-flex align-items-center gap-2 mb-1.5"><span class="nav-legend-dot bg-warning"></span> Ragu-Ragu</div>
            <div class="d-flex align-items-center gap-2"><span class="nav-legend-dot bg-secondary-subtle border"></span> Belum Dijawab</div>
        </div>

        <div class="mt-4 d-grid">
            <button type="button" class="btn btn-success fw-bold py-2.5" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasNavigasi')).hide(); showFinishModal();">
                <i class="fa-solid fa-flag-checkered me-1"></i> Selesaikan Ujian
            </button>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Selesai Ujian -->
<div class="modal fade" id="modalFinishConfirmation" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-circle-question me-2"></i>Konfirmasi Pengumpulan Ujian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="text-slate-600 mb-3">Apakah Anda sudah yakin ingin mengakhiri dan mengumpulkan jawaban ujian ini?</p>

                <!-- Rekap Status Jawaban -->
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="p-2 rounded bg-light border">
                            <div class="text-muted small">Total Soal</div>
                            <div class="fw-bold fs-5 text-slate-800">{{ count($soals) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-success-subtle border border-success-subtle">
                            <div class="text-success small fw-semibold">Terjawab</div>
                            <div class="fw-bold fs-5 text-success" id="finishModalAnswered">0</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-danger-subtle border border-danger-subtle">
                            <div class="text-danger small fw-semibold">Kosong</div>
                            <div class="fw-bold fs-5 text-danger" id="finishModalUnanswered">0</div>
                        </div>
                    </div>
                </div>

                <div id="finishModalWarning" class="alert alert-warning small d-none py-2 mb-0" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Masih ada nomor soal yang belum dijawab atau bertanda ragu-ragu.
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Periksa Kembali</button>
                <form method="POST" action="{{ route('siswa.ujian.selesai', $ujian->id) }}" id="finishExamForm">
                    @csrf
                    <button type="submit" class="btn btn-success fw-bold px-4">
                        <i class="fa-solid fa-check me-1"></i> Ya, Kumpulkan Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer { cursor: pointer; }
    .transition-all { transition: all 0.2s ease; }
    
    /* Option Card Styling */
    .option-card {
        background: #ffffff;
        border-color: #e2e8f0;
    }
    .option-card:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }
    .option-card.selected-option {
        background-color: #eff6ff !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 1px #3b82f6;
    }
    .option-card.selected-option .option-badge {
        background-color: #3b82f6 !important;
        color: #ffffff !important;
        border-color: #3b82f6 !important;
    }
    
    /* Navigation Number Buttons */
    .nav-number-btn {
        height: 38px;
        font-weight: 600;
        font-size: 13px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
    }
    .btn-nav-empty {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
    .btn-nav-empty:hover {
        background-color: #e2e8f0;
    }
    .btn-nav-answered {
        background-color: #10b981 !important;
        color: #ffffff !important;
        border: 1px solid #059669 !important;
    }
    .btn-nav-ragu {
        background-color: #f59e0b !important;
        color: #1e293b !important;
        border: 1px solid #d97706 !important;
    }
    .active-number {
        outline: 3px solid #6366f1 !important;
        outline-offset: 1px;
    }
    
    .nav-legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }
</style>

<script>
    const TOTAL_SOAL = {{ count($soals) }};
    const SAVE_URL = "{{ route('siswa.ujian.simpanJawaban', $ujian->id) }}";
    const SYNC_TIME_URL = "{{ route('siswa.ujian.syncWaktu', $ujian->id) }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";
    let currentIndex = 0;
    let sisaDetik = Math.max(0, Math.floor({{ (int) $sisaDetik }}));
    let endTime = Date.now() + (sisaDetik * 1000);

    // Hitung sisa detik secara akurat berbasis selisih waktu
    function getRemainingSeconds() {
        return Math.max(0, Math.ceil((endTime - Date.now()) / 1000));
    }

    // Jawaban state lokal
    const userAnswers = {};
    const userGambar = {};
    const userRagu = {};

    @foreach($soals as $idx => $s)
        @php
            $ansObj = $jawabans->get($s->id);
            $rawAns = $ansObj ? (string) $ansObj->jawaban : '';
            $rawImg = $ansObj && $ansObj->gambar_jawaban ? asset('storage/' . $ansObj->gambar_jawaban) : '';
        @endphp
        userAnswers[{{ $s->id }}] = {!! json_encode($rawAns) !!};
        userGambar[{{ $s->id }}] = {!! json_encode($rawImg) !!};
        userRagu[{{ $s->id }}] = {{ ($ansObj && $ansObj->ragu_ragu) ? 'true' : 'false' }};
    @endforeach

    // Navigasi Soal
    function showQuestion(index) {
        if (index < 0 || index >= TOTAL_SOAL) return;

        document.querySelectorAll('.question-slide').forEach(el => el.style.display = 'none');
        const activeSlide = document.getElementById(`questionSlide${index}`);
        if (activeSlide) {
            activeSlide.style.display = 'block';
        }

        currentIndex = index;

        // Update active class on buttons
        document.querySelectorAll('.nav-number-btn').forEach(btn => btn.classList.remove('active-number'));
        const activeDesktopBtn = document.getElementById(`navBtn_${index}`);
        const activeMobileBtn = document.getElementById(`mNavBtn_${index}`);
        if (activeDesktopBtn) activeDesktopBtn.classList.add('active-number');
        if (activeMobileBtn) activeMobileBtn.classList.add('active-number');

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function nextQuestion() {
        if (currentIndex < TOTAL_SOAL - 1) {
            showQuestion(currentIndex + 1);
        }
    }

    function prevQuestion() {
        if (currentIndex > 0) {
            showQuestion(currentIndex - 1);
        }
    }

    function goToQuestion(index) {
        showQuestion(index);
    }

    // Pemilihan Opsi Jawaban (Pilihan Ganda) & Auto-Save
    function selectOption(index, soalId, optionKey) {
        userAnswers[soalId] = optionKey;

        // Visual selection
        ['A', 'B', 'C', 'D'].forEach(k => {
            const label = document.getElementById(`label_${soalId}_${k}`);
            if (label) {
                if (k === optionKey) {
                    label.classList.add('selected-option');
                } else {
                    label.classList.remove('selected-option');
                }
            }
        });

        updateNavButton(index, soalId);
        saveAnswerToServer(soalId, optionKey, userRagu[soalId] || false);
    }

    // Input Jawaban Essay / Uraian & Debounced Auto-Save
    let essayDebounceTimer = null;
    function onEssayChange(index, soalId, text) {
        userAnswers[soalId] = text.trim();

        // Update jumlah kata
        const countSpan = document.getElementById(`essayWordCount_${soalId}`);
        if (countSpan) {
            const trimmed = text.trim();
            const words = trimmed ? trimmed.split(/\s+/).length : 0;
            countSpan.innerText = `${words} kata`;
        }

        updateNavButton(index, soalId);

        clearTimeout(essayDebounceTimer);
        essayDebounceTimer = setTimeout(function () {
            saveAnswerToServer(soalId, text, userRagu[soalId] || false);
        }, 800);
    }

    // Pilihan Pasangan Menjodohkan & Auto-Save
    function onMatchChange(index, soalId) {
        const selects = document.querySelectorAll(`.match-select-${soalId}`);
        const pairsResult = {};
        let anySelected = false;

        selects.forEach(sel => {
            const pid = sel.getAttribute('data-pid');
            const val = sel.value;
            if (val) {
                pairsResult[pid] = val;
                anySelected = true;
            }
        });

        const jsonVal = anySelected ? JSON.stringify(pairsResult) : '';
        userAnswers[soalId] = jsonVal;

        updateNavButton(index, soalId);
        saveAnswerToServer(soalId, jsonVal, userRagu[soalId] || false);
    }

    // Upload Foto Jawaban Essay
    function uploadGambarJawaban(index, soalId, input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran berkas gambar maksimal 5MB!');
            input.value = '';
            return;
        }

        const statusEl = document.getElementById(`imgUploadStatus_${soalId}`);
        const btnUpload = document.getElementById(`btnUploadImg_${soalId}`);
        if (statusEl) statusEl.innerHTML = '<span class="text-primary"><i class="fa-solid fa-spinner fa-spin me-1"></i>Mengunggah foto...</span>';
        if (btnUpload) btnUpload.disabled = true;

        const formData = new FormData();
        formData.append('soal_id', soalId);
        formData.append('gambar_jawaban', file);
        formData.append('jawaban', userAnswers[soalId] || '');
        formData.append('ragu_ragu', userRagu[soalId] ? 1 : 0);
        formData.append('sisa_waktu', getRemainingSeconds());

        fetch(SAVE_URL, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (btnUpload) btnUpload.disabled = false;
            if (data.success && data.gambar_url) {
                userGambar[soalId] = data.gambar_url;
                const wrapper = document.getElementById(`imgPreviewWrapper_${soalId}`);
                const preview = document.getElementById(`imgPreview_${soalId}`);
                const link = document.getElementById(`imgLink_${soalId}`);
                if (preview) preview.src = data.gambar_url;
                if (link) link.href = data.gambar_url;
                if (wrapper) wrapper.classList.remove('d-none');
                if (btnUpload) btnUpload.querySelector('span').innerText = 'Ganti Foto Jawaban';
                if (statusEl) statusEl.innerHTML = '<span class="text-success fw-semibold"><i class="fa-solid fa-check me-1"></i>Foto berhasil diunggah</span>';
                setTimeout(() => { if (statusEl) statusEl.innerHTML = ''; }, 3000);
                updateNavButton(index, soalId);
            } else {
                if (statusEl) statusEl.innerHTML = '<span class="text-danger">Gagal mengunggah foto.</span>';
            }
        })
        .catch(err => {
            if (btnUpload) btnUpload.disabled = false;
            if (statusEl) statusEl.innerHTML = '<span class="text-danger">Koneksi terputus saat mengunggah.</span>';
        });
    }

    // Hapus Foto Jawaban Essay
    function hapusGambarJawaban(index, soalId) {
        if (!confirm('Apakah Anda yakin ingin menghapus foto lampiran ini?')) return;

        const statusEl = document.getElementById(`imgUploadStatus_${soalId}`);
        if (statusEl) statusEl.innerHTML = '<span class="text-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i>Menghapus...</span>';

        const formData = new FormData();
        formData.append('soal_id', soalId);
        formData.append('hapus_gambar', 1);
        formData.append('jawaban', userAnswers[soalId] || '');
        formData.append('ragu_ragu', userRagu[soalId] ? 1 : 0);
        formData.append('sisa_waktu', getRemainingSeconds());

        fetch(SAVE_URL, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            userGambar[soalId] = '';
            const wrapper = document.getElementById(`imgPreviewWrapper_${soalId}`);
            if (wrapper) wrapper.classList.add('d-none');
            const fileInput = document.getElementById(`essayImgInput_${soalId}`);
            if (fileInput) fileInput.value = '';
            const btnUpload = document.getElementById(`btnUploadImg_${soalId}`);
            if (btnUpload) btnUpload.querySelector('span').innerText = 'Ambil Foto / Unggah Gambar';
            if (statusEl) statusEl.innerHTML = '<span class="text-muted">Foto dihapus</span>';
            setTimeout(() => { if (statusEl) statusEl.innerHTML = ''; }, 2000);
            updateNavButton(index, soalId);
        })
        .catch(() => {
            if (statusEl) statusEl.innerHTML = '<span class="text-danger">Gagal menghapus foto.</span>';
        });
    }

    // Toggle Ragu-Ragu
    function toggleRagu(index, soalId) {
        const isCurrentlyRagu = userRagu[soalId] || false;
        const newRagu = !isCurrentlyRagu;
        userRagu[soalId] = newRagu;

        const raguBtn = document.getElementById(`btnRagu${index}`);
        if (raguBtn) {
            if (newRagu) {
                raguBtn.classList.remove('btn-outline-warning');
                raguBtn.classList.add('btn-warning');
            } else {
                raguBtn.classList.remove('btn-warning');
                raguBtn.classList.add('btn-outline-warning');
            }
        }

        updateNavButton(index, soalId);
        saveAnswerToServer(soalId, userAnswers[soalId] || null, newRagu);
    }

    function updateNavButton(index, soalId) {
        const ans = userAnswers[soalId];
        const img = userGambar[soalId];
        const hasAns = (ans !== null && ans !== undefined && ans !== '') || (img !== null && img !== undefined && img !== '');
        const isRagu = userRagu[soalId] || false;

        const dBtn = document.getElementById(`navBtn_${index}`);
        const mBtn = document.getElementById(`mNavBtn_${index}`);

        [dBtn, mBtn].forEach(btn => {
            if (!btn) return;
            btn.classList.remove('btn-nav-empty', 'btn-nav-answered', 'btn-nav-ragu');
            if (isRagu) {
                btn.classList.add('btn-nav-ragu');
            } else if (hasAns) {
                btn.classList.add('btn-nav-answered');
            } else {
                btn.classList.add('btn-nav-empty');
            }
        });

        updateStatsBadge();
    }

    function updateStatsBadge() {
        let count = 0;
        Object.keys(userAnswers).forEach(sid => {
            const ans = userAnswers[sid];
            const img = userGambar[sid];
            if ((ans !== null && ans !== undefined && ans !== '') || (img !== null && img !== undefined && img !== '')) count++;
        });

        const badge = document.getElementById('answeredCountBadge');
        if (badge) badge.innerText = `${count} / ${TOTAL_SOAL}`;
    }

    // Background Auto-Save via AJAX
    function saveAnswerToServer(soalId, jawaban, ragu) {
        const statusText = document.getElementById('saveStatusText');
        if (statusText) statusText.innerText = 'Menyimpan...';

        const remaining = getRemainingSeconds();

        fetch(SAVE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                soal_id: soalId,
                jawaban: jawaban,
                ragu_ragu: ragu ? 1 : 0,
                sisa_waktu: remaining
            })
        })
        .then(res => res.json())
        .then(data => {
            if (statusText) statusText.innerText = 'Tersimpan ✓';
        })
        .catch(err => {
            if (statusText) statusText.innerText = 'Koneksi Lambat ⚠️';
        });
    }

    // Background sync sisa waktu ke server (Heartbeat & Tab close/hide)
    function syncWaktuToServer(isBeacon = false) {
        const remaining = getRemainingSeconds();
        try {
            if (isBeacon && navigator.sendBeacon) {
                const formData = new FormData();
                formData.append('_token', CSRF_TOKEN);
                formData.append('sisa_waktu', remaining);
                navigator.sendBeacon(SYNC_TIME_URL, formData);
            } else {
                fetch(SYNC_TIME_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    keepalive: isBeacon,
                    body: JSON.stringify({ sisa_waktu: remaining })
                }).catch(() => {});
            }
        } catch (e) {}
    }

    // Modal Konfirmasi Selesai
    function showFinishModal() {
        let answered = 0;
        let raguCount = 0;

        Object.keys(userAnswers).forEach(sid => {
            const ans = userAnswers[sid];
            const img = userGambar[sid];
            if ((ans !== null && ans !== undefined && ans !== '') || (img !== null && img !== undefined && img !== '')) answered++;
            if (userRagu[sid]) raguCount++;
        });

        const unanswered = TOTAL_SOAL - answered;

        document.getElementById('finishModalAnswered').innerText = answered;
        document.getElementById('finishModalUnanswered').innerText = unanswered;

        const warningEl = document.getElementById('finishModalWarning');
        if (unanswered > 0 || raguCount > 0) {
            warningEl.classList.remove('d-none');
        } else {
            warningEl.classList.add('d-none');
        }

        const modal = new bootstrap.Modal(document.getElementById('modalFinishConfirmation'));
        modal.show();
    }

    // Format durasi detik menjadi MM:SS atau HH:MM:SS tanpa angka desimal
    function formatTime(totalSec) {
        const sec = Math.max(0, Math.floor(totalSec));
        const hours = Math.floor(sec / 3600);
        const minutes = Math.floor((sec % 3600) / 60);
        const seconds = Math.floor(sec % 60);

        const pad = (n) => String(n).padStart(2, '0');

        if (hours > 0) {
            return `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
        }
        return `${pad(minutes)}:${pad(seconds)}`;
    }

    // Countdown Timer berbasis target timestamp (kebal terhadap throttling tab background)
    function startTimer() {
        const display = document.getElementById('timerDisplay');
        const box = document.getElementById('timerBox');
        const icon = document.getElementById('timerIcon');

        function updateTick() {
            const remaining = getRemainingSeconds();

            display.innerText = formatTime(remaining);

            // Peringatan jika waktu tersisa <= 5 menit (300 detik)
            if (remaining <= 300) {
                box.classList.add('bg-danger-subtle', 'border-danger');
                display.classList.add('text-danger');
                if (icon) {
                    icon.classList.remove('text-primary');
                    icon.classList.add('text-danger');
                }
            }

            if (remaining <= 0) {
                clearInterval(interval);
                display.innerText = '00:00';
                syncWaktuToServer(true);
                alert('Waktu ujian telah berakhir! Lembar jawaban Anda akan otomatis dikumpulkan.');
                document.getElementById('finishExamForm').submit();
            }
        }

        // Jalankan segera update pertama
        updateTick();

        const interval = setInterval(updateTick, 1000);

        // Heartbeat sinkronisasi waktu ke server setiap 10 detik
        setInterval(function () {
            syncWaktuToServer(false);
        }, 10000);
    }

    // Deteksi Anti-Curang (Pindah Tab / Minimize Layar) & Sinkronisasi Sisa Waktu
    let cheatWarnings = 0;
    document.addEventListener('visibilitychange', function () {
        syncWaktuToServer(false);
        if (document.hidden) {
            cheatWarnings++;
            alert(`⚠️ PERINGATAN KECURANGAN (#${cheatWarnings}): Anda terdeteksi meninggalkan layar ujian! Tetap fokus pada layar asesmen ini.`);
        }
    });

    // Sinkronisasi sisa waktu saat menutup atau merefresh halaman / tab
    window.addEventListener('beforeunload', function () {
        syncWaktuToServer(true);
    });
    window.addEventListener('pagehide', function () {
        syncWaktuToServer(true);
    });

    document.addEventListener('DOMContentLoaded', function () {
        updateStatsBadge();
        startTimer();
    });
</script>
@endsection

