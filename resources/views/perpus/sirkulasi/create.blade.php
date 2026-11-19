@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Header Banner -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 fs-7">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('perpus.sirkulasi.index') }}" class="text-decoration-none">Sirkulasi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Peminjaman Kilat</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-bolt text-warning me-2"></i>Meja Sirkulasi Kilat (Tanpa Kartu)
            </h3>
            <p class="text-muted small mb-0">Cukup ketik 2-3 huruf nama/NIS siswa, pilih buku yang dipinjam, lalu simpan dalam hitungan detik.</p>
        </div>
        <div>
            <a href="{{ route('perpus.sirkulasi.index') }}" class="btn btn-outline-secondary rounded-3 shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Riwayat Sirkulasi
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('perpus.sirkulasi.store') }}" method="POST" id="formPeminjaman">
        @csrf
        <input type="hidden" name="siswa_id" id="selectedSiswaId" required>
        <input type="hidden" name="buku_id" id="selectedBukuId" required>

        <div class="row g-4">
            <!-- Langkah 1: Pilih Siswa Tanpa Kartu (Kiri) -->
            <div class="col-lg-6">
                <div class="card card-custom p-4 bg-white shadow-sm h-100 border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h5 class="fw-bold text-dark mb-0">
                            <span class="badge bg-primary rounded-circle p-2 px-2.5 me-2 fs-7">1</span>
                            Identitas Peminjam (Siswa)
                        </h5>
                        <span class="badge bg-success-subtle text-success border border-success-subtle fs-8">Cardless / Tanpa Kartu</span>
                    </div>

                    <!-- Input Live Search Siswa -->
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-semibold fs-7"><i class="fa-solid fa-magnifying-glass text-primary me-1"></i> Cari Nama / NIS Siswa</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                            <input type="text" id="inputCariSiswa" class="form-control border-start-0 py-2.5" placeholder="Ketik nama atau NIS (contoh: Anita / 55)..." autocomplete="off" autofocus>
                        </div>
                        <!-- Dropdown Hasil Pencarian Siswa -->
                        <div id="dropdownHasilSiswa" class="list-group shadow-lg position-absolute w-100 mt-1 d-none rounded-3" style="z-index: 1050; max-height: 260px; overflow-y: auto;"></div>
                    </div>

                    <!-- Kartu Informasi Siswa Terpilih -->
                    <div id="kartuSiswaTerpilih" class="d-none">
                        <div class="p-3 rounded-4 border bg-slate-50 mb-3" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                            <div class="d-flex align-items-start gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm flex-shrink-0" 
                                     id="avatarSiswa"
                                     style="width: 50px; height: 50px; background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); font-size: 20px;">
                                    A
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="fw-bold text-dark mb-0" id="namaSiswaDisplay">-</h5>
                                            <div class="small text-muted font-monospace">NIS: <span id="nisSiswaDisplay">-</span> &bull; Kelas <span id="kelasSiswaDisplay" class="fw-semibold text-primary">-</span></div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="resetPilihanSiswa()" title="Ganti Siswa">
                                            <i class="fa-solid fa-xmark"></i> Ganti
                                        </button>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top border-slate-200">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-8">
                                            <i class="fa-solid fa-award me-1"></i> <span id="poinSiswaDisplay">0</span> Poin Literasi
                                        </span>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle fs-8">
                                            <i class="fa-solid fa-book me-1"></i> Sedang Pinjam: <strong id="pinjamSiswaDisplay">0</strong> / 3 Buku
                                        </span>
                                        <span id="badgeOverdueSiswa" class="badge bg-danger-subtle text-danger border border-danger-subtle fs-8 d-none">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Ada Buku Terlambat!
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Placeholder Belum Memilih Siswa -->
                    <div id="placeholderSiswa" class="p-4 text-center text-muted border border-dashed rounded-3">
                        <i class="fa-solid fa-user-plus fs-2 mb-2 text-secondary opacity-50"></i>
                        <div class="small">Ketik nama siswa di kolom pencarian di atas untuk memilih peminjam.</div>
                    </div>
                </div>
            </div>

            <!-- Langkah 2: Pilih Buku & Atur Masa Pinjam (Kanan) -->
            <div class="col-lg-6">
                <div class="card card-custom p-4 bg-white shadow-sm h-100 border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h5 class="fw-bold text-dark mb-0">
                            <span class="badge bg-primary rounded-circle p-2 px-2.5 me-2 fs-7">2</span>
                            Buku yang Dipinjam
                        </h5>
                        <span class="badge bg-secondary-subtle text-secondary fs-8">Katalog Perpustakaan</span>
                    </div>

                    <!-- Input Live Search Buku -->
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-semibold fs-7"><i class="fa-solid fa-barcode text-primary me-1"></i> Cari Judul / Kode Buku</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-book-bookmark text-muted"></i></span>
                            <input type="text" id="inputCariBuku" class="form-control border-start-0 py-2.5" placeholder="Ketik judul buku atau scan barcode (contoh: Laskar / BK-001)..." autocomplete="off">
                        </div>
                        <!-- Dropdown Hasil Pencarian Buku -->
                        <div id="dropdownHasilBuku" class="list-group shadow-lg position-absolute w-100 mt-1 d-none rounded-3" style="z-index: 1050; max-height: 260px; overflow-y: auto;"></div>
                    </div>

                    <!-- Kartu Informasi Buku Terpilih -->
                    <div id="kartuBukuTerpilih" class="d-none">
                        <div class="p-3 rounded-4 border bg-slate-50 mb-3" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                            <div class="d-flex align-items-start gap-3">
                                <div class="rounded-3 bg-white border d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0 shadow-sm" style="width: 50px; height: 68px;">
                                    <i class="fa-solid fa-book text-primary fs-3"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0" id="judulBukuDisplay">-</h6>
                                            <div class="small text-muted">
                                                <span class="badge bg-secondary-subtle text-secondary font-monospace" id="kodeBukuDisplay">-</span>
                                                &bull; Penulis: <span id="pengarangBukuDisplay">-</span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="resetPilihanBuku()" title="Ganti Buku">
                                            <i class="fa-solid fa-xmark"></i> Ganti
                                        </button>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top border-slate-200">
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle fs-8">
                                            <i class="fa-solid fa-box-archive me-1"></i> Rak: <strong id="rakBukuDisplay">-</strong>
                                        </span>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle fs-8">
                                            <i class="fa-solid fa-cubes-stacked me-1"></i> Stok Tersedia: <strong id="stokBukuDisplay">-</strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Placeholder Belum Memilih Buku -->
                    <div id="placeholderBuku" class="p-4 text-center text-muted border border-dashed rounded-3 mb-3">
                        <i class="fa-solid fa-book-medical fs-2 mb-2 text-secondary opacity-50"></i>
                        <div class="small">Ketik judul atau kode buku di kolom pencarian di atas.</div>
                    </div>

                    <!-- Pengaturan Durasi Peminjaman -->
                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold fs-7">Lama Pinjam (Hari)</label>
                            <select name="durasi_hari" id="selectDurasiHari" class="form-select" onchange="hitungJatuhTempo()">
                                <option value="3">3 Hari</option>
                                <option value="7" selected>7 Hari (1 Minggu - Standar)</option>
                                <option value="14">14 Hari (2 Minggu)</option>
                                <option value="21">21 Hari (3 Minggu)</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold fs-7">Batas Pengembalian</label>
                            <input type="text" id="displayJatuhTempo" class="form-control bg-light fw-bold text-primary" readonly value="-">
                        </div>
                        <div class="col-12 mt-2">
                            <label class="form-label fw-semibold fs-7">Catatan Peminjaman (Opsional)</label>
                            <input type="text" name="catatan" class="form-control" placeholder="Catatan kondisi buku atau keperluan tugas...">
                        </div>
                    </div>

                    <!-- Tombol Konfirmasi Final -->
                    <button type="submit" id="btnSubmitPinjam" class="btn btn-primary-custom w-100 py-3 rounded-3 shadow fw-bold fs-6 mt-2" disabled>
                        <i class="fa-solid fa-circle-check me-2"></i> Konfirmasi & Pinjamkan Buku
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const searchSiswaUrl = "{{ route('perpus.sirkulasi.searchSiswa') }}";
    const searchBukuUrl = "{{ route('perpus.sirkulasi.searchBuku') }}";

    let debounceTimerSiswa;
    let debounceTimerBuku;

    // 1. Live Search Siswa
    document.getElementById('inputCariSiswa').addEventListener('input', function() {
        const q = this.value.trim();
        clearTimeout(debounceTimerSiswa);
        const dropdown = document.getElementById('dropdownHasilSiswa');

        if (q.length < 1) {
            dropdown.classList.add('d-none');
            return;
        }

        debounceTimerSiswa = setTimeout(() => {
            fetch(`${searchSiswaUrl}?q=${encodeURIComponent(q)}`)
                .then(res => {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(data => {
                    dropdown.innerHTML = '';
                    if (data.length === 0) {
                        dropdown.innerHTML = '<div class="list-group-item text-muted small p-3 text-center">Tidak ditemukan siswa dengan kata kunci tersebut.</div>';
                    } else {
                        data.forEach(s => {
                            const item = document.createElement('a');
                            item.href = 'javascript:void(0)';
                            item.className = 'list-group-item list-group-item-action p-2.5 d-flex justify-content-between align-items-center';
                            item.innerHTML = `
                                <div>
                                    <div class="fw-bold text-dark fs-7">${s.nama}</div>
                                    <div class="small text-muted font-monospace">NIS: ${s.nis} &bull; Kelas: ${s.kelas}</div>
                                </div>
                                <div class="text-end">
                                    <span class="badge ${s.active_loans >= 3 ? 'bg-danger' : 'bg-primary-subtle text-primary'} rounded-pill">
                                        Pinjam: ${s.active_loans}/3
                                    </span>
                                </div>
                            `;
                            item.addEventListener('click', () => pilihSiswa(s));
                            dropdown.appendChild(item);
                        });
                    }
                    dropdown.classList.remove('d-none');
                })
                .catch(err => {
                    console.error('Error search siswa:', err);
                    dropdown.innerHTML = '<div class="list-group-item text-danger small p-3 text-center">Gagal memuat data siswa. Silakan coba lagi.</div>';
                    dropdown.classList.remove('d-none');
                });
        }, 250);
    });

    function pilihSiswa(s) {
        document.getElementById('selectedSiswaId').value = s.id;
        document.getElementById('namaSiswaDisplay').innerText = s.nama;
        document.getElementById('nisSiswaDisplay').innerText = s.nis;
        document.getElementById('kelasSiswaDisplay').innerText = s.kelas;
        
        const avatarEl = document.getElementById('avatarSiswa');
        if (s.foto_wajah) {
            avatarEl.innerHTML = `<img src="${s.foto_wajah}" class="w-100 h-100 rounded-circle object-fit-cover">`;
        } else {
            avatarEl.innerText = s.nama.charAt(0).toUpperCase();
        }

        document.getElementById('poinSiswaDisplay').innerText = s.total_poin;
        document.getElementById('pinjamSiswaDisplay').innerText = s.active_loans;

        const badgeOverdue = document.getElementById('badgeOverdueSiswa');
        if (s.overdue_loans > 0) {
            badgeOverdue.classList.remove('d-none');
        } else {
            badgeOverdue.classList.add('d-none');
        }

        document.getElementById('inputCariSiswa').value = '';
        document.getElementById('dropdownHasilSiswa').classList.add('d-none');
        document.getElementById('placeholderSiswa').classList.add('d-none');
        document.getElementById('kartuSiswaTerpilih').classList.remove('d-none');

        checkReadyToSubmit();
        document.getElementById('inputCariBuku').focus();
    }

    function resetPilihanSiswa() {
        document.getElementById('selectedSiswaId').value = '';
        document.getElementById('kartuSiswaTerpilih').classList.add('d-none');
        document.getElementById('placeholderSiswa').classList.remove('d-none');
        document.getElementById('inputCariSiswa').focus();
        checkReadyToSubmit();
    }

    // 2. Live Search Buku
    document.getElementById('inputCariBuku').addEventListener('input', function() {
        const q = this.value.trim();
        clearTimeout(debounceTimerBuku);
        const dropdown = document.getElementById('dropdownHasilBuku');

        if (q.length < 1) {
            dropdown.classList.add('d-none');
            return;
        }

        debounceTimerBuku = setTimeout(() => {
            fetch(`${searchBukuUrl}?q=${encodeURIComponent(q)}`)
                .then(res => {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(data => {
                    dropdown.innerHTML = '';
                    if (data.length === 0) {
                        dropdown.innerHTML = '<div class="list-group-item text-muted small p-3 text-center">Buku tidak ditemukan.</div>';
                    } else {
                        data.forEach(b => {
                            const item = document.createElement('a');
                            item.href = 'javascript:void(0)';
                            item.className = `list-group-item list-group-item-action p-2.5 d-flex justify-content-between align-items-center ${!b.is_available ? 'disabled opacity-50 bg-light' : ''}`;
                            item.innerHTML = `
                                <div>
                                    <div class="fw-bold text-dark fs-7">${b.judul}</div>
                                    <div class="small text-muted font-monospace">${b.kode_buku} &bull; Rak: ${b.nomor_rak} &bull; ${b.kategori}</div>
                                </div>
                                <div class="text-end">
                                    <span class="badge ${b.is_available ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'} rounded-pill">
                                        Stok: ${b.stok_tersedia}
                                    </span>
                                </div>
                            `;
                            if (b.is_available) {
                                item.addEventListener('click', () => pilihBuku(b));
                            }
                            dropdown.appendChild(item);
                        });
                    }
                    dropdown.classList.remove('d-none');
                })
                .catch(err => {
                    console.error('Error search buku:', err);
                    dropdown.innerHTML = '<div class="list-group-item text-danger small p-3 text-center">Gagal memuat data buku. Silakan coba lagi.</div>';
                    dropdown.classList.remove('d-none');
                });
        }, 250);
    });

    function pilihBuku(b) {
        document.getElementById('selectedBukuId').value = b.id;
        document.getElementById('judulBukuDisplay').innerText = b.judul;
        document.getElementById('kodeBukuDisplay').innerText = b.kode_buku;
        document.getElementById('pengarangBukuDisplay').innerText = b.pengarang;
        document.getElementById('rakBukuDisplay').innerText = b.nomor_rak;
        document.getElementById('stokBukuDisplay').innerText = b.stok_tersedia;

        document.getElementById('inputCariBuku').value = '';
        document.getElementById('dropdownHasilBuku').classList.add('d-none');
        document.getElementById('placeholderBuku').classList.add('d-none');
        document.getElementById('kartuBukuTerpilih').classList.remove('d-none');

        checkReadyToSubmit();
    }

    function resetPilihanBuku() {
        document.getElementById('selectedBukuId').value = '';
        document.getElementById('kartuBukuTerpilih').classList.add('d-none');
        document.getElementById('placeholderBuku').classList.remove('d-none');
        document.getElementById('inputCariBuku').focus();
        checkReadyToSubmit();
    }

    function hitungJatuhTempo() {
        const days = parseInt(document.getElementById('selectDurasiHari').value) || 7;
        const target = new Date();
        target.setDate(target.getDate() + days);

        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        document.getElementById('displayJatuhTempo').value = target.toLocaleDateString('id-ID', options);
    }

    function checkReadyToSubmit() {
        const siswaId = document.getElementById('selectedSiswaId').value;
        const bukuId = document.getElementById('selectedBukuId').value;
        const btn = document.getElementById('btnSubmitPinjam');

        if (siswaId && bukuId) {
            btn.removeAttribute('disabled');
            btn.classList.add('animate__animated', 'animate__pulse');
        } else {
            btn.setAttribute('disabled', 'disabled');
        }
    }

    // Inisialisasi hitung jatuh tempo saat load
    hitungJatuhTempo();

    // Tutup dropdown saat klik di luar area input
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#inputCariSiswa') && !e.target.closest('#dropdownHasilSiswa')) {
            const el = document.getElementById('dropdownHasilSiswa');
            if (el) el.classList.add('d-none');
        }
        if (!e.target.closest('#inputCariBuku') && !e.target.closest('#dropdownHasilBuku')) {
            const el = document.getElementById('dropdownHasilBuku');
            if (el) el.classList.add('d-none');
        }
    });
</script>
@endsection
