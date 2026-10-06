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

        $hasUsernameColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'username');
        $isEmail = (bool) filter_var($loginInput, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            if (Auth::attempt(['email' => $loginInput, 'password' => $password], $request->boolean('remember'))) {
                $request->session()->regenerate();
                $target = Auth::user()->role === 'siswa' ? route('siswa.dashboard') : route('dashboard');

                return redirect()->intended($target)->with('success', 'Selamat datang kembali, '.Auth::user()->name.'!');
            }
        } else {
            $field = $hasUsernameColumn ? 'username' : 'name';
            if (Auth::attempt([$field => $loginInput, 'password' => $password], $request->boolean('remember'))) {
                $request->session()->regenerate();
                $target = Auth::user()->role === 'siswa' ? route('siswa.dashboard') : route('dashboard');

                return redirect()->intended($target)->with('success', 'Selamat datang kembali, '.Auth::user()->name.'!');
            }
        }

        // Fallback search berdasarkan username (jika kolom ada), email, atau name (nama lengkap)
        $userQuery = User::query();
        if ($hasUsernameColumn) {
            $userQuery->where('username', $loginInput)
                ->orWhere('email', $loginInput)
                ->orWhere('name', $loginInput);
        } else {
            $userQuery->where('email', $loginInput)
                ->orWhere('name', $loginInput);
        }
        $user = $userQuery->first();

        if ($user && Auth::attempt(['email' => $user->email, 'password' => $password], $request->boolean('remember'))) {
            $request->session()->regenerate();
            $target = Auth::user()->role === 'siswa' ? route('siswa.dashboard') : route('dashboard');

            return redirect()->intended($target)->with('success', 'Selamat datang kembali, '.Auth::user()->name.'!');
        }

        // =========================================================================
        // AUTO-PROVISIONING / DIRECT LOGIN SISWA VIA NIS
        // Memastikan siswa selalu bisa login menggunakan NIS meskipun database
        // online hosting belum menjalankan migrasi akun siswa secara batch.
        // =========================================================================
        $siswa = Siswa::where('nis', $loginInput)->first();
        if ($siswa && $password === (string) $siswa->nis) {
            // Pastikan kolom siswa_id ada pada tabel users (auto-migrasi jika belum ada di hosting)
            if (\Illuminate\Support\Facades\Schema::hasTable('users') && !\Illuminate\Support\Facades\Schema::hasColumn('users', 'siswa_id')) {
                try {
                    \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->unsignedBigInteger('siswa_id')->nullable()->after('id');
                    });
                } catch (\Throwable $e) {
                    // Abaikan jika sudah ada
                }
            }

            $hasSiswaIdCol = \Illuminate\Support\Facades\Schema::hasColumn('users', 'siswa_id');
            $userSiswa = User::where('username', $siswa->nis)->first();

            $emailCandidate = $siswa->nis . '@siswa.smpn5ciamis.sch.id';
            $userData = [
                'name' => $siswa->nama,
                'username' => $siswa->nis,
                'email' => $emailCandidate,
                'password' => \Illuminate\Support\Facades\Hash::make($siswa->nis),
                'role' => 'siswa',
            ];
            if ($hasSiswaIdCol) {
                $userData['siswa_id'] = $siswa->id;
            }

            if ($userSiswa) {
                $userSiswa->update($userData);
            } else {
                $userSiswa = User::create($userData);
            }

            Auth::login($userSiswa, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->route('siswa.dashboard')->with('success', 'Selamat datang, ' . $userSiswa->name . '!');
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
