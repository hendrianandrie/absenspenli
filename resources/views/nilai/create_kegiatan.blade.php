@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-plus-circle text-success me-2"></i> Tambah Kegiatan Penilaian Baru</h3>
        <p class="text-muted mb-0">Buat judul kegiatan (Tugas / Ulangan / UTS / UAS) dan langsung input nilai siswa.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('nilai.index', ['kelas' => $selectedKelas, 'mata_pelajaran_id' => $selectedMapelId]) }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Matriks
        </a>
    </div>
</div>

<form action="{{ route('nilai.kegiatan.store') }}" method="POST">
    @csrf
    <div class="card card-custom p-4 bg-white mb-4 shadow-sm">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-file-pen text-primary me-2"></i> Detail Kegiatan Penilaian</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Mata Pelajaran</label>
                <select name="mata_pelajaran_id" id="selectMapelKegiatan" class="form-select" required onchange="filterKelasKegiatanByMapel(this, true)">
                    @foreach($mapels as $m)
                        <option value="{{ $m->id }}" data-tingkat="{{ $m->tingkat ?? 'Semua' }}" {{ $selectedMapelId == $m->id ? 'selected' : '' }}>
                            {{ $m->nama_mapel }} @if(($m->tingkat ?? 'Semua') !== 'Semua') [Tingkat {{ $m->tingkat }}] @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Kelas</label>
                <select name="kelas" id="selectKelas" class="form-select" required>
                    @foreach($daftarKelas as $k)
                        @php $t = \App\Models\MataPelajaran::getTingkatFromKelas($k); @endphp
                        <option value="{{ $k }}" data-tingkat="{{ $t }}" {{ $selectedKelas == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Jenis Penilaian</label>
                <select name="jenis" class="form-select" required>
                    <option value="Tugas">Tugas</option>
                    <option value="UH">Ulangan Harian (UH)</option>
                    <option value="UTS">UTS</option>
                    <option value="UAS">UAS</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Tanggal Pelaksanaan</label>
                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold fs-7">Nama / Judul Kegiatan</label>
                <input type="text" name="nama_kegiatan" class="form-control" placeholder="Contoh: UH 1 Bab 1 Aljabar" required>
            </div>
        </div>
    </div>

    <!-- Input Nilai Siswa -->
    <div class="card card-custom p-4 bg-white shadow-sm">
        <h5 class="fw-bold mb-3">
            <i class="fa-solid fa-list-check text-primary me-2"></i> Input Nilai Siswa <span id="labelKelasHeader">Kelas {{ $selectedKelas }}</span>
        </h5>
        
        <div id="emptyContainer" class="text-center py-4 text-muted {{ $siswas->isEmpty() ? '' : 'd-none' }}">
            Belum ada siswa terdaftar di <span id="labelKelasEmpty">kelas {{ $selectedKelas }}</span>.
        </div>

        <div id="tableContainer" class="{{ $siswas->isEmpty() ? 'd-none' : '' }}">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th style="width: 200px;">Nilai (0 - 100)</th>
                        </tr>
                    </thead>
                    <tbody id="siswaTbody">
                        @foreach($siswas as $idx => $siswa)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td><span class="badge bg-light text-dark border font-monospace">{{ $siswa->nis }}</span></td>
                                <td class="fw-semibold">{{ $siswa->nama }}</td>
                                <td>
                                    <input type="number" step="0.1" min="0" max="100" name="nilai[{{ $siswa->id }}]" class="form-control fw-bold nilai-input" placeholder="0 - 100" oninput="validateNilaiInput(this)">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-success px-4 py-2 rounded-3 shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Kegiatan & Nilai Siswa
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    function validateNilaiInput(input) {
        if (!input || input.value === '') return;
        let val = parseFloat(input.value);
        if (isNaN(val)) {
            input.value = '';
            return;
        }
        if (val > 100) {
            input.value = 100;
        } else if (val < 0) {
            input.value = 0;
        }
    }

    const allAvailableClasses = [
        @foreach(($allDaftarKelas ?? $daftarKelas) as $k)
            { kelas: @json($k), tingkat: @json(\App\Models\MataPelajaran::getTingkatFromKelas($k)) },
        @endforeach
    ];

    function filterKelasKegiatanByMapel(selectMapelElem, isUserChange = false) {
        if (!selectMapelElem) return;
        const selectedOption = selectMapelElem.options[selectMapelElem.selectedIndex];
        const tingkat = selectedOption ? (selectedOption.getAttribute('data-tingkat') || 'Semua') : 'Semua';
        const selectKelas = document.getElementById('selectKelas');
        if (!selectKelas) return;

        const previousVal = selectKelas.value;
        selectKelas.innerHTML = '';

        let matchedFirst = null;
        let hasCurrent = false;

        allAvailableClasses.forEach(function(item) {
            if (tingkat === 'Semua' || item.tingkat === 'Semua' || item.tingkat === tingkat) {
                const opt = document.createElement('option');
                opt.value = item.kelas;
                opt.setAttribute('data-tingkat', item.tingkat);
                opt.textContent = 'Kelas ' + item.kelas;
                if (item.kelas === previousVal) {
                    opt.selected = true;
                    hasCurrent = true;
                }
                if (!matchedFirst) matchedFirst = item.kelas;
                selectKelas.appendChild(opt);
            }
        });

        if (!hasCurrent && matchedFirst) {
            selectKelas.value = matchedFirst;
        }

        if (selectKelas.options.length === 0) {
            const emptyOpt = document.createElement('option');
            emptyOpt.value = '';
            emptyOpt.textContent = '(Tidak ada kelas diampu untuk tingkat ini)';
            selectKelas.appendChild(emptyOpt);
        }

        selectKelas.dispatchEvent(new Event('change'));
    }

    document.addEventListener('DOMContentLoaded', function() {
        const siswasByKelas = @json($siswasByKelas);

        document.getElementById('selectKelas').addEventListener('change', function() {
            const selectedKelas = this.value;
            const labelHeader = document.getElementById('labelKelasHeader');
            const labelEmpty = document.getElementById('labelKelasEmpty');
            const tbody = document.getElementById('siswaTbody');
            const emptyContainer = document.getElementById('emptyContainer');
            const tableContainer = document.getElementById('tableContainer');

            if (labelHeader) labelHeader.innerText = 'Kelas ' + selectedKelas;
            if (labelEmpty) labelEmpty.innerText = 'kelas ' + selectedKelas;

            const siswas = siswasByKelas[selectedKelas] || [];

            if (siswas.length === 0) {
                tableContainer.classList.add('d-none');
                emptyContainer.classList.remove('d-none');
                tbody.innerHTML = '';
            } else {
                emptyContainer.classList.add('d-none');
                tableContainer.classList.remove('d-none');

                let html = '';
                siswas.forEach((siswa, index) => {
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td><span class="badge bg-light text-dark border font-monospace">${siswa.nis || '-'}</span></td>
                            <td class="fw-semibold">${siswa.nama}</td>
                            <td>
                                <input type="number" step="0.1" min="0" max="100" name="nilai[${siswa.id}]" class="form-control fw-bold nilai-input" placeholder="0 - 100" oninput="validateNilaiInput(this)">
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            }
        });

        // Inisialisasi filter kelas sesuai mapel tingkat awal
        filterKelasKegiatanByMapel(document.getElementById('selectMapelKegiatan'));
    });
</script>
@endsection
