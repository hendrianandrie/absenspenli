<?php

namespace App\Http\Controllers\Perpus;

use App\Http\Controllers\Controller;
use App\Models\PerpusPoinLog;
use App\Models\PerpusResume;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResumeController extends Controller
{
    /**
     * Tampilkan antrean ulasan / resume buku siswa untuk diverifikasi
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'menunggu');
        $search = trim($request->get('q', ''));
        $kelas = $request->get('kelas');

        $query = PerpusResume::with(['siswa', 'buku', 'verifikator', 'peminjaman']);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('judul_buku', 'like', "%{$search}%")
                  ->orWhere('ringkasan', 'like', "%{$search}%")
                  ->orWhereHas('siswa', function ($sq) use ($search) {
                      $sq->where('nama', 'like', "%{$search}%")
                         ->orWhere('nis', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($kelas)) {
            $query->whereHas('siswa', function ($sq) use ($kelas) {
                $sq->where('kelas', $kelas);
            });
        }

        $resumes = $query->orderByRaw("CASE WHEN status = 'menunggu' THEN 1 WHEN status = 'disetujui' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        $stats = [
            'total_menunggu' => PerpusResume::where('status', 'menunggu')->count(),
            'total_disetujui' => PerpusResume::where('status', 'disetujui')->count(),
            'total_ditolak' => PerpusResume::where('status', 'ditolak')->count(),
            'total_all' => PerpusResume::count(),
        ];

        return view('perpus.resume.index', compact('resumes', 'daftarKelas', 'status', 'search', 'kelas', 'stats'));
    }

    /**
     * Setujui ulasan / resume dan berikan poin literasi
     */
    public function approve(Request $request, PerpusResume $resume)
    {
        $request->validate([
            'poin' => 'nullable|integer|min:5|max:50',
            'catatan' => 'nullable|string|max:255',
        ]);

        if ($resume->status === 'disetujui') {
            return back()->with('info', 'Resume ini sudah disetujui sebelumnya.');
        }

        $poinDiberikan = (int) ($request->poin ?? 15);

        DB::transaction(function () use ($resume, $poinDiberikan, $request) {
            $resume->update([
                'status' => 'disetujui',
                'poin_diberikan' => $poinDiberikan,
                'catatan_petugas' => $request->catatan ?: 'Ulasan buku disetujui. Sangat baik!',
                'diverifikasi_oleh' => auth()->id(),
                'tanggal_verifikasi' => Carbon::now(),
            ]);

            // Catat log poin literasi siswa
            PerpusPoinLog::catatPoin(
                $resume->siswa_id,
                'resume_disetujui',
                $poinDiberikan,
                $resume->id,
                'Resume disetujui: ' . $resume->judul_buku
            );
        });

        return back()->with('success', "Resume siswa {$resume->siswa->nama} berhasil disetujui! Siswa mendapatkan +{$poinDiberikan} Poin Literasi.");
    }

    /**
     * Tolak ulasan / resume dengan catatan perbaikan
     */
    public function reject(Request $request, PerpusResume $resume)
    {
        $request->validate([
            'catatan' => 'required|string|max:255',
        ]);

        $resume->update([
            'status' => 'ditolak',
            'poin_diberikan' => 0,
            'catatan_petugas' => $request->catatan,
            'diverifikasi_oleh' => auth()->id(),
            'tanggal_verifikasi' => Carbon::now(),
        ]);

        return back()->with('warning', "Resume siswa {$resume->siswa->nama} dikembalikan dengan catatan perbaikan.");
    }
}
