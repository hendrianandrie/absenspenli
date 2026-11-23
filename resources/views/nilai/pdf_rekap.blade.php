<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Nilai {{ $mapel->nama_mapel }} Kelas {{ $kelas }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0 0; font-size: 12px; color: #555; }
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { padding: 3px 0; font-size: 11px; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-data th, .table-data td { border: 1px solid #444; padding: 6px 4px; text-align: center; }
        .table-data th { background-color: #e2e8f0; font-weight: bold; }
        .table-data td.text-start { text-align: left; }
        .footer { margin-top: 30px; float: right; width: 220px; text-align: center; font-size: 11px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>REKAPITULASI PENILAIAN MATA PELAJARAN</h2>
        <p>SEKOLAH MENENGAH PERTAMA (SMP)</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Mata Pelajaran</strong></td>
            <td width="35%">: {{ $mapel->nama_mapel }} ({{ $mapel->kode_mapel }})</td>
            <td width="15%"><strong>Kelas</strong></td>
            <td width="35%">: {{ $kelas }}</td>
        </tr>
        <tr>
            <td><strong>Semester</strong></td>
            <td>: {{ $semester ?? 1 }} ({{ ($semester ?? 1) == 1 ? 'Ganjil' : 'Genap' }})</td>
            <td><strong>Tahun Ajaran</strong></td>
            <td>: {{ $tahunAjaran ?? '2026/2027' }}</td>
        </tr>
        <tr>
            <td><strong>KKM</strong></td>
            <td>: {{ $mapel->kkm }}</td>
            <td><strong>Bobot Penilaian</strong></td>
            <td>: Tugas ({{ $mapel->bobot_tugas ?? 20 }}%), UH ({{ $mapel->bobot_uh ?? 30 }}%), UTS ({{ $mapel->bobot_uts ?? 25 }}%), UAS ({{ $mapel->bobot_uas ?? 25 }}%)</td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="10%">NIS</th>
                <th class="text-start" width="22%">Nama Siswa</th>
                @foreach($kegiatans as $k)
                    <th>
                        {{ $k->nama_kegiatan }}<br>
                        <small style="font-weight: normal; font-size: 9px;">({{ $k->jenis }})</small>
                    </th>
                @endforeach
                <th width="10%">Rata-Rata Murni</th>
                <th width="11%" style="background-color: #dbeafe;">Nilai Akhir Rapor</th>
                <th width="10%">Predikat</th>
                <th width="13%">Status Ketuntasan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($siswas as $idx => $siswa)
                @php
                    $info = $rekap[$siswa->id] ?? ['scores' => [], 'rata_rata' => null, 'nilai_akhir' => null, 'predikat' => '-', 'status_ketuntasan' => '-'];
                    $avg = $info['rata_rata'];
                    $nilaiAkhir = $info['nilai_akhir'];
                    $kkm = $mapel->kkm ?? 75;
                    $finalScore = $nilaiAkhir ?? $avg;
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $siswa->nis }}</td>
                    <td class="text-start">{{ $siswa->nama }}</td>
                    @foreach($kegiatans as $k)
                        @php $v = $info['scores'][$k->id] ?? null; @endphp
                        <td>{{ $v !== null ? $v : '-' }}</td>
                    @endforeach
                    <td style="font-weight: bold;">{{ $avg !== null ? $avg : '-' }}</td>
                    <td style="font-weight: bold; background-color: #f1f5f9; color: {{ ($finalScore !== null && $finalScore < $kkm) ? '#b91c1c' : '#1e40af' }};">
                        {{ $nilaiAkhir !== null ? $nilaiAkhir : '-' }}
                    </td>
                    <td>{{ $info['predikat'] }}</td>
                    <td>
                        @if($finalScore !== null)
                            @if($finalScore >= $kkm)
                                <strong style="color: #15803d;">TUNTAS</strong>
                            @else
                                <span style="color: #b91c1c; font-weight: bold;">BELUM TUNTAS</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>......, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        <p>Guru Mata Pelajaran,</p>
        <br><br><br>
        <p><strong><u>________________________</u></strong></p>
    </div>

</body>
</html>
