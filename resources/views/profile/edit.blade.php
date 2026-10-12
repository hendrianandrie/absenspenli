@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Title & Back Link -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge rounded-pill px-3 py-1-5 fw-semibold" 
                      style="background: #e0e7ff; color: #3730a3; font-size: 11px;">
                    <i class="fa-solid fa-user-shield me-1"></i> Pengaturan Akun
                </span>
                <span class="text-muted small">&bull;</span>
                <span class="text-muted small">Semua Peran ({{ ucfirst($user->role) }})</span>
            </div>
            <h3 class="fw-bold text-slate-900 mb-1">Profil & Keamanan Akun</h3>
            <p class="text-muted small mb-0">Kelola identitas akun Anda dan ubah kata sandi lama menjadi kata sandi baru untuk menjaga keamanan akses.</p>
        </div>
        <div>
            <a href="{{ $user->role === 'siswa' ? route('siswa.dashboard') : route('dashboard') }}" 
               class="btn btn-outline-secondary btn-sm px-3 py-2 rounded-3 shadow-xs fw-semibold">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Alert Success / Error Feedback -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert" style="background: #ecfdf5; color: #065f46; border-left: 4px solid #10b981 !important;">
            <i class="fa-solid fa-circle-check fs-5 text-success"></i>
            <div class="flex-grow-1 fw-medium">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 d-flex align-items-start gap-2" role="alert" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444 !important;">
            <i class="fa-solid fa-triangle-exclamation fs-5 text-danger mt-1"></i>
            <div class="flex-grow-1">
                <div class="fw-bold mb-1">Terjadi kesalahan pada data yang Anda kirimkan:</div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Sidebar Kartu Info Akun -->
        <div class="col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
                <div class="p-4 text-center" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%); color: #ffffff;">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle text-white fw-bold shadow-md mb-3" 
                         style="width: 76px; height: 76px; font-size: 30px; background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); border: 2px solid rgba(255,255,255,0.3);">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h5 class="fw-bold mb-1 text-white">{{ $user->name }}</h5>
                    <div class="text-white-50 font-monospace small mb-2">@ {{ $user->username ?? '-' }}</div>
                    <div>
                        <span class="badge rounded-pill px-3 py-1-5 fw-bold" 
                              style="{{ $user->role === 'admin' ? 'background:#fee2e2; color:#b91c1c;' : ($user->role === 'guru' ? 'background:#dbeafe; color:#1e40af;' : ($user->role === 'siswa' ? 'background:#e0f2fe; color:#0369a1;' : 'background:#fef3c7; color:#92400e;')) }} font-size: 11px;">
                            <i class="fa-solid {{ $user->role === 'admin' ? 'fa-shield-halved' : ($user->role === 'guru' ? 'fa-chalkboard-user' : ($user->role === 'siswa' ? 'fa-user-graduate' : 'fa-clipboard-user')) }} me-1"></i>
                            {{ strtoupper($user->role) }}
                        </span>
                    </div>
                </div>

                <div class="p-3 bg-white">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2-5 border-0 border-bottom">
                            <span class="text-muted"><i class="fa-regular fa-envelope me-2 text-primary"></i> Email:</span>
                            <span class="fw-semibold text-truncate text-end ps-2 text-slate-800" style="max-width: 160px;" title="{{ $user->email }}">{{ $user->email }}</span>
                        </li>

                        @if($user->nip)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2-5 border-0 border-bottom">
                                <span class="text-muted"><i class="fa-solid fa-id-card me-2 text-primary"></i> NIP:</span>
                                <span class="fw-semibold font-monospace text-slate-800">{{ $user->nip }}</span>
                            </li>
                        @endif

                        @if($user->role === 'siswa' && $user->siswa)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2-5 border-0 border-bottom">
                                <span class="text-muted"><i class="fa-solid fa-id-badge me-2 text-info"></i> NIS:</span>
                                <span class="fw-semibold font-monospace text-slate-800">{{ $user->siswa->nis }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2-5 border-0 border-bottom">
                                <span class="text-muted"><i class="fa-solid fa-users-rectangle me-2 text-info"></i> Kelas:</span>
                                <span class="badge bg-primary-subtle text-primary fw-bold">{{ $user->siswa->kelas }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2-5 border-0 border-bottom">
                                <span class="text-muted"><i class="fa-solid fa-venus-mars me-2 text-info"></i> Jenis Kelamin:</span>
                                <span class="fw-semibold text-slate-800">{{ $user->siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                            </li>
                        @endif

                        @if($user->role === 'guru')
                            @php
                                $mapelNama = $user->mataPelajarans->isNotEmpty() 
                                    ? $user->mataPelajarans->pluck('nama_mapel')->join(', ') 
                                    : optional($user->mataPelajaran)->nama_mapel;
                            @endphp
                            @if($mapelNama)
                                <li class="list-group-item px-2 py-2-5 border-0 border-bottom">
                                    <div class="text-muted mb-1"><i class="fa-solid fa-book-open me-2 text-success"></i> Mapel Diampu:</div>
                                    <span class="fw-semibold text-slate-800">{{ $mapelNama }}</span>
                                </li>
                            @endif

                            @if($user->is_wali_kelas && $user->waliKelas)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2-5 border-0 border-bottom">
                                    <span class="text-muted"><i class="fa-solid fa-crown me-2 text-warning"></i> Wali Kelas:</span>
                                    <span class="badge bg-warning-subtle text-warning-emphasis fw-bold">Kelas {{ optional($user->waliKelas)->kelas }}</span>
                                </li>
                            @endif
                        @endif

                        <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2-5 border-0">
                            <span class="text-muted"><i class="fa-regular fa-clock me-2 text-secondary"></i> Terdaftar:</span>
                            <span class="text-muted">{{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Kartu Tips Keamanan -->
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-light border-start border-4 border-primary">
                <div class="d-flex gap-2">
                    <i class="fa-solid fa-shield-halved text-primary fs-5 mt-1"></i>
                    <div>
                        <h6 class="fw-bold mb-1 text-slate-800 small">Tips Keamanan Akun</h6>
                        <p class="text-muted small mb-0" style="font-size: 12px; line-height: 1.5;">
                            Gunakan kombinasi huruf kapital, angka, dan karakter unik agar kata sandi Anda tidak mudah ditebak. Jangan bagikan kata sandi Anda kepada orang lain.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulir Edit Profil & Ubah Password -->
        <div class="col-lg-8 col-xl-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <form action="{{ route('profile.update') }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <!-- SECTION 1: INFORMASI PROFIL DASAR -->
                    <div class="mb-4 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <div class="rounded-3 p-2 d-flex align-items-center justify-content-center bg-primary-subtle text-primary" style="width: 36px; height: 36px;">
                                <i class="fa-solid fa-user-pen"></i>
                            </div>
                            <h5 class="fw-bold text-slate-900 mb-0">Informasi Dasar Profil</h5>
                        </div>
                        <p class="text-muted small mb-0 ps-5 ms-1">Perbarui informasi identitas diri dan kontak email akun Anda.</p>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold text-slate-700 small">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-id-card"></i></span>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $user->name) }}" 
                                       placeholder="Masukkan nama lengkap" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small text-muted">Untuk guru/staf, dapat menyertakan gelar akademik lengkap.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold text-slate-700 small">
                                Alamat Email <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}" 
                                       placeholder="alamat@email.com" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small text-muted">Email aktif untuk notifikasi dan pemulihan akun.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="username" class="form-label fw-semibold text-slate-700 small">
                                Username Login <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted fw-bold">@</span>
                                <input type="text" 
                                       class="form-control font-monospace @error('username') is-invalid @enderror" 
                                       id="username" 
                                       name="username" 
                                       value="{{ old('username', $user->username) }}" 
                                       {{ $user->role === 'siswa' ? 'readonly' : 'required' }}>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small text-muted">
                                @if($user->role === 'siswa')
                                    <i class="fa-solid fa-lock text-warning me-1"></i> Username siswa terikat dengan NIS dan tidak dapat diubah sendiri.
                                @else
                                    Username unik untuk masuk ke dalam sistem SI-KASEP.
                                @endif
                            </div>
                        </div>

                        @if($user->role !== 'siswa')
                            <div class="col-md-6">
                                <label for="nip" class="form-label fw-semibold text-slate-700 small">
                                    NIP (Nomor Induk Pegawai)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-address-card"></i></span>
                                    <input type="text" 
                                           class="form-control font-monospace @error('nip') is-invalid @enderror" 
                                           id="nip" 
                                           name="nip" 
                                           value="{{ old('nip', $user->nip) }}" 
                                           placeholder="19xxxxxxxxxxxxxx">
                                    @error('nip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text small text-muted">Opsional jika berstatus PNS / PPPK.</div>
                            </div>
                        @else
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-slate-700 small">
                                    Kelas Siswa Terdaftar
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-school"></i></span>
                                    <input type="text" 
                                           class="form-control bg-light" 
                                           value="Kelas {{ optional($user->siswa)->kelas ?? '-' }}" 
                                           readonly>
                                </div>
                                <div class="form-text small text-muted">Diatur oleh wali kelas & administrator.</div>
                            </div>
                        @endif
                    </div>

                    <!-- SECTION 2: UBAH KATA SANDI -->
                    <div class="mt-5 mb-4 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <div class="rounded-3 p-2 d-flex align-items-center justify-content-center bg-danger-subtle text-danger" style="width: 36px; height: 36px;">
                                <i class="fa-solid fa-key"></i>
                            </div>
                            <h5 class="fw-bold text-slate-900 mb-0">Ubah Kata Sandi (Password)</h5>
                        </div>
                        <p class="text-muted small mb-0 ps-5 ms-1">
                            Gunakan bagian ini jika Anda ingin mengubah kata sandi lama menjadi kata sandi baru. Kosongkan semua kolom di bawah jika <strong>tidak</strong> ingin mengganti kata sandi.
                        </p>
                    </div>

                    <div class="p-3-5 p-md-4 rounded-4 mb-4 border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                        <div class="row g-3">
                            <!-- Password Lama -->
                            <div class="col-12">
                                <label for="current_password" class="form-label fw-semibold text-slate-800 small">
                                    <i class="fa-solid fa-unlock-keyhole text-secondary me-1"></i> Kata Sandi Saat Ini (Password Lama)
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" 
                                           name="current_password" 
                                           placeholder="Ketikkan kata sandi lama Anda...">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('current_password', this)" title="Lihat kata sandi">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text small text-muted">
                                    Wajib diisi hanya jika Anda ingin mengganti kata sandi baru.
                                </div>
                            </div>

                            <!-- Password Baru -->
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold text-slate-800 small">
                                    <i class="fa-solid fa-lock text-primary me-1"></i> Kata Sandi Baru
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Minimal 6 karakter" 
                                           minlength="6">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('password', this)" title="Lihat kata sandi">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text small text-muted">Minimal 6 karakter kombinasi yang aman.</div>
                            </div>

                            <!-- Konfirmasi Password Baru -->
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-semibold text-slate-800 small">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i> Konfirmasi Kata Sandi Baru
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           placeholder="Ketik ulang kata sandi baru" 
                                           minlength="6">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('password_confirmation', this)" title="Lihat kata sandi">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text small text-muted">Pastikan sama persis dengan kolom kata sandi baru.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-flex flex-column flex-sm-row justify-content-end align-items-center gap-2 pt-3 border-top">
                        <a href="{{ $user->role === 'siswa' ? route('siswa.dashboard') : route('dashboard') }}" 
                           class="btn btn-light border px-4 py-2 rounded-3 text-secondary fw-semibold w-100 w-sm-auto">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm w-100 w-sm-auto" style="background: #4f46e5; border-color: #4338ca;">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePassVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endpush
@endsection
