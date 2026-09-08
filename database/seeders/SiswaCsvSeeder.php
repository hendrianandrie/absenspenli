<?php

namespace Database\Seeders;

use App\Models\Siswa;
use Illuminate\Database\Seeder;

class SiswaCsvSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/siswa_import.csv');
        if (! file_exists($path)) {
            $this->command->error("File CSV tidak ditemukan di: {$path}");

            return;
        }

        $file = fopen($path, 'r');
        // Read header
        fgetcsv($file, 1000, ';');

        $imported = 0;
        while (($row = fgetcsv($file, 1000, ';')) !== false) {
            if (count($row) >= 4) {
                $nis = trim($row[0]);
                $nama = trim($row[1]);
                $kelas = trim($row[2]);
                $jk = strtoupper(trim($row[3])) === 'P' ? 'P' : 'L';

                if (! empty($nama)) {
                    Siswa::updateOrCreate(
                        ['nis' => $nis],
                        [
                            'nama' => $nama,
                            'kelas' => $kelas,
                            'jenis_kelamin' => $jk,
                        ]
                    );
                    $imported++;
                }
            }
        }
        fclose($file);

        $this->command->info("Berhasil mengimpor {$imported} siswa SPENLI. Total siswa saat ini: ".Siswa::count());
    }
}
