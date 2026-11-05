@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <a href="{{ route('ujian.soal', $ujian->id) }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Bank Soal
            </a>
            <h4 class="fw-bold text-slate-800 mb-1">
                <i class="fa-solid fa-clipboard-check text-primary me-2"></i>Koreksi Essay: {{ $ujian->judul }}
            </h4>
            <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
                <span class="badge bg-indigo-subtle text-indigo" style="background:#e0e7ff; color:#3730a3;">{{ $ujian->jenis }}</span>
                <span>•</span>
                <span>{{ optional($ujian->mataPelajaran)->nama_mapel }}</span>
                <span>•</span>
                <span>Kelas {{ $ujian->kelas }}</span>
                <span>•</span>
                <span>Total Butir Essay: <strong>{{ $essaySoals->count() }} Soal</strong></span>
            </div>
        </div>

        <div>
            <a href="{{ route('ujian.monitoring', $ujian->id) }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-display me-1"></i> Live Monitor
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Students Submission List -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="fw-bold text-slate-800">
                <i class="fa-solid fa-users-viewfinder text-primary me-2"></i>Daftar Lembar Ujian Siswa
            </div>
            <span class="badge bg-slate-100 text-slate-700 border">{{ $sesis->count() }} Siswa Telah Mengumpulkan</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-slate-600 small text-uppercase">
                    <tr>
                        <th class="ps-4" style="width: 50px;">No</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Waktu Selesai</th>
                        <th class="text-center">Status Koreksi</th>
                        <th class="text-center">Nilai Akhir</th>
                        <th class="text-end pe-4" style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sesis as $idx => $s)
                        @php
                            $isPerluKoreksi = $s->status_koreksi === 'menunggu_koreksi';
                        @endphp
                        <tr>
                            <td class="ps-4 fw-medium text-slate-500">{{ $idx + 1 }}</td>
                            <td>
                                <div class="fw-bold text-slate-800">{{ optional($s->siswa)->nama }}</div>
                                <div class="small text-muted">NISN: {{ optional($s->siswa)->nisn ?: '-' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ optional($s->siswa)->kelas }}</span>
                            </td>
                            <td class="small text-muted">
                                {{ $s->waktu_selesai ? $s->waktu_selesai->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="text-center">
                                @if($isPerluKoreksi)
                                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill shadow-xs">
                                        <i class="fa-solid fa-clock me-1"></i>Menunggu Koreksi
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill">
                                        <i class="fa-solid fa-check-circle me-1"></i>Sudah Dinilai
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="fs-5 fw-bold {{ $s->nilai_akhir >= 75 ? 'text-success' : 'text-danger' }}">
                                    {{ $s->nilai_akhir }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('ujian.koreksi.siswa', [$ujian->id, $s->id]) }}" 
                                   class="btn btn-sm {{ $isPerluKoreksi ? 'btn-primary' : 'btn-outline-primary' }} px-3 shadow-xs">
                                    <i class="fa-solid {{ $isPerluKoreksi ? 'fa-pen-to-square' : 'fa-eye' }} me-1"></i>
                                    {{ $isPerluKoreksi ? 'Koreksi Essay' : 'Lihat / Edit Nilai' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-user-clock fs-1 text-slate-300 d-block mb-2"></i>
                                <div>Belum ada siswa yang menyelesaikan asesmen ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

