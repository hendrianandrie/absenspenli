<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceRecognitionController extends Controller
{
    /**
     * Dashboard / Status Presensi Wajah
     */
    public function index(Request $request)
    {
        $selectedKelas = $request->get('kelas', 'IX E');
        $today = Carbon::today()->toDateString();

        // Daftar kelas berurutan logis
        $order = ['VII' => 1, 'VIII' => 2, 'IX' => 3];
        $rawKelas = Siswa::select('kelas')->distinct()->pluck('kelas');
        $daftarKelas = $rawKelas->sort(function ($a, $b) use ($order) {
            $tA = $order[strtoupper(MataPelajaran::getTingkatFromKelas($a))] ?? 99;
            $tB = $order[strtoupper(MataPelajaran::getTingkatFromKelas($b))] ?? 99;
            if ($tA !== $tB) return $tA <=> $tB;
            return strnatcasecmp($a, $b);
        })->values();

        $querySiswa = Siswa::query();
        if ($selectedKelas !== 'Semua') {
            $querySiswa->where('kelas', $selectedKelas);
        }
        $siswas = $querySiswa->orderBy('nama')->get();

        $totalSiswa = $siswas->count();
        $enrolledCount = $siswas->whereNotNull('face_descriptor')->count();
        $notEnrolledCount = $totalSiswa - $enrolledCount;

        // Ambil presensi hari ini untuk siswa di rombel ini
        $absensisToday = Absensi::whereIn('siswa_id', $siswas->pluck('id'))
            ->whereDate('tanggal', $today)
            ->get()
            ->keyBy('siswa_id');

        $hadirFaceCount = $absensisToday->where('status', 'Hadir')->where('metode', 'face')->count();
        $hadirManualCount = $absensisToday->where('status', 'Hadir')->where('metode', '!=', 'face')->count();
        $sakitCount = $absensisToday->where('status', 'Sakit')->count();
        $izinCount = $absensisToday->where('status', 'Izin')->count();
        $alphaCount = $absensisToday->where('status', 'Alpha')->count();
        $belumPresensiCount = $totalSiswa - $absensisToday->count();

        return view('face.index', compact(
            'selectedKelas',
            'daftarKelas',
            'siswas',
            'totalSiswa',
            'enrolledCount',
            'notEnrolledCount',
            'absensisToday',
            'hadirFaceCount',
            'hadirManualCount',
            'sakitCount',
            'izinCount',
            'alphaCount',
            'belumPresensiCount',
            'today'
        ));
    }

    /**
     * Halaman Rekam Wajah (Enrollment Lab)
     */
    public function enrollView(Request $request)
    {
        $selectedKelas = $request->get('kelas', 'IX E');
        $preselectedSiswaId = $request->get('siswa_id');

        $order = ['VII' => 1, 'VIII' => 2, 'IX' => 3];
        $rawKelas = Siswa::select('kelas')->distinct()->pluck('kelas');
        $daftarKelas = $rawKelas->sort(function ($a, $b) use ($order) {
            $tA = $order[strtoupper(MataPelajaran::getTingkatFromKelas($a))] ?? 99;
            $tB = $order[strtoupper(MataPelajaran::getTingkatFromKelas($b))] ?? 99;
            if ($tA !== $tB) return $tA <=> $tB;
            return strnatcasecmp($a, $b);
        })->values();

        $querySiswa = Siswa::query();
        if ($selectedKelas !== 'Semua') {
            $querySiswa->where('kelas', $selectedKelas);
        }
        $siswas = $querySiswa->orderBy('nama')->get();

        $currentSiswa = null;
        if ($preselectedSiswaId) {
            $currentSiswa = Siswa::find($preselectedSiswaId);
        } elseif ($siswas->isNotEmpty()) {
            $currentSiswa = $siswas->first();
        }

        return view('face.enroll', compact(
            'selectedKelas',
            'daftarKelas',
            'siswas',
            'currentSiswa'
        ));
    }

    /**
     * Simpan sampel wajah dan vektor descriptor siswa
     */
    public function saveEnrollment(Request $request, Siswa $siswa)
    {
        $request->validate([
            'descriptor' => 'required',
            'image' => 'nullable|string', // base64 data url
        ]);

        $descriptor = is_array($request->descriptor) ? $request->descriptor : json_decode($request->descriptor, true);

        if (!$descriptor || count($descriptor) !== 128) {
            return response()->json([
                'success' => false,
                'message' => 'Format vektor descriptor wajah tidak valid (harus 128 angka float).',
            ], 422);
        }

        $imagePath = $siswa->foto_wajah;

        // Simpan foto capture jika dikirimkan
        if ($request->filled('image') && str_starts_with($request->image, 'data:image')) {
            try {
                $imageParts = explode(';base64,', $request->image);
                $imageTypeAux = explode('image/', $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'jpeg';
                $imageBase64 = base64_decode($imageParts[1]);

                $filename = 'faces/' . $siswa->nis . '_' . time() . '.' . $imageType;
                Storage::disk('public')->put($filename, $imageBase64);

                // Hapus foto lama jika ada
                if ($siswa->foto_wajah && Storage::disk('public')->exists($siswa->foto_wajah)) {
                    Storage::disk('public')->delete($siswa->foto_wajah);
                }

                $imagePath = $filename;
            } catch (\Exception $e) {
                // Abaikan jika penyimpanan file gagal, descriptor tetap disimpan
            }
        }

        $siswa->update([
            'face_descriptor' => json_encode($descriptor),
            'foto_wajah' => $imagePath,
            'face_enrolled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Sampel wajah siswa {$siswa->nama} (NIS: {$siswa->nis}) berhasil direkam dan disimpan!",
            'siswa' => [
                'id' => $siswa->id,
                'nama' => $siswa->nama,
                'nis' => $siswa->nis,
                'kelas' => $siswa->kelas,
                'foto_url' => $imagePath ? asset('storage/' . $imagePath) : null,
                'enrolled_at' => $siswa->face_enrolled_at ? $siswa->face_enrolled_at->format('d/m/Y H:i') : null,
            ],
        ]);
    }

    /**
     * Hapus / Reset data wajah siswa
     */
    public function deleteEnrollment(Siswa $siswa)
    {
        if ($siswa->foto_wajah && Storage::disk('public')->exists($siswa->foto_wajah)) {
            Storage::disk('public')->delete($siswa->foto_wajah);
        }

        $siswa->update([
            'face_descriptor' => null,
            'foto_wajah' => null,
            'face_enrolled_at' => null,
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data sampel wajah {$siswa->nama} berhasil direset.",
            ]);
        }

        return redirect()->back()->with('success', "Data sampel wajah {$siswa->nama} berhasil direset.");
    }

    /**
     * Halaman Scanner / Kiosk Presensi Wajah
     */
    public function scannerView(Request $request)
    {
        $selectedKelas = $request->get('kelas', 'IX E');
        $today = Carbon::today()->toDateString();

        $order = ['VII' => 1, 'VIII' => 2, 'IX' => 3];
        $rawKelas = Siswa::select('kelas')->distinct()->pluck('kelas');
        $daftarKelas = $rawKelas->sort(function ($a, $b) use ($order) {
            $tA = $order[strtoupper(MataPelajaran::getTingkatFromKelas($a))] ?? 99;
            $tB = $order[strtoupper(MataPelajaran::getTingkatFromKelas($b))] ?? 99;
            if ($tA !== $tB) return $tA <=> $tB;
            return strnatcasecmp($a, $b);
        })->values();

        // Ambil riwayat scan wajah hari ini
        $recentScans = Absensi::with('siswa')
            ->whereDate('tanggal', $today)
            ->where('metode', 'face')
            ->orderBy('updated_at', 'desc')
            ->take(20)
            ->get();

        return view('face.scanner', compact(
            'selectedKelas',
            'daftarKelas',
            'recentScans',
            'today'
        ));
    }

    /**
     * API JSON: Mengambil data siswa yang sudah terdaftar wajahnya
     */
    public function getEnrolledStudents(Request $request)
    {
        $selectedKelas = $request->get('kelas', 'IX E');

        $query = Siswa::whereNotNull('face_descriptor');
        if ($selectedKelas !== 'Semua') {
            $query->where('kelas', $selectedKelas);
        }

        $students = $query->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'nama' => $s->nama,
                'nis' => $s->nis,
                'kelas' => $s->kelas,
                'descriptor' => json_decode($s->face_descriptor),
                'foto_url' => $s->foto_wajah ? asset('storage/' . $s->foto_wajah) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $students->count(),
            'students' => $students,
        ]);
    }

    /**
     * API: Catat presensi kehadiran setelah wajah terverifikasi
     */
    public function recordAttendance(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'distance' => 'nullable|numeric',
            'image' => 'nullable|string',
        ]);

        $siswa = Siswa::findOrFail($request->siswa_id);
        $today = Carbon::today()->toDateString();

        $absensi = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $today)
            ->first();

        $statusLama = $absensi ? $absensi->status : null;
        $isAlreadyHadir = $absensi && $absensi->status === 'Hadir';

        $snapshotPath = null;
        if ($request->filled('image') && str_starts_with($request->image, 'data:image')) {
            try {
                $imageParts = explode(';base64,', $request->image);
                $imageBase64 = base64_decode($imageParts[1]);
                $filename = 'faces/scans/' . $siswa->nis . '_' . date('Ymd_His') . '.jpg';
                Storage::disk('public')->put($filename, $imageBase64);
                $snapshotPath = $filename;
            } catch (\Exception $e) {
                // Ignore
            }
        }

        if ($absensi) {
            $absensi->update([
                'status' => 'Hadir',
                'metode' => 'face',
                'foto_scan' => $snapshotPath ?: $absensi->foto_scan,
            ]);
        } else {
            $absensi = Absensi::create([
                'siswa_id' => $siswa->id,
                'tanggal' => $today,
                'status' => 'Hadir',
                'metode' => 'face',
                'foto_scan' => $snapshotPath,
            ]);
        }

        $waktuScan = Carbon::parse($absensi->updated_at)->format('H:i:s');

        return response()->json([
            'success' => true,
            'is_duplicate' => $isAlreadyHadir,
            'message' => $isAlreadyHadir
                ? "Siswa {$siswa->nama} sudah tercatat Hadir sebelumnya."
                : "Presensi BERHASIL! {$siswa->nama} ({$siswa->kelas}) tercatat Hadir pada {$waktuScan} WIB.",
            'siswa' => [
                'id' => $siswa->id,
                'nama' => $siswa->nama,
                'nis' => $siswa->nis,
                'kelas' => $siswa->kelas,
                'foto_url' => $siswa->foto_wajah ? asset('storage/' . $siswa->foto_wajah) : null,
                'waktu' => $waktuScan,
            ],
            'distance' => $request->distance,
        ]);
    }

    /**
     * Halaman Siswa Belum Presensi Hari Ini (Fasilitas Input Manual)
     */
    public function unattendedView(Request $request)
    {
        $selectedKelas = $request->get('kelas', 'IX E');
        $today = Carbon::today()->toDateString();

        $order = ['VII' => 1, 'VIII' => 2, 'IX' => 3];
        $rawKelas = Siswa::select('kelas')->distinct()->pluck('kelas');
        $daftarKelas = $rawKelas->sort(function ($a, $b) use ($order) {
            $tA = $order[strtoupper(MataPelajaran::getTingkatFromKelas($a))] ?? 99;
            $tB = $order[strtoupper(MataPelajaran::getTingkatFromKelas($b))] ?? 99;
            if ($tA !== $tB) return $tA <=> $tB;
            return strnatcasecmp($a, $b);
        })->values();

        $querySiswa = Siswa::query();
        if ($selectedKelas !== 'Semua') {
            $querySiswa->where('kelas', $selectedKelas);
        }
        $siswas = $querySiswa->orderBy('nama')->get();

        $absensisToday = Absensi::whereIn('siswa_id', $siswas->pluck('id'))
            ->whereDate('tanggal', $today)
            ->get()
            ->keyBy('siswa_id');

        // Siswa yang sama sekali belum diabsen hari ini
        $belumPresensi = $siswas->filter(function ($s) use ($absensisToday) {
            return !isset($absensisToday[$s->id]);
        })->values();

        // Siswa yang sudah diabsen hari ini
        $sudahPresensi = $siswas->filter(function ($s) use ($absensisToday) {
            return isset($absensisToday[$s->id]);
        })->values();

        return view('face.unattended', compact(
            'selectedKelas',
            'daftarKelas',
            'belumPresensi',
            'sudahPresensi',
            'absensisToday',
            'today'
        ));
    }

    /**
     * Input Manual Cepat: Sakit, Izin, atau Alpa
     */
    public function markStatus(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'status' => 'required|in:Hadir,Sakit,Izin,Alpha',
        ]);

        $today = Carbon::today()->toDateString();
        $siswa = Siswa::findOrFail($request->siswa_id);

        Absensi::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'tanggal' => $today,
            ],
            [
                'status' => $request->status,
                'metode' => 'manual',
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Status {$siswa->nama} berhasil diatur menjadi {$request->status}.",
            ]);
        }

        return redirect()->back()->with('success', "Status {$siswa->nama} berhasil diatur menjadi {$request->status}.");
    }

    /**
     * Tandai semua siswa yang belum absen di kelas ini sebagai Alpa (Bulk Alpa)
     */
    public function markBulkAlpa(Request $request)
    {
        $selectedKelas = $request->get('kelas', 'IX E');
        $today = Carbon::today()->toDateString();

        $querySiswa = Siswa::query();
        if ($selectedKelas !== 'Semua') {
            $querySiswa->where('kelas', $selectedKelas);
        }
        $siswas = $querySiswa->get();

        $absensisToday = Absensi::whereIn('siswa_id', $siswas->pluck('id'))
            ->whereDate('tanggal', $today)
            ->pluck('siswa_id')
            ->toArray();

        $count = 0;
        foreach ($siswas as $s) {
            if (!in_array($s->id, $absensisToday)) {
                Absensi::create([
                    'siswa_id' => $s->id,
                    'tanggal' => $today,
                    'status' => 'Alpha',
                    'metode' => 'manual',
                ]);
                $count++;
            }
        }

        return redirect()->back()->with('success', "Sebanyak {$count} siswa yang belum presensi di kelas {$selectedKelas} berhasil ditandai sebagai Alpa.");
    }
}
