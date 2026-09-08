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

        foreach ($mapels as $mapel) {
            MataPelajaran::firstOrCreate(['kode_mapel' => $mapel['kode_mapel']], $mapel);
        }

        // Sample Siswa if table is empty
        if (Siswa::count() === 0) {
            $siswaData = [
                ['nis' => '8001', 'nama' => 'Aditya Pratama', 'kelas' => '7A', 'jenis_kelamin' => 'L'],
                ['nis' => '8002', 'nama' => 'Budi Santoso', 'kelas' => '7A', 'jenis_kelamin' => 'L'],
                ['nis' => '8003', 'nama' => 'Citra Lestari', 'kelas' => '7A', 'jenis_kelamin' => 'P'],
                ['nis' => '8004', 'nama' => 'Dewi Anggraini', 'kelas' => '7A', 'jenis_kelamin' => 'P'],
                ['nis' => '8005', 'nama' => 'Eko Prasetyo', 'kelas' => '7A', 'jenis_kelamin' => 'L'],
                ['nis' => '8006', 'nama' => 'Fani Wijaya', 'kelas' => '7B', 'jenis_kelamin' => 'P'],
                ['nis' => '8007', 'nama' => 'Gilang Ramadhan', 'kelas' => '7B', 'jenis_kelamin' => 'L'],
            ];

            foreach ($siswaData as $s) {
                Siswa::create($s);
            }
        }
    }
}
