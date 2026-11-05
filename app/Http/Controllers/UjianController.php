<?php

namespace App\Http\Controllers;

use App\Exports\UjianSoalTemplateExport;
use App\Imports\UjianSoalImport;
use App\Models\Kegiatan;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\Ujian;
use App\Models\UjianJawaban;
use App\Models\UjianSiswa;
use App\Models\UjianSoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class UjianController extends Controller
{
    /**
     * Helper cek otorisasi guru terhadap ujian
     */
    private function checkUjianAccess(Ujian $ujian)
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return true;
        }

        if ($ujian->user_id !== $user->id) {
            $assignedIds = $user->assigned_mapel_ids ?? [];
            if (!in_array($ujian->mata_pelajaran_id, $assignedIds)) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengelola ujian ini.');
            }
        }
    }

    /**
     * Daftar Semua Paket Ujian CBT
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGuru = in_array($user->role, ['guru', 'walikelas']);

        // Filter Mapel yang diampu
        if ($isGuru) {
            $assignedIds = $user->assigned_mapel_ids;
            $mapels = !empty($assignedIds)
                ? MataPelajaran::whereIn('id', $assignedIds)->orderBy('tingkat')->orderBy('nama_mapel')->get()
                : collect();
        } else {
            $mapels = MataPelajaran::orderBy('tingkat')->orderBy('nama_mapel')->get();
        }

        $query = Ujian::with(['mataPelajaran', 'user'])->withCount(['soals', 'ujianSiswas']);

        if ($isGuru) {
            $mapelIds = $mapels->pluck('id')->toArray();
            $query->where(function ($q) use ($user, $mapelIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('mata_pelajaran_id', $mapelIds);
            });
        }

        if ($request->filled('mata_pelajaran_id')) {
            $query->where('mata_pelajaran_id', $request->mata_pelajaran_id);
        }

        if ($request->filled('kelas') && $request->kelas !== 'Semua') {
            $targetKelas = $request->kelas;
            $query->where(function ($q) use ($targetKelas) {
                $q->where('kelas', 'Semua')
                  ->orWhere('kelas', $targetKelas)
                  ->orWhere('kelas', 'LIKE', '%' . $targetKelas . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ujians = $query->orderBy('created_at', 'desc')->paginate(15);

        // Daftar kelas untuk filter
        $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        return view('ujian.index', compact('ujians', 'mapels', 'daftarKelas'));
    }

    /**
     * Form Buat Paket Ujian Baru
     */
    public function create()
    {
        $user = Auth::user();
        $isGuru = in_array($user->role, ['guru', 'walikelas']);

        if ($isGuru) {
            $assignedIds = $user->assigned_mapel_ids;
            $mapels = !empty($assignedIds)
                ? MataPelajaran::whereIn('id', $assignedIds)->orderBy('tingkat')->orderBy('nama_mapel')->get()
                : collect();
        } else {
            $mapels = MataPelajaran::orderBy('tingkat')->orderBy('nama_mapel')->get();
        }

        $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $groupedKelas = $daftarKelas->groupBy(function ($k) {
            $parts = explode(' ', trim($k));
            return $parts[0] ?? 'Lainnya';
        });

        return view('ujian.create', compact('mapels', 'daftarKelas', 'groupedKelas'));
    }

    /**
     * Simpan Paket Ujian Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'judul' => 'required|string|max:200',
            'jenis' => 'required|in:Ulangan Harian,Sumatif Tengah Semester,Sumatif Akhir Semester,Try Out,UH,UTS,UAS,Sumatif',
            'kelas' => 'required|array|min:1',
            'kelas.*' => 'required|string',
            'durasi_menit' => 'required|integer|min:5|max:300',
            'waktu_mulai' => 'nullable|date',
            'waktu_selesai' => 'nullable|date|after_or_equal:waktu_mulai',
            'acak_soal' => 'nullable|boolean',
            'acak_opsi' => 'nullable|boolean',
            'tampilkan_nilai' => 'nullable|boolean',
        ], [
            'kelas.required' => 'Pilih minimal satu kelas target ujian.',
            'kelas.min' => 'Pilih minimal satu kelas target ujian.',
        ]);

        $allClasses = Siswa::select('kelas')->distinct()->pluck('kelas')->toArray();
        $selectedKelas = array_values(array_filter($request->input('kelas', [])));

        if (in_array('Semua', $selectedKelas, true) || (count($allClasses) > 0 && count($selectedKelas) >= count($allClasses))) {
            $kelasValue = 'Semua';
        } else {
            sort($selectedKelas, SORT_NATURAL);
            $kelasValue = implode(', ', $selectedKelas);
        }

        $semester = Kegiatan::determineSemester($request->waktu_mulai ?? now());
        $tahunAjaran = Kegiatan::determineTahunAjaran($request->waktu_mulai ?? now());

        $ujian = Ujian::create([
            'user_id' => Auth::id(),
            'mata_pelajaran_id' => $request->mata_pelajaran_id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'jenis' => $request->jenis,
            'kelas' => $kelasValue,
            'semester' => $semester,
            'tahun_ajaran' => $tahunAjaran,
            'durasi_menit' => $request->durasi_menit,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'token' => strtoupper(\Illuminate\Support\Str::random(5)),
            'acak_soal' => $request->boolean('acak_soal', true),
            'acak_opsi' => $request->boolean('acak_opsi', false),
            'tampilkan_nilai' => $request->boolean('tampilkan_nilai', true),
            'status' => 'draft',
        ]);

        return redirect()->route('ujian.soal', $ujian->id)
            ->with('success', 'Paket ujian berhasil dibuat! Silakan tambahkan butir soal atau impor dari Excel.');
    }

    /**
     * Form Edit Paket Ujian
     */
    public function edit(Ujian $ujian)
    {
        $this->checkUjianAccess($ujian);

        $user = Auth::user();
        $isGuru = in_array($user->role, ['guru', 'walikelas']);

        if ($isGuru) {
            $assignedIds = $user->assigned_mapel_ids;
            $mapels = !empty($assignedIds)
                ? MataPelajaran::whereIn('id', $assignedIds)->orderBy('tingkat')->orderBy('nama_mapel')->get()
                : collect();
        } else {
            $mapels = MataPelajaran::orderBy('tingkat')->orderBy('nama_mapel')->get();
        }

        $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $groupedKelas = $daftarKelas->groupBy(function ($k) {
            $parts = explode(' ', trim($k));
            return $parts[0] ?? 'Lainnya';
        });

        return view('ujian.edit', compact('ujian', 'mapels', 'daftarKelas', 'groupedKelas'));
    }

    /**
     * Update Paket Ujian
     */
    public function update(Request $request, Ujian $ujian)
    {
        $this->checkUjianAccess($ujian);

        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'judul' => 'required|string|max:200',
            'jenis' => 'required|in:Ulangan Harian,Sumatif Tengah Semester,Sumatif Akhir Semester,Try Out,UH,UTS,UAS,Sumatif',
            'kelas' => 'required|array|min:1',
            'kelas.*' => 'required|string',
            'durasi_menit' => 'required|integer|min:5|max:300',
            'waktu_mulai' => 'nullable|date',
            'waktu_selesai' => 'nullable|date|after_or_equal:waktu_mulai',
        ], [
            'kelas.required' => 'Pilih minimal satu kelas target ujian.',
            'kelas.min' => 'Pilih minimal satu kelas target ujian.',
        ]);

        $allClasses = Siswa::select('kelas')->distinct()->pluck('kelas')->toArray();
        $selectedKelas = array_values(array_filter($request->input('kelas', [])));

        if (in_array('Semua', $selectedKelas, true) || (count($allClasses) > 0 && count($selectedKelas) >= count($allClasses))) {
            $kelasValue = 'Semua';
        } else {
            sort($selectedKelas, SORT_NATURAL);
            $kelasValue = implode(', ', $selectedKelas);
        }

        $ujian->update([
            'mata_pelajaran_id' => $request->mata_pelajaran_id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'jenis' => $request->jenis,
            'kelas' => $kelasValue,
            'durasi_menit' => $request->durasi_menit,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'acak_soal' => $request->boolean('acak_soal', true),
            'acak_opsi' => $request->boolean('acak_opsi', false),
            'tampilkan_nilai' => $request->boolean('tampilkan_nilai', true),
        ]);

        return redirect()->route('ujian.index')
            ->with('success', 'Pengaturan ujian berhasil diperbarui.');
    }

    /**
     * Hapus Paket Ujian
     */
    public function destroy(Ujian $ujian)
    {
        $this->checkUjianAccess($ujian);
        $ujian->delete();

        return redirect()->route('ujian.index')
            ->with('success', 'Paket ujian berhasil dihapus.');
    }

    /**
     * Ubah Status Ujian (Draft <-> Aktif <-> Selesai)
     */
    public function toggleStatus(Request $request, Ujian $ujian)
    {
        $this->checkUjianAccess($ujian);

        $targetStatus = $request->input('status');
        if (!in_array($targetStatus, ['draft', 'aktif', 'selesai'])) {
            return back()->with('error', 'Status ujian tidak valid.');
        }

        // Cek jika ingin mengaktifkan tapi belum ada soal
        if ($targetStatus === 'aktif' && $ujian->soals()->count() === 0) {
            return back()->with('error', 'Ujian belum dapat diaktifkan karena belum memiliki butir soal.');
        }

        $ujian->update(['status' => $targetStatus]);

        return back()->with('success', "Status ujian berhasil diubah menjadi: {$targetStatus}.");
    }

    /**
     * Generate Token Baru
     */
    public function generateToken(Ujian $ujian)
    {
        $this->checkUjianAccess($ujian);
        $token = $ujian->generateToken();

        return back()->with('success', "Token ujian berhasil diperbarui: {$token}");
    }

    /**
     * Halaman Kelola Soal Ujian
     */
    public function kelolaSoal(Ujian $ujian)
    {
        $this->checkUjianAccess($ujian);
        $soals = $ujian->soals()->orderBy('nomor_urut')->get();

        return view('ujian.soal', compact('ujian', 'soals'));
    }

    /**
     * Tambah Butir Soal Baru Secara Manual
     */
    public function storeSoal(Request $request, Ujian $ujian)
    {
        $this->checkUjianAccess($ujian);

        $request->validate([
            'pertanyaan' => 'required|string',
            'opsi_a' => 'nullable|string',
            'opsi_b' => 'nullable|string',
            'opsi_c' => 'nullable|string',
            'opsi_d' => 'nullable|string',
            'kunci_jawaban' => 'required|in:A,B,C,D',
            'bobot' => 'nullable|numeric|min:0.1',
            'gambar' => 'nullable|image|max:3072',
            'gambar_a' => 'nullable|image|max:3072',
            'gambar_b' => 'nullable|image|max:3072',
            'gambar_c' => 'nullable|image|max:3072',
            'gambar_d' => 'nullable|image|max:3072',
        ]);

        // Validasi: Setiap opsi wajib memiliki minimal teks ATAU gambar
        foreach (['a', 'b', 'c', 'd'] as $opt) {
            if (!$request->filled("opsi_{$opt}") && !$request->hasFile("gambar_{$opt}")) {
                return back()->withInput()->with('error', "Pilihan Opsi " . strtoupper($opt) . " harus memiliki teks atau gambar!");
            }
        }

        $gambarPath = $request->hasFile('gambar') ? $request->file('gambar')->store('ujian_soal', 'public') : null;
        $gambarAPath = $request->hasFile('gambar_a') ? $request->file('gambar_a')->store('ujian_soal', 'public') : null;
        $gambarBPath = $request->hasFile('gambar_b') ? $request->file('gambar_b')->store('ujian_soal', 'public') : null;
        $gambarCPath = $request->hasFile('gambar_c') ? $request->file('gambar_c')->store('ujian_soal', 'public') : null;
        $gambarDPath = $request->hasFile('gambar_d') ? $request->file('gambar_d')->store('ujian_soal', 'public') : null;

        $nomorUrut = ($ujian->soals()->max('nomor_urut') ?? 0) + 1;

        UjianSoal::create([
            'ujian_id' => $ujian->id,
            'nomor_urut' => $nomorUrut,
            'pertanyaan' => $request->pertanyaan,
            'gambar' => $gambarPath,
            'opsi_a' => $request->opsi_a,
            'gambar_a' => $gambarAPath,
            'opsi_b' => $request->opsi_b,
            'gambar_b' => $gambarBPath,
            'opsi_c' => $request->opsi_c,
            'gambar_c' => $gambarCPath,
            'opsi_d' => $request->opsi_d,
            'gambar_d' => $gambarDPath,
            'kunci_jawaban' => strtoupper($request->kunci_jawaban),
            'bobot' => $request->filled('bobot') ? (float) $request->bobot : 1.0,
        ]);

        return back()->with('success', 'Butir soal berhasil ditambahkan!');
    }

    /**
     * Update Butir Soal
     */
    public function updateSoal(Request $request, Ujian $ujian, UjianSoal $soal)
    {
        $this->checkUjianAccess($ujian);

        $request->validate([
            'pertanyaan' => 'required|string',
            'opsi_a' => 'nullable|string',
            'opsi_b' => 'nullable|string',
            'opsi_c' => 'nullable|string',
            'opsi_d' => 'nullable|string',
            'kunci_jawaban' => 'required|in:A,B,C,D',
            'bobot' => 'nullable|numeric|min:0.1',
            'gambar' => 'nullable|image|max:3072',
            'gambar_a' => 'nullable|image|max:3072',
            'gambar_b' => 'nullable|image|max:3072',
            'gambar_c' => 'nullable|image|max:3072',
            'gambar_d' => 'nullable|image|max:3072',
        ]);

        // Cek kelengkapan opsi (bisa teks, gambar baru, atau gambar lama)
        foreach (['a', 'b', 'c', 'd'] as $opt) {
            $hasText = $request->filled("opsi_{$opt}");
            $hasNewImg = $request->hasFile("gambar_{$opt}");
            $hasOldImg = !empty($soal->{"gambar_{$opt}"});
            if (!$hasText && !$hasNewImg && !$hasOldImg) {
                return back()->withInput()->with('error', "Pilihan Opsi " . strtoupper($opt) . " harus memiliki teks atau gambar!");
            }
        }

        $gambarPath = $soal->gambar;
        if ($request->hasFile('gambar')) {
            if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
                Storage::disk('public')->delete($soal->gambar);
            }
            $gambarPath = $request->file('gambar')->store('ujian_soal', 'public');
        }

        $gambarAPath = $soal->gambar_a;
        if ($request->hasFile('gambar_a')) {
            if ($soal->gambar_a && Storage::disk('public')->exists($soal->gambar_a)) {
                Storage::disk('public')->delete($soal->gambar_a);
            }
            $gambarAPath = $request->file('gambar_a')->store('ujian_soal', 'public');
        }

        $gambarBPath = $soal->gambar_b;
        if ($request->hasFile('gambar_b')) {
            if ($soal->gambar_b && Storage::disk('public')->exists($soal->gambar_b)) {
                Storage::disk('public')->delete($soal->gambar_b);
            }
            $gambarBPath = $request->file('gambar_b')->store('ujian_soal', 'public');
        }

        $gambarCPath = $soal->gambar_c;
        if ($request->hasFile('gambar_c')) {
            if ($soal->gambar_c && Storage::disk('public')->exists($soal->gambar_c)) {
                Storage::disk('public')->delete($soal->gambar_c);
            }
            $gambarCPath = $request->file('gambar_c')->store('ujian_soal', 'public');
        }

        $gambarDPath = $soal->gambar_d;
        if ($request->hasFile('gambar_d')) {
            if ($soal->gambar_d && Storage::disk('public')->exists($soal->gambar_d)) {
                Storage::disk('public')->delete($soal->gambar_d);
            }
            $gambarDPath = $request->file('gambar_d')->store('ujian_soal', 'public');
        }

        $soal->update([
            'pertanyaan' => $request->pertanyaan,
            'gambar' => $gambarPath,
            'opsi_a' => $request->opsi_a,
            'gambar_a' => $gambarAPath,
            'opsi_b' => $request->opsi_b,
            'gambar_b' => $gambarBPath,
            'opsi_c' => $request->opsi_c,
            'gambar_c' => $gambarCPath,
            'opsi_d' => $request->opsi_d,
            'gambar_d' => $gambarDPath,
            'kunci_jawaban' => strtoupper($request->kunci_jawaban),
            'bobot' => $request->filled('bobot') ? (float) $request->bobot : 1.0,
        ]);

        return back()->with('success', 'Butir soal berhasil diperbarui!');
    }

    /**
     * Hapus Butir Soal
     */
    public function destroySoal(Ujian $ujian, UjianSoal $soal)
    {
        $this->checkUjianAccess($ujian);

        foreach (['gambar', 'gambar_a', 'gambar_b', 'gambar_c', 'gambar_d'] as $imgField) {
            if ($soal->$imgField && Storage::disk('public')->exists($soal->$imgField)) {
                Storage::disk('public')->delete($soal->$imgField);
            }
        }

        $soal->delete();

        return back()->with('success', 'Butir soal berhasil dihapus.');
    }

    /**
     * Unduh Template Excel Soal
     */
    public function downloadTemplate(Ujian $ujian)
    {
        $cleanJudul = preg_replace('/[^A-Za-z0-9_\-]/', '_', $ujian->judul);
        $fileName = 'Template_Soal_' . $cleanJudul . '.xlsx';

        return Excel::download(new UjianSoalTemplateExport(), $fileName);
    }

    /**
     * Import Soal dari Excel
     */
    public function importSoal(Request $request, Ujian $ujian)
    {
        $this->checkUjianAccess($ujian);

        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file_excel.required' => 'Pilih file Excel yang akan diunggah.',
            'file_excel.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV.',
            'file_excel.max' => 'Ukuran file Excel maksimal 5MB.',
        ]);

        try {
            $import = new UjianSoalImport($ujian);
            Excel::import($import, $request->file('file_excel'));

            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();

            return back()->with('success', "Berhasil mengimpor {$imported} butir soal! ({$skipped} baris dilewati karena data tidak lengkap).");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Live Monitoring Pengawas Ujian
     */
    public function monitoring(Ujian $ujian)
    {
        $this->checkUjianAccess($ujian);

        // Ambil seluruh siswa pada kelas target
        $querySiswa = Siswa::query();
        if ($ujian->kelas !== 'Semua') {
            $querySiswa->whereIn('kelas', $ujian->daftar_kelas);
        }
        $siswas = $querySiswa->orderBy('kelas')->orderBy('nama')->get();

        // Ambil sesi ujian siswa yang sudah ada
        $sesiList = UjianSiswa::where('ujian_id', $ujian->id)
            ->with('siswa')
            ->get()
            ->keyBy('siswa_id');

        $totalSoal = $ujian->soals()->count();

        // Statistik
        $totalSiswa = count($siswas);
        $sudahSelesai = $sesiList->where('status', 'selesai')->count();
        $sedangMengerjakan = $sesiList->where('status', 'mengerjakan')->count();
        $belumMulai = max(0, $totalSiswa - ($sudahSelesai + $sedangMengerjakan));

        return view('ujian.monitor', compact(
            'ujian',
            'siswas',
            'sesiList',
            'totalSoal',
            'totalSiswa',
            'sudahSelesai',
            'sedangMengerjakan',
            'belumMulai'
        ));
    }

    /**
     * Reset Sesi Siswa (jika HP mati / koneksi putus dan ingin mengulang)
     */
    public function resetSesiSiswa(Ujian $ujian, Siswa $siswa)
    {
        $this->checkUjianAccess($ujian);

        $sesi = UjianSiswa::where('ujian_id', $ujian->id)->where('siswa_id', $siswa->id)->first();
        if ($sesi) {
            // Hapus jawaban & sesi
            $sesi->jawabans()->delete();
            $sesi->delete();
        }

        return back()->with('success', "Sesi pengerjaan siswa {$siswa->nama} berhasil direset.");
    }

    /**
     * Paksa Selesai Ujian Siswa (Force Submit)
     */
    public function forceSubmitSiswa(Ujian $ujian, Siswa $siswa)
    {
        $this->checkUjianAccess($ujian);

        $sesi = UjianSiswa::where('ujian_id', $ujian->id)->where('siswa_id', $siswa->id)->first();
        if ($sesi && $sesi->status !== 'selesai') {
            $sesi->hitungHasil();
        }

        return back()->with('success', "Ujian untuk siswa {$siswa->nama} berhasil diselesaikan dan dinilai.");
    }

    /**
     * Sinkronkan Skor Ujian ke Buku Nilai SI-KASEP (Tabel Kegiatans & Nilais)
     */
    public function sinkronNilai(Ujian $ujian)
    {
        $this->checkUjianAccess($ujian);

        // Ambil seluruh siswa yang sudah menyelesaikan ujian beserta relasi siswa
        $sesiSelesai = UjianSiswa::where('ujian_id', $ujian->id)
            ->where('status', 'selesai')
            ->with('siswa')
            ->get();

        if ($sesiSelesai->isEmpty()) {
            return back()->with('error', 'Belum ada siswa yang menyelesaikan ujian ini untuk disinkronkan.');
        }

        // Map jenis ujian ke enum jenis kegiatan (Tugas, UH, UTS, UAS)
        $jenisKegiatan = match($ujian->jenis) {
            'Ulangan Harian', 'UH' => 'UH',
            'Sumatif Tengah Semester', 'UTS' => 'UTS',
            'Sumatif Akhir Semester', 'UAS' => 'UAS',
            'Try Out' => 'UH',
            default => 'UH'
        };

        // Kelompokkan siswa berdasarkan kelas untuk sinkronisasi ke buku nilai per kelas
        $groupedByKelas = $sesiSelesai->groupBy(function ($sesi) {
            return $sesi->siswa->kelas ?? 'Umum';
        });

        $totalSynced = 0;
        $kegiatanNames = [];

        foreach ($groupedByKelas as $kls => $sesis) {
            // Cari atau buat Kegiatan untuk mapel & kelas ini
            $kegiatan = Kegiatan::firstOrCreate(
                [
                    'mata_pelajaran_id' => $ujian->mata_pelajaran_id,
                    'kelas' => $kls,
                    'nama_kegiatan' => 'CBT: ' . $ujian->judul,
                ],
                [
                    'jenis' => $jenisKegiatan,
                    'semester' => $ujian->semester,
                    'tahun_ajaran' => $ujian->tahun_ajaran ?: '2026/2027',
                    'tanggal' => now()->toDateString(),
                ]
            );

            // Simpan referensi kegiatan pertama jika ujian belum punya kegiatan_id
            if (!$ujian->kegiatan_id) {
                $ujian->update(['kegiatan_id' => $kegiatan->id]);
            }

            foreach ($sesis as $sesi) {
                Nilai::updateOrCreate(
                    [
                        'kegiatan_id' => $kegiatan->id,
                        'siswa_id' => $sesi->siswa_id,
                    ],
                    [
                        'nilai' => $sesi->nilai_akhir,
                    ]
                );
                $totalSynced++;
            }

            $kegiatanNames[] = $kls;
        }

        $daftarKelasTersinkron = implode(', ', $kegiatanNames);
        return back()->with('success', "Berhasil menyinkronkan {$totalSynced} nilai siswa ke modul Penilaian SI-KASEP untuk kelas: {$daftarKelasTersinkron}!");
    }
}

