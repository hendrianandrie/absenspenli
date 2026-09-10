<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor / Transkrip Hasil Belajar Siswa</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 10px;
        }
        .header-kop {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-kop h2 {
            margin: 0 0 2px 0;
            font-size: 16px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-kop h3 {
            margin: 0 0 4px 0;
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        .header-kop p {
            margin: 0;
            font-size: 9px;
            color: #64748b;
        }

        .bio-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .bio-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 11px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 15px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #94a3b8;
            padding: 6px 8px;
            font-size: 10px;
        }
        table.data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        table.data-table td.text-center {
            text-align: center;
        }

        .absen-box {
            width: 48%;
            border-collapse: collapse;
            float: left;
        }
        .absen-box th, .absen-box td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 10px;
        }
        .absen-box th {
            background-color: #334155;
            color: white;
            text-align: center;
        }

        .sig-box {
            width: 48%;
            float: right;
            text-align: center;
            font-size: 10px;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>

    <!-- KOP HEADER -->
    <div class="header-kop">
        <h2>PEMERINTAH KABUPATEN CIAMIS</h2>
        <h3>SMP NEGERI 5 CIAMIS</h3>
        <p>SI-KASEP (Sistem Informasi Rekap Absen dan Nilai SMP Negeri 5 Ciamis) — Transkrip Rapor Hasil Belajar Siswa</p>
    </div>

    <h4 style="text-align: center; margin: 0 0 12px 0; text-transform: uppercase; font-size: 12px; color: #1e3a8a;">
        LEMBAR RAPOR HASIL BELAJAR SISWA
    </h4>

    <!-- BIODATA SISWA -->
    <table class="bio-table">
        <tr>
            <td style="width: 15%; font-weight: bold;">Nama Siswa</td>
            <td style="width: 35%;">: {{ $siswa->nama }}</td>
            <td style="width: 15%; font-weight: bold;">Kelas</td>
            <td style="width: 35%;">: {{ $siswa->kelas }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">NIS / NISN</td>
            <td>: {{ $siswa->nis ?? '-' }}</td>
            <td style="font-weight: bold;">Jenis Kelamin</td>
            <td>: {{ $siswa->jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Sekolah</td>
            <td>: SMP Negeri 5 Ciamis</td>
            <td style="font-weight: bold;">Tahun Ajaran</td>
            <td>: {{ date('Y') }}/{{ date('Y') + 1 }}</td>
        </tr>
    </table>

    <!-- TABEL HASIL BELAJAR MAPEL -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 25%;">Mata Pelajaran</th>
                <th style="width: 8%;">KKM</th>
                <th style="width: 10%;">Nilai Akhir</th>
                <th style="width: 10%;">Predikat</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 28%;">Capaian Kompetensi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($raporMapel as $index => $r)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td style="font-weight: bold;">{{ $r['mapel']->nama_mapel }}</td>
                <td class="text-center">{{ $r['mapel']->kkm }}</td>
                <td class="text-center" style="font-weight: bold; color: {{ ($r['nilai_akhir'] && $r['nilai_akhir'] >= $r['mapel']->kkm) ? '#16a34a' : '#dc2626' }};">
                    {{ $r['nilai_akhir'] !== null ? number_format($r['nilai_akhir'], 1) : '-' }}
                </td>
                <td class="text-center" style="font-weight: bold;">{{ $r['predikat'] }}</td>
                <td class="text-center">
                    <span style="font-weight: bold; color: {{ $r['status'] === 'Perlu Bimbingan' ? '#dc2626' : '#16a34a' }};">
                        {{ $r['status'] }}
                    </span>
                </td>
                <td style="font-size: 9px; color: #334155;">{{ $r['deskripsi'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- CATATAN KEHADIRAN SISWA -->
    <div style="margin-bottom: 20px;">
        <table class="absen-box">
            <thead>
                <tr>
                    <th colspan="2">Rekap Kehadiran Siswa (Semester Ini)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Hadir</td>
                    <td style="font-weight: bold; color: #16a34a; text-align: center;">{{ $rekapAbsensi['hadir'] }} Hari</td>
                </tr>
                <tr>
                    <td>Sakit (S)</td>
                    <td style="font-weight: bold; color: #d97706; text-align: center;">{{ $rekapAbsensi['sakit'] }} Hari</td>
                </tr>
                <tr>
                    <td>Izin (I)</td>
                    <td style="font-weight: bold; color: #0284c7; text-align: center;">{{ $rekapAbsensi['izin'] }} Hari</td>
                </tr>
                <tr>
                    <td>Tanpa Keterangan (Alpha)</td>
                    <td style="font-weight: bold; color: #dc2626; text-align: center;">{{ $rekapAbsensi['alpha'] }} Hari</td>
                </tr>
            </tbody>
        </table>

        <div class="sig-box">
            <p style="margin: 0 0 45px 0;">
                Ciamis, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}<br>
                Wali Kelas / Guru Pembimbing
            </p>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                (_________________________)
            </p>
            <small style="color: #64748b;">NIP. ........................................</small>
        </div>
        <div class="clear"></div>
    </div>

    <div style="text-align: center; margin-top: 15px; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px;">
        Dicetak secara resmi dari SI-KASEP (Sistem Informasi Rekap Absen dan Nilai) SMP Negeri 5 Ciamis
    </div>

</body>
</html>
