<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PiketAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_piket_user_can_login_with_username_and_password(): void
    {
        $piketUser = User::create([
            'name' => 'piket',
            'email' => 'piket@smp.sch.id',
            'password' => bcrypt('piket123'),
            'role' => 'piket',
        ]);

        $response = $this->post('/login', [
            'login' => 'piket',
            'password' => 'piket123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($piketUser);
    }

    public function test_piket_user_has_access_to_absensi_and_siswa_only(): void
    {
        $piketUser = User::create([
            'name' => 'piket',
            'email' => 'piket@smp.sch.id',
            'password' => bcrypt('piket123'),
            'role' => 'piket',
        ]);

        $response = $this->actingAs($piketUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Petugas Piket (Absensi)');
        $response->assertDontSee('Mata Pelajaran');

        $this->actingAs($piketUser)->get('/siswa')->assertStatus(200);
        $this->actingAs($piketUser)->get('/absensi/harian')->assertStatus(200);
        $this->actingAs($piketUser)->get('/rekap')->assertStatus(200);
    }

    public function test_piket_user_is_blocked_from_grade_management_and_mapel(): void
    {
        $piketUser = User::create([
            'name' => 'piket',
            'email' => 'piket@smp.sch.id',
            'password' => bcrypt('piket123'),
            'role' => 'piket',
        ]);

        $responseMapel = $this->actingAs($piketUser)->get('/mapel');
        $responseMapel->assertRedirect('/dashboard');

        $responseNilai = $this->actingAs($piketUser)->get('/nilai');
        $responseNilai->assertRedirect('/dashboard');
    }
}
