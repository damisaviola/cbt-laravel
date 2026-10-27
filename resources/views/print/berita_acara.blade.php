<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Berita Acara Ujian - {{ $session->session_name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #000;
            background: #fff;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 14pt;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 4px 0;
            font-size: 13pt;
        }
        .header p {
            margin: 0;
            font-size: 10pt;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            text-decoration: underline;
            margin-bottom: 20px;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .content-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .notes-box {
            border: 1px solid #000;
            min-height: 80px;
            padding: 10px;
            margin: 10px 0 20px;
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }
        .footer-sig {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .sig-box {
            text-align: center;
            width: 250px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background: #0f172a; color: white; padding: 12px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; font-family: sans-serif;">
        <div>
            <strong>Berita Acara Pelaksanaan Ujian</strong> - {{ $session->session_name }} ({{ $session->room_name }})
        </div>
        <button onclick="window.print()" style="background: #185338; color: white; border: none; padding: 8px 18px; border-radius: 9999px; cursor: pointer; font-weight: bold; font-size: 13px;">
            Cetak Berita Acara (Print / PDF)
        </button>
    </div>

    <div class="header">
        <h2>{{ $school->school_name }}</h2>
        <h3>PANITIA PENILAIAN / ASESMEN SUMATIF SEKOLAH</h3>
        <p>Tahun Ajaran {{ $school->active_academic_year }} (Semester {{ $school->active_semester }}) | NPSN: {{ $school->npsn }}</p>
    </div>

    <div class="title">
        BERITA ACARA PELAKSANAAN UJIAN BERBASIS KOMPUTER (CBT)
    </div>

    <p>
        Pada hari ini, <strong>{{ \Carbon\Carbon::now()->translatedFormat('l') }}</strong> tanggal <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</strong>, telah diselenggarakan Ujian Berbasis Komputer dengan rincian sebagai berikut:
    </p>

    <table class="content-table">
        <tr>
            <td style="width: 220px;">1. Mata Pelajaran</td>
            <td style="width: 20px;">:</td>
            <td><strong>{{ $session->examSchedule->subject->name }}</strong></td>
        </tr>
        <tr>
            <td>2. Nama Ujian / Jadwal</td>
            <td>:</td>
            <td>{{ $session->examSchedule->title }}</td>
        </tr>
        <tr>
            <td>3. Sesi / Ruang Ujian</td>
            <td>:</td>
            <td>{{ $session->session_name }} / {{ $session->room_name }}</td>
        </tr>
        <tr>
            <td>4. Waktu Pelaksanaan</td>
            <td>:</td>
            <td>{{ $session->start_time->format('H:i') }} s.d. {{ $session->end_time->format('H:i') }} WIB (Durasi: {{ $session->examSchedule->duration_minutes }} Menit)</td>
        </tr>
        <tr>
            <td>5. Token Ruang Ujian</td>
            <td>:</td>
            <td><strong>{{ $session->access_token }}</strong></td>
        </tr>
        <tr>
            <td>6. Jumlah Peserta Seharusnya</td>
            <td>:</td>
            <td>{{ $totalTarget }} Orang</td>
        </tr>
        <tr>
            <td>7. Jumlah Peserta Hadir</td>
            <td>:</td>
            <td>{{ $totalPresent }} Orang</td>
        </tr>
        <tr>
            <td>8. Jumlah Peserta Tidak Hadir</td>
            <td>:</td>
            <td>{{ $totalAbsent }} Orang</td>
        </tr>
    </table>

    <p style="margin-bottom: 4px;"><strong>Catatan Kejadian Selama Ujian Berlangsung:</strong></p>
    <div class="notes-box">
        {{ $session->proctor_notes ?: 'Ujian berlangsung tertib, lancar, dan aman. Tidak ada gangguan jaringan server maupun kecurangan yang berarti.' }}
    </div>

    <p>
        Demikian Berita Acara ini dibuat dengan sebenarnya sesuai dengan kondisi pelaksanaan di ruang ujian untuk dipergunakan sebagaimana mestinya.
    </p>

    <div class="footer-sig">
        <div class="sig-box">
            Mengetahui,<br>
            Kepala Sekolah<br><br><br><br><br>
            <strong>{{ $school->headmaster_name }}</strong><br>
            NIP. {{ $school->headmaster_nip }}
        </div>
        <div class="sig-box">
            Pengawas Ruang Ujian,<br><br><br><br><br>
            <strong>{{ $session->proctor ? $session->proctor->name : '( ............................................ )' }}</strong><br>
            NIP. {{ $session->proctor ? $session->proctor->username : '.............................................' }}
        </div>
    </div>
</body>
</html>
