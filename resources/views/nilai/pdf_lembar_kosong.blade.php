<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Penilaian & Presensi Physical Kelas {{ $kelas }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .header h3 { margin: 0; font-size: 14px; text-transform: uppercase; }
        .header p { margin: 2px 0 0; font-size: 11px; color: #555; }
        .info { width: 100%; margin-bottom: 10px; }
        .info td { font-size: 10px; padding: 2px 0; }
        .table-data { width: 100%; border-collapse: collapse; }
        .table-data th, .table-data td { border: 1px solid #333; padding: 5px 4px; text-align: center; font-size: 10px; }
        .table-data th { background-color: #f1f5f9; }
        .table-data td.text-start { text-align: left; }
        .empty-col { width: 45px; }
    </style>
</head>
<body>

    <div class="header">
        <h3>LEMBAR CATATAN NILAI & PRESENSI KELAS FISIK</h3>
        <p>SEKOLAH MENENGAH PERTAMA (SMP)</p>
    </div>

    <table class="info">
        <tr>
            <td width="15%"><strong>Mata Pelajaran</strong></td>
            <td width="35%">: {{ $mapel->nama_mapel ?? '........................' }}</td>
            <td width="15%"><strong>Kelas / Semester</strong></td>
            <td width="35%">: Kelas {{ $kelas }} / Genap</td>
        </tr>
        <tr>
            <td><strong>Guru Pengampu</strong></td>
            <td>: ....................................................</td>
            <td><strong>Tahun Ajaran</strong></td>
            <td>: 2025/2026</td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th width="4%" rowspan="2">No</th>
                <th width="10%" rowspan="2">NIS</th>
                <th class="text-start" width="28%" rowspan="2">Nama Siswa</th>
                <th colspan="6">Catatan Nilai / Absensi Harian (Tanggal & Kegiatan)</th>
                <th width="15%" rowspan="2">Catatan Guru</th>
            </tr>
            <tr>
                <th class="empty-col">/</th>
                <th class="empty-col">/</th>
                <th class="empty-col">/</th>
                <th class="empty-col">/</th>
                <th class="empty-col">/</th>
                <th class="empty-col">/</th>
            </tr>
        </thead>
        <tbody>
            @foreach($siswas as $idx => $siswa)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $siswa->nis }}</td>
                    <td class="text-start">{{ $siswa->nama }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
