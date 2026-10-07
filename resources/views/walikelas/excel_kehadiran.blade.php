@php
    $isSemua = ($kelas === 'Semua');
    $totalCols = $isSemua ? 12 : 11;
@endphp
<table>
    <thead>
        <!-- Judul Laporan -->
        <tr>
            <th colspan="{{ $totalCols }}" style="font-weight: bold; font-size: 14px; text-align: center; height: 28px;">
                REKAPITULASI KEHADIRAN SISWA BULANAN
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalCols }}" style="font-weight: bold; font-size: 13px; text-align: center; height: 24px;">
                SMP NEGERI 5 CIAMIS
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalCols }}" style="text-align: center; height: 20px;">
                Kelas: {{ $isSemua ? 'Semua Kelas (Seluruh Siswa)' : $kelas }} | Bulan: {{ $namaBulan }} {{ $tahun }} | Tahun Ajaran: {{ $waliKelas?->tahun_ajaran ?? date('Y') . '/' . (date('Y') + 1) }}
            </th>
        </tr>
        @if(!$isSemua)
        <tr>
            <th colspan="{{ $totalCols }}" style="text-align: center; height: 20px;">
                Wali Kelas: {{ $waliKelas?->user?->name ?? 'Belum Ditentukan' }} @if($waliKelas?->user?->nip) | NIP: '{{ $waliKelas?->user?->nip }} @endif
            </th>
        </tr>
        @endif
        <tr></tr>

        <!-- Header Tabel Kolom -->
        <tr>
            <th style="font-weight: bold; background-color: #0284c7; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">No</th>
            <th style="font-weight: bold; background-color: #0284c7; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">NIS</th>
            <th style="font-weight: bold; background-color: #0284c7; color: #ffffff; border: 1px solid #000000; text-align: left; vertical-align: middle;">Nama Siswa</th>
            @if($isSemua)
                <th style="font-weight: bold; background-color: #0284c7; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">Kelas</th>
            @endif
            <th style="font-weight: bold; background-color: #0284c7; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">L/P</th>
            <th style="font-weight: bold; background-color: #16a34a; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">Hadir (H)</th>
            <th style="font-weight: bold; background-color: #d97706; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">Sakit (S)</th>
            <th style="font-weight: bold; background-color: #0284c7; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">Izin (I)</th>
            <th style="font-weight: bold; background-color: #dc2626; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">Alpa (A)</th>
            <th style="font-weight: bold; background-color: #475569; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">Total Hari</th>
            <th style="font-weight: bold; background-color: #4338ca; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">% Hadir</th>
            <th style="font-weight: bold; background-color: #0284c7; color: #ffffff; border: 1px solid #000000; text-align: center; vertical-align: middle;">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($siswas as $idx => $s)
            @php
                $item = $rekapSiswa[$s->id] ?? [
                    'hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'total' => 0, 'persen' => 100
                ];
                $persen = $item['persen'];
                $ket = $persen >= 90 ? 'Sangat Baik' : ($persen >= 80 ? 'Baik' : ($persen >= 75 ? 'Cukup' : 'Perlu Perhatian'));
            @endphp
            <tr>
                <td style="border: 1px solid #000000; text-align: center;">{{ $idx + 1 }}</td>
                <td style="border: 1px solid #000000; text-align: center;">'{{ $s->nis }}</td>
                <td style="border: 1px solid #000000; text-align: left;">{{ $s->nama }}</td>
                @if($isSemua)
                    <td style="border: 1px solid #000000; text-align: center; font-weight: bold;">{{ $s->kelas }}</td>
                @endif
                <td style="border: 1px solid #000000; text-align: center;">{{ $s->jenis_kelamin }}</td>
                <td style="border: 1px solid #000000; text-align: center; font-weight: bold; background-color: #f0fdf4;">{{ $item['hadir'] }}</td>
                <td style="border: 1px solid #000000; text-align: center; background-color: #fffbeb;">{{ $item['sakit'] }}</td>
                <td style="border: 1px solid #000000; text-align: center; background-color: #eff6ff;">{{ $item['izin'] }}</td>
                <td style="border: 1px solid #000000; text-align: center; {{ $item['alpha'] > 0 ? 'background-color: #fef2f2; color: #b91c1c; font-weight: bold;' : '' }}">{{ $item['alpha'] }}</td>
                <td style="border: 1px solid #000000; text-align: center;">{{ $item['total'] }}</td>
                <td style="border: 1px solid #000000; text-align: center; font-weight: bold;">{{ $persen }}%</td>
                <td style="border: 1px solid #000000; text-align: center;">{{ $ket }}</td>
            </tr>
        @endforeach

        <!-- Baris Total & Ringkasan Kelas -->
        <tr style="font-weight: bold; background-color: #f8fafc;">
            <td colspan="{{ $isSemua ? 5 : 4 }}" style="border: 1px solid #000000; text-align: center; font-weight: bold;">
                TOTAL / RATA-RATA {{ $isSemua ? 'SEMUA KELAS' : 'KELAS' }}
            </td>
            <td style="border: 1px solid #000000; text-align: center; font-weight: bold; background-color: #dcfce7;">
                {{ $analytics['total_hadir'] }}
            </td>
            <td style="border: 1px solid #000000; text-align: center; font-weight: bold; background-color: #fef3c7;">
                {{ $analytics['total_sakit'] }}
            </td>
            <td style="border: 1px solid #000000; text-align: center; font-weight: bold; background-color: #e0f2fe;">
                {{ $analytics['total_izin'] }}
            </td>
            <td style="border: 1px solid #000000; text-align: center; font-weight: bold; background-color: #fee2e2;">
                {{ $analytics['total_alpha'] }}
            </td>
            <td style="border: 1px solid #000000; text-align: center; font-weight: bold;">
                {{ $analytics['total_hari_efektif'] }} hari
            </td>
            <td style="border: 1px solid #000000; text-align: center; font-weight: bold; background-color: #e0e7ff;">
                {{ $analytics['avg_persen'] }}%
            </td>
            <td style="border: 1px solid #000000; text-align: center;">-</td>
        </tr>
    </tbody>
</table>

<!-- Bagian Tanda Tangan -->
<table>
    <tbody>
        <tr></tr>
        <tr></tr>
        <tr>
            <td colspan="{{ $isSemua ? 4 : 3 }}" style="text-align: center;">
                Mengetahui,<br>
                Kepala SMP Negeri 5 Ciamis
            </td>
            <td colspan="4"></td>
            <td colspan="4" style="text-align: center;">
                Ciamis, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                {{ $isSemua ? 'Petugas / Administrator' : 'Wali Kelas ' . $kelas }}
            </td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <td colspan="{{ $isSemua ? 4 : 3 }}" style="text-align: center; font-weight: bold; text-decoration: underline;">
                ( .................................................... )
            </td>
            <td colspan="4"></td>
            <td colspan="4" style="text-align: center; font-weight: bold; text-decoration: underline;">
                {{ $isSemua ? (auth()->user()?->name ?? 'Administrator') : ($waliKelas?->user?->name ?? '( .................................................... )') }}
            </td>
        </tr>
        <tr>
            <td colspan="{{ $isSemua ? 4 : 3 }}" style="text-align: center;">
                NIP. .............................................
            </td>
            <td colspan="4"></td>
            <td colspan="4" style="text-align: center;">
                @if(!$isSemua && $waliKelas?->user?->nip)
                    NIP. '{{ $waliKelas?->user?->nip }}
                @elseif($isSemua && auth()->user()?->nip)
                    NIP. '{{ auth()->user()?->nip }}
                @else
                    NIP. -
                @endif
            </td>
        </tr>
    </tbody>
</table>
