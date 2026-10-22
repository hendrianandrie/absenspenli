<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Daftarkan atau perbarui Expo Push Token dari perangkat pengguna
     */
    public function register(Request $request)
    {
        $request->validate([
            'expo_push_token' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $token = $request->expo_push_token;
        $deviceName = $request->device_name ?: 'Mobile Device';

        // Tentukan siswa_id jika user adalah akun siswa
        $siswaId = $user->siswa_id;
        if (!$siswaId && $user->role === 'siswa') {
            $siswa = \App\Models\Siswa::where('nis', $user->username)->first();
            $siswaId = $siswa ? $siswa->id : null;
        }

        $deviceToken = DeviceToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'expo_push_token' => $token,
            ],
            [
                'siswa_id' => $siswaId,
                'device_name' => $deviceName,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Perangkat berhasil didaftarkan untuk notifikasi push.',
            'data' => [
                'id' => $deviceToken->id,
                'user_id' => $user->id,
                'siswa_id' => $siswaId,
                'device_name' => $deviceName,
            ]
        ]);
    }

    /**
     * Hapus token perangkat saat logout
     */
    public function unregister(Request $request)
    {
        $request->validate([
            'expo_push_token' => 'required|string',
        ]);

        $user = $request->user();
        if ($user) {
            DeviceToken::where('user_id', $user->id)
                ->where('expo_push_token', $request->expo_push_token)
                ->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Perangkat berhasil dikeluarkan dari notifikasi push.',
        ]);
    }

    /**
     * Kirim notifikasi tes ke perangkat saat ini
     */
    public function test(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $title = "🔔 Tes Notifikasi SMPN 5 Ciamis";
        $body = "Halo {$user->name}, notifikasi push di HP Anda berhasil terhubung dan siap menerima pembaruan absensi & nilai!";

        $result = NotificationService::sendToUser($user->id, $title, $body, [
            'type' => 'test',
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->json([
            'status' => $result['success'] ? 'success' : 'warning',
            'message' => $result['success'] ? 'Notifikasi tes berhasil dikirim.' : $result['message'],
            'result' => $result,
        ]);
    }
}
