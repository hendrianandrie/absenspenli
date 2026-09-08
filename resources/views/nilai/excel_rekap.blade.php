<table>
    <thead>
        <tr>
            <th colspan="{{ 5 + count($kegiatans) }}" style="font-weight: bold; font-size: 14px; text-align: center;">
                REKAPITULASI NILAI {{ strtoupper($mapel->nama_mapel) }} KELAS {{ $kelas }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ 5 + count($kegiatans) }}" style="text-align: center;">
                Kode Mapel: {{ $mapel->kode_mapel }} | KKM: {{ $mapel->kkm }} | Metode Perhitungan: Rata-Rata Murni
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th style="font-weight: bold; background-color: #dbeafe; border: 1px solid #000000; text-align: center;">No</th>
            <th style="font-weight: bold; background-color: #dbeafe; border: 1px solid #000000; text-align: center;">NIS</th>
            <th style="font-weight: bold; background-color: #dbeafe; border: 1px solid #000000; text-align: left;">Nama Siswa</th>
            @foreach($kegiatans as $k)
                <th style="font-weight: bold; background-color: #dbeafe; border: 1px solid #000000; text-align: center;">
                    {{ $k->nama_kegiatan }} ({{ $k->jenis }})
                </th>
            @endforeach
            <th style="font-weight: bold; background-color: #bfdbfe; border: 1px solid #000000; text-align: center;">Rata-Rata Murni</th>
            <th style="font-weight: bold; background-color: #dbeafe; border: 1px solid #000000; text-align: center;">Predikat</th>
            <th style="font-weight: bold; background-color: #dbeafe; border: 1px solid #000000; text-align: center;">Ketuntasan KKM</th>
        </tr>
    </thead>
    <tbody>
        @foreach($siswas as $idx => $siswa)
            @php
                $info = $rekap[$siswa->id] ?? ['scores' => [], 'rata_rata' => null, 'predikat' => '-'];
                $avg = $info['rata_rata'];
                $kkm = $mapel->kkm;
            @endphp
            <tr>
                <td style="border: 1px solid #000000; text-align: center;">{{ $idx + 1 }}</td>
                <td style="border: 1px solid #000000; text-align: center;">'{{ $siswa->nis }}</td>
                <td style="border: 1px solid #000000; text-align: left;">{{ $siswa->nama }}</td>
                @foreach($kegiatans as $k)
                    @php $v = $info['scores'][$k->id] ?? null; @endphp
                    <td style="border: 1px solid #000000; text-align: center;">{{ $v !== null ? $v : '-' }}</td>
                @endforeach
                <td style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #eff6ff;">
                    {{ $avg !== null ? $avg : '-' }}
                </td>
                <td style="border: 1px solid #000000; text-align: center;">{{ $info['predikat'] }}</td>
                <td style="border: 1px solid #000000; text-align: center; font-weight: bold; color: {{ ($avg !== null && $avg >= $kkm) ? '#15803d' : '#b91c1c' }};">
                    @if($avg !== null)
                        {{ $avg >= $kkm ? 'TUNTAS' : 'BELUM TUNTAS' }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
