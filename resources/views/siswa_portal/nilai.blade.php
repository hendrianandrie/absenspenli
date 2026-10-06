@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Page Title -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge rounded-pill px-3 py-1 bg-indigo-50 text-indigo-700" 
                      style="background: #e0e7ff; color: #4338ca; font-weight: 600; font-size: 11px;">
                    <i class="fa-solid fa-star me-1"></i> Penilaian Riil
                </span>
                <span class="text-slate-400 small">&bull;</span>
                <span class="text-slate-500 small">Kelas {{ $siswa->kelas }}</span>
            </div>
            <h3 class="fw-bold text-slate-800 mb-0">Rekapitulasi Nilai Siswa Per Mapel</h3>
            <p class="text-slate-500 small mb-0">
                Daftar seluruh kegiatan tugas, ulangan harian, dan ujian riil yang telah dilaksanakan untuk mata pelajaran pilihanmu.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('siswa.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Subject Selector Bar (Pill Bar & Dropdown for Responsiveness) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('siswa.nilai') }}" method="GET" id="mapelFilterForm">
                <div class="row align-items-center g-3">
                    <div class="col-md-5 col-lg-4">
                        <label for="mapelSelect" class="form-label small fw-semibold text-slate-700 mb-1">
                            <i class="fa-solid fa-book-open text-primary me-1"></i> Pilih Mata Pelajaran:
                        </label>
                        <select name="mapel_id" id="mapelSelect" class="form-select form-select-md rounded-3 border-slate-300" onchange="document.getElementById('mapelFilterForm').submit()">
                            @foreach($mapels as $mapel)
                                <option value="{{ $mapel->id }}" {{ optional($selectedMapel)->id == $mapel->id ? 'selected' : '' }}>
                                    {{ $mapel->nama_mapel }} (KKM: {{ $mapel->kkm ?? 75 }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-7 col-lg-8">
                        <!-- Horizontal Quick Pills for Desktop -->
                        <div class="d-none d-md-flex align-items-center gap-2 overflow-auto py-1" style="white-space: nowrap;">
                            <span class="text-slate-400 small me-1">Pintasan:</span>
                            @foreach($mapels->take(6) as $m)
                                <a href="{{ route('siswa.nilai', ['mapel_id' => $m->id]) }}" 
                                   class="btn btn-sm rounded-pill px-3 {{ optional($selectedMapel)->id == $m->id ? 'btn-primary' : 'btn-light border text-slate-600' }}"
                                   style="font-size: 12px;">
                                    {{ $m->nama_mapel }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedMapel)
        <!-- Selected Subject KPI Banner -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" 
                             style="width: 44px; height: 44px; background: #e0e7ff;">
                            <i class="fa-solid fa-clipboard-list fs-5"></i>
                        </div>
                        <div>
                            <div class="text-slate-400" style="font-size: 11px; font-weight: 600;">TOTAL KEGIATAN</div>
                            <h4 class="fw-bold text-slate-800 mb-0">{{ $statsMapel['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-success" 
                             style="width: 44px; height: 44px; background: #dcfce7;">
                            <i class="fa-solid fa-circle-check fs-5"></i>
                        </div>
                        <div>
                            <div class="text-slate-400" style="font-size: 11px; font-weight: 600;">STATUS TUNTAS</div>
                            <h4 class="fw-bold text-success mb-0">{{ $statsMapel['tuntas'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-danger" 
                             style="width: 44px; height: 44px; background: #fee2e2;">
                            <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                        </div>
                        <div>
                            <div class="text-slate-400" style="font-size: 11px; font-weight: 600;">REMEDIAL</div>
                            <h4 class="fw-bold text-danger mb-0">{{ $statsMapel['remedial'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-indigo" 
                             style="width: 44px; height: 44px; background: #ede9fe; color: #7c3aed;">
                            <i class="fa-solid fa-bullseye fs-5"></i>
                        </div>
                        <div>
                            <div class="text-slate-400" style="font-size: 11px; font-weight: 600;">STANDAR KKM</div>
                            <h4 class="fw-bold text-slate-800 mb-0">{{ $selectedMapel->kkm ?? 75 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table of Assessments -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px; background: #e0e7ff; color: #4338ca;">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-slate-800 mb-0 fs-6">
                            Penilaian: {{ $selectedMapel->nama_mapel }}
                        </h5>
                        <small class="text-slate-400">Kelas {{ $siswa->kelas }} &bull; Standar KKM: {{ $selectedMapel->kkm ?? 75 }}</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill bg-light border text-slate-600 px-3 py-2" style="font-size: 12px;">
                        <i class="fa-solid fa-check-double text-success me-1"></i> {{ $statsMapel['dinilai'] }} / {{ $statsMapel['total'] }} Kegiatan Dinilai
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 text-slate-500 font-semibold" style="font-size: 12px; width: 60px;">NO</th>
                            <th class="text-slate-500 font-semibold" style="font-size: 12px; width: 140px;">TANGGAL</th>
                            <th class="text-slate-500 font-semibold" style="font-size: 12px;">NAMA KEGIATAN PENILAIAN</th>
                            <th class="text-slate-500 font-semibold text-center" style="font-size: 12px; width: 130px;">JENIS</th>
                            <th class="text-slate-500 font-semibold text-center" style="font-size: 12px; width: 110px;">NILAI ANDA</th>
                            <th class="text-slate-500 font-semibold text-center" style="font-size: 12px; width: 90px;">KKM</th>
                            <th class="pe-4 text-slate-500 font-semibold text-center" style="font-size: 12px; width: 150px;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kegiatanList as $index => $item)
                            <tr>
                                <td class="ps-4 fw-semibold text-slate-400">{{ $index + 1 }}</td>
                                <td class="text-slate-600 font-monospace small">
                                    {{ $item['tanggal'] }}
                                </td>
                                <td>
                                    <div class="fw-semibold text-slate-800">{{ $item['nama_kegiatan'] }}</div>
                                </td>
                                <td class="text-center">
                                    @if($item['jenis'] === 'Tugas')
                                        <span class="badge rounded-pill" style="background: #e0f2fe; color: #0369a1; font-size: 11px;">
                                            <i class="fa-solid fa-file-pen me-1"></i> Tugas
                                        </span>
                                    @elseif($item['jenis'] === 'UH')
                                        <span class="badge rounded-pill" style="background: #fef3c7; color: #92400e; font-size: 11px;">
                                            <i class="fa-solid fa-pencil me-1"></i> Ulangan Harian
                                        </span>
                                    @elseif($item['jenis'] === 'UTS')
                                        <span class="badge rounded-pill" style="background: #ede9fe; color: #6d28d9; font-size: 11px;">
                                            <i class="fa-solid fa-graduation-cap me-1"></i> UTS
                                        </span>
                                    @elseif($item['jenis'] === 'UAS')
                                        <span class="badge rounded-pill" style="background: #fae8ff; color: #86198f; font-size: 11px;">
                                            <i class="fa-solid fa-award me-1"></i> UAS
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-light text-slate-700 border" style="font-size: 11px;">
                                            {{ $item['jenis'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item['nilai'] !== null)
                                        <span class="fw-bold fs-6 {{ $item['nilai'] >= $item['kkm'] ? 'text-success' : 'text-danger' }}">
                                            {{ $item['nilai'] }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1" style="font-size: 11px;">
                                            Belum Dinilai
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center text-slate-500 font-monospace small">
                                    {{ $item['kkm'] }}
                                </td>
                                <td class="pe-4 text-center">
                                    @if($item['status'] === 'Tuntas')
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-1-5" style="font-size: 11px;">
                                            <i class="fa-solid fa-circle-check me-1"></i> Tuntas
                                        </span>
                                    @elseif($item['status'] === 'Perlu Remedial')
                                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3 py-1-5" style="font-size: 11px;">
                                            <i class="fa-solid fa-rotate-right me-1"></i> Perlu Remedial
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-slate-100 text-slate-500 border px-3 py-1-5" style="font-size: 11px;">
                                            <i class="fa-regular fa-clock me-1"></i> Menunggu Guru
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-slate-400">
                                    <div class="py-4">
                                        <i class="fa-regular fa-folder-open fs-1 mb-2 text-slate-300"></i>
                                        <h6 class="fw-semibold text-slate-700">Belum Ada Kegiatan Penilaian</h6>
                                        <p class="small text-slate-400 mb-0">Guru mata pelajaran {{ $selectedMapel->nama_mapel }} belum membuat kegiatan penilaian untuk kelas {{ $siswa->kelas }}.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Card Footer Note -->
            <div class="card-footer bg-slate-50 py-3 px-4 border-top">
                <div class="d-flex align-items-center gap-2 text-slate-500 small">
                    <i class="fa-solid fa-circle-info text-primary"></i>
                    <span>Catatan: Nilai di atas merupakan nilai riil per kegiatan tanpa pembobotan rapor. Jika ada perbaikan nilai remedial, nilai akan diperbarui setelah guru menginput revisi.</span>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
            <i class="fa-solid fa-book-bookmark fs-1 text-slate-300 mb-3"></i>
            <h5 class="fw-bold text-slate-700">Tidak Ada Mata Pelajaran Ditemukan</h5>
            <p class="text-slate-400 small mb-0">Silakan hubungi administrator atau guru kurikulum.</p>
        </div>
    @endif
</div>
@endsection
