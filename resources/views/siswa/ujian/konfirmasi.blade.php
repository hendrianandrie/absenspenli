@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="mb-3">
                <a href="{{ route('siswa.ujian.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Batal & Kembali
                </a>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="bg-primary text-white p-4 text-center" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%) !important;">
                    <div class="badge bg-white text-primary px-3 py-1 mb-2 fw-semibold rounded-pill">{{ $ujian->jenis }}</div>
                    <h4 class="fw-bold mb-1">{{ $ujian->judul }}</h4>
                    <p class="mb-0 text-white-50 small">{{ optional($ujian->mataPelajaran)->nama_mapel }} • Kelas {{ $siswa->kelas }}</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <!-- Exam Details Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 bg-light border text-center">
                                <i class="fa-regular fa-clock text-primary fs-4 mb-2"></i>
                                <div class="text-muted small">Alokasi Waktu</div>
                                <div class="fw-bold fs-5 text-slate-800">{{ $ujian->durasi_menit }} Menit</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 bg-light border text-center">
                                <i class="fa-solid fa-list-ol text-success fs-4 mb-2"></i>
                                <div class="text-muted small">Jumlah Soal</div>
                                <div class="fw-bold fs-5 text-slate-800">{{ $totalSoal }} Butir</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 bg-light border text-center">
                                <i class="fa-solid fa-user-check text-info fs-4 mb-2"></i>
                                <div class="text-muted small">Target Kelas</div>
                                <div class="fw-bold fs-5 text-slate-800">{{ $ujian->kelas }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions / Rules -->
                    <div class="p-3 rounded-3 bg-slate-50 border border-slate-200 mb-4">
                        <h6 class="fw-bold text-slate-800 mb-2">
                            <i class="fa-solid fa-circle-info text-primary me-2"></i>Petunjuk Pengerjaan Asesmen:
                        </h6>
                        <ul class="small text-slate-600 mb-0 ps-3" style="line-height: 1.7;">
                            <li>Waktu pengerjaan akan mulai dihitung mundur begitu Anda menekan tombol <strong>Mulai Ujian</strong> di bawah.</li>
                            <li>Pilihlah salah satu jawaban yang paling tepat (A, B, C, atau D).</li>
                            <li><strong>Auto-Save:</strong> Setiap kali Anda memilih opsi jawaban, jawaban akan langsung tersimpan otomatis di sistem.</li>
                            <li>Gunakan tombol <strong>Ragu-Ragu</strong> berwarna kuning jika Anda ingin menandai soal untuk dicek kembali nanti.</li>
                            <li>Dilarang membuka tab browser lain, berpindah aplikasi, atau melakukan kecurangan selama ujian berlangsung.</li>
                        </ul>
                        @if(!empty($ujian->deskripsi))
                            <div class="mt-3 pt-3 border-top small text-slate-700">
                                <span class="fw-bold">Pesan Tambahan dari Guru:</span><br>
                                {!! nl2br(e($ujian->deskripsi)) !!}
                            </div>
                        @endif
                    </div>

                    <!-- Form Token & Submit -->
                    <form method="POST" action="{{ route('siswa.ujian.mulai', $ujian->id) }}">
                        @csrf

                        @if(!empty($ujian->token))
                            <div class="mb-4 text-center">
                                <label class="form-label fw-bold text-slate-800">
                                    <i class="fa-solid fa-key text-warning me-1"></i> Masukkan Kode Token Ujian
                                </label>
                                <div class="col-md-6 mx-auto">
                                    <input type="text" name="token" class="form-control form-control-lg text-center fw-bold font-monospace text-uppercase" placeholder="Contoh: X9AB4" style="letter-spacing: 4px;" required autocomplete="off">
                                    <div class="form-text small">Minta kode token 5 karakter kepada guru pengawas di kelas.</div>
                                </div>
                            </div>
                        @endif

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg shadow fw-bold py-3">
                                <i class="fa-solid fa-play me-2"></i> Mulai Mengerjakan Ujian Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

