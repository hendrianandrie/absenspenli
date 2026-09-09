@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-pen-to-square text-primary me-2"></i> Manajemen Nilai & Matriks Kelas</h3>
        <p class="text-muted mb-0">Kelola kegiatan penilaian (Tugas, UH, UTS, UAS) dan pantau Rata-rata Murni siswa.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        @if($selectedKelas && $selectedMapelId)
            <a href="{{ route('nilai.rekapExcel', ['kelas' => $selectedKelas, 'mata_pelajaran_id' => $selectedMapelId]) }}" class="btn btn-outline-success shadow-sm">
                <i class="fa-solid fa-file-excel me-1"></i> Rekap Excel
            </a>
            <a href="{{ route('nilai.rekapPdf', ['kelas' => $selectedKelas, 'mata_pelajaran_id' => $selectedMapelId]) }}" class="btn btn-outline-danger shadow-sm">
                <i class="fa-solid fa-file-pdf me-1"></i> Rekap PDF
            </a>
            <a href="{{ route('nilai.lembarKosongPdf', ['kelas' => $selectedKelas, 'mata_pelajaran_id' => $selectedMapelId]) }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fa-solid fa-print me-1"></i> Lembar Kosong PDF
            </a>
        @endif
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-custom p-4 bg-white mb-4">
    <form method="GET" action="{{ route('nilai.index') }}" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label fw-semibold fs-7"><i class="fa-solid fa-chalkboard-user me-1"></i> Pilih Kelas</label>
            <select name="kelas" class="form-select" onchange="this.form.submit()">
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k }}" {{ $selectedKelas == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label fw-semibold fs-7"><i class="fa-solid fa-book-open me-1"></i> Pilih Mata Pelajaran</label>
            <select name="mata_pelajaran_id" class="form-select" onchange="this.form.submit()">
                @foreach ($mapels as $m)
                    <option value="{{ $m->id }}" {{ $selectedMapelId == $m->id ? 'selected' : '' }}>
                        {{ $m->nama_mapel }} @if(($m->tingkat ?? 'Semua') !== 'Semua') [Tingkat {{ $m->tingkat }}] @endif (KKM: {{ $m->kkm }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary-custom w-100 py-2">
                <i class="fa-solid fa-filter me-1"></i> Filter
            </button>
        </div>
    </form>
</div>

@if($selectedKelas && $selectedMapel)
    <!-- Card Analytics Ketuntasan & Statistik Penilaian -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-success border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Ketuntasan KKM</span>
                        <h2 class="fw-bold mb-0 text-success">{{ $analytics['pct_tuntas'] }}%</h2>
                        <small class="text-muted" style="font-size: 11px;">{{ $analytics['tuntas_count'] }} Tuntas / {{ $analytics['belum_tuntas_count'] }} Remedial</small>
                    </div>
                    <div class="bg-success-subtle p-3 rounded-circle text-success">
                        <i class="fa-solid fa-circle-check fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-primary border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Rata-Rata Kelas</span>
                        <h2 class="fw-bold mb-0 text-primary">{{ $analytics['avg_kelas'] }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Target KKM: {{ $selectedMapel->kkm }}</small>
                    </div>
                    <div class="bg-primary-subtle p-3 rounded-circle text-primary">
                        <i class="fa-solid fa-chart-simple fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-info border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Nilai Tertinggi</span>
                        <h2 class="fw-bold mb-0 text-info">{{ $analytics['highest'] }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Skor terbaik kelas</small>
                    </div>
                    <div class="bg-info-subtle p-3 rounded-circle text-info">
                        <i class="fa-solid fa-trophy fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 bg-white stat-card border-start border-warning border-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Nilai Terendah</span>
                        <h2 class="fw-bold mb-0 text-warning">{{ $analytics['lowest'] }}</h2>
                        <small class="text-muted" style="font-size: 11px;">Perlu pembinaan</small>
                    </div>
                    <div class="bg-warning-subtle p-3 rounded-circle text-warning">
                        <i class="fa-solid fa-triangle-exclamation fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header Informasi & Tombol Tambah Kegiatan -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold mb-0">
                Matriks Penilaian Kelas {{ $selectedKelas }} — {{ $selectedMapel->nama_mapel }} 
                <span class="badge bg-primary fs-6 ms-2">KKM: {{ $selectedMapel->kkm }}</span>
            </h5>
            <small class="text-muted">
                Bobot Mapel: Tugas ({{ $selectedMapel->bobot_tugas ?? 20 }}%), UH ({{ $selectedMapel->bobot_uh ?? 30 }}%), UTS ({{ $selectedMapel->bobot_uts ?? 25 }}%), UAS ({{ $selectedMapel->bobot_uas ?? 25 }}%).
            </small>
        </div>
        <a href="{{ route('nilai.kegiatan.create', ['kelas' => $selectedKelas, 'mata_pelajaran_id' => $selectedMapelId]) }}" class="btn btn-success rounded-3 shadow-sm">
            <i class="fa-solid fa-plus-circle me-1"></i> Tambah Kegiatan Penilaian Baru
        </a>
    </div>

    <!-- Daftar Matriks Nilai -->
    <div class="card card-custom p-4 bg-white mb-4 shadow-sm">
        @if($siswas->isEmpty())
            <div class="text-center py-4 text-muted">Belum ada data siswa di kelas ini.</div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th class="text-start" style="min-width: 180px;">Nama Siswa</th>
                            @forelse($kegiatans as $k)
                                <th style="min-width: 120px;">
                                    <div class="fw-bold text-dark">{{ $k->nama_kegiatan }}</div>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $k->jenis }}</span>
                                    <div class="small text-muted fw-normal fs-8">{{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/y') }}</div>
                                    <div class="mt-1">
                                        <a href="{{ route('nilai.kegiatan.edit', $k->id) }}" class="text-primary text-decoration-none me-1" title="Edit Nilai Kegiatan"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <form action="{{ route('nilai.kegiatan.destroy', $k->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kegiatan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="border-0 bg-transparent text-danger p-0" title="Hapus Kegiatan"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </th>
                            @empty
                                <th class="text-muted fw-normal">Belum ada kegiatan penilaian</th>
                            @endforelse
                            <th class="bg-info-subtle text-info fw-bold" style="width: 120px;">Rata-Rata Murni</th>
                            <th class="bg-primary-subtle text-primary fw-bold" style="width: 130px;">Nilai Akhir Rapor</th>
                            <th class="bg-light fw-bold" style="width: 140px;">Status KKM</th>
                            <th class="bg-light fw-bold" style="width: 110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siswas as $idx => $siswa)
                            @php
                                $info = $rekapNilai[$siswa->id] ?? ['scores' => [], 'rata_rata' => null, 'nilai_akhir' => null, 'predikat' => '-', 'status_ketuntasan' => '-'];
                                $avgMurni = $info['rata_rata'];
                                $nilaiAkhir = $info['nilai_akhir'];
                                $kkm = $selectedMapel->kkm;
                            @endphp
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="text-start fw-semibold">{{ $siswa->nama }}</td>
                                @forelse($kegiatans as $k)
                                    @php
                                        $val = $info['scores'][$k->id] ?? null;
                                    @endphp
                                    <td>
                                        @if($val !== null)
                                            <span class="fw-bold {{ $val < $kkm ? 'text-danger' : 'text-dark' }}">{{ $val }}</span>
                                        @else
                                            <span class="text-muted fs-7">-</span>
                                        @endif
                                    </td>
                                @empty
                                    <td class="text-muted">-</td>
                                @endforelse
                                <td class="bg-info-subtle fw-semibold">
                                    {{ $avgMurni !== null ? $avgMurni : '-' }}
                                </td>
                                <td class="bg-primary-subtle fw-bold fs-6">
                                    @if($nilaiAkhir !== null)
                                        <span class="{{ $nilaiAkhir < $kkm ? 'text-danger' : 'text-primary' }}">{{ $nilaiAkhir }}</span>
                                    @else
                                        <span class="text-muted fs-7">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($nilaiAkhir !== null)
                                        @if($nilaiAkhir >= $kkm)
                                            <span class="badge badge-kkm-pass px-2 py-1 fs-7"><i class="fa-solid fa-circle-check me-1"></i> {{ $info['predikat'] }}</span>
                                        @else
                                            <span class="badge badge-kkm-fail px-2 py-1 fs-7"><i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $info['predikat'] }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted fs-7">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('nilai.raporSiswaPdf', $siswa->id) }}" class="btn btn-sm btn-outline-danger" title="Cetak Rapor Siswa PDF">
                                        <i class="fa-solid fa-file-pdf"></i> Rapor
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif
@endsection
