<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\User;
use App\Models\WaliKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    /**
     * Login API untuk Multi-Role (Siswa, Guru, Wali Kelas, Piket, Admin)
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = trim($request->input('login'));
        $password = $request->input('password');

        $user = null;
        $isEmail = (bool) filter_var($loginInput, FILTER_VALIDATE_EMAIL);

        // 1. Coba cari user di tabel users
        if ($isEmail) {
            $user = User::where('email', $loginInput)->first();
        } else {
            $user = User::where('username', $loginInput)
                ->orWhere('nip', $loginInput)
                ->orWhere('email', $loginInput)
                ->first();
        }

        // 2. Verifikasi Password jika user ditemukan
        if ($user && Hash::check($password, $user->password)) {
            return $this->respondWithToken($user, 'Login berhasil.');
        }

        // 3. Fallback Auto-Provisioning Siswa via NIS
        $siswa = Siswa::where('nis', $loginInput)->first();
        if ($siswa && $password === (string) $siswa->nis) {
            $hasSiswaIdCol = Schema::hasColumn('users', 'siswa_id');
            $userSiswa = User::where('username', $siswa->nis)->first();

            $emailCandidate = $siswa->nis . '@siswa.smpn5ciamis.sch.id';
            $userData = [
                'name' => $siswa->nama,
                'username' => $siswa->nis,
                'email' => $emailCandidate,
                'password' => Hash::make($siswa->nis),
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

            return $this->respondWithToken($userSiswa, 'Login siswa berhasil.');
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Username/NIS atau Password yang Anda masukkan tidak sesuai.',
        ], 401);
    }

    /**
     * Mendapatkan profil pengguna yang sedang login
     */
    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'status' => 'success',
            'data' => $this->formatUserData($user),
        ]);
    }

    /**
     * Logout & Revoke Sanctum Token
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            // Revoke current token
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil. Token telah dihapus.',
        ]);
    }

    /**
     * Helper untuk generate token & format response
     */
    private function respondWithToken(User $user, string $message)
    {
        // Hapus token lama perangkat jika ingin single-session per device (opsional)
        // $user->tokens()->delete();

        $token = $user->createToken('mobile-app-auth')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'token' => $token,
            'user' => $this->formatUserData($user),
        ]);
    }

    /**
     * Format data user untuk respon mobile
     */
    private function formatUserData(User $user): array
    {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'nip' => $user->nip,
            'is_wali_kelas' => false,
            'kelas_binaan' => null,
            'siswa' => null,
        ];

        // Jika Guru / Wali Kelas: periksa status kelas binaan
        if (in_array($user->role, ['guru', 'walikelas'])) {
            $wali = WaliKelas::where('user_id', $user->id)->first();
            if ($wali) {
                $data['is_wali_kelas'] = true;
                $data['kelas_binaan'] = $wali->kelas;
            }
        }

        // Jika Siswa: sertakan info detail siswa
        if ($user->role === 'siswa') {
            $siswa = $user->siswa;
            if (!$siswa) {
                $siswa = Siswa::where('nis', $user->username)->first();
            }

            if ($siswa) {
                $waliKelas = WaliKelas::where('kelas', $siswa->kelas)->with('user')->first();
                $data['siswa'] = [
                    'id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'nis' => $siswa->nis,
                    'nisn' => $siswa->nisn ?? null,
                    'kelas' => $siswa->kelas,
                    'jenis_kelamin' => $siswa->jenis_kelamin,
                    'wali_kelas' => $waliKelas ? [
                        'nama' => optional($waliKelas->user)->name ?? 'Belum Ditentukan',
                        'nip' => optional($waliKelas->user)->nip ?? '-',
                    ] : null,
                ];
            }
        }

        return $data;
    }
}
