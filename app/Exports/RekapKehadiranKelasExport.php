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
    protected $tanggalMulai;
    protected $tanggalSelesai;

    public function __construct($kelas, $waliKelas, int $bulan, int $tahun, string $namaBulan, array $data, ?string $tanggalMulai = null, ?string $tanggalSelesai = null)
    {
        $this->kelas = $kelas;
        $this->waliKelas = $waliKelas;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->namaBulan = $namaBulan;
        $this->data = $data;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
    }

    public function view(): View
    {
        return view('walikelas.excel_kehadiran', array_merge([
            'kelas' => $this->kelas,
            'waliKelas' => $this->waliKelas,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'namaBulan' => $this->namaBulan,
            'tanggalMulai' => $this->tanggalMulai,
            'tanggalSelesai' => $this->tanggalSelesai,
        ], $this->data));
    }
}
