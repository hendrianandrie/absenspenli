<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LegerNilaiExport implements FromView, ShouldAutoSize
{
    protected $kelas;
    protected $waliKelas;
    protected $data;

    public function __construct($kelas, $waliKelas, array $data)
    {
        $this->kelas = $kelas;
        $this->waliKelas = $waliKelas;
        $this->data = $data;
    }

    public function view(): View
    {
        return view('walikelas.excel_rekap', array_merge([
            'kelas' => $this->kelas,
            'waliKelas' => $this->waliKelas,
        ], $this->data));
    }
}
