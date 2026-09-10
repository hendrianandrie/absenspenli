<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Kehadiran Siswa</title>
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
        
        .badge-date {
            background-color: #e2e8f0;
            color: #1e293b;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }

        .summary-box {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .summary-box td {
            width: 25%;
            padding: 6px 8px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            text-align: center;
        }
        .summary-box .label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
        }
        .summary-box .val {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }
        .val-hadir { color: #16a34a !important; }
        .val-sakit { color: #d97706 !important; }
        .val-izin { color: #0284c7 !important; }
        .val-alpha { color: #dc2626 !important; }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            text-align: center;
        }
        table.data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
        }
        table.data-table th.sub-th {
            background-color: #1e40af;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        table.data-table td.text-left {
            text-align: left;
        }
        table.data-table tfoot td {
            background-color: #e2e8f0;
            font-weight: bold;
            border-top: 2px solid #0f172a;
        }

        .footer-sig {
            margin-top: 25px;
            width: 100%;
        }
        .footer-sig td {
            vertical-align: top;
        }
        .note-box {
            font-size: 9px;
            color: #475569;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <!-- KOP HEADER -->
    <div class="header-kop">
        <h2>PEMERINTAH KABUPATEN CIAMIS</h2>
        <h3>SMP NEGERI 5 CIAMIS</h3>
        <p>SI-KASEP (Sistem Informasi Rekap Absen SMP Negeri 5 Ciamis) — Laporan Kehadiran Siswa Per Kelas</p>
    </div>

    <!-- TANGGAL REKAP -->
    <div style="text-align: justify;">
        <span class="badge-date">
            TANGGAL REKAP: {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('l, d F Y') }}
        </span>
    </div>

    <!-- RINGKASAN STATISTIK HARI INI -->
    @if(isset($summary))
    <table class="summary-box">
        <tr>
            <td>
                <span class="label">Total Siswa</span>
                <div class="val">{{ $summary['total_siswa'] }} Siswa</div>
            </td>
            <td>
                <span class="label">Total Hadir</span>
                <div class="val val-hadir">{{ $summary['hadir'] }} <small style="font-size:10px;">({{ $summary['persen'] }}%)</small></div>
            </td>
            <td>
                <span class="label">Rincian Tidak Hadir</span>
                <div class="val" style="font-size: 11px;">
                    <span class="val-sakit">S: {{ $summary['sakit'] }}</span> | 
                    <span class="val-izin">I: {{ $summary['izin'] }}</span> | 
                    <span class="val-alpha">A: {{ $summary['alpha'] }}</span>
                </div>
            </td>
            <td>
                <span class="label">Total Tidak Hadir</span>
                <div class="val val-alpha">{{ $summary['tidak_hadir'] }} Siswa</div>
            </td>
        </tr>
    </table>
    @endif

    <!-- TABEL UTAMA KEHADIRAN PER KELAS -->
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 4%;">No</th>
                <th rowspan="2" style="width: 14%;">Kelas</th>
                <th rowspan="2" style="width: 12%;">Total Siswa</th>
                <th rowspan="2" style="width: 12%;">Hadir</th>
                <th colspan="3" style="width: 30%;">Rincian Tidak Hadir</th>
                <th rowspan="2" style="width: 14%;">Total Tidak Hadir</th>
                <th rowspan="2" style="width: 14%;">% Kehadiran</th>
            </tr>
            <tr>
                <th class="sub-th" style="width: 10%;">Sakit (S)</th>
                <th class="sub-th" style="width: 10%;">Izin (I)</th>
                <th class="sub-th" style="width: 10%;">Alpha (A)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataKelas as $index => $data)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left" style="font-weight: bold;">{{ $data['kelas'] }}</td>
                <td>{{ $data['total'] }}</td>
                <td style="color: #16a34a; font-weight: bold;">{{ $data['hadir'] }}</td>
                <td style="color: #d97706; font-weight: bold;">{{ $data['sakit'] }}</td>
                <td style="color: #0284c7; font-weight: bold;">{{ $data['izin'] }}</td>
                <td style="color: #dc2626; font-weight: bold;">{{ $data['alpha'] }}</td>
                <td style="font-weight: bold; color: {{ $data['tidak_hadir'] > 0 ? '#dc2626' : '#64748b' }};">
                    {{ $data['tidak_hadir'] }}
                </td>
                <td style="font-weight: bold;">{{ $data['persen'] }}%</td>
            </tr>
            @endforeach
        </tbody>
        @if(isset($summary))
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right; padding-right: 10px;">TOTAL KESELURUHAN</td>
                <td>{{ $summary['total_siswa'] }}</td>
                <td style="color: #16a34a;">{{ $summary['hadir'] }}</td>
                <td style="color: #d97706;">{{ $summary['sakit'] }}</td>
                <td style="color: #0284c7;">{{ $summary['izin'] }}</td>
                <td style="color: #dc2626;">{{ $summary['alpha'] }}</td>
                <td style="color: #dc2626;">{{ $summary['tidak_hadir'] }}</td>
                <td style="color: #1e3a8a;">{{ $summary['persen'] }}%</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- TANDA TANGAN & CATATAN -->
    <table class="footer-sig">
        <tr>
            <td style="width: 55%;">
                <div class="note-box">
                    <strong>Keterangan Status Absensi:</strong><br>
                    • <strong>Hadir</strong>: Siswa mengikuti KBM di kelas.<br>
                    • <strong>Sakit (S)</strong>: Ada surat keterangan sakit.<br>
                    • <strong>Izin (I)</strong>: Ada surat ijin resmi orang tua/wali.<br>
                    • <strong>Alpha (A)</strong>: Tidak hadir tanpa keterangan.
                </div>
            </td>
            <td style="width: 45%; text-align: center;">
                <p style="margin: 0 0 40px 0;">
                    Ciamis, {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y') }}<br>
                    <strong>Petugas Piket / Pengelola Absensi</strong>
                </p>
                <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                    ({{ auth()->check() ? auth()->user()->name : 'Petugas Piket SPENLI' }})
                </p>
                <small style="color: #64748b;">NIP / ID: {{ auth()->check() ? (auth()->user()->role ?? 'Piket') : '-' }}</small>
            </td>
        </tr>
    </table>

    <div style="margin-top: 15px; text-align: right; font-size: 8px; color: #94a3b8;">
        Dicetak secara otomatis dari SI-KASEP pada: {{ now()->format('d/m/Y H:i:s') }} WIB
    </div>

</body>
</html>
