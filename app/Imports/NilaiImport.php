<?php

namespace App\Imports;

use App\Models\Kegiatan;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Services\NotificationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NilaiImport implements ToCollection, WithHeadingRow, WithCustomCsvSettings
{
    protected Kegiatan $kegiatan;
    protected string $kelas;
    protected $kkm;
    protected string $mapelName;
    protected string $namaGuru;

    public int $successCount = 0;
    public int $skippedCount = 0;
    public int $tuntasCount = 0;
    public int $remedialCount = 0;
    public array $unmatched = [];
    public array $errors = [];

    public function __construct(Kegiatan $kegiatan)
    {
        $this->kegiatan = $kegiatan;
        $this->kelas = (string) $kegiatan->kelas;

        $mapel = $kegiatan->mataPelajaran;
        $this->mapelName = $mapel ? $mapel->nama_mapel : 'Mata Pelajaran';
        $this->kkm = $mapel->kkm ?? 75;

        $currentUser = Auth::user();
        $this->namaGuru = $currentUser ? ($currentUser->name ?: $currentUser->username) : 'Guru Pengampu';
    }

    public function collection(Collection $rows)
    {
        // Cache data siswa di kelas yang bersangkutan untuk performa cepat
        $siswas = Siswa::where('kelas', $this->kelas)->get();
        $siswaByNis = [];
        $siswaByCleanNis = [];
        $siswaByName = [];

        foreach ($siswas as $s) {
            $rawNis = trim((string) $s->nis);
            $cleanNis = ltrim($rawNis, '0');
            $cleanName = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string) $s->nama)));

            $siswaByNis[$rawNis] = $s;
            if ($cleanNis !== '') {
                $siswaByCleanNis[$cleanNis] = $s;
            }
            if ($cleanName !== '') {
                $siswaByName[$cleanName] = $s;
            }
        }

        foreach ($rows as $index => $row) {
            $rowArr = $row instanceof Collection ? $row->toArray() : (array) $row;

            // Bersihkan dan normalisasi keys
            $cleanRow = [];
            foreach ($rowArr as $key => $val) {
                $k = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string) $key)));
                $cleanRow[$k] = is_string($val) ? trim($val) : $val;
            }

            // 1. Ekstraksi NISN / NIS
            $rawNisInput = $cleanRow['nisn'] 
                ?? $cleanRow['nis'] 
                ?? $cleanRow['noinduk'] 
                ?? $cleanRow['nomorinduk'] 
                ?? $cleanRow['nissiswa']
                ?? (isset($rowArr[1]) ? trim((string) $rowArr[1]) : null);

            $rawNisStr = is_string($rawNisInput) ? trim(ltrim($rawNisInput, "'")) : (string) $rawNisInput;

            // 2. Ekstraksi Nama
            $namaInput = $cleanRow['namasiswa'] 
                ?? $cleanRow['nama'] 
                ?? $cleanRow['namalengkap'] 
                ?? $cleanRow['siswa'] 
                ?? (isset($rowArr[2]) ? trim((string) $rowArr[2]) : '');

            // 3. Ekstraksi Nilai
            $nilaiInput = $cleanRow['nilai'] 
                ?? $cleanRow['nilai0100'] 
                ?? $cleanRow['skor'] 
                ?? $cleanRow['score'] 
                ?? $cleanRow['angka']
                ?? (isset($rowArr[3]) ? $rowArr[3] : null);

            // Abaikan baris kosong total atau baris yang mengulang header
            if (empty($rawNisStr) && empty($namaInput) && ($nilaiInput === null || $nilaiInput === '')) {
                continue;
            }

            $namaUpper = strtoupper(trim((string) $namaInput));
            if ($namaUpper === 'NAMA' || $namaUpper === 'NAMA SISWA' || $namaUpper === 'NAMA_SISWA') {
                continue;
            }

            // 4. Cocokkan Siswa di kelas terkait
            $matchedSiswa = null;
            if (!empty($rawNisStr)) {
                if (isset($siswaByNis[$rawNisStr])) {
                    $matchedSiswa = $siswaByNis[$rawNisStr];
                } else {
                    $cleanNisSearch = ltrim($rawNisStr, '0');
                    if (isset($siswaByCleanNis[$cleanNisSearch])) {
                        $matchedSiswa = $siswaByCleanNis[$cleanNisSearch];
                    } else {
                        // Coba dengan padding 10 digit (format NISN standar)
                        $paddedNis = str_pad($rawNisStr, 10, '0', STR_PAD_LEFT);
                        if (isset($siswaByNis[$paddedNis])) {
                            $matchedSiswa = $siswaByNis[$paddedNis];
                        }
                    }
                }
            }

            // Jika belum cocok dengan NIS, coba cocokkan dengan nama
            if (!$matchedSiswa && !empty($namaInput)) {
                $cleanSearchName = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string) $namaInput)));
                if (isset($siswaByName[$cleanSearchName])) {
                    $matchedSiswa = $siswaByName[$cleanSearchName];
                }
            }

            if (!$matchedSiswa) {
                $this->unmatched[] = [
                    'baris' => $index + 2,
                    'nisn' => $rawNisStr ?: '-',
                    'nama' => $namaInput ?: '-',
                    'alasan' => "Siswa tidak ditemukan di kelas {$this->kelas}",
                ];
                $this->skippedCount++;
                continue;
            }

            // 5. Validasi & Parsing Nilai
            if ($nilaiInput === null || $nilaiInput === '' || $nilaiInput === '-') {
                // Nilai kosong (misal belum mengikuti susulan) -> lewati atau biarkan belum dinilai
                $this->skippedCount++;
                continue;
            }

            // Konversi koma desimal ke titik (misal 85,5 -> 85.5)
            if (is_string($nilaiInput)) {
                $nilaiInput = str_replace(',', '.', trim($nilaiInput));
            }

            if (!is_numeric($nilaiInput)) {
                $this->errors[] = [
                    'baris' => $index + 2,
                    'siswa' => $matchedSiswa->nama,
                    'alasan' => "Format nilai tidak valid ('{$nilaiInput}')",
                ];
                $this->skippedCount++;
                continue;
            }

            $score = min(100, max(0, floatval($nilaiInput)));

            // 6. Simpan / Perbarui Nilai
            Nilai::updateOrCreate(
                [
                    'kegiatan_id' => $this->kegiatan->id,
                    'siswa_id' => $matchedSiswa->id,
                ],
                [
                    'nilai' => $score,
                ]
            );

            $this->successCount++;
            if ($score >= $this->kkm) {
                $this->tuntasCount++;
            } else {
                $this->remedialCount++;
            }

            // 7. Notifikasi Siswa
            try {
                $statusKetuntasan = $score >= $this->kkm ? 'Tuntas' : 'Perlu Remedial';
                NotificationService::sendToSiswa(
                    $matchedSiswa->id,
                    "🔔 Nilai Kegiatan: {$this->mapelName}",
                    "Nilai {$this->kegiatan->nama_kegiatan} Ananda {$matchedSiswa->nama} telah dimasukkan oleh {$this->namaGuru}: {$score} ({$statusKetuntasan}). KKM: {$this->kkm}.",
                    [
                        'type' => 'nilai',
                        'kegiatan_id' => $this->kegiatan->id,
                        'siswa_id' => $matchedSiswa->id,
                        'mapel' => $this->mapelName,
                        'nilai' => $score,
                        'guru' => $this->namaGuru,
                    ]
                );
            } catch (\Throwable $e) {
                // Abaikan jika notifikasi background gagal
            }
        }
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'enclosure' => '"',
            'input_encoding' => 'UTF-8',
        ];
    }
}

