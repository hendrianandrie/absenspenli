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
        // Cari token berdasarkan siswa_id langsung atau via user siswa
        $tokens = DeviceToken::where('siswa_id', $siswaId)
            ->orWhereHas('user', function ($q) use ($siswaId) {
                $q->where('siswa_id', $siswaId);
            })
            ->pluck('expo_push_token')
            ->unique()
            ->values()
            ->toArray();

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
            if (!empty($token) && str_starts_with($token, 'ExponentPushToken')) {
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
                'success' => false,
                'message' => 'Tidak ada token valid Expo.',
                'sent_count' => 0,
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
