<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RekapKehadiranKelasExport implements FromView, ShouldAutoSize
{
    protected $kelas;
    protected $waliKelas;
    protected $bulan;
    protected $tahun;
    protected $namaBulan;
    protected $data;

    public function __construct($kelas, $waliKelas, int $bulan, int $tahun, string $namaBulan, array $data)
    {
        $this->kelas = $kelas;
        $this->waliKelas = $waliKelas;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->namaBulan = $namaBulan;
        $this->data = $data;
    }

    public function view(): View
    {
        return view('walikelas.excel_kehadiran', array_merge([
            'kelas' => $this->kelas,
            'waliKelas' => $this->waliKelas,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'namaBulan' => $this->namaBulan,
        ], $this->data));
    }
}
