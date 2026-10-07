@extends('layouts.app')

@section('content')
<div class="d-flex flex-column gap-3">
    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge rounded-pill px-2.5 py-1" style="background: #e0f2fe; color: #0284c7; font-size: 11px;">
                    <i class="fa-solid fa-clipboard-user me-1"></i> Kehadiran Kelas Binaan
                </span>
                <span class="text-slate-400" style="font-size: 12px;">•</span>
                <span class="text-slate-500 small" style="font-size: 12px;">
                    Wali Kelas: <strong>{{ optional($waliKelas->user)->name ?? 'Belum Ditentukan' }}</strong>
                </span>
            </div>
            <h2 class="fw-bold mb-1 text-slate-800" style="font-size: 1.4rem; letter-spacing: -0.02em;">
                <i class="fa-solid fa-calendar-check text-primary me-2"></i> Rekap Kehadiran Siswa — Kelas {{ $selectedKelas }}
            </h2>
            <p class="text-slate-500 mb-0" style="font-size: 13px;">
                Pantau rekapitulasi absensi bulanan siswa (Hadir, Sakit, Izin, Alpa) dan unduh berkas rekap resmi dalam format Excel.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('walikelas.myClass', ['kelas' => $selectedKelas]) }}" class="btn btn-light btn-sm text-slate-700 border rounded-pill px-3 py-1.5 shadow-xs" style="font-size: 12px;">
                <i class="fa-solid fa-graduation-cap me-1.5 text-warning"></i> Nilai Kelas
            </a>
            <a href="{{ route('walikelas.kehadiran.exportExcel', ['kelas' => $selectedKelas, 'bulan' => $bulan, 'tahun' => $tahun]) }}" 
               class="btn btn-success btn-sm rounded-pill px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 12px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                <i class="fa-solid fa-file-excel me-1.5"></i> Download Rekap Excel
            </a>
        </div>
    </div>

    <!-- Filter Bar (Bulan, Tahun, Rombel jika Admin) -->
    <div class="card border-0 shadow-sm rounded-4 bg-white">
        <div class="card-body p-3 p-md-3.5">
            <form action="{{ route('walikelas.kehadiran') }}" method="GET" id="filterKehadiranForm" class="row g-2 align-items-end">
                @if(Auth::check() && Auth::user()->role === 'admin')
                    <div class="col-sm-4 col-md-3">
                        <label for="selectKelas" class="form-label small fw-semibold text-slate-700 mb-1" style="font-size: 11.5px;">
                            <i class="fa-solid fa-chalkboard text-primary me-1"></i> Pilih Rombel Kelas:
                        </label>
                        <select name="kelas" id="selectKelas" class="form-select form-select-sm rounded-3 border-slate-300" onchange="document.getElementById('filterKehadiranForm').submit()">
                            @foreach($daftarKelas as $k)
                                <option value="{{ $k }}" {{ $selectedKelas == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="kelas" value="{{ $selectedKelas }}">
                @endif

                <div class="col-sm-4 col-md-3">
                    <label for="selectBulan" class="form-label small fw-semibold text-slate-700 mb-1" style="font-size: 11.5px;">
                        <i class="fa-regular fa-calendar text-primary me-1"></i> Bulan:
                    </label>
                    <select name="bulan" id="selectBulan" class="form-select form-select-sm rounded-3 border-slate-300" onchange="document.getElementById('filterKehadiranForm').submit()">
                        @foreach($daftarBulan as $num => $namaBulan)
                            <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>
                                {{ $namaBulan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-4 col-md-2">
                    <label for="selectTahun" class="form-label small fw-semibold text-slate-700 mb-1" style="font-size: 11.5px;">
                        <i class="fa-regular fa-calendar-days text-primary me-1"></i> Tahun:
                    </label>
                    <select name="tahun" id="selectTahun" class="form-select form-select-sm rounded-3 border-slate-300" onchange="document.getElementById('filterKehadiranForm').submit()">
                        @foreach($daftarTahun as $t)
                            <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>
                                {{ $t }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 d-flex align-items-center gap-2 pt-1 pt-md-0 ms-auto justify-content-md-end">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 shadow-xs fw-semibold" style="font-size: 12px;">
                        <i class="fa-solid fa-filter me-1"></i> Terapkan
                    </button>
                    @php
                        $now = \Carbon\Carbon::now();
                    @endphp
                    @if($bulan != $now->month || $tahun != $now->year)
                        <a href="{{ route('walikelas.kehadiran', ['kelas' => $selectedKelas, 'bulan' => $now->month, 'tahun' => $now->year]) }}" 
                           class="btn btn-light btn-sm text-slate-600 border rounded-pill px-3 py-1.5" style="font-size: 12px;">
                            Bulan Ini
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Analytics KPI Cards -->
    <div class="row g-3">
        <!-- Total Siswa -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400 fw-bold" style="font-size: 10.5px; letter-spacing: 0.05em;">SISWA</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" 
                         style="width: 32px; height: 32px; background: #e0e7ff;">
                        <i class="fa-solid fa-users" style="font-size: 13px;"></i>
                    </div>
                </div>
                <div class="fw-bold text-slate-800 fs-5 mb-0">{{ $analytics['total_siswa'] }}</div>
                <small class="text-slate-400" style="font-size: 10.5px;">Kelas {{ $selectedKelas }}</small>
            </div>
        </div>

        <!-- Hadir -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400 fw-bold" style="font-size: 10.5px; letter-spacing: 0.05em;">HADIR (H)</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-success" 
                         style="width: 32px; height: 32px; background: #dcfce7;">
                        <i class="fa-solid fa-circle-check" style="font-size: 13px;"></i>
                    </div>
                </div>
                <div class="fw-bold text-success fs-5 mb-0">{{ $analytics['total_hadir'] }}</div>
                <small class="text-slate-400" style="font-size: 10.5px;">Total kehadiran</small>
            </div>
        </div>

        <!-- Sakit -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400 fw-bold" style="font-size: 10.5px; letter-spacing: 0.05em;">SAKIT (S)</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-warning" 
                         style="width: 32px; height: 32px; background: #fef3c7;">
                        <i class="fa-solid fa-notes-medical" style="font-size: 13px;"></i>
                    </div>
                </div>
                <div class="fw-bold text-warning fs-5 mb-0">{{ $analytics['total_sakit'] }}</div>
                <small class="text-slate-400" style="font-size: 10.5px;">Surat dokter/izin</small>
            </div>
        </div>

        <!-- Izin -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400 fw-bold" style="font-size: 10.5px; letter-spacing: 0.05em;">IZIN (I)</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-info" 
                         style="width: 32px; height: 32px; background: #e0f2fe;">
                        <i class="fa-solid fa-envelope-open-text" style="font-size: 13px;"></i>
                    </div>
                </div>
                <div class="fw-bold text-primary fs-5 mb-0">{{ $analytics['total_izin'] }}</div>
                <small class="text-slate-400" style="font-size: 10.5px;">Izin orang tua</small>
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
                <div class="fw-bold text-danger fs-5 mb-0">{{ $analytics['total_alpha'] }}</div>
                <small class="text-slate-400" style="font-size: 10.5px;">Tanpa keterangan</small>
            </div>
        </div>

        <!-- Rata-rata % Kehadiran -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400 fw-bold" style="font-size: 10.5px; letter-spacing: 0.05em;">RATA-RATA</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-indigo" 
                         style="width: 32px; height: 32px; background: #ede9fe; color: #7c3aed;">
                        <i class="fa-solid fa-chart-pie" style="font-size: 13px;"></i>
                    </div>
                </div>
                <div class="fw-bold text-indigo fs-5 mb-0" style="color: #6366f1;">{{ $analytics['avg_persen'] }}%</div>
                <small class="text-slate-400" style="font-size: 10.5px;">Kehadiran kelas</small>
            </div>
        </div>
    </div>

    <!-- Attendance Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 34px; height: 34px; background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-table-list"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-slate-800 mb-0" style="font-size: 13.5px;">
                        Tabel Rekapitulasi Presensi — Bulan {{ $daftarBulan[$bulan] ?? '' }} {{ $tahun }}
                    </h6>
                    <small class="text-slate-400" style="font-size: 11.5px;">
                        Total {{ $analytics['total_siswa'] }} siswa &bull; {{ $analytics['total_hari_efektif'] }} hari efektif presensi tercatat
                    </small>
                </div>
            </div>

            <!-- Client-Side Search Box -->
            <div style="min-width: 240px;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-slate-50 border-end-0 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" id="tableSearchInput" class="form-control form-control-sm border-start-0 ps-0 bg-slate-50" 
                           placeholder="Cari siswa atau NIS..." style="font-size: 12px;" onkeyup="filterStudentTable()">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="studentAttendanceTable" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3.5 text-slate-500 font-semibold" style="font-size: 11.5px; width: 50px;">NO</th>
                        <th class="text-slate-500 font-semibold" style="font-size: 11.5px; width: 110px;">NIS</th>
                        <th class="text-slate-500 font-semibold" style="font-size: 11.5px; min-width: 200px;">NAMA LENGKAP SISWA</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 60px;">L/P</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 90px;">HADIR (H)</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 90px;">SAKIT (S)</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 90px;">IZIN (I)</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 90px;">ALPA (A)</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 100px;">TOTAL HARI</th>
                        <th class="pe-3.5 text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 160px;">% KEHADIRAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswas as $index => $siswa)
                        @php
                            $item = $rekapSiswa[$siswa->id] ?? [
                                'hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'total' => 0, 'persen' => 100
                            ];
                            $persen = $item['persen'];
                        @endphp
                        <tr class="student-row">
                            <td class="ps-3.5 fw-semibold text-slate-400">{{ $index + 1 }}</td>
                            <td class="text-slate-600 font-monospace small student-nis">{{ $siswa->nis }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width: 30px; height: 30px; font-size: 11.5px; background: {{ $siswa->jenis_kelamin === 'P' ? 'linear-gradient(135deg, #ec4899, #db2777)' : 'linear-gradient(135deg, #3b82f6, #1d4ed8)' }};">
                                        {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                                    </div>
                                    <span class="fw-semibold text-slate-800 student-name">{{ $siswa->nama }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill {{ $siswa->jenis_kelamin === 'P' ? 'bg-pink-subtle text-pink' : 'bg-primary-subtle text-primary' }}" 
                                      style="font-size: 10.5px; {{ $siswa->jenis_kelamin === 'P' ? 'background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8;' : 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;' }}">
                                    {{ $siswa->jenis_kelamin }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill fw-bold px-2.5 py-1" style="background: #dcfce7; color: #15803d; font-size: 11.5px; border: 1px solid #bbf7d0;">
                                    {{ $item['hadir'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($item['sakit'] > 0)
                                    <span class="badge rounded-pill fw-semibold px-2 py-1" style="background: #fef3c7; color: #92400e; font-size: 11px; border: 1px solid #fde68a;">
                                        {{ $item['sakit'] }}
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item['izin'] > 0)
                                    <span class="badge rounded-pill fw-semibold px-2 py-1" style="background: #e0f2fe; color: #0369a1; font-size: 11px; border: 1px solid #bae6fd;">
                                        {{ $item['izin'] }}
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item['alpha'] > 0)
                                    <span class="badge rounded-pill fw-bold px-2 py-1" style="background: #fee2e2; color: #b91c1c; font-size: 11.5px; border: 1px solid #fecaca;">
                                        {{ $item['alpha'] }}
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="text-center fw-semibold text-slate-700">
                                {{ $item['total'] }}
                            </td>
                            <td class="pe-3.5 text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; border-radius: 999px; background: #e2e8f0; max-width: 70px;">
                                        <div class="progress-bar rounded-pill" role="progressbar" 
                                             style="width: {{ $persen }}%; background: {{ $persen >= 85 ? '#10b981' : ($persen >= 75 ? '#f59e0b' : '#ef4444') }};" 
                                             aria-valuenow="{{ $persen }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <span class="fw-bold" style="font-size: 11.5px; color: {{ $persen >= 85 ? '#059669' : ($persen >= 75 ? '#d97706' : '#dc2626') }}; min-width: 42px;">
                                        {{ $persen }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-slate-400">
                                <div class="py-4">
                                    <i class="fa-regular fa-folder-open fs-1 mb-2 text-slate-300"></i>
                                    <h6 class="fw-semibold text-slate-700">Belum Ada Data Siswa</h6>
                                    <p class="small text-slate-400 mb-0">Tidak ada siswa yang terdaftar di rombel kelas {{ $selectedKelas }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Footer Total -->
                <tfoot class="table-light border-top">
                    <tr class="fw-bold text-slate-700" style="font-size: 12px;">
                        <td colspan="4" class="ps-3.5 py-2.5 text-uppercase">TOTAL KELAS {{ $selectedKelas }}</td>
                        <td class="text-center text-success py-2.5">{{ $analytics['total_hadir'] }}</td>
                        <td class="text-center text-warning py-2.5">{{ $analytics['total_sakit'] }}</td>
                        <td class="text-center text-primary py-2.5">{{ $analytics['total_izin'] }}</td>
                        <td class="text-center text-danger py-2.5">{{ $analytics['total_alpha'] }}</td>
                        <td class="text-center py-2.5">{{ $analytics['total_hari_efektif'] }} hari</td>
                        <td class="pe-3.5 text-center text-indigo py-2.5" style="color: #6366f1;">
                            {{ $analytics['avg_persen'] }}% Rata-rata
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function filterStudentTable() {
        const query = document.getElementById('tableSearchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.student-row');

        rows.forEach(row => {
            const name = row.querySelector('.student-name').textContent.toLowerCase();
            const nis = row.querySelector('.student-nis').textContent.toLowerCase();
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
