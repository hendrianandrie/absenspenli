<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Ujian;
use App\Models\UjianJawaban;
use App\Models\UjianSiswa;
use App\Models\UjianSoal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UjianSiswaController extends Controller
{
    /**
     * Helper mendapatkan objek Siswa dari akun yang login
     */
    private function getSiswa(): ?Siswa
    {
        $user = Auth::user();
        if (!$user) return null;

        if ($user->siswa) return $user->siswa;
        if (!empty($user->siswa_id)) return Siswa::find($user->siswa_id);
        if (!empty($user->username)) {
            $s = Siswa::where('nis', $user->username)->first();
            if ($s) return $s;
        }

        return Siswa::where('nama', $user->name)->first();
    }

    /**
     * Daftar Asesmen CBT untuk Siswa
     */
    public function index()
    {
        $siswa = $this->getSiswa();
        if (!$siswa) {
            abort(403, 'Data siswa Anda tidak terhubung dengan profil kelas yang valid.');
        }

        // Ambil ujian aktif untuk kelas siswa atau untuk Semua
        $ujians = Ujian::where('status', 'aktif')
            ->where(function ($q) use ($siswa) {
                $q->where('kelas', 'Semua')
                  ->orWhere('kelas', $siswa->kelas)
                  ->orWhere('kelas', 'LIKE', '%' . $siswa->kelas . '%');
            })
            ->with(['mataPelajaran', 'user'])
            ->withCount('soals')
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($u) use ($siswa) {
                return $u->matchesKelas($siswa->kelas);
            })
            ->values();

        // Ambil riwayat sesi pengerjaan siswa ini
        $sesiMap = UjianSiswa::where('siswa_id', $siswa->id)
            ->whereIn('ujian_id', $ujians->pluck('id'))
            ->get()
            ->keyBy('ujian_id');

        return view('siswa.ujian.index', compact('siswa', 'ujians', 'sesiMap'));
    }

    /**
     * Layar Konfirmasi & Input Token Sebelum Masuk Ujian
     */
    public function konfirmasi(Ujian $ujian)
    {
        $siswa = $this->getSiswa();
        if (!$siswa) {
            abort(403, 'Akses tidak sah.');
        }

        if (!$ujian->matchesKelas($siswa->kelas)) {
            abort(403, 'Ujian ini tidak dialokasikan untuk kelas Anda.');
        }

        if (!$ujian->isAktif()) {
            return redirect()->route('siswa.ujian.index')->with('error', 'Ujian ini sedang tidak aktif.');
        }

        $sesi = UjianSiswa::where('ujian_id', $ujian->id)->where('siswa_id', $siswa->id)->first();
        if ($sesi && $sesi->status === 'selesai') {
            return redirect()->route('siswa.ujian.hasil', $ujian->id);
        }

        $totalSoal = $ujian->soals()->count();

        return view('siswa.ujian.konfirmasi', compact('siswa', 'ujian', 'sesi', 'totalSoal'));
    }

    /**
     * Mulai / Buka Lembar Ujian
     */
    public function mulai(Request $request, Ujian $ujian)
    {
        $siswa = $this->getSiswa();
        if (!$siswa) {
            abort(403, 'Akses tidak sah.');
        }

        if (!$ujian->matchesKelas($siswa->kelas)) {
            abort(403, 'Ujian ini tidak dialokasikan untuk kelas Anda.');
        }

        if (!$ujian->isAktif()) {
            return redirect()->route('siswa.ujian.index')->with('error', 'Ujian ini sedang tidak aktif.');
        }

        // Cek Token jika ujian memiliki token
        if (!empty($ujian->token)) {
            $inputToken = strtoupper(trim((string) $request->input('token')));
            if ($inputToken !== strtoupper($ujian->token)) {
                return back()->with('error', 'Kode Token Ujian salah! Silakan tanyakan kepada pengawas.');
            }
        }

        $sesi = UjianSiswa::where('ujian_id', $ujian->id)->where('siswa_id', $siswa->id)->first();

        if ($sesi && $sesi->status === 'selesai') {
            return redirect()->route('siswa.ujian.hasil', $ujian->id);
        }

        if (!$sesi) {
            // Siapkan urutan soal
            $soalIds = $ujian->soals()->pluck('id')->toArray();
            if ($ujian->acak_soal) {
                shuffle($soalIds);
            }

            $durasiDetik = $ujian->durasi_menit * 60;

            $sesi = UjianSiswa::create([
                'ujian_id' => $ujian->id,
                'siswa_id' => $siswa->id,
                'waktu_mulai' => now(),
                'sisa_waktu_detik' => $durasiDetik,
                'status' => 'mengerjakan',
                'urutan_soal' => $soalIds,
            ]);
        }

        return redirect()->route('siswa.ujian.kerjakan', $ujian->id);
    }

    /**
     * Lembar Pengerjaan Soal CBT Siswa
     */
    public function kerjakan(Ujian $ujian)
    {
        $siswa = $this->getSiswa();
        if (!$siswa) {
            abort(403, 'Akses tidak sah.');
        }

        $sesi = UjianSiswa::where('ujian_id', $ujian->id)->where('siswa_id', $siswa->id)->first();
        if (!$sesi) {
            return redirect()->route('siswa.ujian.konfirmasi', $ujian->id);
        }

        if ($sesi->status === 'selesai') {
            return redirect()->route('siswa.ujian.hasil', $ujian->id);
        }

        // Cek kedaluwarsa waktu
        $maxSeconds = $ujian->durasi_menit * 60;
        $elapsed = now()->diffInSeconds($sesi->waktu_mulai);

        if ($elapsed >= $maxSeconds) {
            $sesi->hitungHasil();
            return redirect()->route('siswa.ujian.hasil', $ujian->id)->with('info', 'Waktu ujian telah habis.');
        }

        $sisaDetik = max(0, $maxSeconds - $elapsed);

        // Ambil soal sesuai urutan
        $urutanSoalIds = $sesi->urutan_soal ?: $ujian->soals()->pluck('id')->toArray();
        $soalsRaw = $ujian->soals()->whereIn('id', $urutanSoalIds)->get()->keyBy('id');

        $soals = collect($urutanSoalIds)->map(function ($id) use ($soalsRaw) {
            return $soalsRaw->get($id);
        })->filter();

        // Ambil jawaban yang tersimpan
        $jawabans = $sesi->jawabans()->get()->keyBy('soal_id');

        return view('siswa.ujian.kerjakan', compact('siswa', 'ujian', 'sesi', 'soals', 'jawabans', 'sisaDetik'));
    }

    /**
     * Simpan Jawaban Butir Soal (Auto-Save via AJAX)
     */
    public function simpanJawaban(Request $request, Ujian $ujian)
    {
        $siswa = $this->getSiswa();
        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $sesi = UjianSiswa::where('ujian_id', $ujian->id)->where('siswa_id', $siswa->id)->first();
        if (!$sesi || $sesi->status === 'selesai') {
            return response()->json(['success' => false, 'message' => 'Sesi ujian telah berakhir.'], 403);
        }

        $request->validate([
            'soal_id' => 'required|exists:ujian_soals,id',
            'jawaban' => 'nullable|in:A,B,C,D',
            'ragu_ragu' => 'nullable|boolean',
        ]);

        $jawaban = UjianJawaban::updateOrCreate(
            [
                'ujian_siswa_id' => $sesi->id,
                'soal_id' => $request->soal_id,
            ],
            [
                'jawaban' => $request->filled('jawaban') ? strtoupper($request->jawaban) : null,
                'ragu_ragu' => $request->boolean('ragu_ragu', false),
            ]
        );

        return response()->json([
            'success' => true,
            'soal_id' => $jawaban->soal_id,
            'jawaban' => $jawaban->jawaban,
            'ragu_ragu' => $jawaban->ragu_ragu,
        ]);
    }

    /**
     * Selesaikan Ujian & Hitung Hasil
     */
    public function selesai(Request $request, Ujian $ujian)
    {
        $siswa = $this->getSiswa();
        if (!$siswa) {
            abort(403, 'Akses tidak sah.');
        }

        $sesi = UjianSiswa::where('ujian_id', $ujian->id)->where('siswa_id', $siswa->id)->first();
        if ($sesi && $sesi->status !== 'selesai') {
            $sesi->hitungHasil();
        }

        return redirect()->route('siswa.ujian.hasil', $ujian->id)->with('success', 'Ujian berhasil dikumpulkan dan dinilai!');
    }

    /**
     * Halaman Hasil Ujian
     */
    public function hasil(Ujian $ujian)
    {
        $siswa = $this->getSiswa();
        if (!$siswa) {
            abort(403, 'Akses tidak sah.');
        }

        $sesi = UjianSiswa::where('ujian_id', $ujian->id)->where('siswa_id', $siswa->id)->first();
        if (!$sesi) {
            return redirect()->route('siswa.ujian.index');
        }

        $totalSoal = $ujian->soals()->count();

        return view('siswa.ujian.hasil', compact('siswa', 'ujian', 'sesi', 'totalSoal'));
    }
}

