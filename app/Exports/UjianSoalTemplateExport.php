<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UjianSoalTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $soals;

    public function __construct($soals = null)
    {
        $this->soals = $soals;
    }

    public function headings(): array
    {
        return [
            'NO',
            'PERTANYAAN',
            'OPSI_A',
            'OPSI_B',
            'OPSI_C',
            'OPSI_D',
            'KUNCI_JAWABAN',
            'BOBOT',
        ];
    }

    public function array(): array
    {
        if ($this->soals && count($this->soals) > 0) {
            $data = [];
            $no = 1;
            foreach ($this->soals as $soal) {
                $data[] = [
                    $no++,
                    $soal->pertanyaan,
                    $soal->opsi_a,
                    $soal->opsi_b,
                    $soal->opsi_c,
                    $soal->opsi_d,
                    strtoupper($soal->kunci_jawaban),
                    $soal->bobot ?: 1,
                ];
            }
            return $data;
        }

        // Contoh panduan pengisian
        return [
            [
                1,
                'Contoh: Berapakah hasil perkalian dari 25 x 4?',
                '100',
                '90',
                '80',
                '110',
                'A',
                1,
            ],
            [
                2,
                'Contoh: Lagu kebangsaan Republik Indonesia adalah...',
                'Halo-Halo Bandung',
                'Indonesia Raya',
                'Padamu Negeri',
                'Garuda Pancasila',
                'B',
                1,
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = max(3, $sheet->getHighestRow());

        // Header style
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'], // Deep Blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Border & Alignment
        $sheet->getStyle("A1:H{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G2:G{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H2:H{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
