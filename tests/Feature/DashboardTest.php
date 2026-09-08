<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_attendance_percentages_correctly(): void
    {
        $user = User::factory()->create();
        $siswa = Siswa::create([
            'nis' => '9999',
            'nama' => 'Testing Siswa',
            'kelas' => '7A',
            'jenis_kelamin' => 'L',
        ]);

        Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'status' => 'Hadir',
        ]);

        Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'status' => 'Sakit',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Persentase Kehadiran, Izin, Sakit & Alpa Siswa', false);
        $response->assertSee('Minggu Ini');
        $response->assertSee('Bulan Ini');
        $response->assertSee('Keseluruhan');
        $response->assertSee('50%');
    }
}
