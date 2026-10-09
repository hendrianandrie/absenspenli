@extends('layouts.app')

@section('content')
<div class="d-flex flex-column gap-3">
    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('face.index', ['kelas' => $selectedKelas]) }}" class="text-decoration-none small text-slate-500">
                    <i class="fa-solid fa-arrow-left me-1"></i> Dashboard Biometrik
                </a>
                <span class="text-slate-300">•</span>
                <span class="badge rounded-pill px-2.5 py-1" style="background: #fef3c7; color: #b45309; font-size: 11px;">
                    <i class="fa-solid fa-clipboard-question me-1"></i> Penanganan Ketidakhadiran
                </span>
            </div>
            <h2 class="fw-bold mb-1 text-slate-800" style="font-size: 1.4rem; letter-spacing: -0.02em;">
                Kelola Siswa Belum Presensi — Kelas {{ $selectedKelas }}
            </h2>
            <p class="text-slate-500 mb-0" style="font-size: 13px;">
                Tentukan status siswa yang tidak hadir (Sakit, Izin, atau Alpa) secara cepat hanya dengan 1 kali klik.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('face.scanner', ['kelas' => $selectedKelas]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 shadow-xs" style="font-size: 12px;">
                <i class="fa-solid fa-expand me-1.5"></i> Buka Scanner Kamera
            </a>
            <a href="{{ route('face.index', ['kelas' => $selectedKelas]) }}" class="btn btn-light btn-sm text-slate-700 border rounded-pill px-3 py-1.5 shadow-xs" style="font-size: 12px;">
                <i class="fa-solid fa-table-list me-1.5"></i> Lihat Semua Siswa
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center gap-2 p-3 mb-0" style="background: #dcfce7; color: #15803d;">
            <i class="fa-solid fa-circle-check fs-6"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filter Bar & Bulk Actions -->
    <div class="card border-0 shadow-sm rounded-4 bg-white">
        <div class="card-body p-3 p-md-3.5">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <form action="{{ route('face.unattended') }}" method="GET" id="filterUnattendedForm" class="d-flex align-items-center gap-2">
                    <label for="selectKelasUnattended" class="form-label small fw-semibold text-slate-700 mb-0" style="font-size: 11.5px; white-space: nowrap;">
                        <i class="fa-solid fa-chalkboard text-primary me-1"></i> Rombel Kelas:
                    </label>
                    <select name="kelas" id="selectKelasUnattended" class="form-select form-select-sm rounded-3 border-slate-300" onchange="document.getElementById('filterUnattendedForm').submit()">
                        @foreach($daftarKelas as $k)
                            <option value="{{ $k }}" {{ $selectedKelas == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                        @endforeach
                    </select>
                </form>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if($belumPresensi->count() > 0)
                        <form action="{{ route('face.markBulkAlpa') }}" method="POST" onsubmit="return confirm('Tandai semua {{ $belumPresensi->count() }} siswa yang belum absen di kelas {{ $selectedKelas }} ini sebagai ALPA (A)?')">
                            @csrf
                            <input type="hidden" name="kelas" value="{{ $selectedKelas }}">
                            <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 py-1.5 shadow-xs fw-semibold" style="font-size: 12px;">
                                <i class="fa-solid fa-user-xmark me-1.5"></i> Tandai Semua Sisanya Sebagai Alpa ({{ $belumPresensi->count() }})
                            </button>
                        </form>
                    @endif
                    <span class="small text-slate-500">
                        Tanggal: <strong>{{ \Carbon\Carbon::parse($today)->translatedFormat('d F Y') }}</strong>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel 1: Siswa yang BELUM Presensi Hari Ini (Perlu Penanganan) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 34px; height: 34px; background: #fee2e2; color: #dc2626;">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-slate-800 mb-0" style="font-size: 13.5px;">
                        Siswa Belum Ada Catatan Presensi Hari Ini ({{ $belumPresensi->count() }} Siswa)
                    </h6>
                    <small class="text-slate-400" style="font-size: 11.5px;">
                        Siswa berikut belum scan wajah di gerbang/kamera hari ini. Klik tombol untuk menentukan statusnya.
                    </small>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3.5 text-slate-500 font-semibold" style="font-size: 11.5px; width: 50px;">NO</th>
                        <th class="text-slate-500 font-semibold" style="font-size: 11.5px; width: 110px;">NIS</th>
                        <th class="text-slate-500 font-semibold" style="font-size: 11.5px; min-width: 200px;">NAMA LENGKAP SISWA</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 60px;">L/P</th>
                        <th class="pe-3.5 text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 320px;">TENTUKAN KETERANGAN (1-KLIK)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($belumPresensi as $idx => $s)
                        <tr>
                            <td class="ps-3.5 fw-semibold text-slate-400">{{ $idx + 1 }}</td>
                            <td class="text-slate-600 font-monospace small">{{ $s->nis }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($s->foto_wajah)
                                        <img src="{{ asset('storage/' . $s->foto_wajah) }}" alt="Foto" 
                                             class="rounded-circle object-fit-cover border" width="30" height="30"
                                             onerror="this.onerror=null; this.outerHTML='<div class=\'rounded-circle text-white fw-bold d-flex align-items-center justify-content-center\' style=\'width: 30px; height: 30px; font-size: 11.5px; background: {{ $s->jenis_kelamin === \'P\' ? \'linear-gradient(135deg, #ec4899, #db2777)\' : \'linear-gradient(135deg, #3b82f6, #1d4ed8)\' }};\'>{{ strtoupper(substr($s->nama, 0, 1)) }}</div>';">
                                    @else
                                        <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center"
                                             style="width: 30px; height: 30px; font-size: 11.5px; background: {{ $s->jenis_kelamin === 'P' ? 'linear-gradient(135deg, #ec4899, #db2777)' : 'linear-gradient(135deg, #3b82f6, #1d4ed8)' }};">
                                            {{ strtoupper(substr($s->nama, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="fw-semibold text-slate-800">{{ $s->nama }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill {{ $s->jenis_kelamin === 'P' ? 'bg-pink-subtle text-pink' : 'bg-primary-subtle text-primary' }}" 
                                      style="font-size: 10.5px; {{ $s->jenis_kelamin === 'P' ? 'background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8;' : 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;' }}">
                                    {{ $s->jenis_kelamin }}
                                </span>
                            </td>
                            <td class="pe-3.5 text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1.5 flex-wrap">
                                    <!-- Tombol Hadir Manual -->
                                    <form action="{{ route('face.markStatus') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="siswa_id" value="{{ $s->id }}">
                                        <input type="hidden" name="status" value="Hadir">
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1" style="font-size: 11px;" title="Tandai Hadir Manual">
                                            <i class="fa-solid fa-check me-1"></i> Hadir
                                        </button>
                                    </form>

                                    <!-- Tombol Sakit -->
                                    <form action="{{ route('face.markStatus') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="siswa_id" value="{{ $s->id }}">
                                        <input type="hidden" name="status" value="Sakit">
                                        <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-2.5 py-1 text-dark" style="font-size: 11px; background: #fef9c3; border-color: #fde047;" title="Tandai Sakit">
                                            <i class="fa-solid fa-notes-medical me-1 text-warning-emphasis"></i> Sakit
                                        </button>
                                    </form>

                                    <!-- Tombol Izin -->
                                    <form action="{{ route('face.markStatus') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="siswa_id" value="{{ $s->id }}">
                                        <input type="hidden" name="status" value="Izin">
                                        <button type="submit" class="btn btn-sm btn-outline-info rounded-pill px-2.5 py-1 text-dark" style="font-size: 11px; background: #e0f2fe; border-color: #7dd3fc;" title="Tandai Izin">
                                            <i class="fa-solid fa-envelope-open-text me-1 text-primary"></i> Izin
                                        </button>
                                    </form>

                                    <!-- Tombol Alpa -->
                                    <form action="{{ route('face.markStatus') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="siswa_id" value="{{ $s->id }}">
                                        <input type="hidden" name="status" value="Alpha">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1" style="font-size: 11px;" title="Tandai Alpa">
                                            <i class="fa-solid fa-xmark me-1"></i> Alpa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-slate-400">
                                <div class="py-3">
                                    <i class="fa-solid fa-circle-check fs-1 text-success mb-2"></i>
                                    <h6 class="fw-bold text-slate-700">Semua Siswa Sudah Memiliki Catatan Presensi!</h6>
                                    <p class="small text-slate-400 mb-0">Tidak ada siswa di kelas {{ $selectedKelas }} yang berstatus menggantung hari ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabel 2: Siswa yang SUDAH Memiliki Catatan Presensi Hari Ini -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 34px; height: 34px; background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-slate-800 mb-0" style="font-size: 13.5px;">
                        Siswa yang Sudah Presensi Hari Ini ({{ $sudahPresensi->count() }} Siswa)
                    </h6>
                    <small class="text-slate-400" style="font-size: 11.5px;">
                        Status kehadiran siswa yang sudah terekam baik lewat scanner wajah maupun manual.
                    </small>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3.5 text-slate-500 font-semibold" style="font-size: 11.5px; width: 50px;">NO</th>
                        <th class="text-slate-500 font-semibold" style="font-size: 11.5px; width: 110px;">NIS</th>
                        <th class="text-slate-500 font-semibold" style="font-size: 11.5px; min-width: 200px;">NAMA LENGKAP SISWA</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 130px;">STATUS</th>
                        <th class="text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 130px;">METODE</th>
                        <th class="pe-3.5 text-slate-500 font-semibold text-center" style="font-size: 11.5px; width: 160px;">UBAH STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sudahPresensi as $idx => $s)
                        @php
                            $ab = $absensisToday[$s->id] ?? null;
                        @endphp
                        <tr>
                            <td class="ps-3.5 fw-semibold text-slate-400">{{ $idx + 1 }}</td>
                            <td class="text-slate-600 font-monospace small">{{ $s->nis }}</td>
                            <td class="fw-semibold text-slate-800">{{ $s->nama }}</td>
                            <td class="text-center">
                                @if($ab && $ab->status === 'Hadir')
                                    <span class="badge rounded-pill px-2.5 py-1" style="background: #dcfce7; color: #15803d; font-size: 11px; border: 1px solid #bbf7d0;">
                                        Hadir
                                    </span>
                                @elseif($ab && $ab->status === 'Sakit')
                                    <span class="badge rounded-pill px-2.5 py-1" style="background: #fef3c7; color: #92400e; font-size: 11px; border: 1px solid #fde68a;">
                                        Sakit
                                    </span>
                                @elseif($ab && $ab->status === 'Izin')
                                    <span class="badge rounded-pill px-2.5 py-1" style="background: #e0f2fe; color: #0369a1; font-size: 11px; border: 1px solid #bae6fd;">
                                        Izin
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2.5 py-1" style="background: #fee2e2; color: #b91c1c; font-size: 11px; border: 1px solid #fecaca;">
                                        Alpa
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($ab && $ab->metode === 'face')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" style="font-size: 10.5px;">
                                        <i class="fa-solid fa-camera me-1"></i> Scan Wajah
                                    </span>
                                @else
                                    <span class="badge bg-slate-100 text-slate-600 border rounded-pill px-2 py-0.5" style="font-size: 10.5px;">
                                        Manual
                                    </span>
                                @endif
                            </td>
                            <td class="pe-3.5 text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size: 11px;">
                                        Ubah Ke...
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-slate-200" style="font-size: 12px;">
                                        <li>
                                            <form action="{{ route('face.markStatus') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="siswa_id" value="{{ $s->id }}">
                                                <input type="hidden" name="status" value="Hadir">
                                                <button class="dropdown-item text-success" type="submit">
                                                    <i class="fa-solid fa-check me-2"></i> Hadir
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('face.markStatus') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="siswa_id" value="{{ $s->id }}">
                                                <input type="hidden" name="status" value="Sakit">
                                                <button class="dropdown-item text-warning" type="submit">
                                                    <i class="fa-solid fa-notes-medical me-2"></i> Sakit
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('face.markStatus') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="siswa_id" value="{{ $s->id }}">
                                                <input type="hidden" name="status" value="Izin">
                                                <button class="dropdown-item text-primary" type="submit">
                                                    <i class="fa-solid fa-envelope-open-text me-2"></i> Izin
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('face.markStatus') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="siswa_id" value="{{ $s->id }}">
                                                <input type="hidden" name="status" value="Alpha">
                                                <button class="dropdown-item text-danger" type="submit">
                                                    <i class="fa-solid fa-xmark me-2"></i> Alpa
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-slate-400">
                                Belum ada siswa yang diabsen hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
