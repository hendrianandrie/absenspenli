<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Perpus\LeaderboardController;
use App\Models\Absensi;
use App\Models\Kegiatan;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\PerpusBuku;
use App\Models\PerpusKategori;
use App\Models\PerpusPeminjaman;
use App\Models\PerpusPoinLog;
use App\Models\PerpusResume;
use App\Models\WaliKelas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SiswaPortalController extends Controller
{
    /**
     * Pastikan akun yang mengakses adalah Siswa yang valid
     */
    private function getSiswaOrAbort()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'siswa') {
            abort(403, 'Akses terbatas untuk akun Siswa.');
        }

        $siswa = $user->siswa;
        if (!$siswa) {
            $siswa = \App\Models\Siswa::where('nis', $user->username)->first();
            if ($siswa) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'siswa_id')) {
                    $user->update(['siswa_id' => $siswa->id]);
                }
            } else {
                abort(404, 'Data profil siswa tidak ditemukan atau belum terhubung.');
            }
        }

        return $siswa;
    }

    /**
     * Dashboard Siswa
     */
    public function dashboard()
    {
        $siswa = $this->getSiswaOrAbort();
        $now = Carbon::now();

        // Wali Kelas
        $waliKelas = WaliKelas::where('kelas', $siswa->kelas)->with('user')->first();

        // Kehadiran Bulan Berjalan
        $absensiBulanIni = Absensi::where('siswa_id', $siswa->id)
            ->whereYear('tanggal', $now->year)
            ->whereMonth('tanggal', $now->month)
            ->get();

        $hadir = $absensiBulanIni->where('status', 'Hadir')->count();
        $sakit = $absensiBulanIni->where('status', 'Sakit')->count();
        $izin = $absensiBulanIni->where('status', 'Izin')->count();
        $alpha = $absensiBulanIni->where('status', 'Alpha')->count();
        $totalTercatat = $hadir + $sakit + $izin + $alpha;
        $hadirPct = $totalTercatat > 0 ? round(($hadir / $totalTercatat) * 100, 1) : 100;

        $statsAbsen = [
            'bulan' => $now->translatedFormat('F Y'),
            'hadir' => $hadir,
            'sakit' => $sakit,
            'izin' => $izin,
            'alpha' => $alpha,
            'total' => $totalTercatat,
            'hadir_pct' => $hadirPct,
        ];

        // Kegiatan & Penilaian di Kelas Siswa
        $kegiatanKelas = Kegiatan::where('kelas', $siswa->kelas)
            ->with('mataPelajaran')
            ->orderBy('tanggal', 'desc')
            ->get();

        $kegiatanIds = $kegiatanKelas->pluck('id');
        $nilaiSiswaMap = Nilai::whereIn('kegiatan_id', $kegiatanIds)
            ->where('siswa_id', $siswa->id)
            ->get()
            ->keyBy('kegiatan_id');

        $totalKegiatan = $kegiatanKelas->count();
        $totalDinilai = $nilaiSiswaMap->count();

        // 5 Penilaian Terbaru yang Sudah Dinilai
        $nilaiTerbaru = [];
        foreach ($kegiatanKelas as $keg) {
            if ($nilaiSiswaMap->has($keg->id)) {
                $nilaiObj = $nilaiSiswaMap->get($keg->id);
                $kkm = $keg->mataPelajaran->kkm ?? 75;
                $score = $nilaiObj->nilai;
                $nilaiTerbaru[] = [
                    'mapel' => $keg->mataPelajaran->nama_mapel ?? '-',
                    'nama_kegiatan' => $keg->nama_kegiatan,
                    'jenis' => $keg->jenis,
                    'tanggal' => Carbon::parse($keg->tanggal)->translatedFormat('d M Y'),
                    'nilai' => $score,
                    'kkm' => $kkm,
                    'is_tuntas' => $score >= $kkm,
                ];
                if (count($nilaiTerbaru) >= 5) {
                    break;
                }
            }
        }

        return view('siswa_portal.dashboard', compact(
            'siswa',
            'waliKelas',
            'statsAbsen',
            'totalKegiatan',
            'totalDinilai',
            'nilaiTerbaru'
        ));
    }

    /**
     * Rekap Nilai Siswa (Seluruh Penilaian Riil per Mata Pelajaran)
     */
    public function rekapNilai(Request $request)
    {
        $siswa = $this->getSiswaOrAbort();
        $tingkat = MataPelajaran::getTingkatFromKelas($siswa->kelas);

        // Filter mapel yang relevan untuk tingkat kelas siswa ini
        $mapelQuery = MataPelajaran::query();
        if ($tingkat !== 'Semua') {
            $mapelQuery->where(function ($q) use ($tingkat) {
                $q->where('tingkat', $tingkat)
                  ->orWhere('tingkat', 'Semua')
                  ->orWhereNull('tingkat');
            });
        }
        $mapels = $mapelQuery->orderBy('nama_mapel')->get();

        $selectedMapelId = $request->get('mapel_id') ?: optional($mapels->first())->id;
        $selectedMapel = $mapels->firstWhere('id', $selectedMapelId) ?: $mapels->first();

        $semester = (int) $request->get('semester', Kegiatan::currentSemester());
        $tahunAjaran = $request->get('tahun_ajaran', Kegiatan::currentTahunAjaran());

        $kegiatanList = [];
        $statsMapel = [
            'total' => 0,
            'dinilai' => 0,
            'tuntas' => 0,
            'remedial' => 0,
            'avg' => null,
        ];

        if ($selectedMapel) {
            $kegiatans = Kegiatan::where('mata_pelajaran_id', $selectedMapel->id)
                ->where('kelas', $siswa->kelas)
                ->where('semester', $semester)
                ->where('tahun_ajaran', $tahunAjaran)
                ->orderBy('tanggal', 'asc')
                ->get();

            $scores = [];
            foreach ($kegiatans as $keg) {
                $nilaiObj = Nilai::where('kegiatan_id', $keg->id)
                    ->where('siswa_id', $siswa->id)
                    ->first();

                $score = $nilaiObj ? $nilaiObj->nilai : null;
                $kkm = $selectedMapel->kkm ?? 75;

                $status = 'Belum Dinilai';
                if ($score !== null) {
                    $scores[] = $score;
                    $status = $score >= $kkm ? 'Tuntas' : 'Perlu Remedial';
                }

                $kegiatanList[] = [
                    'id' => $keg->id,
                    'tanggal' => Carbon::parse($keg->tanggal)->translatedFormat('d F Y'),
                    'nama_kegiatan' => $keg->nama_kegiatan,
                    'jenis' => $keg->jenis,
                    'nilai' => $score,
                    'kkm' => $kkm,
                    'status' => $status,
                ];
            }

            $tuntasCount = count(array_filter($kegiatanList, fn ($k) => $k['status'] === 'Tuntas'));
            $remedialCount = count(array_filter($kegiatanList, fn ($k) => $k['status'] === 'Perlu Remedial'));

            $statsMapel = [
                'total' => count($kegiatanList),
                'dinilai' => count($scores),
                'tuntas' => $tuntasCount,
                'remedial' => $remedialCount,
                'avg' => (count($scores) > 0 && count($kegiatanList) > 0) ? round(array_sum($scores) / count($kegiatanList), 1) : null,
            ];
        }

        return view('siswa_portal.nilai', compact(
            'siswa',
            'mapels',
            'selectedMapel',
            'kegiatanList',
            'statsMapel',
            'tingkat',
            'semester',
            'tahunAjaran'
        ));
    }

    /**
     * Rekap Absen Siswa (Bulanan dengan Pilihan Bulan & Tahun)
     */
    public function rekapAbsen(Request $request)
    {
        $siswa = $this->getSiswaOrAbort();
        $now = Carbon::now();

        $bulan = (int) $request->get('bulan', $now->month);
        $tahun = (int) $request->get('tahun', $now->year);

        // Ambil riwayat presensi di bulan & tahun terpilih
        $absensis = Absensi::where('siswa_id', $siswa->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal', 'asc')
            ->get();

        $hadir = $absensis->where('status', 'Hadir')->count();
        $sakit = $absensis->where('status', 'Sakit')->count();
        $izin = $absensis->where('status', 'Izin')->count();
        $alpha = $absensis->where('status', 'Alpha')->count();
        $total = $hadir + $sakit + $izin + $alpha;
        $persenHadir = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;

        $statsBulan = [
            'hadir' => $hadir,
            'sakit' => $sakit,
            'izin' => $izin,
            'alpha' => $alpha,
            'total' => $total,
            'persen' => $persenHadir,
        ];

        // Daftar nama bulan
        $daftarBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Daftar tahun (3 tahun terakhir hingga tahun depan)
        $daftarTahun = range($now->year - 2, $now->year + 1);

        return view('siswa_portal.absen', compact(
            'siswa',
            'bulan',
            'tahun',
            'absensis',
            'statsBulan',
            'daftarBulan',
            'daftarTahun'
        ));
    }

    /**
     * Katalog Buku Perpustakaan untuk Siswa
     */
    public function perpusKatalog(Request $request)
    {
        $siswa = $this->getSiswaOrAbort();
        $search = trim($request->get('q', ''));
        $kategoriId = $request->get('kategori_id');

        $query = PerpusBuku::with('kategori');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%")
                  ->orWhere('kode_buku', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($kategoriId) {
            $query->where('kategori_id', $kategoriId);
        }

        $bukus = $query->orderBy('judul')->paginate(12)->withQueryString();
        $kategoris = PerpusKategori::withCount('bukus')->orderBy('nama_kategori')->get();

        // Rekap Poin Siswa & Badge
        $totalPoin = (int) PerpusPoinLog::where('siswa_id', $siswa->id)->sum('poin');
        $badge = LeaderboardController::getBadgeByPoin($totalPoin);

        return view('siswa_portal.perpus_katalog', compact('siswa', 'bukus', 'kategoris', 'search', 'kategoriId', 'totalPoin', 'badge'));
    }

    /**
     * Riwayat Pinjaman & Pengiriman Resume Buku Siswa
     */
    public function perpusPinjaman(Request $request)
    {
        $siswa = $this->getSiswaOrAbort();

        $activeLoans = PerpusPeminjaman::where('siswa_id', $siswa->id)
            ->where('status', 'dipinjam')
            ->with(['buku', 'resume'])
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->get();

        $pastLoans = PerpusPeminjaman::where('siswa_id', $siswa->id)
            ->where('status', '!=', 'dipinjam')
            ->with(['buku', 'resume'])
            ->orderBy('tanggal_kembali', 'desc')
            ->paginate(10);

        $totalPoin = (int) PerpusPoinLog::where('siswa_id', $siswa->id)->sum('poin');
        $badge = LeaderboardController::getBadgeByPoin($totalPoin);
        $totalBukuDibaca = PerpusPeminjaman::where('siswa_id', $siswa->id)->where('status', 'kembali')->count();
        $totalResumeDisetujui = PerpusResume::where('siswa_id', $siswa->id)->where('status', 'disetujui')->count();

        // Riwayat Poin Log Terbaru
        $poinLogs = PerpusPoinLog::where('siswa_id', $siswa->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('siswa_portal.perpus_pinjaman', compact(
            'siswa',
            'activeLoans',
            'pastLoans',
            'totalPoin',
            'badge',
            'totalBukuDibaca',
            'totalResumeDisetujui',
            'poinLogs'
        ));
    }

    /**
     * Siswa Mengirimkan Ulasan / Resume Buku
     */
    public function perpusStoreResume(Request $request)
    {
        $siswa = $this->getSiswaOrAbort();

        $request->validate([
            'peminjaman_id' => 'required|exists:perpus_peminjamans,id',
            'rating' => 'required|integer|min:1|max:5',
            'ringkasan' => 'required|string|min:30',
            'pesan_moral' => 'nullable|string|max:255',
        ], [
            'ringkasan.min' => 'Rangkuman ulasan buku minimal 30 karakter agar dapat dinilai oleh petugas perpus.',
        ]);

        $peminjaman = PerpusPeminjaman::where('id', $request->peminjaman_id)
            ->where('siswa_id', $siswa->id)
            ->with('buku')
            ->firstOrFail();

        // Cek apakah sudah pernah submit resume
        $existing = PerpusResume::where('peminjaman_id', $peminjaman->id)->first();
        if ($existing && $existing->status === 'disetujui') {
            return back()->with('info', 'Ulasan untuk buku ini sudah disetujui sebelumnya.');
        }

        PerpusResume::updateOrCreate(
            ['peminjaman_id' => $peminjaman->id],
            [
                'siswa_id' => $siswa->id,
                'buku_id' => $peminjaman->buku_id,
                'judul_buku' => $peminjaman->buku->judul,
                'rating' => (int) $request->rating,
                'ringkasan' => trim($request->ringkasan),
                'pesan_moral' => $request->pesan_moral ? trim($request->pesan_moral) : null,
                'status' => 'menunggu',
                'catatan_petugas' => null,
            ]
        );

        return back()->with('success', 'Ulasan buku berhasil dikirimkan! Petugas perpustakaan akan segera memverifikasi dan memberikan poin literasi untukmu.');
    }

    /**
     * Papan Peringkat Literasi dari Sudut Pandang Siswa
     */
    public function perpusLeaderboard(Request $request)
    {
        $siswa = $this->getSiswaOrAbort();
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');

        $selectedBulan = $request->get('bulan', $currentMonth);
        $selectedTahun = (int) $request->get('tahun', $currentYear);
        $filterMode = $request->get('mode', 'bulanan');

        $daftarBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        // Poin Saya & Ranking Saya
        $myPoinQuery = PerpusPoinLog::where('siswa_id', $siswa->id);
        if ($filterMode === 'bulanan' && $selectedBulan !== 'all') {
            $myPoinQuery->where('bulan', $selectedBulan)->where('tahun', $selectedTahun);
        }
        $myPoin = (int) $myPoinQuery->sum('poin');
        $myBadge = LeaderboardController::getBadgeByPoin((int) PerpusPoinLog::where('siswa_id', $siswa->id)->sum('poin'));

        // Hitung Ranking Siswa Ini di Sekolah
        $allRankQuery = PerpusPoinLog::select('siswa_id', DB::raw('SUM(poin) as total_pts'))
            ->groupBy('siswa_id');
        if ($filterMode === 'bulanan' && $selectedBulan !== 'all') {
            $allRankQuery->where('bulan', $selectedBulan)->where('tahun', $selectedTahun);
        }
        $allRankings = $allRankQuery->orderBy('total_pts', 'desc')->get();
        $myRank = $allRankings->search(fn($r) => $r->siswa_id == $siswa->id);
        $myRankPosition = $myRank !== false ? $myRank + 1 : '-';

        // Top 10 Siswa
        $topSiswaLogs = $allRankings->take(10);
        $topSiswaIds = $topSiswaLogs->pluck('siswa_id');
        $siswasMap = \App\Models\Siswa::whereIn('id', $topSiswaIds)->get()->keyBy('id');

        $topSiswaList = [];
        foreach ($topSiswaLogs as $idx => $ts) {
            $s = $siswasMap->get($ts->siswa_id);
            if (!$s) continue;
            $topSiswaList[] = [
                'rank' => $idx + 1,
                'is_me' => ($s->id == $siswa->id),
                'nama' => $s->nama,
                'kelas' => $s->kelas,
                'poin' => (int) $ts->total_pts,
                'badge' => LeaderboardController::getBadgeByPoin((int) $ts->total_pts),
            ];
        }

        // Leaderboard Kelas
        $allSiswasGrouped = \App\Models\Siswa::all()->groupBy('kelas');
        $kelasRankings = [];
        foreach ($allSiswasGrouped as $kName => $classStudents) {
            $cCount = $classStudents->count();
            if ($cCount === 0) continue;

            $cLog = PerpusPoinLog::whereIn('siswa_id', $classStudents->pluck('id'));
            if ($filterMode === 'bulanan' && $selectedBulan !== 'all') {
                $cLog->where('bulan', $selectedBulan)->where('tahun', $selectedTahun);
            }
            $cSum = (int) $cLog->sum('poin');
            $avgPoin = $cCount > 0 ? round($cSum / $cCount, 1) : 0;

            $kelasRankings[] = [
                'kelas' => $kName,
                'is_my_class' => ($kName === $siswa->kelas),
                'total_siswa' => $cCount,
                'total_poin' => $cSum,
                'indeks_rata_rata' => $avgPoin,
            ];
        }

        usort($kelasRankings, fn($a, $b) => $b['indeks_rata_rata'] <=> $a['indeks_rata_rata']);
        foreach ($kelasRankings as $idx => &$kr) {
            $kr['rank'] = $idx + 1;
        }

        return view('siswa_portal.perpus_leaderboard', compact(
            'siswa',
            'myPoin',
            'myBadge',
            'myRankPosition',
            'topSiswaList',
            'kelasRankings',
            'selectedBulan',
            'selectedTahun',
            'filterMode',
            'daftarBulan'
        ));
    }
}
