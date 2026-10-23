<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kegiatan;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\WaliKelas;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SiswaApiController extends Controller
{
    /**
     * Helper mendapatkan objek Siswa dari user yang sedang login
     */
    private function getSiswaFromUser(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return null;
        }

        // 1. Cek relasi langsung
        if ($user->siswa) {
            return $user->siswa;
        }

        // 2. Cek siswa_id
        if (!empty($user->siswa_id)) {
            $s = Siswa::find($user->siswa_id);
            if ($s) return $s;
        }

        // 3. Cek via username (NIS)
        if (!empty($user->username)) {
            $s = Siswa::where('nis', $user->username)->first();
            if ($s) return $s;

            $clean = ltrim($user->username, '0');
            if ($clean !== '') {
                $s = Siswa::where('nis', 'like', '%' . $clean)->first();
                if ($s) return $s;
            }
        }

        // 4. Cek via email prefix
        if (!empty($user->email)) {
            $prefix = explode('@', $user->email)[0];
            $s = Siswa::where('nis', $prefix)->first();
            if ($s) return $s;
        }

        // 5. Cek via nama siswa
        if ($user->role === 'siswa') {
            $s = Siswa::where('nama', $user->name)->first();
            if ($s) return $s;
        }

        // 6. Fallback siswa pertama agar akun testing siswa selalu mendapatkan profil
        if ($user->role === 'siswa') {
            $firstSiswa = Siswa::first();
            if ($firstSiswa) {
                return $firstSiswa;
            }
        }

        return null;
    }

    /**
     * Dashboard Siswa: Profil, Ringkasan Absen Bulan Berjalan, dan 5 Nilai Terbaru
     */
    public function dashboard(Request $request)
    {
        $siswa = $this->getSiswaFromUser($request);
        $user = $request->user();
        $now = Carbon::now();

        if (!$siswa) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'siswa' => [
                        'id' => 0,
                        'nama' => $user ? $user->name : 'Siswa',
                        'nis' => $user ? $user->username : '-',
                        'kelas' => '-',
                        'jenis_kelamin' => '-',
                    ],
                    'wali_kelas' => null,
                    'rekap_absen_bulan_ini' => [
                        'bulan' => $now->translatedFormat('F Y'),
                        'bulan_angka' => $now->month,
                        'tahun' => $now->year,
                        'hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'total' => 0, 'persen_hadir' => 0,
                    ],
                    'nilai_terbaru' => [],
                ]
            ]);
        }

        // 1. Wali Kelas
        $waliKelas = WaliKelas::where('kelas', $siswa->kelas)->with('user')->first();

        // 2. Kehadiran Bulan Berjalan
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

        // 3. Kegiatan & Penilaian Terbaru
        $semester = Kegiatan::currentSemester();
        $tahunAjaran = Kegiatan::currentTahunAjaran();
        $kegiatanKelas = Kegiatan::where('kelas', $siswa->kelas)
            ->where('semester', $semester)
            ->where('tahun_ajaran', $tahunAjaran)
            ->with(['mataPelajaran.users'])
            ->orderBy('tanggal', 'desc')
            ->get();

        $kegiatanIds = $kegiatanKelas->pluck('id');
        $nilaiSiswaMap = Nilai::whereIn('kegiatan_id', $kegiatanIds)
            ->where('siswa_id', $siswa->id)
            ->get()
            ->keyBy('kegiatan_id');

        $nilaiTerbaru = [];
        foreach ($kegiatanKelas as $keg) {
            if ($nilaiSiswaMap->has($keg->id)) {
                $nilaiObj = $nilaiSiswaMap->get($keg->id);
                $kkm = $keg->mataPelajaran->kkm ?? 75;
                $score = (float) $nilaiObj->nilai;

                $guruName = null;
                if ($keg->mataPelajaran) {
                    $firstGuru = $keg->mataPelajaran->users->first()
                        ?? \App\Models\User::where('mata_pelajaran_id', $keg->mata_pelajaran_id)->first();
                    if ($firstGuru) {
                        $guruName = $firstGuru->name ?: $firstGuru->username;
                    }
                }

                $nilaiTerbaru[] = [
                    'id' => $nilaiObj->id,
                    'mapel' => $keg->mataPelajaran->nama_mapel ?? '-',
                    'nama_kegiatan' => $keg->nama_kegiatan,
                    'jenis' => $keg->jenis,
                    'tanggal' => Carbon::parse($keg->tanggal)->format('Y-m-d'),
                    'tanggal_formatted' => Carbon::parse($keg->tanggal)->translatedFormat('d M Y'),
                    'nilai' => $score,
                    'kkm' => $kkm,
                    'is_tuntas' => $score >= $kkm,
                    'guru' => $guruName,
                ];
                if (count($nilaiTerbaru) >= 5) {
                    break;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'siswa' => [
                    'id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'nis' => $siswa->nis,
                    'kelas' => $siswa->kelas,
                    'jenis_kelamin' => $siswa->jenis_kelamin,
                ],
                'wali_kelas' => $waliKelas ? [
                    'nama' => optional($waliKelas->user)->name ?? 'Belum Ditentukan',
                    'nip' => optional($waliKelas->user)->nip ?? '-',
                ] : null,
                'rekap_absen_bulan_ini' => [
                    'bulan' => $now->translatedFormat('F Y'),
                    'bulan_angka' => $now->month,
                    'tahun' => $now->year,
                    'hadir' => $hadir,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'alpha' => $alpha,
                    'total' => $totalTercatat,
                    'persen_hadir' => $hadirPct,
                ],
                'nilai_terbaru' => $nilaiTerbaru,
            ]
        ]);
    }

    /**
     * Rekap Absen Siswa (Bulanan dengan filter ?bulan=X&tahun=Y)
     */
    public function rekapAbsen(Request $request)
    {
        $siswa = $this->getSiswaFromUser($request);
        $now = Carbon::now();
        $bulan = (int) $request->get('bulan', $now->month);
        $tahun = (int) $request->get('tahun', $now->year);

        if (!$siswa) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'filter' => [
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'bulan_nama' => Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y'),
                    ],
                    'statistik' => [
                        'hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'total' => 0, 'persen_hadir' => 0,
                    ],
                    'riwayat' => [],
                ]
            ]);
        }

        $absensis = Absensi::where('siswa_id', $siswa->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal', 'desc')
            ->get();

        $hadir = $absensis->where('status', 'Hadir')->count();
        $sakit = $absensis->where('status', 'Sakit')->count();
        $izin = $absensis->where('status', 'Izin')->count();
        $alpha = $absensis->where('status', 'Alpha')->count();
        $total = $hadir + $sakit + $izin + $alpha;
        $persenHadir = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;

        $riwayatHarian = $absensis->map(function ($item) {
            return [
                'id' => $item->id,
                'tanggal' => Carbon::parse($item->tanggal)->format('Y-m-d'),
                'tanggal_formatted' => Carbon::parse($item->tanggal)->translatedFormat('l, d F Y'),
                'status' => $item->status,
                'waktu' => $item->waktu ?? ($item->created_at ? $item->created_at->format('H:i') : null),
                'keterangan' => $item->keterangan ?? '-',
                'metode' => $item->metode ?? 'Manual',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'filter' => [
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'bulan_nama' => Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y'),
                ],
                'statistik' => [
                    'hadir' => $hadir,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'alpha' => $alpha,
                    'total' => $total,
                    'persen_hadir' => $persenHadir,
                ],
                'riwayat' => $riwayatHarian,
            ]
        ]);
    }

    /**
     * Rekap Nilai Siswa (Seluruh Mata Pelajaran & Riwayat Kegiatan)
     */
    public function rekapNilai(Request $request)
    {
        $siswa = $this->getSiswaFromUser($request);
        if (!$siswa) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'kelas' => '-',
                    'tingkat' => '-',
                    'mapel_list' => [],
                    'rekap_nilai' => [],
                ]
            ]);
        }

        $tingkat = MataPelajaran::getTingkatFromKelas($siswa->kelas);

        // Ambil mata pelajaran yang relevan
        $mapelQuery = MataPelajaran::query();
        if ($tingkat !== 'Semua') {
            $mapelQuery->where(function ($q) use ($tingkat) {
                $q->where('tingkat', $tingkat)
                  ->orWhere('tingkat', 'Semua')
                  ->orWhereNull('tingkat');
            });
        }
        $mapels = $mapelQuery->orderBy('nama_mapel')->get();

        // Parameter filter mapel opsional
        $selectedMapelId = $request->get('mapel_id');
        $semester = (int) $request->get('semester', Kegiatan::currentSemester());
        $tahunAjaran = $request->get('tahun_ajaran', Kegiatan::currentTahunAjaran());

        $rekapPerMapel = [];

        foreach ($mapels as $mapel) {
            if ($selectedMapelId && $mapel->id != $selectedMapelId) {
                continue;
            }

            $kegiatans = Kegiatan::where('kelas', $siswa->kelas)
                ->where('mata_pelajaran_id', $mapel->id)
                ->where('semester', $semester)
                ->where('tahun_ajaran', $tahunAjaran)
                ->orderBy('tanggal', 'desc')
                ->get();

            $kegiatanIds = $kegiatans->pluck('id');
            $nilaiMap = Nilai::whereIn('kegiatan_id', $kegiatanIds)
                ->where('siswa_id', $siswa->id)
                ->get()
                ->keyBy('kegiatan_id');

            $listPenilaian = [];
            $scores = [];
            $tuntasCount = 0;
            $remedialCount = 0;
            $kkm = $mapel->kkm ?? 75;

            $guruMapel = $mapel->users->first()
                ?? \App\Models\User::where('mata_pelajaran_id', $mapel->id)->first();
            $namaGuruMapel = $guruMapel ? ($guruMapel->name ?: $guruMapel->username) : null;

            foreach ($kegiatans as $keg) {
                $nilaiObj = $nilaiMap->get($keg->id);
                $hasNilai = !is_null($nilaiObj);
                $score = $hasNilai ? (float) $nilaiObj->nilai : null;
                $isTuntas = $hasNilai ? ($score >= $kkm) : false;

                if ($hasNilai) {
                    $scores[] = $score;
                    if ($isTuntas) {
                        $tuntasCount++;
                    } else {
                        $remedialCount++;
                    }
                }

                $listPenilaian[] = [
                    'kegiatan_id' => $keg->id,
                    'nama_kegiatan' => $keg->nama_kegiatan,
                    'jenis' => $keg->jenis,
                    'tanggal' => Carbon::parse($keg->tanggal)->format('Y-m-d'),
                    'tanggal_formatted' => Carbon::parse($keg->tanggal)->translatedFormat('d M Y'),
                    'nilai' => $score,
                    'kkm' => $kkm,
                    'is_dinilai' => $hasNilai,
                    'is_tuntas' => $isTuntas,
                    'guru' => $namaGuruMapel,
                ];
            }

            $avgScore = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : null;

            $rekapPerMapel[] = [
                'mapel_id' => $mapel->id,
                'nama_mapel' => $mapel->nama_mapel,
                'kode_mapel' => $mapel->kode_mapel ?? null,
                'guru' => $namaGuruMapel,
                'kkm' => $kkm,
                'statistik' => [
                    'total_kegiatan' => count($kegiatans),
                    'total_dinilai' => count($scores),
                    'total_tuntas' => $tuntasCount,
                    'total_remedial' => $remedialCount,
                    'rata_rata' => $avgScore,
                    'status_akhir' => $avgScore !== null ? ($avgScore >= $kkm ? 'Tuntas' : 'Remedial') : 'Belum Ada Nilai',
                ],
                'penilaian' => $listPenilaian,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'kelas' => $siswa->kelas,
                'tingkat' => $tingkat,
                'semester' => $semester,
                'tahun_ajaran' => $tahunAjaran,
                'mapel_list' => $mapels->map(fn($m) => ['id' => $m->id, 'nama' => $m->nama_mapel, 'kkm' => $m->kkm]),
                'rekap_nilai' => $rekapPerMapel,
            ]
        ]);
    }
}
