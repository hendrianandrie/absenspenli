<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_profile_page(): void
    {
        $response = $this->get('/profile');
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_profile_page(): void
    {
        $user = User::create([
            'name' => 'Lia Marliah, S.Pd.',
            'username' => 'liamarliah',
            'email' => 'lia@smp.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'guru',
            'nip' => '198909202022211004',
        ]);

        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('Profil & Keamanan Akun');
        $response->assertSee('Lia Marliah, S.Pd.');
        $response->assertSee('198909202022211004');
    }

    public function test_user_can_update_profile_info(): void
    {
        $user = User::create([
            'name' => 'Lia Marliah',
            'username' => 'liamarliah',
            'email' => 'lia@smp.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'guru',
        ]);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Lia Marliah, S.Pd.',
            'username' => 'liamarliah_guru',
            'email' => 'liamarliah@gmail.com',
            'nip' => '198909202022211004',
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Lia Marliah, S.Pd.', $user->name);
        $this->assertEquals('liamarliah_guru', $user->username);
        $this->assertEquals('liamarliah@gmail.com', $user->email);
        $this->assertEquals('198909202022211004', $user->nip);
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = User::create([
            'name' => 'Lia Marliah',
            'username' => 'liamarliah',
            'email' => 'lia@smp.sch.id',
            'password' => Hash::make('oldpassword123'),
            'role' => 'guru',
        ]);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Lia Marliah',
            'username' => 'liamarliah',
            'email' => 'lia@smp.sch.id',
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::create([
            'name' => 'Lia Marliah',
            'username' => 'liamarliah',
            'email' => 'lia@smp.sch.id',
            'password' => Hash::make('oldpassword123'),
            'role' => 'guru',
        ]);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Lia Marliah',
            'username' => 'liamarliah',
            'email' => 'lia@smp.sch.id',
            'current_password' => 'passwordsalah',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors(['current_password']);

        $user->refresh();
        $this->assertTrue(Hash::check('oldpassword123', $user->password));
    }
}
