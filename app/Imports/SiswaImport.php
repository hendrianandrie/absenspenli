<?php

namespace App\Imports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToModel, WithCustomCsvSettings, WithHeadingRow
{
    private static $usedNis = [];

    private static $autoIndex = 1;

    public function model(array $row)
    {
        // Normalisasi kunci array (lowercase dan hapus karakter khusus)
        $cleanRow = [];
        foreach ($row as $key => $val) {
            $k = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string) $key)));
            $cleanRow[$k] = is_string($val) ? trim($val) : $val;
        }

        // 1. Deteksi Nama Siswa
        $nama = $cleanRow['nama'] ?? $cleanRow['namasiswa'] ?? $cleanRow['namalengkap'] ?? $cleanRow['siswa'] ?? null;
        if (! $nama && isset($row[1])) {
            $nama = trim((string) $row[1]);
        }

        // Abaikan jika Nama kosong atau merupakan header tabel yang terikut
        if (empty($nama)) {
            return null;
        }
        $namaUpper = strtoupper(trim((string) $nama));
        if ($namaUpper === 'NAMA' || $namaUpper === 'NAMA SISWA' || $namaUpper === 'NAMA LENGKAP') {
            return null;
        }

        // 2. Deteksi Kelas
        $kelas = $cleanRow['kelas'] ?? $cleanRow['kls'] ?? $cleanRow['rombel'] ?? null;
        if (! $kelas && isset($row[2])) {
            $kelas = trim((string) $row[2]);
        }
        $kelasStr = ! empty($kelas) ? trim((string) $kelas) : '9A';

        // 3. Deteksi Jenis Kelamin
        $jkRaw = $cleanRow['jeniskelamin'] ?? $cleanRow['jk'] ?? $cleanRow['jklp'] ?? $cleanRow['lp'] ?? $cleanRow['sex'] ?? $cleanRow['gender'] ?? null;
        if (! $jkRaw && isset($row[3])) {
            $jkRaw = trim((string) $row[3]);
        }
        $jkUpper = strtoupper(trim((string) $jkRaw));
        $jenisKelamin = 'L';
        if (str_contains($jkUpper, 'P') || str_contains($jkUpper, 'WANITA') || str_contains($jkUpper, 'PEREMPUAN')) {
            $jenisKelamin = 'P';
        }

        // 4. Deteksi NIS
        $nis = $cleanRow['nis'] ?? $cleanRow['noinduk'] ?? $cleanRow['nomorinduk'] ?? null;
        if (! $nis && isset($row[0]) && is_numeric($row[0])) {
            $nis = trim((string) $row[0]);
        }

        $nisStr = ! empty($nis) ? trim((string) $nis) : null;

        // Jika NIS kosong atau bernilai sama untuk semua orang (seperti '1234'), berikan NIS unik per siswa
        if (empty($nisStr) || strtolower($nisStr) === 'nis' || strtolower($nisStr) === 'no') {
            $nisStr = $this->generateUniqueNis();
        } else {
            $existingSiswa = Siswa::where('nis', $nisStr)->first();
            if (isset(self::$usedNis[$nisStr]) || ($existingSiswa && strtolower(trim($existingSiswa->nama)) !== strtolower(trim((string) $nama)))) {
                $nisStr = $this->generateUniqueNis($nisStr);
            }
        }

        self::$usedNis[$nisStr] = true;

        // Cek jika siswa dengan NIS & Nama yang persis sama sudah ada
        $existing = Siswa::where('nis', $nisStr)->first();
        if ($existing) {
            $existing->update([
                'nama' => trim((string) $nama),
                'kelas' => $kelasStr,
                'jenis_kelamin' => $jenisKelamin,
            ]);

            return null;
        }

        // Buat record siswa baru
        return new Siswa([
            'nis' => $nisStr,
            'nama' => trim((string) $nama),
            'kelas' => $kelasStr,
            'jenis_kelamin' => $jenisKelamin,
        ]);
    }

    private function generateUniqueNis($baseNis = null)
    {
        if (self::$autoIndex === 1) {
            self::$autoIndex = 1000 + Siswa::count() + 1;
        }

        do {
            if ($baseNis && is_numeric($baseNis)) {
                $candidate = $baseNis.sprintf('%03d', self::$autoIndex++);
            } else {
                $candidate = (string) (8000 + (self::$autoIndex++));
            }
        } while (Siswa::where('nis', $candidate)->exists() || isset(self::$usedNis[$candidate]));

        return $candidate;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
        ];
    }
}
