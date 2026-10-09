@extends('layouts.app')

@section('content')
<div class="d-flex flex-column gap-3">
    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge rounded-pill px-2.5 py-1" style="background: #e0f2fe; color: #0284c7; font-size: 11px;">
                    <i class="fa-solid fa-microchip me-1"></i> Modul Inovasi Biometrik
                </span>
                <span class="text-slate-400" style="font-size: 12px;">•</span>
                <span class="badge bg-amber-subtle text-amber rounded-pill px-2 py-0.5" style="background: #fef3c7; color: #b45309; font-size: 11px;">
                    <i class="fa-solid fa-flask me-1"></i> Tahap Uji Coba (Beta)
                </span>
            </div>
            <h2 class="fw-bold mb-1 text-slate-800" style="font-size: 1.4rem; letter-spacing: -0.02em;">
                <i class="fa-solid fa-camera-retro text-primary me-2"></i> Presensi Wajah (Face Recognition) — Kelas {{ $selectedKelas }}
            </h2>
            <p class="text-slate-500 mb-0" style="font-size: 13px;">
                Uji coba absensi cerdas berbasis kamera web browser. Tidak membebani server dan tidak mengganggu presensi manual yang sedang berjalan.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('face.scanner', ['kelas' => $selectedKelas]) }}" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 12px;">
                <i class="fa-solid fa-expand me-1.5"></i> Buka Scanner Kamera
            </a>
            <a href="{{ route('face.enroll', ['kelas' => $selectedKelas]) }}" class="btn btn-light btn-sm text-slate-700 border rounded-pill px-3 py-1.5 shadow-xs fw-semibold" style="font-size: 12px;">
                <i class="fa-solid fa-user-plus me-1.5 text-primary"></i> Rekam Wajah Siswa
            </a>
            <a href="{{ route('face.unattended', ['kelas' => $selectedKelas]) }}" class="btn btn-warning btn-sm text-dark rounded-pill px-3 py-1.5 shadow-xs fw-semibold" style="font-size: 12px; background: #fef08a; border: 1px solid #fde047;">
                <i class="fa-solid fa-clipboard-question me-1.5 text-warning-emphasis"></i> Kelola Siswa Belum Absen
            </a>
        </div>
    </div>

    <!-- Alert Informasi Uji Coba -->
    <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center gap-3 p-3 mb-0" style="background: #f0fdf4; border: 1px solid #bbf7d0 !important;">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
             style="width: 36px; height: 36px; background: #dcfce7; color: #16a34a;">
            <i class="fa-solid fa-circle-info fs-6"></i>
        </div>
        <div class="small text-slate-700">
            <strong>Fokus Pengujian (Kelas IX E):</strong> Modul ini saat ini difokuskan untuk rombel <strong>Kelas IX E</strong> ({{ $totalSiswa }} siswa). Silakan lakukan perekaman sampel wajah pada 1 atau beberapa siswa terlebih dahulu, lalu uji coba pemindaian pada menu <strong>Scanner Kamera</strong>.
        </div>
    </div>

    <!-- Filter Bar & Statistik -->
    <div class="card border-0 shadow-sm rounded-4 bg-white">
        <div class="card-body p-3 p-md-3.5">
            <form action="{{ route('face.index') }}" method="GET" id="filterFaceForm" class="row g-2 align-items-center">
                <div class="col-sm-5 col-md-4">
                    <label for="selectKelas" class="form-label small fw-semibold text-slate-700 mb-1" style="font-size: 11.5px;">
                        <i class="fa-solid fa-chalkboard text-primary me-1"></i> Rombel Kelas Pengujian:
                    </label>
                    <select name="kelas" id="selectKelas" class="form-select form-select-sm rounded-3 border-slate-300" onchange="document.getElementById('filterFaceForm').submit()">
                        @foreach($daftarKelas as $k)
                            <option value="{{ $k }}" {{ $selectedKelas == $k ? 'selected' : '' }}>Kelas {{ $k }} {{ $k === 'IX E' ? '(Rekomendasi Uji Coba)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-7 col-md-8 text-sm-end pt-2 pt-sm-0">
                    <span class="small text-slate-500">Tanggal Hari Ini: <strong>{{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}</strong></span>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3">
        <!-- Terdaftar Wajah -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400 fw-bold" style="font-size: 10.5px; letter-spacing: 0.05em;">TEREKAM WAJAH</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" 
                         style="width: 32px; height: 32px; background: #e0e7ff;">
                        <i class="fa-solid fa-face-smile" style="font-size: 13px;"></i>
                    </div>
                </div>
                <div class="fw-bold text-slate-800 fs-5 mb-0">{{ $enrolledCount }} / {{ $totalSiswa }}</div>
                <small class="text-slate-400" style="font-size: 10.5px;">{{ round(($totalSiswa > 0 ? ($enrolledCount / $totalSiswa) * 100 : 0)) }}% siswa siap scan</small>
            </div>
        </div>

        <!-- Hadir via Wajah -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400 fw-bold" style="font-size: 10.5px; letter-spacing: 0.05em;">HADIR (WAJAH)</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-success" 
                         style="width: 32px; height: 32px; background: #dcfce7;">
                        <i class="fa-solid fa-camera" style="font-size: 13px;"></i>
                    </div>
                </div>
                <div class="fw-bold text-success fs-5 mb-0">{{ $hadirFaceCount }}</div>
                <small class="text-slate-400" style="font-size: 10.5px;">Scan wajah sukses hari ini</small>
            </div>
        </div>

        <!-- Hadir Manual -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400 fw-bold" style="font-size: 10.5px; letter-spacing: 0.05em;">HADIR (MANUAL)</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-info" 
                         style="width: 32px; height: 32px; background: #e0f2fe;">
                        <i class="fa-solid fa-clipboard-check" style="font-size: 13px;"></i>
                    </div>
                </div>
                <div class="fw-bold text-primary fs-5 mb-0">{{ $hadirManualCount }}</div>
                <small class="text-slate-400" style="font-size: 10.5px;">Input manual guru/piket</small>
            </div>
        </div>

        <!-- Sakit / Izin -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400 fw-bold" style="font-size: 10.5px; letter-spacing: 0.05em;">SAKIT / IZIN</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-warning" 
                         style="width: 32px; height: 32px; background: #fef3c7;">
                        <i class="fa-solid fa-notes-medical" style="font-size: 13px;"></i>
                    </div>
                </div>
                <div class="fw-bold text-warning fs-5 mb-0">{{ $sakitCount + $izinCount }}</div>
                <small class="text-slate-400" style="font-size: 10.5px;">S: {{ $sakitCount }} | I: {{ $izinCount }}</small>
            </div>
        </div>

        <!-- Alpa -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400 fw-bold" style="font-size: 10.5px; letter-spacing: 0.05em;">ALPA (A)</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-danger" 
                         style="width: 32px; height: 32px; background: #fee2e2;">
                        <i class="fa-solid fa-circle-xmark" style="font-size: 13px;"></i>
                    </div>
                </div>
                <div class="fw-bold text-danger fs-5 mb-0">{{ $alphaCount }}</div>
                <small class="text-slate-400" style="font-size: 10.5px;">Tanpa keterangan</small>
            </div>
        </div>

        <!-- Belum Presensi -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400 fw-bold" style="font-size: 10.5px; letter-spacing: 0.05em;">BELUM ABSEN</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-secondary" 
                         style="width: 32px; height: 32px; background: #f1f5f9;">
                        <i class="fa-solid fa-hourglass-half" style="font-size: 13px;"></i>
                    </div>
                </div>
                <div class="fw-bold text-slate-700 fs-5 mb-0">{{ $belumPresensiCount }}</div>
                <small class="text-slate-400" style="font-size: 10.5px;">Perlu ditindaklanjuti</small>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Siswa & Status Wajah -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 34px; height: 34px; background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-id-badge"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-slate-800 mb-0" style="font-size: 13.5px;">
                        Daftar Siswa Kelas {{ $selectedKelas }} & Status Sampel Wajah
                    </h6>
                    <small class="text-slate-400" style="font-size: 11.5px;">
                        Total {{ $totalSiswa }} siswa &bull; {{ $enrolledCount }} sudah memiliki data biometrik wajah
                    </small>
                </div>
            </div>

            <!-- Client-Side Search Box -->
            <div style="min-width: 240px;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-slate-50 border-end-0 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" id="faceStudentSearch" class="form-control form-control-sm border-start-0 ps-0 bg-slate-50" 
                           placeholder="Cari nama atau NIS siswa..." style="font-size: 12px;" onkeyup="filterFaceTable()">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="faceStudentTable" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3.5 text-slate-500 font-semibold" style="font-size: 11.5px; width: 50px;">NO</th>
                        <th class="text-slate-500 font-semibold" style="font-size: 11.5px; width: 110px;">NIS</th>
                        <th class="text-slate-500 font-semibold" style="font-size: 11.5px; min-width: 220px;">NAMA LENGKAP SISWA</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 60px;">L/P</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 150px;">STATUS WAJAH</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 150px;">ABSENSI HARI INI</th>
                        <th class="pe-3.5 text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 180px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswas as $idx => $s)
                        @php
                            $isEnrolled = !empty($s->face_descriptor);
                            $absenToday = $absensisToday[$s->id] ?? null;
                        @endphp
                        <tr class="face-row">
                            <td class="ps-3.5 fw-semibold text-slate-400">{{ $idx + 1 }}</td>
                            <td class="text-slate-600 font-monospace small face-nis">{{ $s->nis }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($isEnrolled && $s->foto_wajah)
                                        <img src="{{ asset('storage/' . $s->foto_wajah) }}" alt="Foto {{ $s->nama }}" 
                                             class="rounded-circle object-fit-cover flex-shrink-0 border" width="32" height="32"
                                             onerror="this.onerror=null; this.outerHTML='<div class=\'rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0\' style=\'width: 32px; height: 32px; font-size: 11.5px; background: {{ $s->jenis_kelamin === \'P\' ? \'linear-gradient(135deg, #ec4899, #db2777)\' : \'linear-gradient(135deg, #3b82f6, #1d4ed8)\' }};\'>{{ strtoupper(substr($s->nama, 0, 1)) }}</div>';">
                                    @else
                                        <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width: 32px; height: 32px; font-size: 11.5px; background: {{ $s->jenis_kelamin === 'P' ? 'linear-gradient(135deg, #ec4899, #db2777)' : 'linear-gradient(135deg, #3b82f6, #1d4ed8)' }};">
                                            {{ strtoupper(substr($s->nama, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="fw-semibold text-slate-800 face-name">{{ $s->nama }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill {{ $s->jenis_kelamin === 'P' ? 'bg-pink-subtle text-pink' : 'bg-primary-subtle text-primary' }}" 
                                      style="font-size: 10.5px; {{ $s->jenis_kelamin === 'P' ? 'background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8;' : 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;' }}">
                                    {{ $s->jenis_kelamin }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($isEnrolled)
                                    <span class="badge rounded-pill px-2.5 py-1" style="background: #dcfce7; color: #15803d; font-size: 11.5px; border: 1px solid #bbf7d0;">
                                        <i class="fa-solid fa-circle-check me-1"></i> Terdaftar
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2.5 py-1 text-slate-500" style="background: #f1f5f9; font-size: 11px; border: 1px solid #e2e8f0;">
                                        <i class="fa-regular fa-circle-xmark me-1 text-slate-400"></i> Belum Rekam
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($absenToday)
                                    @if($absenToday->status === 'Hadir')
                                        <span class="badge rounded-pill px-2.5 py-1" style="background: #dcfce7; color: #15803d; font-size: 11px; border: 1px solid #bbf7d0;">
                                            <i class="fa-solid fa-check me-1"></i> Hadir {{ $absenToday->metode === 'face' ? '(Wajah)' : '(Manual)' }}
                                        </span>
                                    @elseif($absenToday->status === 'Sakit')
                                        <span class="badge rounded-pill px-2 py-1" style="background: #fef3c7; color: #92400e; font-size: 11px; border: 1px solid #fde68a;">
                                            Sakit
                                        </span>
                                    @elseif($absenToday->status === 'Izin')
                                        <span class="badge rounded-pill px-2 py-1" style="background: #e0f2fe; color: #0369a1; font-size: 11px; border: 1px solid #bae6fd;">
                                            Izin
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-2 py-1" style="background: #fee2e2; color: #b91c1c; font-size: 11px; border: 1px solid #fecaca;">
                                            Alpa
                                        </span>
                                    @endif
                                @else
                                    <span class="badge rounded-pill px-2 py-1 text-slate-400 bg-light border" style="font-size: 10.5px;">
                                        Belum Presensi
                                    </span>
                                @endif
                            </td>
                            <td class="pe-3.5 text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1.5">
                                    <a href="{{ route('face.enroll', ['kelas' => $s->kelas, 'siswa_id' => $s->id]) }}" 
                                       class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" style="font-size: 11.5px;" title="Rekam Wajah">
                                        <i class="fa-solid fa-camera me-1"></i> {{ $isEnrolled ? 'Rekam Ulang' : 'Rekam' }}
                                    </a>
                                    @if($isEnrolled)
                                        <form action="{{ route('face.enroll.delete', $s->id) }}" method="POST" onsubmit="return confirm('Reset data sampel wajah {{ $s->nama }}?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" style="font-size: 11.5px;" title="Reset Wajah">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-slate-400">
                                <i class="fa-regular fa-folder-open fs-2 mb-2 text-slate-300"></i>
                                <p class="mb-0">Tidak ada siswa ditemukan di kelas {{ $selectedKelas }}.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function filterFaceTable() {
        const query = document.getElementById('faceStudentSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.face-row');
        rows.forEach(row => {
            const name = row.querySelector('.face-name').textContent.toLowerCase();
            const nis = row.querySelector('.face-nis').textContent.toLowerCase();
            if (name.includes(query) || nis.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection
