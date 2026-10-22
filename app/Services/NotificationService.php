<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    const EXPO_API_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Kirim notifikasi push ke semua perangkat yang terhubung dengan Siswa
     */
    public static function sendToSiswa($siswaId, string $title, string $body, array $data = []): array
    {
        $siswa = Siswa::find($siswaId);
        $nis = $siswa ? $siswa->nis : null;
        $cleanNis = $nis ? ltrim($nis, '0') : null;

        // Cari token berdasarkan siswa_id langsung, user.siswa_id, atau user.username yang cocok dengan NIS
        $tokens = DeviceToken::where('siswa_id', $siswaId)
            ->orWhereHas('user', function ($q) use ($siswaId, $nis, $cleanNis) {
                $q->where('siswa_id', $siswaId);
                if ($nis) {
                    $q->orWhere('username', $nis);
                    if ($cleanNis !== '') {
                        $q->orWhere('username', $cleanNis)
                          ->orWhere('username', 'like', '%' . $cleanNis);
                    }
                }
            })
            ->pluck('expo_push_token')
            ->unique()
            ->values()
            ->toArray();

        // Simpan notifikasi ke cache agar bisa langsung ditarik oleh aplikasi (Expo Go maupun APK)
        try {
            \Illuminate\Support\Facades\Cache::put("latest_notif_siswa_{$siswaId}", [
                'id' => uniqid('notif_'),
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'timestamp' => now()->timestamp,
            ], 86400);
        } catch (\Throwable $e) {}

        if (empty($tokens)) {
            return [
                'success' => false,
                'message' => 'Tidak ada perangkat terdaftar untuk siswa ini.',
                'sent_count' => 0,
            ];
        }

        return self::sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Kirim notifikasi push ke semua perangkat milik User tertentu
     */
    public static function sendToUser($userId, string $title, string $body, array $data = []): array
    {
        // Simpan notifikasi ke cache user
        try {
            \Illuminate\Support\Facades\Cache::put("latest_notif_user_{$userId}", [
                'id' => uniqid('notif_'),
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'timestamp' => now()->timestamp,
            ], 86400);
        } catch (\Throwable $e) {}

        $tokens = DeviceToken::where('user_id', $userId)
            ->pluck('expo_push_token')
            ->unique()
            ->values()
            ->toArray();

        if (empty($tokens)) {
            return [
                'success' => false,
                'message' => 'Tidak ada perangkat terdaftar untuk pengguna ini.',
                'sent_count' => 0,
            ];
        }

        return self::sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Kirim batch notifikasi ke daftar Expo Push Tokens
     */
    public static function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $messages = [];
        foreach ($tokens as $token) {
            if (!empty($token) && (str_starts_with($token, 'ExponentPushToken') || str_starts_with($token, 'ExpoPushToken'))) {
                $messages[] = [
                    'to' => $token,
                    'sound' => 'default',
                    'title' => $title,
                    'body' => $body,
                    'data' => $data,
                    'priority' => 'high',
                    'channelId' => 'default',
                ];
            }
        }

        if (empty($messages)) {
            return [
                'success' => true,
                'message' => 'Perangkat terhubung. Notifikasi berhasil dikirim ke aplikasi.',
                'sent_count' => count($tokens),
            ];
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate',
                'Content-Type' => 'application/json',
            ])->timeout(8)->post(self::EXPO_API_URL, $messages);

            $result = $response->json();

            // Periksa jika ada token yang sudah tidak terdaftar (DeviceNotRegistered) untuk dibersihkan
            if (isset($result['data']) && is_array($result['data'])) {
                foreach ($result['data'] as $idx => $ticket) {
                    if (isset($ticket['status']) && $ticket['status'] === 'error') {
                        if (isset($ticket['details']['error']) && $ticket['details']['error'] === 'DeviceNotRegistered') {
                            $deadToken = $messages[$idx]['to'] ?? null;
                            if ($deadToken) {
                                DeviceToken::where('expo_push_token', $deadToken)->delete();
                                Log::info("NotificationService: Menghapus token tidak aktif: {$deadToken}");
                            }
                        }
                    }
                }
            }

            return [
                'success' => true,
                'sent_count' => count($messages),
                'response' => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('NotificationService Push Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'sent_count' => 0,
            ];
        }
    }
}
