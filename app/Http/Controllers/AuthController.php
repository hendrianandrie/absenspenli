<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $today = Carbon::today()->toDateString();
        $hasDataToday = Absensi::where('tanggal', $today)->exists();

        // Jika hari ini sudah ada data absensi, gunakan tanggal hari ini.
        // Jika belum ada data absensi hari ini, gunakan tanggal absensi terbaru di database.
        $tanggal = $hasDataToday ? $today : (Absensi::max('tanggal') ?? $today);
        $totalSiswa = Siswa::count();

        $query = Absensi::where('tanggal', $tanggal);
        $totalHariIni = (clone $query)->count();

        if ($totalHariIni === 0) {
            $statsHariIni = [
                'total' => 0,
                'hadir' => 0, 'hadir_pct' => 0,
                'sakit' => 0, 'sakit_pct' => 0,
                'izin' => 0, 'izin_pct' => 0,
                'alpha' => 0, 'alpha_pct' => 0,
            ];
        } else {
            $counts = (clone $query)->selectRaw('status, COUNT(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status');

            $hadir = $counts['Hadir'] ?? 0;
            $sakit = $counts['Sakit'] ?? 0;
            $izin = $counts['Izin'] ?? 0;
            $alpha = $counts['Alpha'] ?? 0;

            $recordedCount = $hadir + $sakit + $izin + $alpha;
            if ($recordedCount < $totalSiswa && ($sakit + $izin + $alpha) > 0 && $hadir == 0) {
                $hadir = max(0, $totalSiswa - ($sakit + $izin + $alpha));
                $effectiveTotal = $totalSiswa;
            } else {
                $effectiveTotal = max($recordedCount, $totalSiswa);
                if ($hadir == 0 && ($sakit + $izin + $alpha) > 0) {
                    $hadir = max(0, $totalSiswa - ($sakit + $izin + $alpha));
                }
            }

            $statsHariIni = [
                'total' => $effectiveTotal,
                'hadir' => $hadir,
                'hadir_pct' => $effectiveTotal > 0 ? round(($hadir / $effectiveTotal) * 100, 1) : 0,
                'sakit' => $sakit,
                'sakit_pct' => $effectiveTotal > 0 ? round(($sakit / $effectiveTotal) * 100, 1) : 0,
                'izin' => $izin,
                'izin_pct' => $effectiveTotal > 0 ? round(($izin / $effectiveTotal) * 100, 1) : 0,
                'alpha' => $alpha,
                'alpha_pct' => $effectiveTotal > 0 ? round(($alpha / $effectiveTotal) * 100, 1) : 0,
            ];
        }

        $kelasList = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $dataKelas = [];

        foreach ($kelasList as $kelas) {
            $hasAbsenKelas = Absensi::join('siswas', 'absensis.siswa_id', '=', 'siswas.id')
                ->where('siswas.kelas', $kelas)
                ->where('absensis.tanggal', $tanggal)
                ->exists();

            $totalSiswaKelas = Siswa::where('kelas', $kelas)->count();

            if (! $hasAbsenKelas) {
                $jumlahHadir = 0;
                $jumlahTidakHadir = 0;
            } else {
                $countsKelas = Absensi::join('siswas', 'absensis.siswa_id', '=', 'siswas.id')
                    ->where('siswas.kelas', $kelas)
                    ->where('absensis.tanggal', $tanggal)
                    ->selectRaw('absensis.status, COUNT(*) as aggregate')
                    ->groupBy('absensis.status')
                    ->pluck('aggregate', 'absensis.status');

                $sakit = $countsKelas['Sakit'] ?? 0;
                $izin = $countsKelas['Izin'] ?? 0;
                $alpha = $countsKelas['Alpha'] ?? 0;
                $hadirExp = $countsKelas['Hadir'] ?? 0;

                $jumlahTidakHadir = $sakit + $izin + $alpha;
                $jumlahHadir = max($hadirExp, $totalSiswaKelas - $jumlahTidakHadir);
            }

            $dataKelas[] = [
                'kelas' => $kelas,
                'total' => $totalSiswaKelas,
                'hadir' => $jumlahHadir,
                'tidak_hadir' => $jumlahTidakHadir,
                'persen' => $totalSiswaKelas > 0 ? round(($jumlahHadir / $totalSiswaKelas) * 100) : 0,
            ];
        }

        return view('auth.login', compact('statsHariIni', 'tanggal', 'totalSiswa', 'dataKelas'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = trim($request->input('login'));
        $password = $request->input('password');

        // Cek apakah input berupa email atau username (name)
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (Auth::attempt([$field => $loginInput, 'password' => $password], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali, '.Auth::user()->name.'!');
        }

        // Fallback search
        $user = User::where('name', 'LIKE', $loginInput)->orWhere('email', 'LIKE', $loginInput)->first();
        if ($user && Auth::attempt(['email' => $user->email, 'password' => $password], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali, '.Auth::user()->name.'!');
        }

        return back()->withErrors([
            'login' => 'Username/Email atau Password yang Anda masukkan tidak sesuai.',
        ])->withInput($request->only('login'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar (logout).');
    }
}
