<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Kartu Peserta Ujian - {{ $school->school_name }}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #0f172a;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .card-box {
            border: 1.5px solid #0f172a;
            border-radius: 4px;
            padding: 10px;
            page-break-inside: avoid;
            background: #fff;
        }
        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .header-text {
            text-align: center;
            flex: 1;
        }
        .header-text h3 {
            margin: 0;
            font-size: 13px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header-text p {
            margin: 2px 0 0;
            font-size: 10px;
            color: #334155;
        }
        .card-body {
            display: flex;
            gap: 12px;
        }
        .photo-box {
            width: 70px;
            height: 90px;
            border: 1px solid #94a3b8;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #64748b;
            text-align: center;
            background: #f8fafc;
        }
        .info-table {
            flex: 1;
            font-size: 11px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .label {
            font-weight: 600;
            width: 80px;
            color: #334155;
        }
        .barcode {
            font-family: monospace;
            font-weight: bold;
            font-size: 13px;
            letter-spacing: 2px;
            background: #f1f5f9;
            padding: 2px 6px;
            display: inline-block;
            border-radius: 2px;
            border: 1px dashed #94a3b8;
        }
        .footer {
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 9px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 4px;
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
            <strong>SmartCBT - Kartu Peserta Ujian</strong> ({{ count($students) }} Siswa)
        </div>
        <button onclick="window.print()" style="background: #0f52ba; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold;">
            Cetak Kartu (Print / Simpan PDF)
        </button>
    </div>

    <div class="cards-grid">
        @foreach($students as $student)
        <div class="card-box">
            <div class="header">
                <div class="header-text">
                    <h3>KARTU PESERTA UJIAN (CBT)</h3>
                    <p>{{ $school->school_name }} | TA {{ $school->active_academic_year }} ({{ $school->active_semester }})</p>
                </div>
            </div>

            <div class="card-body">
                <div class="photo-box">
                    <span>FOTO 2x3</span>
                </div>
                <table class="info-table">
                    <tr>
                        <td class="label">NISN / No</td>
                        <td>:</td>
                        <td><span class="barcode">{{ $student->username }}</span></td>
                    </tr>
                    <tr>
                        <td class="label">Nama</td>
                        <td>:</td>
                        <td><strong>{{ $student->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Kelas / Sesi</td>
                        <td>:</td>
                        <td>{{ $student->classRoom ? $student->classRoom->name : '-' }} / Sesi 1</td>
                    </tr>
                    <tr>
                        <td class="label">Ruang Ujian</td>
                        <td>:</td>
                        <td>{{ $student->exam_room_name ?? 'Lab Komputer 1' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Kata Sandi</td>
                        <td>:</td>
                        <td><span style="font-family: monospace; background: #e2e8f0; padding: 1px 4px; border-radius: 2px;">siswa123</span></td>
                    </tr>
                </table>
            </div>

            <div class="footer">
                <div>
                    <em>* Harap simpan kartu ini & jangan bagikan kata sandi.</em>
                </div>
                <div style="text-align: right;">
                    Kepala Sekolah<br>
                    <strong>{{ $school->headmaster_name }}</strong>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</body>
</html>
