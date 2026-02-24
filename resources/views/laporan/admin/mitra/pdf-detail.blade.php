<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Mitra: {{ $mitra->nama }}</title>
    <style>        
        /* 1. PERBAIKAN UTAMA: Gunakan selector 'body' (bukan .body) */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.2;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* 2. Pastikan Tabel mewarisi font (DomPDF terkadang mereset font di dalam tabel) */
        table, tr, td, th, tbody, thead, tfoot {
            font-family: 'DejaVu Sans', sans-serif;
        }
        
        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1e3c72;
        }
        
        .header h1 {
            color: #1e3c72;
            margin: 0 0 4px 0;
            font-size: 20pt;
            font-weight: bold;
            line-height: 1.1;
        }
        
        .header h2 {
            color: #2c3e50;
            margin: 0 0 6px 0;
            font-size: 18pt;
            font-weight: normal;
            line-height: 1.1;
        }
        
        .header p {
            color: #7f8c8d;
            margin: 0;
            font-size: 9pt;
            line-height: 1.1;
        }
        
        /* INFO MITRA */
        .info-section {
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }
        
        .info-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1e3c72;
            margin: 0 0 8px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #dee2e6;
            line-height: 1.1;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 5px 0;
            vertical-align: top;
            width: 25%;
        }
        
        .info-label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 10pt;
            display: block;
            margin-bottom: 2px;
            line-height: 1.1;
            font-family: 'DejaVu Sans', sans-serif;
        }
        
        .info-value {
            color: #34495e;
            font-size: 9pt;
            display: block;
            line-height: 1.1;
        }
        
        /* TABLE KURSUS */
        .table-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1e3c72;
            margin: 15px 0 8px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #dee2e6;
            line-height: 1.1;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9pt;
            page-break-inside: auto;
        }
        
        .data-table thead {
            background: #1e3c72;
            color: white;
        }
        
        .data-table th {
            padding: 6px 5px;
            font-weight: 600;
            font-size: 10pt;
            border: 1px solid #2a5298;
            line-height: 1.1;
        }
        
        .data-table td {
            padding: 6px 5px;
            border: 1px solid #dee2e6;
            vertical-align: top;
            font-size: 9pt;
            page-break-inside: avoid;
            line-height: 1.1;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .course-desc {
            font-size: 8pt; 
            color: #6c757d; 
            margin-top: 2px; 
            line-height: 1.1;
        }
        
        /* FOOTER */
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 8pt;
            line-height: 1.1;
        }
        
        /* UTILITY */
        .text-center { text-align: center; }
        .mb-2 { margin-bottom: 8px; }
        .mt-2 { margin-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN BELAJAR MITRA</h1>
        <h2>{{ $mitra->nama }}</h2>
        <p>
            ID Sobat: {{ $mitra->biodata->id_sobat ?? '-' }} |
            Dicetak: {{ now('Asia/Jakarta')->format('d F Y H:i') }} WIB
        </p>
    </div>
    
    <div class="info-section">
        <div class="info-title">Informasi Mitra</div>
        <table class="info-table">
            <tr>
                <td>
                    <span class="info-label">Nama Lengkap</span>
                    <span class="info-value">{{ $mitra->nama }}</span>
                </td>
                <td>
                    <span class="info-label">ID Sobat</span>
                    <span class="info-value">{{ $mitra->biodata->id_sobat ?? '-' }}</span>
                </td>
                <td>
                    <span class="info-label">Kecamatan, Desa</span>
                    <span class="info-value">
                        {{ $mitra->biodata->kecamatan ?? '-' }}, 
                        {{ $mitra->biodata->desa ?? '-' }}
                    </span>
                </td>
                <td>
                    <span class="info-label">No. Telepon</span>
                    <span class="info-value">{{ $mitra->biodata->no_telepon ?? '-' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="info-label">Kursus Diikuti</span>
                    <span class="info-value">{{ $totalKursus }} kursus</span>
                </td>
                <td>
                    <span class="info-label">Kursus Selesai (100%)</span>
                    <span class="info-value">{{ $kursusSelesai }} kursus</span>
                </td>
                <td>
                    <span class="info-label">Rata-rata Progress</span>
                    <span class="info-value">{{ round($rataProgress, 1) }}%</span>
                </td>
                <td>
                    <span class="info-label">Rata-rata Nilai</span>
                    <span class="info-value">{{ $rataNilai ? round($rataNilai, 2) : '-' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title">Daftar Kursus yang Diikuti</div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:5%; text-align:center;">No</th>
                <th style="width:35%; text-align:center;">Judul Kursus</th>
                <th style="width:12%; text-align:center;">Tanggal Daftar</th>
                <th style="width:12%; text-align:center;">Tanggal Selesai</th>
                <th style="width:18%; text-align:center;">Progress</th>
                <th style="width:10%; text-align:center;">Materi</th>
                <th style="width:10%; text-align:center;">Nilai Akhir</th>

            </tr>
        </thead>
        <tbody>
            @foreach($kursusData as $data)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>
                    {{ $data['kursus']->judul_kursus }}
                    @if($data['kursus']->deskripsi_singkat)
                    <div class="course-desc">
                        {{ Str::limit($data['kursus']->deskripsi_singkat, 60) }}
                    </div>
                    @endif
                </td>
                <td class="text-center">{{ $data['tanggal_daftar'] }}</td>
                <td class="text-center">{{ $data['tanggal_selesai'] }}</td>
                <td class="text-center">
                    {{ $rataProgress !== null ? round($rataProgress, 1) : 0 }}%
                </td>
                <td class="text-center">
                    {{ $data['completed_materials'] }}/{{ $data['total_materials'] }}
                </td>
                <td class="text-center">
                    {{ $data['nilai'] !== null ? round($data['nilai'], 2) : '-' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem MOOC BPS</p>
        <p>© {{ date('Y') }} MOOC BPS - Hak Cipta Dilindungi Undang-Undang</p>
        </div>
</body>
</html>