<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Hadir Ruang Ujian - {{ $room }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 15px;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 2px 0;
            font-size: 13px;
        }
        .header p {
            margin: 2px 0;
            font-size: 10px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 11px;
        }
        .meta-table td {
            padding: 2px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        .data-table th {
            background-color: #f1f5f9;
            text-align: center;
        }
        .sign-col {
            width: 120px;
        }
        .sign-left {
            text-align: left;
            padding-left: 10px;
        }
        .sign-right {
            text-align: right;
            padding-right: 10px;
        }
        .footer-sig {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .sig-box {
            text-align: center;
            width: 200px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background: #0f172a; color: white; padding: 12px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <strong>Daftar Hadir Ruang Ujian: {{ $room }}</strong> ({{ count($students) }} Siswa)
        </div>
        <button onclick="window.print()" style="background: #185338; color: white; border: none; padding: 8px 18px; border-radius: 9999px; cursor: pointer; font-weight: bold; font-size: 13px;">
            Cetak Daftar Hadir (Print / Simpan PDF)
        </button>
    </div>

    <div class="header">
        <h2>{{ $school->school_name }}</h2>
        <h3>DAFTAR HADIR PESERTA UJIAN BERBASIS KOMPUTER (CBT)</h3>
        <p>Tahun Ajaran {{ $school->active_academic_year }} - Semester {{ $school->active_semester }} | NPSN: {{ $school->npsn }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 120px;"><strong>Ruang Ujian</strong></td>
            <td>: {{ $room }}</td>
            <td style="width: 120px;"><strong>Hari / Tanggal</strong></td>
            <td>: {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td><strong>Sesi Ujian</strong></td>
            <td>: Sesi 1 (Pagi)</td>
            <td><strong>Waktu</strong></td>
            <td>: 07.30 - 09.30 WIB</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 90px;">NISN</th>
                <th>Nama Peserta</th>
                <th style="width: 80px;">Kelas</th>
                <th class="sign-col" colspan="2">Tanda Tangan</th>
                <th style="width: 80px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-align: center; font-family: monospace;">{{ $student->username }}</td>
                <td>{{ $student->name }}</td>
                <td style="text-align: center;">{{ $student->classRoom ? $student->classRoom->name : '-' }}</td>
                @if(($index + 1) % 2 == 1)
                    <td class="sign-left">{{ $index + 1 }}. ............</td>
                    <td class="sign-right"></td>
                @else
                    <td class="sign-left"></td>
                    <td class="sign-right">{{ $index + 1 }}. ............</td>
                @endif
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-sig">
        <div class="sig-box">
            Mengetahui,<br>
            Kepala Sekolah<br><br><br><br>
            <strong>{{ $school->headmaster_name }}</strong><br>
            NIP. {{ $school->headmaster_nip }}
        </div>
        <div class="sig-box">
            Pengawas Ruang Ujian,<br><br><br><br><br>
            ( ......................................... )<br>
            NIP.
        </div>
    </div>
</body>
</html>
