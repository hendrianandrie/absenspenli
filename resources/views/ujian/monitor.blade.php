@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <a href="{{ route('ujian.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Ujian
            </a>
            <h4 class="fw-bold text-slate-800 mb-1">
                <i class="fa-solid fa-display text-info me-2"></i>Live Monitor Asesmen: {{ $ujian->judul }}
            </h4>
            <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
                <span class="badge bg-indigo-subtle text-indigo" style="background:#e0e7ff; color:#3730a3;">{{ $ujian->jenis }}</span>
                <span>•</span>
                <span>{{ optional($ujian->mataPelajaran)->nama_mapel }}</span>
                <span>•</span>
                <span>Target: <strong>Kelas {{ $ujian->kelas }}</strong></span>
                <span>•</span>
                <span>Token: <code class="bg-light px-2 py-0.5 rounded border fw-bold">{{ $ujian->token }}</code></span>
                <span>•</span>
                <span class="badge {{ $ujian->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                    Status: {{ strtoupper($ujian->status) }}
                </span>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('ujian.soal', $ujian->id) }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-list-check me-1"></i> Bank Soal
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload();">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Muat Ulang
            </button>
            <form method="POST" action="{{ route('ujian.sinkronNilai', $ujian->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menyinkronkan seluruh nilai siswa yang telah selesai ke Buku Penilaian SI-KASEP?');">
                @csrf
                <button type="submit" class="btn btn-success shadow-sm" {{ $sudahSelesai == 0 ? 'disabled' : '' }}>
                    <i class="fa-solid fa-file-export me-1"></i> Sinkronkan Nilai ke SI-KASEP
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Statistic Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 border-start border-primary border-4">
                <div class="text-muted small">Total Peserta Kelas</div>
                <div class="fs-4 fw-bold text-slate-800">{{ $totalSiswa }} Siswa</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 border-start border-success border-4">
                <div class="text-muted small">Sudah Selesai Dinilai</div>
                <div class="fs-4 fw-bold text-success">{{ $sudahSelesai }} Siswa</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 border-start border-info border-4">
                <div class="text-muted small">Sedang Mengerjakan</div>
                <div class="fs-4 fw-bold text-info d-flex align-items-center gap-2">
                    {{ $sedangMengerjakan }} Siswa
                    @if($sedangMengerjakan > 0)
                        <span class="pulse-dot"></span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 border-start border-secondary border-4">
                <div class="text-muted small">Belum Masuk Ujian</div>
                <div class="fs-4 fw-bold text-secondary">{{ $belumMulai }} Siswa</div>
            </div>
        </div>
    </div>

    <!-- Student Attendance & Monitoring Table -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="fw-bold text-slate-800">
                <i class="fa-solid fa-users-viewfinder text-primary me-2"></i>Daftar Aktivitas Peserta Ujian
            </div>
            <div class="form-check form-switch small">
                <input class="form-check-input" type="checkbox" id="autoRefreshToggle" checked>
                <label class="form-check-label text-muted" for="autoRefreshToggle">Auto-Refresh (15 detik)</label>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-slate-700">
                    <tr class="text-uppercase small fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">
                        <th class="py-3 px-3 text-center" style="width: 50px;">No</th>
                        <th class="py-3 px-3">Siswa</th>
                        <th class="py-3 px-3">Kelas</th>
                        <th class="py-3 px-3 text-center">Status Ujian</th>
                        <th class="py-3 px-3 text-center">Mulai / Durasi</th>
                        <th class="py-3 px-3 text-center">Skor Akhir</th>
                        <th class="py-3 px-3 text-end">Aksi Pengawas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($siswas as $idx => $s)
                        @php
                            $sesi = $sesiList->get($s->id);
                        @endphp
                        <tr>
                            <td class="py-3 px-3 text-center text-muted small">{{ $idx + 1 }}</td>
                            <td class="py-3 px-3">
                                <div class="fw-bold text-slate-800">{{ $s->nama }}</div>
                                <div class="small text-muted font-monospace">NISN: {{ $s->nisn ?: $s->nis }}</div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="badge bg-slate-100 text-slate-700 border small">
                                    {{ $s->kelas }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if(!$sesi)
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                        <i class="fa-regular fa-clock me-1"></i>Belum Masuk
                                    </span>
                                @elseif($sesi->status === 'mengerjakan')
                                    <span class="badge bg-info text-white px-2 py-1 d-inline-flex align-items-center gap-1 shadow-sm">
                                        <span class="pulse-dot"></span> Sedang Mengerjakan
                                    </span>
                                @elseif($sesi->status === 'selesai')
                                    <span class="badge bg-success-subtle text-success border px-2 py-1 fw-bold">
                                        <i class="fa-solid fa-circle-check me-1"></i>Selesai
                                    </span>
                                @else
                                    <span class="badge bg-dark text-white px-2 py-1">{{ $sesi->status }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center small text-muted">
                                @if($sesi)
                                    <div><i class="fa-regular fa-clock me-1"></i>{{ $sesi->waktu_mulai ? $sesi->waktu_mulai->format('H:i:s') : '-' }}</div>
                                    @if($sesi->status === 'selesai' && $sesi->waktu_selesai)
                                        <div style="font-size: 11px;">Selesai: {{ $sesi->waktu_selesai->format('H:i:s') }}</div>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($sesi && $sesi->status === 'selesai')
                                    <span class="fw-bold fs-6 {{ $sesi->nilai_akhir >= 75 ? 'text-success' : 'text-danger' }}">
                                        {{ $sesi->nilai_akhir }}
                                    </span>
                                    <div class="small text-muted" style="font-size: 10px;">
                                        Benar: {{ $sesi->jumlah_benar }} / Salah: {{ $sesi->jumlah_salah }}
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-end">
                                @if($sesi)
                                    <div class="btn-group btn-group-sm">
                                        @if($sesi->status === 'mengerjakan')
                                            <form method="POST" action="{{ route('ujian.forceSubmit', [$ujian->id, $s->id]) }}" onsubmit="return confirm('Paksa selesai pengerjaan untuk siswa ini sekarang?');" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning" title="Paksa Selesai (Submit Sekarang)">
                                                    <i class="fa-solid fa-flag-checkered me-1"></i> Paksa Selesai
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('ujian.resetSesi', [$ujian->id, $s->id]) }}" onsubmit="return confirm('Reset sesi ujian siswa ini? Jawaban sebelumnya akan dihapus dan siswa dapat login kembali.');" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger" title="Reset Sesi (Ulangi Ujian)">
                                                <i class="fa-solid fa-arrow-rotate-left me-1"></i> Reset Sesi
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted small fst-italic">Menunggu Siswa</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Tidak ada data siswa pada kelas {{ $ujian->kelas }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('autoRefreshToggle');
        let refreshTimer = null;

        function setupTimer() {
            if (toggle && toggle.checked) {
                refreshTimer = setTimeout(function () {
                    window.location.reload();
                }, 15000);
            } else if (refreshTimer) {
                clearTimeout(refreshTimer);
            }
        }

        if (toggle) {
            toggle.addEventListener('change', setupTimer);
            setupTimer();
        }
    });
</script>
@endsection
