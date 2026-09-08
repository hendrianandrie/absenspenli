<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Kehadiran</title>
    <style>
        body { font-family: sans-serif; }
        h3, h4 { text-align: center; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #555;
        }
        th, td {
            padding: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h3>Rekap Kehadiran Siswa</h3>
    <h4>Tanggal: {{ $tanggal }}</h4>

    <table>
        <thead>
            <tr>
                <th>Kelas</th>
                <th>Total Siswa</th>
                <th>Hadir</th>
                <th>Tidak Hadir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataKelas as $data)
            <tr>
                <td>{{ $data['kelas'] }}</td>
                <td>{{ $data['total'] }}</td>
                <td>{{ $data['hadir'] }}</td>
                <td>{{ $data['tidak_hadir'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top:40px; text-align:right;">Dicetak pada: {{ now()->format('d M Y, H:i') }}</p>
</body>
</html>
