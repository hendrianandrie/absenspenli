@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-calendar-check text-primary me-2"></i> Fast Check-in Absensi Harian</h3>
        <p class="text-muted mb-0">Secara default seluruh siswa diset **Hadir**. Anda cukup mengubah yang Sakit, Izin, atau Alpha.</p>
    </div>
</div>

<div class="card card-custom p-4 bg-white mb-4">
    <form method="GET" action="{{ route('absensi.harian') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-semibold fs-7"><i class="fa-regular fa-calendar me-1"></i> Tanggal Absensi</label>
            <input type="date" name="tanggal" class="form-control" value="{{ $tanggal ?? date('Y-m-d') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold fs-7"><i class="fa-solid fa-chalkboard-user me-1"></i> Pilih Kelas</label>
            <select name="kelas" class="form-select" required>
                <option value="">-- Pilih Kelas --</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k }}" {{ ($kelas ?? '') == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary-custom w-100 py-2">
                <i class="fa-solid fa-filter me-1"></i> Tampilkan Daftar Siswa
            </button>
        </div>
    </form>
</div>

@if (!empty($kelas) && $siswas->count() > 0)
    <form method="POST" action="{{ route('absensi.harian.simpan') }}">
        @csrf
        <input type="hidden" name="tanggal" value="{{ $tanggal ?? date('Y-m-d') }}">
        <input type="hidden" name="kelas" value="{{ $kelas }}">

        <div class="card card-custom p-4 bg-white shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Daftar Presensi Kelas {{ $kelas }} ({{ count($siswas) }} Siswa)</h5>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="setAllHadir()">
                    <i class="fa-solid fa-check-double me-1"></i> Tandai Semua Hadir
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th class="text-center">L/P</th>
                            <th class="text-center" style="width: 320px;">Status Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($siswas as $index => $siswa)
                            @php
                                $existingAbsen = \App\Models\Absensi::where('siswa_id', $siswa->id)
                                    ->where('tanggal', $tanggal ?? date('Y-m-d'))
                                    ->first();
                                $currentStatus = $existingAbsen ? $existingAbsen->status : 'Hadir';
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><span class="badge bg-light text-dark border font-monospace">{{ $siswa->nis }}</span></td>
                                <td class="fw-semibold">{{ $siswa->nama }}</td>
                                <td class="text-center"><span class="badge bg-secondary-subtle text-secondary">{{ $siswa->jenis_kelamin }}</span></td>
                                <td class="text-center">
                                    <div class="btn-group w-100" role="group" aria-label="Status {{ $siswa->id }}">
                                        <input type="radio" class="btn-check status-hadir" name="absensi[{{ $siswa->id }}]" id="h_{{ $siswa->id }}" value="Hadir" {{ $currentStatus == 'Hadir' ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-success btn-sm" for="h_{{ $siswa->id }}">Hadir</label>

                                        <input type="radio" class="btn-check" name="absensi[{{ $siswa->id }}]" id="s_{{ $siswa->id }}" value="Sakit" {{ $currentStatus == 'Sakit' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-info btn-sm" for="s_{{ $siswa->id }}">Sakit</label>

                                        <input type="radio" class="btn-check" name="absensi[{{ $siswa->id }}]" id="i_{{ $siswa->id }}" value="Izin" {{ $currentStatus == 'Izin' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-warning btn-sm" for="i_{{ $siswa->id }}">Izin</label>

                                        <input type="radio" class="btn-check" name="absensi[{{ $siswa->id }}]" id="a_{{ $siswa->id }}" value="Alpha" {{ $currentStatus == 'Alpha' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-danger btn-sm" for="a_{{ $siswa->id }}">Alpha</label>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-success px-4 py-2 rounded-3 shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Absensi Kelas {{ $kelas }}
                </button>
            </div>
        </div>
    </form>
@elseif(!empty($kelas))
    <div class="alert alert-info text-center py-4">
        <i class="fa-solid fa-info-circle fa-2x mb-2 d-block"></i>
        Belum ada data siswa terdaftar di Kelas {{ $kelas }}.
    </div>
@endif

@push('scripts')
<script>
    function setAllHadir() {
        document.querySelectorAll('.status-hadir').forEach(function(radio) {
            radio.checked = true;
        });
    }
</script>
@endpush
@endsection
