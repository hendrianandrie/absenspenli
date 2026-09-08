@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-file-invoice text-primary me-2"></i> Rekap Absensi Harian</h3>
        <p class="text-muted mb-0">Pantau rekapitulasi kehadiran siswa berdasarkan rentang tanggal dan kelas.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
        <a href="{{ route('absensi.cetakPDF', ['tanggal_mulai' => $tanggalMulai, 'tanggal_sampai' => $tanggalSampai, 'kelas' => $kelas]) }}" class="btn btn-outline-danger shadow-sm" target="_blank">
            <i class="fa-solid fa-file-pdf me-1"></i> Cetak PDF
        </a>
    </div>
</div>

<!-- Filter Rentang Tanggal & Kelas -->
<div class="card card-custom p-4 bg-white mb-4 shadow-sm">
    <form action="{{ route('absensi.rekap') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-semibold fs-7"><i class="fa-regular fa-calendar me-1"></i> Dari Tanggal</label>
            <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai }}" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold fs-7"><i class="fa-regular fa-calendar-check me-1"></i> Sampai Tanggal</label>
            <input type="date" name="tanggal_sampai" value="{{ $tanggalSampai }}" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold fs-7"><i class="fa-solid fa-chalkboard-user me-1"></i> Filter Kelas</label>
            <select name="kelas" class="form-select">
                <option value="">Semua Kelas</option>
                @foreach($daftarKelas as $k)
                    <option value="{{ $k }}" {{ $kelas == $k ? 'selected' : '' }}>
                        Kelas {{ $k }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary-custom w-100 py-2" type="submit">
                <i class="fa-solid fa-filter me-1"></i> Tampilkan Rekap
            </button>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <!-- Tabel Rincian Absensi -->
    <div class="col-lg-7">
        <div class="card card-custom p-4 bg-white shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-list text-primary me-2"></i> Daftar Kehadiran Siswa</h5>
                <span class="badge bg-light text-dark border fs-7">
                    Periode: {{ \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($tanggalSampai)->format('d/m/Y') }}
                </span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th class="text-center">Kelas</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensis as $index => $absen)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="small font-monospace">{{ \Carbon\Carbon::parse($absen->tanggal)->format('d/m/Y') }}</td>
                                <td class="fw-semibold">{{ $absen->siswa->nama ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-primary fs-7">Kelas {{ $absen->siswa->kelas ?? '-' }}</span></td>
                                <td class="text-center">
                                    @if($absen->status == 'Hadir')
                                        <span class="badge bg-success px-3 py-1">Hadir</span>
                                    @elseif($absen->status == 'Izin')
                                        <span class="badge bg-warning text-dark px-3 py-1">Izin</span>
                                    @elseif($absen->status == 'Sakit')
                                        <span class="badge bg-info text-dark px-3 py-1">Sakit</span>
                                    @else
                                        <span class="badge bg-danger px-3 py-1">Alpha</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Tidak ada data absensi pada rentang tanggal ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Ringkasan Stat & Grafik Chart.js -->
    <div class="col-lg-5">
        <div class="card card-custom p-4 bg-white shadow-sm mb-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-chart-pie text-primary me-2"></i> Total Kehadiran Periode Ini</h5>
            <div class="row g-2 text-center mb-3">
                <div class="col-6">
                    <div class="p-2 border rounded bg-success-subtle text-success fw-bold">
                        Hadir: {{ $totalHadir }}
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 border rounded bg-info-subtle text-info fw-bold">
                        Sakit: {{ $totalSakit }}
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 border rounded bg-warning-subtle text-warning-emphasis fw-bold">
                        Izin: {{ $totalIzin }}
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 border rounded bg-danger-subtle text-danger fw-bold">
                        Alpha: {{ $totalAlpha }}
                    </div>
                </div>
            </div>

            <!-- Grafik Pie Chart -->
            <div style="max-width: 320px;" class="mx-auto">
                <canvas id="chartKehadiran"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('chartKehadiran').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Hadir', 'Izin', 'Sakit', 'Alpha'],
            datasets: [{
                label: 'Jumlah Presensi',
                data: [{{ $totalHadir }}, {{ $totalIzin }}, {{ $totalSakit }}, {{ $totalAlpha }}],
                backgroundColor: ['#22c55e', '#f59e0b', '#06b6d4', '#ef4444'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
@endpush
@endsection
