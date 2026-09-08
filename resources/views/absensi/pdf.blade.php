<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi - {{ $tanggalMulai }} s.d {{ $tanggalSampai }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0 0; font-size: 11px; color: #555; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-data th, .table-data td { border: 1px solid #444; padding: 5px; text-align: center; }
        .table-data th { background-color: #f2f2f2; font-weight: bold; }
        .table-data td.text-start { text-align: left; }
        .summary-table { width: 50%; margin: 15px auto 0; border-collapse: collapse; }
        .summary-table th, .summary-table td { border: 1px solid #444; padding: 5px; text-align: center; }
        .footer { margin-top: 25px; float: right; width: 200px; text-align: center; font-size: 11px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>REKAPITULASI ABSENSI SISWA</h2>
        <p>SEKOLAH MENENGAH PERTAMA (SMP)</p>
    </div>

    <table style="width: 100%; margin-bottom: 10px;">
        <tr>
            <td width="15%"><strong>Periode</strong></td>
            <td width="45%">: {{ \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') }} s.d {{ \Carbon\Carbon::parse($tanggalSampai)->format('d/m/Y') }}</td>
            <td width="15%"><strong>Filter Kelas</strong></td>
            <td width="25%">: {{ $kelas ? 'Kelas '.$kelas : 'Semua Kelas' }}</td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th class="text-start">Nama Siswa</th>
                <th width="12%">Kelas</th>
                <th width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($absensis as $index => $absen)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($absen->tanggal)->format('d/m/Y') }}</td>
                    <td class="text-start">{{ $absen->siswa->nama ?? '-' }}</td>
                    <td>{{ $absen->siswa->kelas ?? '-' }}</td>
                    <td>{{ $absen->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada data absensi pada rentang tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary-table">
        <thead>
            <tr><th colspan="2">Total Kehadiran Periode Ini</th></tr>
        </thead>
        <tbody>
            <tr><td>Hadir</td><td style="font-weight: bold; color: green;">{{ $totalHadir }}</td></tr>
            <tr><td>Sakit</td><td style="font-weight: bold; color: blue;">{{ $totalSakit }}</td></tr>
            <tr><td>Izin</td><td style="font-weight: bold; color: orange;">{{ $totalIzin }}</td></tr>
            <tr><td>Alpha</td><td style="font-weight: bold; color: red;">{{ $totalAlpha }}</td></tr>
        </tbody>
    </table>

    <div class="footer">
        <p>......, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        <p><strong>Guru Pengampu</strong></p>
        <br><br><br>
        <p><strong><u>________________________</u></strong></p>
    </div>

</body>
</html>
