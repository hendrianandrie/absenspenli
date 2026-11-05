<?php

namespace App\Imports;

use App\Models\Ujian;
use App\Models\UjianSoal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UjianSoalImport implements ToCollection, WithHeadingRow
{
    protected $ujian;
    protected $importedCount = 0;
    protected $skippedCount = 0;

    public function __construct(Ujian $ujian)
    {
        $this->ujian = $ujian;
    }

    public function collection(Collection $rows)
    {
        $lastNomor = $this->ujian->soals()->max('nomor_urut') ?? 0;

        foreach ($rows as $row) {
            // Normalisasi key array
            $data = [];
            foreach ($row as $k => $v) {
                $cleanKey = strtolower(trim((string) $k));
                $data[$cleanKey] = is_string($v) ? trim($v) : $v;
            }

            $pertanyaan = $data['pertanyaan'] ?? $data['soal'] ?? null;
            $opsiA = $data['opsi_a'] ?? $data['a'] ?? null;
            $opsiB = $data['opsi_b'] ?? $data['b'] ?? null;
            $opsiC = $data['opsi_c'] ?? $data['c'] ?? null;
            $opsiD = $data['opsi_d'] ?? $data['d'] ?? null;
            $kunci = strtoupper((string) ($data['kunci_jawaban'] ?? $data['kunci'] ?? ''));
            $bobot = isset($data['bobot']) && is_numeric($data['bobot']) ? (float) $data['bobot'] : 1.0;

            // Validasi baris
            if (empty($pertanyaan) || empty($opsiA) || empty($opsiB) || empty($opsiC) || empty($opsiD)) {
                $this->skippedCount++;
                continue;
            }

            if (!in_array($kunci, ['A', 'B', 'C', 'D'])) {
                $kunci = 'A'; // fallback jika guru salah ketik
            }

            $lastNomor++;

            UjianSoal::create([
                'ujian_id' => $this->ujian->id,
                'nomor_urut' => $lastNomor,
                'pertanyaan' => $pertanyaan,
                'opsi_a' => (string) $opsiA,
                'opsi_b' => (string) $opsiB,
                'opsi_c' => (string) $opsiC,
                'opsi_d' => (string) $opsiD,
                'kunci_jawaban' => $kunci,
                'bobot' => max(0.1, $bobot),
            ]);

            $this->importedCount++;
        }
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}

