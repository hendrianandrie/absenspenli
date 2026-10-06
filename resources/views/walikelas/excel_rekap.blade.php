@php
    $mapelCount = count($mapels);
    $totalCols = 5 + $mapelCount + 2 + 1; // No, NIS, NISN, Nama, L/P + Mapels + Jml, Rata + Ket
@endphp
<table>
    <thead>
        <!-- Judul Laporan -->
        <tr>
            <th colspan="{{ $totalCols }}" style="font-weight: bold; font-size: 14px; text-align: center; height: 28px;">
                REKAPITULASI NILAI SISWA PER MATA PELAJARAN (LEGER NILAI)
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalCols }}" style="font-weight: bold; font-size: 13px; text-align: center; height: 24px;">
                SMP NEGERI 5 CIAMIS
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalCols }}" style="text-align: center; height: 20px;">
                Kelas: {{ $kelas }} | Tingkat: {{ $tingkat }} | Semester / Tahun Ajaran: {{ $waliKelas->tahun_ajaran ?? '2025/2026' }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalCols }}" style="text-align: center; height: 20px;">
                Wali Kelas: {{ optional($waliKelas->user)->name ?? 'Belum Ditentukan' }} @if(optional($waliKelas->user)->nip) | NIP: '{{ optional($waliKelas->user)->nip }} @endif
            </th>
        </tr>
        <tr></tr>

        <!-- Header Tabel Baris 1 -->
        <tr>
            <th rowspan="2" style="font-weight: bold; background-color: #0284c7; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">No</th>
            <th rowspan="2" style="font-weight: bold; background-color: #0284c7; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">NIS</th>
            <th rowspan="2" style="font-weight: bold; background-color: #0284c7; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">NISN</th>
            <th rowspan="2" style="font-weight: bold; background-color: #0284c7; color: #ffffff; border: 1px solid #000000; text-align: left; vertical-align: middle;">Nama Siswa</th>
            <th rowspan="2" style="font-weight: bold; background-color: #0284c7; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">L/P</th>
            
            <!-- Kolom Mapel Grup -->
            <th colspan="{{ $mapelCount }}" style="font-weight: bold; background-color: #0369a1; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">
                MATA PELAJARAN (NILAI AKHIR BERBOBOT)
            </th>

            <!-- Kolom Rekapitulasi Nilai -->
            <th colspan="2" style="font-weight: bold; background-color: #4338ca; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">
                REKAP NILAI
            </th>

            <th rowspan="2" style="font-weight: bold; background-color: #0284c7; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">Keterangan</th>
        </tr>

        <!-- Header Tabel Baris 2 (Detail Mapel & Sub-Kolom) -->
        <tr>
            @foreach($mapels as $m)
                <th style="font-weight: bold; background-color: #e0f2fe; border: 1px solid #000000; text-align: center; font-size: 10px;">
                    {{ $m->nama_mapel }}<br>(KKM: {{ $m->kkm ?? 75 }})
                </th>
            @endforeach

            <!-- Sub Rekap Nilai -->
            <th style="font-weight: bold; background-color: #e0e7ff; border: 1px solid #000000; text-align: center;">Jumlah</th>
            <th style="font-weight: bold; background-color: #e0e7ff; border: 1px solid #000000; text-align: center;">Rata-Rata</th>
        </tr>
    </thead>
    <tbody>
        @php
            $columnScores = [];
            foreach($mapels as $m) {
                $columnScores[$m->id] = [];
            }
            $allOverallAvgs = [];
        @endphp

        @foreach($siswas as $idx => $s)
            @php
                $rek = $rekapSiswa[$s->id] ?? null;
                $overall = $rek['overall_avg'] ?? null;
                $totalNilai = $rek['total_nilai'] ?? null;
                if ($overall !== null) {
                    $allOverallAvgs[] = $overall;
                }
            @endphp
            <tr>
                <td style="border: 1px solid #000000; text-align: center;">{{ $idx + 1 }}</td>
                <td style="border: 1px solid #000000; text-align: center;">'{{ $s->nis }}</td>
                <td style="border: 1px solid #000000; text-align: center;">'{{ $s->nisn }}</td>
                <td style="border: 1px solid #000000; text-align: left;">{{ strtoupper($s->nama) }}</td>
                <td style="border: 1px solid #000000; text-align: center;">{{ $s->jenis_kelamin }}</td>

                <!-- Nilai Per Mapel -->
                @foreach($mapels as $m)
                    @php
                        $score = $rek['scores'][$m->id] ?? null;
                        $kkm = $m->kkm ?? 75;
                        if ($score !== null) {
                            $columnScores[$m->id][] = $score;
                        }
                    @endphp
                    <td style="border: 1px solid #000000; text-align: center; {{ ($score !== null && $score < $kkm) ? 'color: #dc2626; font-weight: bold;' : '' }}">
                        {{ $score !== null ? number_format($score, 1) : '-' }}
                    </td>
                @endforeach

                <!-- Jumlah & Rata-Rata -->
                <td style="border: 1px solid #000000; text-align: center; font-weight: bold; background-color: #f8fafc;">
                    {{ $totalNilai !== null ? number_format($totalNilai, 1) : '-' }}
                </td>
                <td style="border: 1px solid #000000; text-align: center; font-weight: bold; background-color: #f1f5f9;">
                    {{ $overall !== null ? number_format($overall, 1) : '-' }}
                </td>

                <!-- Keterangan -->
                <td style="border: 1px solid #000000; text-align: center;">
                    @if($overall !== null)
                        {{ $overall >= 75 ? 'TUNTAS' : 'BELUM TUNTAS' }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach

        <!-- Baris Rata-Rata Kelas -->
        <tr>
            <td colspan="5" style="font-weight: bold; text-align: right; border: 1px solid #000000; background-color: #f3f4f6;">RATA-RATA KELAS:</td>
            @foreach($mapels as $m)
                @php
                    $mScores = $columnScores[$m->id] ?? [];
                    $mAvg = count($mScores) > 0 ? round(array_sum($mScores) / count($mScores), 1) : null;
                @endphp
                <td style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #f3f4f6;">
                    {{ $mAvg !== null ? number_format($mAvg, 1) : '-' }}
                </td>
            @endforeach
            <td style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #e2e8f0;">-</td>
            <td style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #e2e8f0;">
                {{ count($allOverallAvgs) > 0 ? number_format(array_sum($allOverallAvgs) / count($allOverallAvgs), 1) : '-' }}
            </td>
            <td style="border: 1px solid #000000; background-color: #f3f4f6;"></td>
        </tr>

        <!-- Baris Nilai Tertinggi -->
        <tr>
            <td colspan="5" style="font-weight: bold; text-align: right; border: 1px solid #000000; background-color: #f9fafb;">NILAI TERTINGGI:</td>
            @foreach($mapels as $m)
                @php
                    $mScores = $columnScores[$m->id] ?? [];
                    $mMax = count($mScores) > 0 ? max($mScores) : null;
                @endphp
                <td style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #f9fafb;">
                    {{ $mMax !== null ? number_format($mMax, 1) : '-' }}
                </td>
            @endforeach
            <td style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #f9fafb;">-</td>
            <td style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #f9fafb;">
                {{ count($allOverallAvgs) > 0 ? number_format(max($allOverallAvgs), 1) : '-' }}
            </td>
            <td style="border: 1px solid #000000; background-color: #f9fafb;"></td>
        </tr>

        <!-- Baris Nilai Terendah -->
        <tr>
            <td colspan="5" style="font-weight: bold; text-align: right; border: 1px solid #000000; background-color: #f9fafb;">NILAI TERENDAH:</td>
            @foreach($mapels as $m)
                @php
                    $mScores = $columnScores[$m->id] ?? [];
                    $mMin = count($mScores) > 0 ? min($mScores) : null;
                @endphp
                <td style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #f9fafb;">
                    {{ $mMin !== null ? number_format($mMin, 1) : '-' }}
                </td>
            @endforeach
            <td style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #f9fafb;">-</td>
            <td style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #f9fafb;">
                {{ count($allOverallAvgs) > 0 ? number_format(min($allOverallAvgs), 1) : '-' }}
            </td>
            <td style="border: 1px solid #000000; background-color: #f9fafb;"></td>
        </tr>
    </tbody>
</table>

<!-- Kolom Tanda Tangan -->
<table>
    <tr></tr>
    <tr>
        <td colspan="4" style="text-align: center;">
            Mengetahui,<br>
            Kepala SMP Negeri 5 Ciamis<br><br><br><br>
            <strong><u>SUPRIATNA, M.Pd.</u></strong><br>
            NIP. 196805121998021004
        </td>
        <td colspan="{{ max(1, $totalCols - 8) }}"></td>
        <td colspan="4" style="text-align: center;">
            Ciamis, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            Wali Kelas {{ $kelas }}<br><br><br><br>
            <strong><u>{{ optional($waliKelas->user)->name ?? '....................................' }}</u></strong><br>
            NIP. {{ optional($waliKelas->user)->nip ?? '....................................' }}
        </td>
    </tr>
</table>
