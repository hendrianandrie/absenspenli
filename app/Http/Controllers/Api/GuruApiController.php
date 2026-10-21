<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GuruApiController extends Controller
{
    /**
     * Dashboard Guru: Ringkasan Mapel & Kelas yang diampu
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['guru', 'walikelas', 'admin'])) {
            return response()->json(['status' => 'error', 'message' => 'Akses terbatas untuk akun Guru/Admin.'], 403);
        }

        $assignedIds = $user->assigned_mapel_ids;
        $mapels = !empty($assignedIds)
            ? MataPelajaran::whereIn('id', $assignedIds)->orderBy('nama_mapel')->get()
            : MataPelajaran::orderBy('nama_mapel')->get();

        $kelasDiampu = !empty($user->kelas_diampu) && is_array($user->kelas_diampu)
            ? $user->kelas_diampu
            : Siswa::select('kelas')->distinct()->pluck('kelas')->toArray();

        // 5 Kegiatan penilaian terbaru yang dibuat
        $kegiatanTerbaru = Kegiatan::whereIn('mata_pelajaran_id', $mapels->pluck('id'))
            ->with('mataPelajaran')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($keg) {
                return [
                    'id' => $keg->id,
                    'nama_kegiatan' => $keg->nama_kegiatan,
                    'jenis' => $keg->jenis,
                    'mapel' => optional($keg->mataPelajaran)->nama_mapel,
                    'kelas' => $keg->kelas,
                    'tanggal' => Carbon::parse($keg->tanggal)->translatedFormat('d M Y'),
                    'total_siswa_dinilai' => Nilai::where('kegiatan_id', $keg->id)->count(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'guru' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nip' => $user->nip,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'mapel_diampu' => $mapels->map(fn($m) => [
                    'id' => $m->id,
                    'nama_mapel' => $m->nama_mapel,
                    'kode_mapel' => $m->kode_mapel,
                    'kkm' => $m->kkm,
                    'tingkat' => $m->tingkat,
                ]),
                'kelas_diampu' => $kelasDiampu,
                'kegiatan_terbaru' => $kegiatanTerbaru,
            ]
        ]);
    }

    /**
     * Daftar Kegiatan Penilaian per Mapel & Kelas
     */
    public function daftarKegiatan(Request $request)
    {
        $request->validate([
            'mata_pelajaran_id' => 'required|integer',
            'kelas' => 'required|string',
        ]);

        $mapel = MataPelajaran::find($request->mata_pelajaran_id);
        if (!$mapel) {
            return response()->json(['status' => 'error', 'message' => 'Mata pelajaran tidak ditemukan.'], 404);
        }

        $semester = (int) $request->get('semester', Kegiatan::currentSemester());
        $tahunAjaran = $request->get('tahun_ajaran', Kegiatan::currentTahunAjaran());

        $kegiatans = Kegiatan::where('mata_pelajaran_id', $mapel->id)
            ->where('kelas', $request->kelas)
            ->where('semester', $semester)
            ->where('tahun_ajaran', $tahunAjaran)
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalSiswaKelas = Siswa::where('kelas', $request->kelas)->count();

        $list = $kegiatans->map(function ($keg) use ($totalSiswaKelas, $mapel) {
            $dinilai = Nilai::where('kegiatan_id', $keg->id)->count();
            return [
                'id' => $keg->id,
                'nama_kegiatan' => $keg->nama_kegiatan,
                'jenis' => $keg->jenis,
                'semester' => $keg->semester,
                'tahun_ajaran' => $keg->tahun_ajaran,
                'tanggal' => Carbon::parse($keg->tanggal)->format('Y-m-d'),
                'tanggal_formatted' => Carbon::parse($keg->tanggal)->translatedFormat('d M Y'),
                'kkm' => $mapel->kkm ?? 75,
                'total_siswa' => $totalSiswaKelas,
                'total_dinilai' => $dinilai,
                'is_lengkap' => $dinilai >= $totalSiswaKelas,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'mapel' => [
                    'id' => $mapel->id,
                    'nama_mapel' => $mapel->nama_mapel,
                    'kkm' => $mapel->kkm,
                ],
                'kelas' => $request->kelas,
                'semester' => $semester,
                'tahun_ajaran' => $tahunAjaran,
                'kegiatan' => $list,
            ]
        ]);
    }

    /**
     * Input & Simpan Nilai Siswa (Bulk Update dari HP)
     */
    public function simpanNilai(Request $request)
    {
        $request->validate([
            'kegiatan_id' => 'required|exists:kegiatans,id',
            'nilai_data' => 'required|array',
            'nilai_data.*.siswa_id' => 'required|exists:siswas,id',
            'nilai_data.*.nilai' => 'nullable|numeric|min:0|max:100',
        ]);

        $kegiatan = Kegiatan::findOrFail($request->kegiatan_id);

        foreach ($request->nilai_data as $item) {
            $siswaId = $item['siswa_id'];
            $score = $item['nilai'];

            if ($score === null || $score === '') {
                Nilai::where('kegiatan_id', $kegiatan->id)
                    ->where('siswa_id', $siswaId)
                    ->delete();
            } else {
                Nilai::updateOrCreate(
                    [
                        'kegiatan_id' => $kegiatan->id,
                        'siswa_id' => $siswaId,
                    ],
                    [
                        'nilai' => $score,
                    ]
                );
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Nilai berhasil disimpan.',
        ]);
    }
}
