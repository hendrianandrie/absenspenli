@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-list-check text-primary me-2"></i> Rekap Absensi Siswa</h3>
        <p class="text-muted mb-0">Riwayat catatan presensi siswa terurut dari yang terbaru.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
        <a href="{{ route('absensi.harian') }}" class="btn btn-primary-custom shadow-sm">
            <i class="fa-solid fa-calendar-check me-1"></i> Fast Check-in
        </a>
    </div>
</div>

<div class="card card-custom p-4 bg-white shadow-sm">
    <!-- Filter berdasarkan kelas -->
    <form method="GET" action="{{ route('absensi.index') }}" class="row g-3 align-items-center mb-4">
        <div class="col-md-4">
            <label class="form-label fw-semibold fs-7"><i class="fa-solid fa-filter me-1"></i> Filter Kelas</label>
            <select name="kelas" class="form-select" onchange="this.form.submit()">
                <option value="">-- Semua Kelas --</option>
                @foreach($daftarKelas as $k)
                    <option value="{{ $k }}" {{ $kelas == $k ? 'selected' : '' }}>
                        Kelas {{ $k }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    <!-- Tabel absensi -->
    @if($absensis->isEmpty())
        <div class="text-center py-4 text-muted">
            <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
            Belum ada riwayat absensi terdaftar.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Siswa</th>
                        <th class="text-center">Kelas</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($absensis as $index => $absen)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-semibold">{{ $absen->siswa->nama ?? '-' }}</td>
                            <td class="text-center"><span class="badge bg-primary fs-7">Kelas {{ $absen->siswa->kelas ?? '-' }}</span></td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($absen->tanggal)->format('d/m/Y') }}</td>
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
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
