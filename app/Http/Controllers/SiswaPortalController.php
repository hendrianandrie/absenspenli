<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Kegiatan;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\WaliKelas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            abort(404, 'Data profil siswa tidak ditemukan atau belum terhubung.');
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
                'avg' => count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : null,
            ];
        }

        return view('siswa_portal.nilai', compact(
            'siswa',
            'mapels',
            'selectedMapel',
            'kegiatanList',
            'statsMapel',
            'tingkat'
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
}
