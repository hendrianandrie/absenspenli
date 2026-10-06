@extends('layouts.app')

@section('content')
<div class="d-flex flex-column gap-3">
    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge rounded-pill px-2.5 py-1" style="background: #eef2ff; color: #4f46e5; font-size: 11px;">
                    <i class="fa-solid fa-graduation-cap me-1"></i> Akademik & Kurikulum
                </span>
                <span class="text-slate-400" style="font-size: 12px;">•</span>
                <span class="text-slate-500 small" style="font-size: 12px;">Tahun Ajaran {{ date('Y') }}/{{ date('Y') + 1 }}</span>
            </div>
            <h2 class="fw-bold mb-1 text-slate-800" style="font-size: 1.4rem; letter-spacing: -0.02em;">
                <i class="fa-solid fa-user-tie text-primary me-2"></i> Pengaturan Wali Kelas
            </h2>
            <p class="text-slate-500 mb-0" style="font-size: 13px;">
                Tetapkan akun guru/pengguna sebagai Wali Kelas penanggung jawab rombongan belajar SMP Negeri 5 Ciamis.
            </p>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row g-3">
        <div class="col-sm-4">
            <div class="cruip-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="mosaic-icon-box" style="width: 36px; height: 36px; background: #eef2ff; color: #4f46e5; border-radius: 10px;">
                        <i class="fa-solid fa-school" style="font-size: 13.5px;"></i>
                    </div>
                    <span class="badge rounded-pill fw-semibold px-2 py-0.5" style="background: #eef2ff; color: #4338ca; font-size: 10.5px;">
                        SMPN 5 Ciamis
                    </span>
                </div>
                <div class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Total Rombel Kelas</div>
                <div class="d-flex align-items-baseline gap-1.5 mt-1">
                    <div class="fw-bold text-slate-800" style="font-size: 1.45rem; line-height: 1.2;">{{ $totalKelas }}</div>
                    <span class="text-slate-400" style="font-size: 11px;">rombongan belajar</span>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="cruip-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="mosaic-icon-box" style="width: 36px; height: 36px; background: #ecfdf5; color: #059669; border-radius: 10px;">
                        <i class="fa-solid fa-user-check" style="font-size: 13.5px;"></i>
                    </div>
                    <span class="badge rounded-pill fw-semibold px-2 py-0.5" style="background: #ecfdf5; color: #047857; font-size: 10.5px;">
                        Aktif
                    </span>
                </div>
                <div class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Wali Kelas Ditetapkan</div>
                <div class="d-flex align-items-baseline gap-1.5 mt-1">
                    <div class="fw-bold text-success" style="font-size: 1.45rem; line-height: 1.2;">{{ $assignedCount }}</div>
                    <span class="text-slate-400" style="font-size: 11px;">kelas</span>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="cruip-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="mosaic-icon-box" style="width: 36px; height: 36px; background: #fff1f2; color: #e11d48; border-radius: 10px;">
                        <i class="fa-solid fa-user-xmark" style="font-size: 13.5px;"></i>
                    </div>
                    <span class="badge rounded-pill fw-semibold px-2 py-0.5" style="background: #fff1f2; color: #be123c; font-size: 10.5px;">
                        Perlu Diisi
                    </span>
                </div>
                <div class="text-slate-500 text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.4px;">Belum Memiliki Wali</div>
                <div class="d-flex align-items-baseline gap-1.5 mt-1">
                    <div class="fw-bold {{ $unassignedCount > 0 ? 'text-danger' : 'text-slate-800' }}" style="font-size: 1.45rem; line-height: 1.2;">{{ $unassignedCount }}</div>
                    <span class="text-slate-400" style="font-size: 11px;">kelas</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Rombel & Wali Kelas -->
    <div class="cruip-card overflow-hidden">
        <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 bg-slate-50">
            <div class="fw-bold text-slate-800" style="font-size: 13.5px;">
                <i class="fa-solid fa-list-check text-primary me-2"></i> Daftar Rombongan Belajar & Penetapan Wali Kelas
            </div>
            <div class="text-slate-400 small" style="font-size: 11.5px;">
                Klik tombol <span class="badge bg-light text-dark border">Ubah Wali</span> untuk memperbarui guru pembimbing
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                <thead class="bg-slate-50 text-slate-500" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-3 py-2.5" style="width: 50px;">No</th>
                        <th class="py-2.5" style="width: 130px;">Rombel Kelas</th>
                        <th class="py-2.5" style="width: 100px;">Tingkat</th>
                        <th class="py-2.5" style="width: 130px;">Jumlah Siswa</th>
                        <th class="py-2.5">Wali Kelas Saat Ini</th>
                        <th class="text-center py-2.5 pe-3" style="width: 250px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($daftarKelas as $index => $kelas)
                        @php
                            $waliObj = $waliMap->get($kelas);
                            $assignedUser = $waliObj ? $waliObj->user : null;
                            $tingkat = \App\Models\MataPelajaran::getTingkatFromKelas($kelas);
                            $jmlSiswa = $siswaCounts[$kelas] ?? 0;
                        @endphp
                        <tr>
                            <td class="ps-3 py-2.5 text-slate-400 fw-medium">{{ $index + 1 }}</td>
                            <td class="py-2.5">
                                <span class="badge rounded-pill fw-bold px-2.5 py-1" style="background: #eef2ff; color: #4338ca; font-size: 12px; border: 1px solid #c7d2fe;">
                                    Kelas {{ $kelas }}
                                </span>
                            </td>
                            <td class="py-2.5">
                                <span class="badge rounded-pill px-2 py-0.5" style="background: #f1f5f9; color: #475569; font-size: 11px;">
                                    Tingkat {{ $tingkat }}
                                </span>
                            </td>
                            <td class="py-2.5">
                                <span class="fw-semibold text-slate-700">{{ $jmlSiswa }}</span>
                                <span class="text-slate-400" style="font-size: 11px;">siswa</span>
                            </td>
                            <td class="py-2.5">
                                @if($assignedUser)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                                             style="width: 32px; height: 32px; background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); font-size: 12px; flex-shrink: 0;">
                                            {{ strtoupper(substr($assignedUser->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-slate-800" style="font-size: 13px;">{{ $assignedUser->name }}</div>
                                            <div class="text-slate-400" style="font-size: 11px;">
                                                <span class="badge bg-light text-slate-700 border font-monospace px-1 py-0.5 rounded" style="font-size: 9.5px;">@ {{ $assignedUser->username ?: strtolower(str_replace(' ', '', $assignedUser->name)) }}</span>
                                                • <i class="fa-regular fa-envelope me-1"></i>{{ $assignedUser->email }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge rounded-pill px-2.5 py-1" style="background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; font-size: 11px;">
                                        <i class="fa-solid fa-circle-exclamation me-1"></i> Belum Ditetapkan
                                    </span>
                                @endif
                            </td>
                            <td class="text-center py-2.5 pe-3">
                                <div class="d-inline-flex gap-1.5">
                                    <!-- Tombol Buka Modal Penetapan (Trigger Single Global Modal) -->
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1"
                                            style="font-size: 11.5px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalAssignWali"
                                            data-kelas="{{ $kelas }}"
                                            data-jml="{{ $jmlSiswa }}"
                                            data-user-id="{{ $assignedUser->id ?? '' }}">
                                        <i class="fa-solid fa-user-pen me-1"></i> {{ $assignedUser ? 'Ubah Wali' : 'Pilih Wali' }}
                                    </button>

                                    <!-- Tombol Lihat Nilai Siswa Rombel Ini -->
                                    <a href="{{ route('walikelas.myClass', ['kelas' => $kelas]) }}"
                                       class="btn btn-sm btn-light text-slate-700 border rounded-pill px-2.5 py-1"
                                       style="font-size: 11.5px;"
                                       title="Monitoring Rekap Nilai Siswa Kelas {{ $kelas }}">
                                        <i class="fa-solid fa-graduation-cap me-1 text-primary"></i> Nilai Siswa
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-slate-400">
                                <i class="fa-solid fa-folder-open fs-3 d-block mb-2"></i>
                                Belum ada data rombongan belajar kelas yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Penetapan Wali Kelas (Placed Outside Card / Table to Prevent Z-Index / Flickering Issue) -->
<div class="modal fade" id="modalAssignWali" tabindex="-1" aria-labelledby="modalAssignWaliLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <form action="{{ route('walikelas.assign') }}" method="POST">
                @csrf
                <input type="hidden" name="kelas" id="modalKelasInput" value="">

                <div class="modal-header border-bottom py-3 px-4 bg-slate-50">
                    <div class="d-flex align-items-center gap-2">
                        <div class="mosaic-icon-box" style="width: 34px; height: 34px; background: #eef2ff; color: #4f46e5; border-radius: 8px;">
                            <i class="fa-solid fa-user-tie" style="font-size: 14px;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-slate-800" id="modalAssignWaliLabel" style="font-size: 14px;">
                                Penetapan Wali Kelas
                            </h5>
                            <small class="text-slate-400" style="font-size: 11px;">Pilih akun yang terdaftar dalam sistem</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-slate-700" style="font-size: 12.5px;">Rombongan Belajar</label>
                        <input type="text" id="modalKelasDisplay" class="form-control form-control-sm bg-light" value="" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="modalUserIdSelect" class="form-label fw-bold text-slate-700" style="font-size: 12.5px;">
                            Pilih Akun Wali Kelas <span class="text-danger">*</span>
                        </label>
                        <select name="user_id" id="modalUserIdSelect" class="form-select form-select-sm" style="font-size: 12.5px;">
                            <option value="">-- Kosongkan / Belum Ada Wali --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">
                                    {{ $u->name }} (@ {{ $u->username ?: strtolower(str_replace(' ', '', $u->name)) }} — {{ ucfirst($u->role) }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-slate-400 mt-1 d-block" style="font-size: 11px;">
                            Akun yang dipilih akan mendapatkan menu khusus <strong>Wali Kelas</strong> pada dashboard akunnya.
                        </small>
                    </div>
                </div>

                <div class="modal-footer border-top py-2.5 px-4 bg-slate-50 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-dismiss="modal" style="font-size: 12px;">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" style="font-size: 12px;">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Penetapan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('modalAssignWali');
    if (modalElement) {
        modalElement.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) return;

            const kelas = button.getAttribute('data-kelas') || '';
            const jml = button.getAttribute('data-jml') || '0';
            const userId = button.getAttribute('data-user-id') || '';

            const labelEl = document.getElementById('modalAssignWaliLabel');
            const kelasInput = document.getElementById('modalKelasInput');
            const kelasDisplay = document.getElementById('modalKelasDisplay');
            const userSelect = document.getElementById('modalUserIdSelect');

            if (labelEl) labelEl.textContent = 'Penetapan Wali Kelas ' + kelas;
            if (kelasInput) kelasInput.value = kelas;
            if (kelasDisplay) kelasDisplay.value = 'Kelas ' + kelas + ' (' + jml + ' Siswa)';
            if (userSelect) userSelect.value = userId;
        });
    }
});
</script>
@endpush
@endsection
