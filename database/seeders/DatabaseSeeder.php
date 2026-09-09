<?php

namespace Database\Seeders;

use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default User Admin
        User::updateOrCreate(
            ['email' => 'admin@smp.sch.id'],
            [
                'name' => 'admin',
                'password' => Hash::make('spenlimantap'),
                'role' => 'admin',
            ]
        );

        // Default User Piket (Hanya Absensi)
        User::updateOrCreate(
            ['name' => 'piket'],
            [
                'email' => 'piket@smp.sch.id',
                'password' => Hash::make('piket123'),
                'role' => 'piket',
            ]
        );

        // Default Mata Pelajaran SMP
        $mapels = [
            ['kode_mapel' => 'MAT-SMP', 'nama_mapel' => 'Matematika', 'kkm' => 75],
            ['kode_mapel' => 'BIN-SMP', 'nama_mapel' => 'Bahasa Indonesia', 'kkm' => 75],
            ['kode_mapel' => 'BIG-SMP', 'nama_mapel' => 'Bahasa Inggris', 'kkm' => 75],
            ['kode_mapel' => 'IPA-SMP', 'nama_mapel' => 'Ilmu Pengetahuan Alam (IPA)', 'kkm' => 75],
            ['kode_mapel' => 'IPS-SMP', 'nama_mapel' => 'Ilmu Pengetahuan Sosial (IPS)', 'kkm' => 75],
            ['kode_mapel' => 'INF-SMP', 'nama_mapel' => 'Informatika', 'kkm' => 75],
            ['kode_mapel' => 'PAI-SMP', 'nama_mapel' => 'Pendidikan Agama & Budi Pekerti', 'kkm' => 75],
            ['kode_mapel' => 'PJK-SMP', 'nama_mapel' => 'PJOK', 'kkm' => 75],
        ];

        $mapelMtk = null;
        $mapelIndo = null;

        foreach ($mapels as $mapel) {
            $m = MataPelajaran::firstOrCreate(['kode_mapel' => $mapel['kode_mapel']], $mapel);
            if ($m->kode_mapel === 'MAT-SMP') $mapelMtk = $m;
            if ($m->kode_mapel === 'BIN-SMP') $mapelIndo = $m;
        }

        // Default Guru Matematika
        User::updateOrCreate(
            ['name' => 'guru_mtk'],
            [
                'email' => 'guru_mtk@smp.sch.id',
                'password' => Hash::make('guru123'),
                'role' => 'guru',
                'mata_pelajaran_id' => $mapelMtk ? $mapelMtk->id : null,
                'kelas_diampu' => ['VII A', 'VII B', 'VIII A', 'IX A'],
            ]
        );

        // Default Guru Bahasa Indonesia
        User::updateOrCreate(
            ['name' => 'guru_indo'],
            [
                'email' => 'guru_indo@smp.sch.id',
                'password' => Hash::make('guru123'),
                'role' => 'guru',
                'mata_pelajaran_id' => $mapelIndo ? $mapelIndo->id : null,
                'kelas_diampu' => ['VII A', 'VII C', 'VIII B', 'IX B'],
            ]
        );

        // Call Siswa CSV Seeder
        $this->call(SiswaCsvSeeder::class);
    }
}
