<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_users_page_and_create_user(): void
    {
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@smp.sch.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/users');
        $response->assertStatus(200);
        $response->assertSee('Kelola Pengguna');

        $storeResponse = $this->actingAs($admin)->post('/users', [
            'name' => 'guru_ipa',
            'email' => 'guru_ipa@smp.sch.id',
            'password' => 'guru12345',
            'role' => 'guru',
        ]);

        $storeResponse->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'name' => 'guru_ipa',
            'email' => 'guru_ipa@smp.sch.id',
            'role' => 'guru',
        ]);
    }

    public function test_non_admin_cannot_access_users_page(): void
    {
        $guru = User::create([
            'name' => 'guru_mtk',
            'email' => 'guru@smp.sch.id',
            'password' => bcrypt('password'),
            'role' => 'guru',
        ]);

        $response = $this->actingAs($guru)->get('/users');
        $response->assertRedirect('/dashboard');
    }
}
