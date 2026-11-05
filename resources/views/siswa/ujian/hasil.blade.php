@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden text-center">
                <div class="bg-success text-white p-5" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;">
                    <div class="rounded-circle bg-white text-success d-inline-flex align-items-center justify-content-center shadow-sm mb-3" style="width: 72px; height: 72px;">
                        <i class="fa-solid fa-check fs-2"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Ujian Berhasil Dikumpulkan!</h3>
                    <p class="mb-0 text-white-50">{{ $ujian->judul }}</p>
                    <div class="small text-white-50 mt-1">{{ optional($ujian->mataPelajaran)->nama_mapel }} • Kelas {{ $siswa->kelas }}</div>
                </div>

                <div class="card-body p-4 p-md-5">
                    @if($ujian->tampilkan_nilai)
                        @php
                            $kkm = optional($ujian->mataPelajaran)->kkm ?? 75;
                            $isTuntas = $sesi->nilai_akhir >= $kkm;
                        @endphp
                        <div class="mb-4">
                            <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 1px;">Skor Akhir Anda</div>
                            <div class="display-3 fw-bold my-2 {{ $isTuntas ? 'text-success' : 'text-danger' }}">
                                {{ $sesi->nilai_akhir }}
                            </div>
                            <span class="badge {{ $isTuntas ? 'bg-success text-white' : 'bg-danger text-white' }} px-3 py-1.5 fs-6 rounded-pill shadow-sm">
                                <i class="fa-solid {{ $isTuntas ? 'fa-circle-check' : 'fa-circle-exclamation' }} me-1"></i>
                                {{ $isTuntas ? 'Tuntas (Di Atas KKM ' . $kkm . ')' : 'Perlu Remedial (Di Bawah KKM ' . $kkm . ')' }}
                            </span>
                        </div>

                        <!-- Breakdown Metrics -->
                        <div class="row g-2 mb-4">
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-light border">
                                    <div class="text-muted small">Total Soal</div>
                                    <div class="fw-bold fs-5 text-slate-800">{{ $totalSoal }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle">
                                    <div class="text-success small fw-semibold">Jawaban Benar</div>
                                    <div class="fw-bold fs-5 text-success">{{ $sesi->jumlah_benar }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-danger-subtle border border-danger-subtle">
                                    <div class="text-danger small fw-semibold">Jawaban Salah</div>
                                    <div class="fw-bold fs-5 text-danger">{{ $sesi->jumlah_salah }}</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-4 rounded-3 bg-light border mb-4">
                            <i class="fa-solid fa-lock text-muted fs-3 mb-2"></i>
                            <h6 class="fw-bold text-slate-800 mb-1">Skor Dirahasiakan</h6>
                            <p class="small text-muted mb-0">Guru mata pelajaran mengatur agar hasil asesmen ini tidak langsung ditampilkan. Nilai resmi akan diumumkan melalui portal akademik atau guru pengampu Anda.</p>
                        </div>
                    @endif

                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                        <a href="{{ route('siswa.ujian.index') }}" class="btn btn-outline-secondary px-4 py-2">
                            <i class="fa-solid fa-list-check me-1"></i> Daftar Asesmen
                        </a>
                        <a href="{{ route('siswa.dashboard') }}" class="btn btn-primary px-4 py-2 shadow-sm">
                            <i class="fa-solid fa-house me-1"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

