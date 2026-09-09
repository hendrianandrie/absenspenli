@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-users-gear text-primary me-2"></i> Kelola Pengguna & Hak Akses</h3>
        <p class="text-muted mb-0">Atur akun login (Username, Email, Password), Role (Admin, Piket, Guru), Mata Pelajaran, dan Kelas Diampu.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Form Tambah Pengguna Baru -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 bg-white shadow-sm">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-user-plus text-primary me-2"></i> Tambah Akun Pengguna</h5>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Username / Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: guru_mtk" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Email Login</label>
                    <input type="email" name="email" class="form-control" placeholder="Contoh: guru_mtk@smp.sch.id" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Password Login</label>
                    <div class="input-group">
                        <input type="password" name="password" id="createPasswordInput" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('createPasswordInput', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7"><i class="fa-solid fa-shield-halved me-1 text-primary"></i> Role / Peran Akun</label>
                    <select name="role" id="createRoleSelect" class="form-select" onchange="handleRoleChange('createRoleSelect', 'createGuruFields')" required>
                        <option value="guru" selected>Guru Mata Pelajaran</option>
                        <option value="piket">Petugas Piket (Absensi)</option>
                        <option value="admin">Administrator (Full Access)</option>
                    </select>
                </div>

                <!-- Section Khusus Role Guru -->
                <div id="createGuruFields" class="bg-light p-3 rounded mb-3 border">
                    <h6 class="fw-bold text-dark fs-7 mb-2"><i class="fa-solid fa-chalkboard-user me-1 text-primary"></i> Pengaturan Akses Guru</h6>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mata Pelajaran Diampu</label>
                        <select name="mata_pelajaran_id" class="form-select form-select-sm">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($mapels as $mapel)
                                <option value="{{ $mapel->id }}">
                                    {{ $mapel->nama_mapel }} @if(($mapel->tingkat ?? 'Semua') !== 'Semua') [Tingkat {{ $mapel->tingkat }}] @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small fw-semibold mb-1">Kelas Diampu</label>
                        <div class="row g-1 overflow-auto pe-1" style="max-height: 140px;">
                            @foreach($daftarKelas as $k)
                                <div class="col-6 col-sm-4">
                                    <div class="form-check small">
                                        <input class="form-check-input" type="checkbox" name="kelas_diampu[]" value="{{ $k }}" id="create_kelas_{{ $loop->index }}">
                                        <label class="form-check-label" for="create_kelas_{{ $loop->index }}">{{ $k }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100 py-2">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Akun Baru
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Pengguna -->
    <div class="col-lg-8">
        <div class="card card-custom p-4 bg-white shadow-sm">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-ul text-primary me-2"></i> Daftar Akun Terdaftar</h5>
            @if($users->isEmpty())
                <div class="text-center py-4 text-muted">Belum ada akun terdaftar.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Pengguna</th>
                                <th>Role</th>
                                <th>Mapel & Kelas Diampu</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            <i class="fa-solid fa-user-circle text-secondary me-1"></i> {{ $user->name }}
                                            @if($user->id === Auth::id())
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 0.7rem;">Anda</span>
                                            @endif
                                        </div>
                                        <small class="text-muted font-monospace"><i class="fa-regular fa-envelope me-1"></i>{{ $user->email }}</small>
                                    </td>
                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge bg-danger px-2 py-1"><i class="fa-solid fa-user-shield me-1"></i> Admin</span>
                                        @elseif($user->role === 'piket')
                                            <span class="badge bg-warning text-dark px-2 py-1"><i class="fa-solid fa-id-card me-1"></i> Piket</span>
                                        @else
                                            <span class="badge bg-success px-2 py-1"><i class="fa-solid fa-chalkboard-user me-1"></i> Guru</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->role === 'guru')
                                            <div class="small fw-semibold text-primary mb-1">
                                                <i class="fa-solid fa-book me-1"></i> {{ $user->mataPelajaran->nama_mapel ?? 'Belum Diatur' }}
                                                @if(isset($user->mataPelajaran->tingkat) && $user->mataPelajaran->tingkat !== 'Semua')
                                                    <span class="badge bg-info text-dark" style="font-size: 0.65rem;">Tingkat {{ $user->mataPelajaran->tingkat }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                @if(!empty($user->kelas_diampu) && is_array($user->kelas_diampu))
                                                    @foreach($user->kelas_diampu as $kls)
                                                        <span class="badge bg-light text-dark border me-1 mb-1">{{ $kls }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted small">Semua / Belum Diatur</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                        </button>
                                        @if($user->id !== Auth::id())
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fa-solid fa-trash me-1"></i> Hapus
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" disabled title="Tidak dapat menghapus akun sendiri">
                                                <i class="fa-solid fa-lock me-1"></i> Hapus
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Container Edit User -->
@foreach($users as $user)
    <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-labelledby="editUserModalLabel{{ $user->id }}" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="editUserModalLabel{{ $user->id }}">
                        <i class="fa-solid fa-user-pen me-2"></i> Edit Akun {{ $user->name }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $user->id }}">
                    <div class="modal-body p-4 text-start">
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-7">Username / Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-7">Email Login</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-7 text-primary">
                                Ubah Password <small class="text-muted fw-normal">(Kosongkan jika tidak ingin diubah)</small>
                            </label>
                            <div class="input-group">
                                <input type="password" name="password" id="editPasswordInput{{ $user->id }}" class="form-control" placeholder="Masukkan password baru...">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('editPasswordInput{{ $user->id }}', this)">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-7">Role / Peran Akun</label>
                            <select name="role" id="editRoleSelect{{ $user->id }}" class="form-select" onchange="handleRoleChange('editRoleSelect{{ $user->id }}', 'editGuruFields{{ $user->id }}')" required>
                                <option value="guru" {{ $user->role === 'guru' ? 'selected' : '' }}>Guru Mata Pelajaran</option>
                                <option value="piket" {{ $user->role === 'piket' ? 'selected' : '' }}>Petugas Piket (Absensi)</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrator (Full Access)</option>
                            </select>
                        </div>

                        <!-- Section Khusus Role Guru Edit -->
                        <div id="editGuruFields{{ $user->id }}" class="bg-light p-3 rounded mb-3 border" style="{{ $user->role === 'guru' ? '' : 'display: none;' }}">
                            <h6 class="fw-bold text-dark fs-7 mb-2"><i class="fa-solid fa-chalkboard-user me-1 text-primary"></i> Pengaturan Akses Guru</h6>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Mata Pelajaran Diampu</label>
                                <select name="mata_pelajaran_id" class="form-select form-select-sm">
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    @foreach($mapels as $mapel)
                                        <option value="{{ $mapel->id }}" {{ $user->mata_pelajaran_id == $mapel->id ? 'selected' : '' }}>
                                            {{ $mapel->nama_mapel }} @if(($mapel->tingkat ?? 'Semua') !== 'Semua') [Tingkat {{ $mapel->tingkat }}] @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label small fw-semibold mb-1">Kelas Diampu</label>
                                <div class="row g-1 overflow-auto pe-1" style="max-height: 140px;">
                                    @php $assigned = is_array($user->kelas_diampu) ? $user->kelas_diampu : []; @endphp
                                    @foreach($daftarKelas as $k)
                                        <div class="col-6 col-sm-4">
                                            <div class="form-check small">
                                                <input class="form-check-input" type="checkbox" name="kelas_diampu[]" value="{{ $k }}" id="edit_kelas_{{ $user->id }}_{{ $loop->index }}" {{ in_array($k, $assigned) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="edit_kelas_{{ $user->id }}_{{ $loop->index }}">{{ $k }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
    function togglePasswordVisibility(inputId, btn) {
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

    function handleRoleChange(selectId, targetContainerId) {
        const select = document.getElementById(selectId);
        const target = document.getElementById(targetContainerId);
        if (select.value === 'guru') {
            target.style.display = 'block';
        } else {
            target.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        handleRoleChange('createRoleSelect', 'createGuruFields');
    });
</script>
@endpush
@endsection
