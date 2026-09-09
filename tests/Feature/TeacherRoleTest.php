<?php

namespace Tests\Feature;

use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_user_can_login_and_see_grade_dashboard(): void
    {
        $mapel = MataPelajaran::create([
            'kode_mapel' => 'MAT-TEST',
            'nama_mapel' => 'Matematika',
            'kkm' => 75,
        ]);

        $guru = User::create([
            'name' => 'guru_mtk',
            'email' => 'guru_mtk@smp.sch.id',
            'password' => bcrypt('guru123'),
            'role' => 'guru',
            'mata_pelajaran_id' => $mapel->id,
            'kelas_diampu' => ['VII A', 'VII B'],
        ]);

        $response = $this->post('/login', [
            'login' => 'guru_mtk',
            'password' => 'guru123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($guru);

        $dashResponse = $this->actingAs($guru)->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Dashboard Guru Mata Pelajaran');
        $dashResponse->assertSee('Matematika');
    }

    public function test_teacher_grade_dropdowns_are_scoped_to_assigned_subject_and_classes(): void
    {
        $mapel1 = MataPelajaran::create(['kode_mapel' => 'MAT-TEST', 'nama_mapel' => 'Matematika', 'kkm' => 75]);
        $mapel2 = MataPelajaran::create(['kode_mapel' => 'IPA-TEST', 'nama_mapel' => 'IPA', 'kkm' => 75]);

        Siswa::create(['nama' => 'Siswa A', 'kelas' => 'VII A', 'jenis_kelamin' => 'L']);
        Siswa::create(['nama' => 'Siswa B', 'kelas' => 'VIII B', 'jenis_kelamin' => 'P']);

        $guru = User::create([
            'name' => 'guru_mtk',
            'email' => 'guru_mtk@smp.sch.id',
            'password' => bcrypt('guru123'),
            'role' => 'guru',
            'mata_pelajaran_id' => $mapel1->id,
            'kelas_diampu' => ['VII A'],
        ]);

        $response = $this->actingAs($guru)->get('/nilai');
        $response->assertStatus(200);
        $response->assertSee('Matematika');
        $response->assertSee('Kelas VII A');
        $response->assertDontSee('IPA');
    }

    public function test_rapor_siswa_pdf_generation(): void
    {
        $siswa = Siswa::create(['nama' => 'Ahmad Test', 'kelas' => 'VII A', 'jenis_kelamin' => 'L']);
        $mapel = MataPelajaran::create(['kode_mapel' => 'MAT-TEST', 'nama_mapel' => 'Matematika', 'kkm' => 75]);

        $admin = User::create([
            'name' => 'admin_test',
            'email' => 'admintest@smp.sch.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get("/nilai/rapor-siswa/{$siswa->id}");
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }
}
