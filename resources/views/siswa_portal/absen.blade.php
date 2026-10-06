@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Page Title -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge rounded-pill px-3 py-1 bg-indigo-50 text-indigo-700" 
                      style="background: #e0e7ff; color: #4338ca; font-weight: 600; font-size: 11px;">
                    <i class="fa-solid fa-calendar-check me-1"></i> Presensi Siswa
                </span>
                <span class="text-slate-400 small">&bull;</span>
                <span class="text-slate-500 small">Kelas {{ $siswa->kelas }}</span>
            </div>
            <h3 class="fw-bold text-slate-800 mb-0">Rekapitulasi Absensi Bulanan</h3>
            <p class="text-slate-500 small mb-0">
                Pantau riwayat kehadiran dan rekapitulasi kehadiran (Hadir, Sakit, Izin, Alpa) per bulan.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('siswa.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Month & Year Filter Form -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('siswa.absen') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <label for="filterBulan" class="form-label small fw-semibold text-slate-700 mb-1">
                        <i class="fa-regular fa-calendar text-primary me-1"></i> Pilih Bulan:
                    </label>
                    <select name="bulan" id="filterBulan" class="form-select rounded-3 border-slate-300" onchange="this.form.submit()">
                        @foreach($daftarBulan as $num => $namaBulan)
                            <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>
                                {{ $namaBulan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-6 col-md-3 col-lg-2">
                    <label for="filterTahun" class="form-label small fw-semibold text-slate-700 mb-1">
                        <i class="fa-regular fa-calendar-days text-primary me-1"></i> Tahun:
                    </label>
                    <select name="tahun" id="filterTahun" class="form-select rounded-3 border-slate-300" onchange="this.form.submit()">
                        @foreach($daftarTahun as $t)
                            <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>
                                {{ $t }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-12 col-md-5 col-lg-7 d-flex align-items-center justify-content-md-end gap-2 pt-2 pt-md-0">
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">
                        <i class="fa-solid fa-filter me-1"></i> Tampilkan
                    </button>
                    @php
                        $now = \Carbon\Carbon::now();
                    @endphp
                    @if($bulan != $now->month || $tahun != $now->year)
                        <a href="{{ route('siswa.absen', ['bulan' => $now->month, 'tahun' => $now->year]) }}" 
                           class="btn btn-light border rounded-3 px-3 fw-medium text-slate-600">
                            Bulan Ini
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Monthly Summary 4 Cards (Hadir, Sakit, Izin, Alpha) + Attendance Rate -->
    <div class="row g-3 mb-4">
        <!-- Hadir -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400" style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em;">HADIR</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 38px; height: 38px; background: #dcfce7; color: #16a34a;">
                        <i class="fa-solid fa-circle-check fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h2 class="fw-bold text-success mb-0">{{ $statsBulan['hadir'] }}</h2>
                    <span class="text-slate-400 small">Hari</span>
                </div>
                <div class="text-slate-400 mt-1" style="font-size: 11px;">
                    Kehadiran tepat waktu & tuntas
                </div>
            </div>
        </div>

        <!-- Sakit -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400" style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em;">SAKIT</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 38px; height: 38px; background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-notes-medical fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h2 class="fw-bold text-warning mb-0">{{ $statsBulan['sakit'] }}</h2>
                    <span class="text-slate-400 small">Hari</span>
                </div>
                <div class="text-slate-400 mt-1" style="font-size: 11px;">
                    Dengan surat izin sakit
                </div>
            </div>
        </div>

        <!-- Izin -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400" style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em;">IZIN</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 38px; height: 38px; background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-envelope-open-text fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h2 class="fw-bold text-primary mb-0">{{ $statsBulan['izin'] }}</h2>
                    <span class="text-slate-400 small">Hari</span>
                </div>
                <div class="text-slate-400 mt-1" style="font-size: 11px;">
                    Dengan pemberitahuan orang tua
                </div>
            </div>
        </div>

        <!-- Alpha -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-slate-400" style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em;">ALPA / TANPA KET.</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 38px; height: 38px; background: #fee2e2; color: #dc2626;">
                        <i class="fa-solid fa-circle-xmark fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h2 class="fw-bold text-danger mb-0">{{ $statsBulan['alpha'] }}</h2>
                    <span class="text-slate-400 small">Hari</span>
                </div>
                <div class="text-slate-400 mt-1" style="font-size: 11px;">
                    Tanpa keterangan resmi
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Percentage Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-2">
                <div>
                    <h6 class="fw-bold text-slate-800 mb-0">
                        Persentase Kehadiran {{ $daftarBulan[$bulan] ?? '' }} {{ $tahun }}
                    </h6>
                    <small class="text-slate-400">Total tercatat: {{ $statsBulan['total'] }} hari efektif presensi</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4 fw-bold {{ $statsBulan['persen'] >= 85 ? 'text-success' : ($statsBulan['persen'] >= 75 ? 'text-warning' : 'text-danger') }}">
                        {{ $statsBulan['persen'] }}%
                    </span>
                    <span class="badge {{ $statsBulan['persen'] >= 85 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill px-3 py-1-5">
                        {{ $statsBulan['persen'] >= 85 ? 'Sangat Baik' : 'Perlu Ditingkatkan' }}
                    </span>
                </div>
            </div>

            <div class="progress mt-2" style="height: 10px; border-radius: 999px; background: #e2e8f0;">
                <div class="progress-bar rounded-pill" role="progressbar" 
                     style="width: {{ $statsBulan['persen'] }}%; background: linear-gradient(90deg, #10b981 0%, #059669 100%);" 
                     aria-valuenow="{{ $statsBulan['persen'] }}" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Attendance Log Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-slate-800 mb-0 fs-6">
                    <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Riwayat Presensi Harian
                </h5>
                <small class="text-slate-400">Daftar presensi harian pada bulan {{ $daftarBulan[$bulan] ?? '' }} {{ $tahun }}</small>
            </div>
            <span class="badge rounded-pill bg-light border text-slate-600 px-3 py-2 font-monospace" style="font-size: 11px;">
                {{ $absensis->count() }} Data Tercatat
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 text-slate-500 font-semibold" style="font-size: 12px; width: 60px;">NO</th>
                        <th class="text-slate-500 font-semibold" style="font-size: 12px; width: 140px;">TANGGAL</th>
                        <th class="text-slate-500 font-semibold" style="font-size: 12px; width: 130px;">HARI</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 12px; width: 150px;">STATUS KEHADIRAN</th>
                        <th class="pe-4 text-slate-500 font-semibold" style="font-size: 12px;">KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensis as $index => $absen)
                        @php
                            $dateCarbon = \Carbon\Carbon::parse($absen->tanggal);
                        @endphp
                        <tr>
                            <td class="ps-4 fw-semibold text-slate-400">{{ $index + 1 }}</td>
                            <td class="text-slate-700 font-monospace">
                                {{ $dateCarbon->translatedFormat('d F Y') }}
                            </td>
                            <td>
                                <span class="fw-medium text-slate-800">{{ $dateCarbon->translatedFormat('l') }}</span>
                            </td>
                            <td class="text-center">
                                @if($absen->status === 'Hadir')
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-1-5" style="font-size: 11px;">
                                        <i class="fa-solid fa-circle-check me-1"></i> Hadir
                                    </span>
                                @elseif($absen->status === 'Sakit')
                                    <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle px-3 py-1-5 text-dark" style="font-size: 11px;">
                                        <i class="fa-solid fa-notes-medical me-1"></i> Sakit
                                    </span>
                                @elseif($absen->status === 'Izin')
                                    <span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle px-3 py-1-5" style="font-size: 11px;">
                                        <i class="fa-solid fa-envelope-open-text me-1"></i> Izin
                                    </span>
                                @elseif($absen->status === 'Alpha')
                                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3 py-1-5" style="font-size: 11px;">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> Alpa
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-light text-slate-600 border px-3 py-1-5" style="font-size: 11px;">
                                        {{ $absen->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="pe-4 text-slate-600">
                                {{ $absen->keterangan ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-slate-400">
                                <div class="py-4">
                                    <i class="fa-regular fa-calendar-xmark fs-1 mb-2 text-slate-300"></i>
                                    <h6 class="fw-semibold text-slate-700">Tidak Ada Data Presensi</h6>
                                    <p class="small text-slate-400 mb-0">Belum ada riwayat absensi yang tercatat untuk bulan {{ $daftarBulan[$bulan] ?? '' }} {{ $tahun }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
