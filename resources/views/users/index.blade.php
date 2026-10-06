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
                    <label class="form-label fw-semibold fs-7">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Dra. Hj. Siti Rohmah, M.Pd." required>
                    <div class="form-text fs-8 text-muted">Gunakan nama lengkap beserta gelar akademik pendidik.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Username Login <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted fs-7">@</span>
                        <input type="text" name="username" class="form-control" placeholder="Contoh: sitirohmah" required pattern="^[a-zA-Z0-9_\.]+$">
                    </div>
                    <div class="form-text fs-8 text-muted">Username unik tanpa spasi/gelar untuk login ke sistem.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">NIP (Nomor Induk Pegawai)</label>
                    <input type="text" name="nip" class="form-control" placeholder="Contoh: 19850712 201001 2 005">
                    <div class="form-text fs-8 text-muted">Opsional untuk pendidik/tenaga kependidikan ber-NIP.</div>
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
                        <option value="siswa">Siswa</option>
                    </select>
                </div>

                <!-- Section Khusus Role Guru -->
                <div id="createGuruFields" class="bg-light p-3 rounded mb-3 border">
                    <h6 class="fw-bold text-dark fs-7 mb-2"><i class="fa-solid fa-chalkboard-user me-1 text-primary"></i> Pengaturan Akses Guru</h6>
                    
                    <!-- Multi-Mapel Diampu -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">
                                <i class="fa-solid fa-book-bookmark text-primary me-1"></i> Mata Pelajaran Diampu
                            </label>
                            <span class="badge bg-white text-secondary border shadow-xs" id="createMapelCountBadge" style="font-size: 0.72rem;">0 dipilih</span>
                        </div>
                        <div class="border rounded-2 p-2 bg-white overflow-auto pe-1" style="max-height: 145px;" id="createMapelCheckboxes">
                            @foreach($mapels as $mapel)
                                @php
                                    $t = $mapel->tingkat ?? 'Semua';
                                    $badgeClass = $t == '7' ? 'bg-info-subtle text-info-emphasis' : ($t == '8' ? 'bg-warning-subtle text-warning-emphasis' : ($t == '9' ? 'bg-danger-subtle text-danger-emphasis' : 'bg-primary-subtle text-primary-emphasis'));
                                @endphp
                                <div class="form-check small py-1 border-bottom border-light">
                                    <input class="form-check-input create-mapel-cb" type="checkbox" name="mata_pelajaran_ids[]" value="{{ $mapel->id }}" id="create_mapel_{{ $mapel->id }}" data-tingkat="{{ $t }}" onchange="updateMultiMapelFilter('create', true)">
                                    <label class="form-check-label d-flex justify-content-between align-items-center w-100 cursor-pointer" for="create_mapel_{{ $mapel->id }}">
                                        <span class="fw-medium text-dark">{{ $mapel->nama_mapel }}</span>
                                        <span class="badge {{ $badgeClass }} border" style="font-size: 0.65rem;">Tingkat {{ $t }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text fs-8 text-muted mt-1"><i class="fa-solid fa-info-circle me-1"></i> Centang satu atau beberapa mata pelajaran yang diampu.</div>
                    </div>

                    <!-- Kelas Diampu -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">Kelas Diampu</label>
                            <span class="badge bg-secondary-subtle text-secondary border" id="createKelasTingkatBadge" style="font-size: 0.72rem;">Semua Tingkat</span>
                        </div>
                        <div class="row g-1 overflow-auto pe-1" id="createKelasContainer" style="max-height: 140px;">
                            @foreach($daftarKelas as $k)
                                @php
                                    $t = 'Semua';
                                    if (preg_match('/^(VIII|8)/i', trim($k))) $t = '8';
                                    elseif (preg_match('/^(VII|7)/i', trim($k))) $t = '7';
                                    elseif (preg_match('/^(IX|9)/i', trim($k))) $t = '9';
                                @endphp
                                <div class="col-6 col-sm-4 kelas-item" data-tingkat="{{ $t }}">
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
            <!-- Header & Role Filter Tabs -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-0 text-slate-800" style="font-size: 1.1rem;">
                        <i class="fa-solid fa-users-gear text-primary me-2"></i> Daftar Akun Terdaftar
                    </h5>
                    <small class="text-slate-400">Total {{ number_format($roleCounts['all'], 0, ',', '.') }} Akun dalam Sistem</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-slate-600 border rounded-pill px-2.5 py-1" style="font-size: 11.5px;">
                        Menampilkan {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} Akun
                    </span>
                </div>
            </div>

            <!-- Role Filter Pills & Search Form -->
            <div class="bg-slate-50 p-2.5 rounded-3 border mb-3">
                <form action="{{ route('users.index') }}" method="GET" id="userFilterForm" class="row g-2 align-items-center">
                    <input type="hidden" name="role" id="filterRoleInput" value="{{ $selectedRole }}">
                    
                    <div class="col-12 col-xl-7">
                        <div class="d-flex flex-wrap align-items-center gap-1.5">
                            <span class="small text-slate-500 fw-semibold me-1 d-none d-sm-inline" style="font-size: 12px;">Filter Role:</span>
                            <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $selectedRole === 'all' ? 'btn-primary' : 'btn-white bg-white border text-slate-600' }}" style="font-size: 11.5px;" onclick="applyRoleFilter('all')">
                                Semua <span class="badge {{ $selectedRole === 'all' ? 'bg-white text-primary' : 'bg-slate-100 text-slate-600' }} rounded-pill ms-1">{{ $roleCounts['all'] }}</span>
                            </button>
                            <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $selectedRole === 'guru' ? 'btn-primary' : 'btn-white bg-white border text-slate-600' }}" style="font-size: 11.5px;" onclick="applyRoleFilter('guru')">
                                <i class="fa-solid fa-chalkboard-user me-1 {{ $selectedRole === 'guru' ? 'text-white' : 'text-success' }}"></i> Guru <span class="badge {{ $selectedRole === 'guru' ? 'bg-white text-primary' : 'bg-slate-100 text-slate-600' }} rounded-pill ms-1">{{ $roleCounts['guru'] }}</span>
                            </button>
                            <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $selectedRole === 'admin' ? 'btn-primary' : 'btn-white bg-white border text-slate-600' }}" style="font-size: 11.5px;" onclick="applyRoleFilter('admin')">
                                <i class="fa-solid fa-shield-halved me-1 {{ $selectedRole === 'admin' ? 'text-white' : 'text-danger' }}"></i> Admin <span class="badge {{ $selectedRole === 'admin' ? 'bg-white text-primary' : 'bg-slate-100 text-slate-600' }} rounded-pill ms-1">{{ $roleCounts['admin'] }}</span>
                            </button>
                            <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $selectedRole === 'piket' ? 'btn-primary' : 'btn-white bg-white border text-slate-600' }}" style="font-size: 11.5px;" onclick="applyRoleFilter('piket')">
                                <i class="fa-solid fa-id-card me-1 {{ $selectedRole === 'piket' ? 'text-white' : 'text-warning' }}"></i> Piket <span class="badge {{ $selectedRole === 'piket' ? 'bg-white text-primary' : 'bg-slate-100 text-slate-600' }} rounded-pill ms-1">{{ $roleCounts['piket'] }}</span>
                            </button>
                            <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $selectedRole === 'siswa' ? 'btn-primary' : 'btn-white bg-white border text-slate-600' }}" style="font-size: 11.5px;" onclick="applyRoleFilter('siswa')">
                                <i class="fa-solid fa-user-graduate me-1 {{ $selectedRole === 'siswa' ? 'text-white' : 'text-info' }}"></i> Siswa <span class="badge {{ $selectedRole === 'siswa' ? 'bg-white text-primary' : 'bg-slate-100 text-slate-600' }} rounded-pill ms-1">{{ $roleCounts['siswa'] }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-xl-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-slate-400"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="q" value="{{ $search }}" class="form-control border-start-0 ps-0" placeholder="Cari nama, username, NIS, atau email..." style="font-size: 12px;">
                            @if(!empty($search))
                                <a href="{{ route('users.index', ['role' => $selectedRole]) }}" class="btn btn-outline-secondary border-start-0" title="Hapus pencarian">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            @endif
                            <button type="submit" class="btn btn-primary px-2.5">
                                Cari
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            @if($users->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-regular fa-folder-open fs-2 mb-2 text-slate-300"></i>
                    <p class="mb-0">Tidak ada akun yang sesuai dengan filter atau kata kunci pencarian.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                        <thead class="bg-slate-50 border-bottom text-slate-600" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">
                            <tr>
                                <th class="py-2.5 ps-3" style="min-width: 220px;">Pengguna</th>
                                <th class="text-center py-2.5" style="width: 100px;">Role</th>
                                <th class="py-2.5" style="min-width: 260px;">Mapel / Kelas</th>
                                <th class="text-end py-2.5 pe-3" style="width: 150px; white-space: nowrap;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td class="ps-3 py-2.5">
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0"
                                                 style="width: 34px; height: 34px; font-size: 13px; background: {{ $user->role === 'admin' ? 'linear-gradient(135deg, #ef4444, #dc2626)' : ($user->role === 'piket' ? 'linear-gradient(135deg, #f59e0b, #d97706)' : ($user->role === 'siswa' ? 'linear-gradient(135deg, #0284c7, #0369a1)' : 'linear-gradient(135deg, #6366f1, #4f46e5)')) }};">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <span class="fw-bold text-slate-800" style="font-size: 13px;">{{ $user->name }}</span>
                                                    @if($user->id === Auth::id())
                                                        <span class="badge rounded-pill fw-semibold px-2 py-0.5" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 10px;">Anda</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex flex-wrap align-items-center gap-1.5 mt-1" style="font-size: 11px;">
                                                    <span class="badge font-monospace px-1.5 py-0.5 rounded" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-size: 10.5px;">
                                                        @ {{ $user->username ?: strtolower(str_replace(' ', '', $user->name)) }}
                                                    </span>
                                                    @if($user->nip)
                                                        <span class="badge font-monospace px-1.5 py-0.5 rounded" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 10.5px;">
                                                            NIP. {{ $user->nip }}
                                                        </span>
                                                    @endif
                                                    <span class="text-slate-400" style="font-size: 11px;">
                                                        <i class="fa-regular fa-envelope me-1"></i>{{ $user->email }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center py-2.5">
                                        @if($user->role === 'admin')
                                            <span class="badge rounded-pill fw-semibold px-2.5 py-1" style="background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; font-size:11px;">
                                                <i class="fa-solid fa-shield-halved me-1"></i> Admin
                                            </span>
                                        @elseif($user->role === 'piket')
                                            <span class="badge rounded-pill fw-semibold px-2.5 py-1" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a; font-size:11px;">
                                                <i class="fa-solid fa-id-card me-1"></i> Piket
                                            </span>
                                        @elseif($user->role === 'siswa')
                                            <span class="badge rounded-pill fw-semibold px-2.5 py-1" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; font-size:11px;">
                                                <i class="fa-solid fa-user-graduate me-1"></i> Siswa
                                            </span>
                                        @else
                                            <span class="badge rounded-pill fw-semibold px-2.5 py-1" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-size:11px;">
                                                <i class="fa-solid fa-chalkboard-user me-1"></i> Guru
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5">
                                        @if($user->role === 'guru')
                                            @php
                                                $userMapels = $user->mataPelajarans->isNotEmpty() 
                                                    ? $user->mataPelajarans 
                                                    : ($user->mataPelajaran ? collect([$user->mataPelajaran]) : collect());
                                            @endphp
                                            <div class="d-flex flex-wrap gap-1 mb-1">
                                                @forelse($userMapels as $m)
                                                    <span class="badge rounded-pill fw-medium px-2 py-0.5" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 11px;">
                                                        <i class="fa-solid fa-book-open me-1" style="font-size: 9.5px;"></i>{{ $m->nama_mapel }}
                                                        @if(isset($m->tingkat) && $m->tingkat !== 'Semua')
                                                             <span class="badge rounded-pill px-1.5 py-0.2 ms-1" style="background: #dbeafe; color: #1d4ed8; font-size: 9px;">Tk. {{ $m->tingkat }}</span>
                                                        @endif
                                                    </span>
                                                @empty
                                                    <span class="text-slate-400 fst-italic" style="font-size: 11.5px;">Belum Diatur</span>
                                                @endforelse
                                            </div>
                                            <div class="d-flex flex-wrap gap-1">
                                                @if(!empty($user->kelas_diampu) && is_array($user->kelas_diampu))
                                                    @foreach($user->kelas_diampu as $kls)
                                                        <span class="badge rounded-pill px-2 py-0.5 border" style="background: #f8fafc; color: #475569; border-color: #e2e8f0; font-size: 10px;">{{ $kls }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-slate-400 fst-italic" style="font-size: 11.5px;">Semua Kelas</span>
                                                @endif
                                            </div>
                                        @elseif($user->role === 'siswa')
                                            @if($user->siswa)
                                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                    <span class="badge rounded-pill fw-semibold px-2 py-0.5" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 11px;">
                                                        <i class="fa-solid fa-chalkboard-user me-1"></i> Kelas {{ $user->siswa->kelas }}
                                                    </span>
                                                    <span class="badge font-monospace px-1.5 py-0.5 rounded text-slate-500 bg-light border" style="font-size: 10px;">
                                                        NIS: {{ $user->siswa->nis }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="badge bg-light text-slate-500 border rounded-pill px-2 py-0.5" style="font-size: 10.5px;">Akun Siswa</span>
                                            @endif
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3 py-2.5" style="white-space: nowrap;">
                                        <div class="d-inline-flex align-items-center gap-1.5">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 shadow-sm" style="font-size: 11.5px;" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}" title="Edit Pengguna">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                            </button>
                                            @if($user->id !== Auth::id())
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 shadow-sm" style="font-size: 11.5px;" title="Hapus Pengguna">
                                                        <i class="fa-solid fa-trash me-1"></i> Hapus
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-sm btn-light border text-slate-400 rounded-pill px-2.5 py-1" style="font-size: 11.5px;" disabled title="Tidak dapat menghapus akun sendiri">
                                                    <i class="fa-solid fa-lock me-1"></i> Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Container -->
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 pt-3 mt-2 border-top">
                    <div class="text-slate-500 small">
                        Menampilkan <strong>{{ $users->firstItem() ?? 0 }}</strong> s.d. <strong>{{ $users->lastItem() ?? 0 }}</strong> dari <strong>{{ $users->total() }}</strong> akun
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
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
                            <label class="form-label fw-semibold fs-7">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                            <div class="form-text fs-8 text-muted">Nama lengkap pendidik beserta gelar akademik.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-7">Username Login <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted fs-7">@</span>
                                <input type="text" name="username" class="form-control" value="{{ $user->username ?: strtolower(str_replace(' ', '', $user->name)) }}" required pattern="^[a-zA-Z0-9_\.]+$">
                            </div>
                            <div class="form-text fs-8 text-muted">Username unik tanpa spasi/gelar untuk login ke sistem.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-7">NIP (Nomor Induk Pegawai)</label>
                            <input type="text" name="nip" class="form-control" value="{{ $user->nip }}" placeholder="Contoh: 19850712 201001 2 005">
                            <div class="form-text fs-8 text-muted">Opsional untuk pendidik/tenaga kependidikan ber-NIP.</div>
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
                                <option value="siswa" {{ $user->role === 'siswa' ? 'selected' : '' }}>Siswa</option>
                            </select>
                        </div>

                        @if($user->role === 'siswa' && $user->siswa)
                            <div class="alert alert-info py-2 px-3 small border-0 mb-3 d-flex align-items-center gap-2 rounded-3" style="background:#e0f2fe; color:#0369a1;">
                                <i class="fa-solid fa-graduation-cap fs-5"></i>
                                <div>
                                    <strong>Data Siswa:</strong> Kelas {{ $user->siswa->kelas }} &bull; NIS: {{ $user->siswa->nis }}
                                </div>
                            </div>
                        @endif

                        <!-- Section Khusus Role Guru Edit -->
                        <div id="editGuruFields{{ $user->id }}" class="bg-light p-3 rounded mb-3 border" style="{{ $user->role === 'guru' ? '' : 'display: none;' }}">
                            <h6 class="fw-bold text-dark fs-7 mb-2"><i class="fa-solid fa-chalkboard-user me-1 text-primary"></i> Pengaturan Akses Guru</h6>
                            
                            @php
                                $assignedMapelIds = $user->assigned_mapel_ids;
                            @endphp

                            <!-- Multi-Mapel Diampu Edit -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-semibold mb-0">
                                        <i class="fa-solid fa-book-bookmark text-primary me-1"></i> Mata Pelajaran Diampu
                                    </label>
                                    <span class="badge bg-white text-secondary border shadow-xs" id="editMapelCountBadge{{ $user->id }}" style="font-size: 0.72rem;">{{ count($assignedMapelIds) }} dipilih</span>
                                </div>
                                <div class="border rounded-2 p-2 bg-white overflow-auto pe-1" style="max-height: 145px;" id="editMapelCheckboxes{{ $user->id }}">
                                    @foreach($mapels as $mapel)
                                        @php
                                            $t = $mapel->tingkat ?? 'Semua';
                                            $badgeClass = $t == '7' ? 'bg-info-subtle text-info-emphasis' : ($t == '8' ? 'bg-warning-subtle text-warning-emphasis' : ($t == '9' ? 'bg-danger-subtle text-danger-emphasis' : 'bg-primary-subtle text-primary-emphasis'));
                                            $isMapelChecked = in_array($mapel->id, $assignedMapelIds);
                                        @endphp
                                        <div class="form-check small py-1 border-bottom border-light">
                                            <input class="form-check-input edit-mapel-cb-{{ $user->id }}" type="checkbox" name="mata_pelajaran_ids[]" value="{{ $mapel->id }}" id="edit_mapel_{{ $user->id }}_{{ $mapel->id }}" data-tingkat="{{ $t }}" {{ $isMapelChecked ? 'checked' : '' }} onchange="updateMultiMapelFilter('{{ $user->id }}', true)">
                                            <label class="form-check-label d-flex justify-content-between align-items-center w-100 cursor-pointer" for="edit_mapel_{{ $user->id }}_{{ $mapel->id }}">
                                                <span class="fw-medium text-dark">{{ $mapel->nama_mapel }}</span>
                                                <span class="badge {{ $badgeClass }} border" style="font-size: 0.65rem;">Tingkat {{ $t }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="form-text fs-8 text-muted mt-1"><i class="fa-solid fa-info-circle me-1"></i> Centang satu atau beberapa mata pelajaran yang diampu.</div>
                            </div>

                            <!-- Kelas Diampu Edit -->
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-semibold mb-0">Kelas Diampu</label>
                                    <span class="badge bg-secondary-subtle text-secondary border" id="editKelasTingkatBadge{{ $user->id }}" style="font-size: 0.72rem;">Semua Tingkat</span>
                                </div>
                                <div class="row g-1 overflow-auto pe-1" id="editKelasContainer{{ $user->id }}" style="max-height: 140px;">
                                    @php $assigned = is_array($user->kelas_diampu) ? $user->kelas_diampu : []; @endphp
                                    @foreach($daftarKelas as $k)
                                        @php
                                            $t = 'Semua';
                                            if (preg_match('/^(VIII|8)/i', trim($k))) $t = '8';
                                            elseif (preg_match('/^(VII|7)/i', trim($k))) $t = '7';
                                            elseif (preg_match('/^(IX|9)/i', trim($k))) $t = '9';
                                        @endphp
                                        <div class="col-6 col-sm-4 kelas-item" data-tingkat="{{ $t }}">
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

    function applyRoleFilter(role) {
        document.getElementById('filterRoleInput').value = role;
        document.getElementById('userFilterForm').submit();
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

    function updateMultiMapelFilter(scope, isUserChange = false) {
        const isCreate = scope === 'create';
        const cbSelector = isCreate ? '.create-mapel-cb:checked' : '.edit-mapel-cb-' + scope + ':checked';
        const countBadge = document.getElementById(isCreate ? 'createMapelCountBadge' : 'editMapelCountBadge' + scope);
        const kelasContainer = document.getElementById(isCreate ? 'createKelasContainer' : 'editKelasContainer' + scope);
        const badge = document.getElementById(isCreate ? 'createKelasTingkatBadge' : 'editKelasTingkatBadge' + scope);

        const checkedBoxes = document.querySelectorAll(cbSelector);
        if (countBadge) {
            countBadge.textContent = checkedBoxes.length + ' dipilih';
        }

        const activeLevels = new Set();
        checkedBoxes.forEach(function (cb) {
            const t = cb.getAttribute('data-tingkat') || 'Semua';
            activeLevels.add(t);
        });

        const isAll = activeLevels.has('Semua') || activeLevels.size === 0;

        if (badge) {
            if (isAll) {
                badge.className = 'badge bg-secondary-subtle text-secondary border';
                badge.textContent = checkedBoxes.length === 0 ? 'Semua Tingkat' : 'Semua (7, 8, 9)';
            } else {
                const sortedLevels = Array.from(activeLevels).sort();
                badge.className = 'badge bg-primary text-white border-0';
                badge.textContent = 'Tingkat ' + sortedLevels.join(' & ');
            }
        }

        if (!kelasContainer) return;
        const items = kelasContainer.querySelectorAll('.kelas-item');
        items.forEach(function (item) {
            const itemTingkat = item.getAttribute('data-tingkat');
            const checkbox = item.querySelector('input[type="checkbox"]');
            const shouldShow = isAll || activeLevels.has(itemTingkat);

            if (shouldShow) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
                if (isUserChange && checkbox) {
                    checkbox.checked = false;
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        handleRoleChange('createRoleSelect', 'createGuruFields');
        updateMultiMapelFilter('create', false);

        // Inisialisasi filter untuk tiap modal edit pengguna
        document.querySelectorAll('[id^="editGuruFields"]').forEach(function (field) {
            const userId = field.id.replace('editGuruFields', '');
            updateMultiMapelFilter(userId, false);
        });
    });
</script>
@endpush
@endsection
