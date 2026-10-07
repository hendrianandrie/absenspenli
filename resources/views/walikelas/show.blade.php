@extends('layouts.app')

@section('content')
<div class="d-flex flex-column gap-3">
    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge rounded-pill px-2.5 py-1" style="background: #ecfdf5; color: #047857; font-size: 11px;">
                    <i class="fa-solid fa-user-tie me-1"></i> Kelas Binaan
                </span>
                <span class="text-slate-400" style="font-size: 12px;">•</span>
                <span class="text-slate-500 small" style="font-size: 12px;">Wali Kelas: <strong>{{ $waliKelas?->user?->name ?? 'Belum Ditentukan' }}</strong></span>
            </div>
            <h2 class="fw-bold mb-1 text-slate-800" style="font-size: 1.4rem; letter-spacing: -0.02em;">
                <i class="fa-solid fa-graduation-cap text-teal me-2" style="color: #0f766e;"></i> Data Nilai Siswa — Kelas {{ $selectedKelas }}
            </h2>
            <p class="text-slate-500 mb-0" style="font-size: 13px;">
                Rekapitulasi perolehan nilai mata pelajaran, rata-rata akademik, dan absensi siswa untuk rombel kelas binaan Anda.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('walikelas.kehadiran', ['kelas' => $selectedKelas]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 shadow-xs" style="font-size: 12px;">
                <i class="fa-solid fa-clipboard-user me-1.5"></i> Rekap Kehadiran
            </a>
            @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('walikelas.index') }}" class="btn btn-light btn-sm text-slate-700 border rounded-pill px-3 py-1.5" style="font-size: 12px;">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Pengaturan
                </a>
            @endif
        </div>
    </div>

    <!-- Analytics KPI Cards -->
    <div class="row g-3">
        <div class="col-sm-6 col-xl-3">
            <div class="cruip-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="mosaic-icon-box" style="width: 36px; height: 36px; background: #eef2ff; color: #4f46e5; border-radius: 10px;">
                        <i class="fa-solid fa-users" style="font-size: 13.5px;"></i>
                    </div>
                    <span class="badge rounded-pill fw-semibold px-2 py-0.5" style="background: #eef2ff; color: #4338ca; font-size: 10.5px;">
                        Kelas {{ $selectedKelas }}
                    </span>
                </div>
                <div class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Total Siswa Binaan</div>
                <div class="d-flex align-items-baseline gap-1.5 mt-1">
                    <div class="fw-bold text-slate-800" style="font-size: 1.45rem; line-height: 1.2;">{{ $analytics['total_siswa'] }}</div>
                    <span class="text-slate-400" style="font-size: 11px;">siswa terdaftar</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="cruip-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="mosaic-icon-box" style="width: 36px; height: 36px; background: #ecfdf5; color: #059669; border-radius: 10px;">
                        <i class="fa-solid fa-chart-line" style="font-size: 13.5px;"></i>
                    </div>
                    <span class="badge rounded-pill fw-semibold px-2 py-0.5" style="background: #ecfdf5; color: #047857; font-size: 10.5px;">
                        Rata-Rata Rombel
                    </span>
                </div>
                <div class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Rata-Rata Nilai Kelas</div>
                <div class="d-flex align-items-baseline gap-1.5 mt-1">
                    <div class="fw-bold text-success" style="font-size: 1.45rem; line-height: 1.2;">{{ $analytics['class_avg'] ?: '-' }}</div>
                    <span class="text-slate-400" style="font-size: 11px;">poin gabungan</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="cruip-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="mosaic-icon-box" style="width: 36px; height: 36px; background: #fef3c7; color: #d97706; border-radius: 10px;">
                        <i class="fa-solid fa-trophy" style="font-size: 13.5px;"></i>
                    </div>
                    <span class="badge rounded-pill fw-semibold px-2 py-0.5" style="background: #fef3c7; color: #b45309; font-size: 10.5px;">
                        Tertinggi
                    </span>
                </div>
                <div class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Nilai Tertinggi Siswa</div>
                <div class="d-flex align-items-baseline gap-1.5 mt-1">
                    <div class="fw-bold text-warning" style="font-size: 1.45rem; line-height: 1.2;">{{ $analytics['highest'] ?: '-' }}</div>
                    <span class="text-slate-400" style="font-size: 11px;">skor tertinggi</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="cruip-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="mosaic-icon-box" style="width: 36px; height: 36px; background: #f0fdf4; color: #16a34a; border-radius: 10px;">
                        <i class="fa-solid fa-book" style="font-size: 13.5px;"></i>
                    </div>
                    <span class="badge rounded-pill fw-semibold px-2 py-0.5" style="background: #f1f5f9; color: #475569; font-size: 10.5px;">
                        Tingkat {{ $tingkat }}
                    </span>
                </div>
                <div class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Jumlah Mata Pelajaran</div>
                <div class="d-flex align-items-baseline gap-1.5 mt-1">
                    <div class="fw-bold text-slate-800" style="font-size: 1.45rem; line-height: 1.2;">{{ $mapels->count() }}</div>
                    <span class="text-slate-400" style="font-size: 11px;">mapel aktif</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Score & Homeroom Matrix Card -->
    <div class="cruip-card overflow-hidden">
        <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3 bg-slate-50">
            <div class="d-flex align-items-center gap-2">
                <div class="fw-bold text-slate-800" style="font-size: 13.5px;">
                    <i class="fa-solid fa-table-list text-primary me-1.5"></i> Rekapitulasi Nilai Siswa Per Mata Pelajaran
                </div>
                <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 11px;">
                    {{ $siswas->count() }} Siswa
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Dropdown Download Excel -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 11.5px;">
                        <i class="fa-solid fa-file-excel me-1 text-success"></i> Unduh Excel
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="font-size: 12px; min-width: 250px;">
                        <li>
                            <a class="dropdown-item py-2 fw-semibold text-success" href="{{ route('walikelas.exportExcel', ['kelas' => $selectedKelas]) }}">
                                <i class="fa-solid fa-table-cells me-2"></i> Rekap Semua Mapel (Leger Nilai)
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li class="dropdown-header text-uppercase text-slate-400 fw-bold" style="font-size: 10px;">Unduh Per Mata Pelajaran:</li>
                        @foreach($mapels as $m)
                            <li>
                                <a class="dropdown-item py-1.5" href="{{ route('nilai.rekapExcel', ['kelas' => $selectedKelas, 'mata_pelajaran_id' => $m->id]) }}">
                                    <i class="fa-regular fa-file-excel text-muted me-2"></i> {{ $m->nama_mapel }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Search box -->
                <div class="position-relative" style="width: 220px;">
                    <input type="text" id="searchInput" class="form-control form-control-sm rounded-pill ps-4" placeholder="Cari nama atau NIS..." style="font-size: 11.5px;">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-2.5 text-slate-400" style="font-size: 11px;"></i>
                </div>
            </div>
        </div>

        <div class="table-responsive" style="max-height: 580px;">
            <table class="table table-hover table-bordered align-middle mb-0 text-center" id="nilaiTable" style="font-size: 12px; border-color: #e2e8f0;">
                <thead class="bg-slate-50 sticky-top" style="z-index: 5;">
                    <tr class="text-slate-600" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">
                        <th class="py-2 px-2" style="width: 40px;">No</th>
                        <th class="py-2 px-2 text-start" style="min-width: 170px;">Nama Siswa</th>
                        <th class="py-2 px-2" style="width: 50px;">L/P</th>
                        <!-- Dynamic Mapel Columns -->
                        @foreach($mapels as $m)
                            <th class="py-2 px-2 text-nowrap" style="min-width: 85px;" title="{{ $m->nama_mapel }} (KKM: {{ $m->kkm ?? 75 }})">
                                <div class="fw-bold text-truncate" style="max-width: 100px;">{{ $m->nama_mapel }}</div>
                                <span class="badge bg-light text-secondary border" style="font-size: 9.5px;">KKM {{ $m->kkm ?? 75 }}</span>
                            </th>
                        @endforeach
                        <th class="py-2 px-2 bg-primary-subtle text-primary fw-bold" style="min-width: 90px;">Rata-Rata</th>
                        <th class="py-2 px-2" style="min-width: 95px;">Rapor PDF</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswas as $idx => $s)
                        @php
                            $rek = $rekapSiswa[$s->id] ?? null;
                            $overall = $rek['overall_avg'] ?? null;
                        @endphp
                        <tr class="student-row">
                            <td class="text-slate-400 fw-medium">{{ $idx + 1 }}</td>
                            <td class="text-start ps-2.5">
                                <div class="fw-bold text-slate-800 student-name">{{ $s->nama }}</div>
                                <small class="text-slate-400 student-nis" style="font-size: 10.5px;">NIS: {{ $s->nis ?: '-' }}</small>
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $s->jenis_kelamin === 'L' ? 'bg-info-subtle text-info' : 'bg-danger-subtle text-danger' }}" style="font-size: 10px;">
                                    {{ $s->jenis_kelamin }}
                                </span>
                            </td>

                            <!-- Scores per Subject -->
                            @foreach($mapels as $m)
                                @php
                                    $score = $rek['scores'][$m->id] ?? null;
                                    $kkm = $m->kkm ?? 75;
                                @endphp
                                <td>
                                    @if($score !== null)
                                        <span class="fw-bold {{ $score >= $kkm ? 'text-success' : 'text-danger' }}" style="font-size: 12px;">
                                            {{ number_format($score, 1) }}
                                        </span>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            @endforeach

                            <!-- Overall Average -->
                            <td class="bg-primary-subtle">
                                @if($overall !== null)
                                    <span class="fw-bold text-primary" style="font-size: 12.5px;">
                                        {{ number_format($overall, 1) }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <!-- Download Rapor PDF -->
                            <td>
                                <a href="{{ route('nilai.raporSiswaPdf', $s->id) }}"
                                   class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5 shadow-sm"
                                   style="font-size: 11px;"
                                   title="Unduh Rapor PDF {{ $s->nama }}">
                                    <i class="fa-solid fa-file-pdf me-1"></i> Rapor
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 5 + $mapels->count() }}" class="text-center py-4 text-slate-400">
                                <i class="fa-solid fa-users-slash fs-3 d-block mb-2"></i>
                                Belum ada siswa yang terdaftar di kelas {{ $selectedKelas }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top bg-slate-50 d-flex flex-wrap justify-content-between align-items-center gap-2 text-slate-500" style="font-size: 11.5px;">
            <div>
                <i class="fa-solid fa-circle-info text-info me-1"></i>
                Nilai yang ditampilkan dihitung berdasarkan bobot kegiatan penilaian (Tugas, UH, UTS, UAS) yang relevan untuk Tingkat {{ $tingkat }}.
            </div>
            <div>
                Total Siswa: <strong>{{ $siswas->count() }} orang</strong>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#nilaiTable tbody tr.student-row');
            rows.forEach(row => {
                const name = row.querySelector('.student-name')?.textContent.toLowerCase() || '';
                const nis = row.querySelector('.student-nis')?.textContent.toLowerCase() || '';
                if (name.includes(query) || nis.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endsection
