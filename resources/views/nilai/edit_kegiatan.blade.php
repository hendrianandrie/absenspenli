@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-pen-to-square text-primary me-2"></i> Edit Nilai Kegiatan Penilaian</h3>
        <p class="text-muted mb-0">Ubah detail kegiatan dan nilai siswa untuk {{ $kegiatan->nama_kegiatan }}.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('nilai.index', ['kelas' => $kegiatan->kelas, 'mata_pelajaran_id' => $kegiatan->mata_pelajaran_id]) }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Matriks
        </a>
    </div>
</div>

<form action="{{ route('nilai.kegiatan.update', $kegiatan->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card card-custom p-4 bg-white mb-4 shadow-sm">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-file-pen text-primary me-2"></i> Detail Kegiatan Penilaian</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Mata Pelajaran</label>
                <input type="text" class="form-control" value="{{ $kegiatan->mataPelajaran->nama_mapel ?? '' }}" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Kelas</label>
                <input type="text" class="form-control" value="Kelas {{ $kegiatan->kelas }}" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Semester</label>
                <select name="semester" class="form-select" required>
                    <option value="1" {{ ($kegiatan->semester ?? 1) == 1 ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                    <option value="2" {{ ($kegiatan->semester ?? 1) == 2 ? 'selected' : '' }}>Semester 2 (Genap)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" class="form-control" value="{{ $kegiatan->tahun_ajaran ?? '2026/2027' }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Jenis Penilaian</label>
                <select name="jenis" class="form-select" required>
                    <option value="Tugas" {{ $kegiatan->jenis == 'Tugas' ? 'selected' : '' }}>Tugas</option>
                    <option value="UH" {{ $kegiatan->jenis == 'UH' ? 'selected' : '' }}>Ulangan Harian (UH)</option>
                    <option value="UTS" {{ $kegiatan->jenis == 'UTS' ? 'selected' : '' }}>UTS</option>
                    <option value="UAS" {{ $kegiatan->jenis == 'UAS' ? 'selected' : '' }}>UAS</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7">Tanggal Pelaksanaan</label>
                <input type="date" name="tanggal" class="form-control" value="{{ $kegiatan->tanggal }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold fs-7">Nama / Judul Kegiatan</label>
                <input type="text" name="nama_kegiatan" class="form-control" value="{{ $kegiatan->nama_kegiatan }}" required>
            </div>
        </div>
    </div>

    <!-- Input Nilai Siswa -->
    <div class="card card-custom p-4 bg-white shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h5 class="fw-bold mb-0">
                <i class="fa-solid fa-list-check text-primary me-2"></i> Nilai Siswa Kelas {{ $kegiatan->kelas }}
            </h5>
            <a href="{{ route('nilai.templateExcel', ['kelas' => $kegiatan->kelas, 'mata_pelajaran_id' => $kegiatan->mata_pelajaran_id, 'kegiatan_id' => $kegiatan->id]) }}" class="btn btn-outline-success btn-sm rounded-3 shadow-sm">
                <i class="fa-solid fa-file-excel me-1"></i> Unduh Data Nilai Kegiatan Ini (Excel)
            </a>
        </div>

        <!-- Banner Opsi Import File Excel -->
        <div class="card bg-light border-0 p-3 mb-3 rounded-3">
            <div class="row align-items-center g-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-excel text-success fs-3"></i>
                        <div>
                            <span class="fw-bold d-block text-dark fs-7">Perbarui Nilai via Excel / CSV</span>
                            <small class="text-muted">Unduh file Excel di atas, sesuaikan nilainya, lalu klik <strong>"Muat ke Tabel"</strong> untuk melihat dan memeriksa nilai di tabel sebelum disimpan.</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-md-end">
                        <div class="input-group input-group-sm flex-grow-1" style="max-width: 300px;">
                            <label class="input-group-text bg-white" for="inputFileExcelEdit"><i class="fa-solid fa-file-excel text-success me-1"></i> File</label>
                            <input type="file" name="file_excel" id="inputFileExcelEdit" class="form-control" accept=".xlsx, .xls, .csv">
                        </div>
                        <button type="button" id="btnMuatExcelEdit" class="btn btn-primary btn-sm px-3 shadow-sm text-nowrap" onclick="muatNilaiDariExcelEdit()">
                            <i class="fa-solid fa-file-import me-1"></i> Muat ke Tabel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wadah Notifikasi / Alert Hasil Muat Excel -->
        <div id="alertBoxMuatExcelEdit" class="d-none"></div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>NISN</th>
                        <th>Nama Siswa</th>
                        <th style="width: 200px;">Nilai (0 - 100)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswas as $idx => $siswa)
                        @php
                            $val = $nilaiMap[$siswa->id] ?? '';
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><span class="badge bg-light text-dark border font-monospace">{{ $siswa->nis }}</span></td>
                            <td class="fw-semibold">{{ $siswa->nama }}</td>
                            <td>
                                <input type="number" step="0.1" min="0" max="100" name="nilai[{{ $siswa->id }}]" class="form-control fw-bold nilai-input" value="{{ $val }}" placeholder="0 - 100" oninput="validateNilaiInput(this)">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary-custom px-4 py-2 rounded-3 shadow-sm">
                <i class="fa-solid fa-floppy-disk me-1"></i> Perbarui Nilai Kegiatan
            </button>
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

    function muatNilaiDariExcelEdit() {
        const fileInput = document.getElementById('inputFileExcelEdit');
        const btn = document.getElementById('btnMuatExcelEdit');
        const alertBox = document.getElementById('alertBoxMuatExcelEdit');
        const kelasVal = '{{ $kegiatan->kelas }}';

        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            alert('Silakan pilih file Excel (.xlsx / .xls / .csv) terlebih dahulu.');
            return;
        }

        const file = fileInput.files[0];
        const formData = new FormData();
        formData.append('file_excel', file);
        formData.append('kelas', kelasVal);

        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Membaca...';

        fetch('{{ route("nilai.parseExcel") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Gagal memproses file Excel'); });
            }
            return response.json();
        })
        .then(data => {
            let filledCount = 0;

            if (data.matched && data.matched.length > 0) {
                data.matched.forEach(item => {
                    const input = document.querySelector(`input[name="nilai[${item.siswa_id}]"]`);
                    if (input) {
                        input.value = item.nilai;
                        input.classList.remove('bg-light');
                        input.classList.add('bg-success-subtle', 'border-success', 'border-2');
                        validateNilaiInput(input);
                        filledCount++;
                    }
                });
            }

            let alertHtml = '';
            if (filledCount > 0) {
                alertHtml = `
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-success text-white p-2 rounded-circle mt-1">
                                <i class="fa-solid fa-check-double"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-success">Berhasil Memuat ${filledCount} Nilai Siswa ke Tabel!</h6>
                                <p class="mb-1 small text-dark">Nilai dari Excel telah dimasukkan ke kolom input di bawah (ditandai dengan kotak hijau). Silakan <strong>periksa kembali</strong> nilai-nilai tersebut, sesuaikan jika ada yang perlu diubah, lalu klik tombol <strong>"Perbarui Nilai Kegiatan"</strong> di bawah untuk menyimpan perubahan permanen.</p>
                                ${data.unmatched_count > 0 ? `<div class="small text-danger fw-semibold mt-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> ${data.unmatched_count} data siswa di file Excel tidak ditemukan di kelas ${kelasVal}.</div>` : ''}
                                ${data.skipped_count > 0 ? `<div class="small text-secondary mt-1"><i class="fa-solid fa-circle-info me-1"></i> ${data.skipped_count} siswa dikosongkan nilainya (belum ikut penilaian / nilai kosong di Excel).</div>` : ''}
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            } else {
                alertHtml = `
                    <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> Tidak ada nilai siswa yang cocok untuk dimasukkan. Pastikan format file menggunakan kolom <strong>NISN</strong> dan <strong>NILAI</strong> yang sesuai.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }

            alertBox.innerHTML = alertHtml;
            alertBox.classList.remove('d-none');
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        })
        .catch(error => {
            alertBox.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
                    <i class="fa-solid fa-circle-xmark me-2"></i> ${error.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            alertBox.classList.remove('d-none');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
        });
    }
</script>
@endsection
