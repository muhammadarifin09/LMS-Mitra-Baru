<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kursus: {{ $kursus->judul_kursus }}</title>
    <style>
        /* ===== RESET & DASAR ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #000;
            background-color: #ffffff;
            margin: 12px 30px; /* Tambah margin di body */
        }
        
        /* ===== HEADER ===== */
        .header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            position: relative;
        }

        .header-container {
            /* display: flex;
            align-items: center;
            justify-content: space-between; */
            text-align: center;
            margin-bottom: 5px;
            padding-bottom: 8px;
            border-bottom: 2px solid #1f3c88; /* Biru BPS */
        }

        .header-logo {
            flex: 0 0 auto;
        }

        .header-logo img {
            height: 55px;
        }

        .header-text {
            flex: 1;
            text-align: center;
            padding: 0 15px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: 700;
            color: #1f3c88; /* Biru BPS */
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 14px;
            font-weight: 600;
            color: #1f3c88; /* Biru BPS */
            margin-bottom: 5px;
        }

        .header-info {
            text-align: right;
            margin-top: 8px;
            font-size: 9px;
            color: #666;
            padding: 5px 0;
            border-top: 1px solid #ddd;
        }
        
        /* ===== INFO KURSUS ===== */
        .info-kursus {
            margin-bottom: 20px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
            border: 1px solid #000;
        }
        
        .info-table td {
            padding: 8px;
            border: 1px solid #000;
            vertical-align: top;
        }
        
        .info-label {
            font-weight: 600;
            color: #000;
            width: 25%;
            background-color: #e6f0ff; /* Biru muda */
        }
        
        /* ===== STATS GRID ===== */
        .stats-grid {
            /* display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px; */
        }
        
        .stat-item {
            background: #f8f9fa;
            border: 1px solid #1f3c88; /* Biru BPS */
            border-radius: 4px;
            padding: 10px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 3px;
            color: #1f3c88; /* Biru BPS */
        }
        
        .stat-label {
            font-size: 9px;
            color: #666;
            line-height: 1.2;
        }
        
        /* ===== TABEL ===== */
        .table-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 12px;
            color: #1f3c88; /* Biru BPS */
            margin-bottom: 12px;
            padding-bottom: 5px;
            border-bottom: 2px solid #1f3c88; /* Biru BPS */
            font-weight: 700;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
            border: 1px solid #000;
        }
        
        thead {
            background-color: #1f3c88; /* Biru BPS */
        }
        
        th {
            padding: 8px 6px;
            text-align: left;
            font-weight: 600;
            font-size: 9px;
            border: 1px solid #000;
            color: white;
        }
        
        td {
            padding: 7px 6px;
            border: 1px solid #000;
            vertical-align: middle;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-center { 
            text-align: center; 
        }
        
        .text-right { 
            text-align: right; 
        }
        
        /* ===== PROGRESS BAR ===== */
        .progress-container,
        .progress-bar,
        .progress-text {
            /* DISABLE progress bar for DomPDF */
        }

        
        /* Warna progress */
        .progress-0-49 { background-color: #002d4dff; }
        .progress-50-79 { background-color: #007ed7ff; }
        .progress-80-100 { background-color: #000071ff; }
        
        /* ===== BADGE STYLE ===== */
        .badge-nilai {
            display: inline-block;
            padding: 4px 8px;
            background: #f8f9fa;
            border: 1px solid #1f3c88; /* Biru BPS */
            border-radius: 3px;
            font-weight: 600;
            font-size: 9px;
            min-width: 45px;
            text-align: center;
            color: #1f3c88; /* Biru BPS */
        }
        
        .badge-type {
            display: inline-block;
            padding: 3px 6px;
            background-color: #e6f0ff; /* Biru muda */
            border: 1px solid #1f3c88; /* Biru BPS */
            border-radius: 3px;
            font-size: 8px;
            text-transform: uppercase;
            color: #1f3c88; /* Biru BPS */
        }
        
        .badge-status {
            display: inline-block;
            padding: 3px 6px;
            background-color: #d4edda;
            border: 1px solid #28a745;
            border-radius: 3px;
            font-size: 8px;
            color: #155724;
        }
        
        /* ===== SUMMARY BOX ===== */
        .summary-box {
            background: #f0f7ff; /* Biru sangat muda */
            border: 1px solid #1f3c88; /* Biru BPS */
            border-radius: 4px;
            padding: 10px;
            margin-top: 12px;
            font-size: 9px;
            color: #1f3c88; /* Biru BPS */
        }
        
        .summary-item {
            display: inline-block;
            margin-right: 20px;
            font-weight: 600;
        }
        
        /* ===== FOOTER ===== */
        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 2px solid #1f3c88; /* Biru BPS */
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        
        .footer-info {
            margin-top: 5px;
            font-size: 8px;
            color: #888;
        }
        
        /* ===== UTILITY ===== */
        .mb-2 { margin-bottom: 10px; }
        .mb-3 { margin-bottom: 15px; }
        .mb-4 { margin-bottom: 20px; }
        .mt-2 { margin-top: 10px; }
        .mt-3 { margin-top: 15px; }
        .mt-4 { margin-top: 20px; }
        .font-weight-bold { font-weight: 700; }
        
        /* ===== PRINT OPTIMIZATION ===== */
        /* @media print {
            body {
                font-size: 12pt;
                margin: 12px 30px;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
            }
            
            .footer {
                position: fixed;
                bottom: 0;
                width: 100%;
                background: white;
            }
        } */
        
        /* ===== WATERMARK ===== */
                .watermark {
            /* position: fixed; */
            display: none;
        }

    </style>
</head>
<body>
    <!-- Watermark -->
    <!-- <div class="watermark">MOOC BPS</div> -->
    
    <!-- Header -->
    <div class="header">
        <div class="header-container">
            <!-- <div class="header-logo">
              <img src="{{ public_path('img/logo-bps.png') }}" alt="Logo BPS">
            </div> -->
            
            <div class="header-text">
                <h1>Laporan Data Kursus MOOC BPS Tanah Laut</h1>
                <h2>LAPORAN DETAIL KURSUS</h2>
            </div>
        </div>
        
       <div class="header-info">
        ID Kursus: {{ $kursus->id }} | Dicetak: {{ now('Asia/Makassar')->format('d/m/Y H:i') }} WITA
        </div>
    </div>
    
    <!-- Informasi Kursus -->
    <div class="info-kursus">
        <h3 class="section-title">INFORMASI KURSUS</h3>
        <table class="info-table">
            <tr>
                <td class="info-label">Judul Kursus</td>
                <td>{{ $kursus->judul_kursus }}</td>
                <td class="info-label">Total Materi</td>
                <td>{{ $kursus->materials->count() }} Materi</td>
            </tr>
            <tr>
                <td class="info-label">Total Peserta</td>
                <td>{{ $kursus->enrollments->count() }} Peserta</td>
                <td class="info-label">Tanggal Dibuat</td>
                <td>{{ $kursus->created_at->format('d M Y H:i') }}</td>
            </tr>
            @if($kursus->deskripsi_singkat)
            <tr>
                <td class="info-label">Deskripsi</td>
                <td colspan="3">{{ $kursus->deskripsi_singkat }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    <!-- Statistik Utama -->
    <!-- <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-value">{{ number_format($totalProgress, 1) }}%</div>
            <div class="stat-label">Rata-rata Progress</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ number_format($totalNilai, 1) }}</div>
            <div class="stat-label">Rata-rata Nilai</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $pesertaSelesai }}</div>
            <div class="stat-label">Peserta Selesai</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ count($pesertaData) - $pesertaSelesai }}</div>
            <div class="stat-label">Belum Selesai</div>
        </div>
    </div> -->
    
    <!-- Daftar Peserta -->
    <div class="table-section">
        <h3 class="section-title">DAFTAR PESERTA</h3>
        
        @if($kursus->enrollments->isEmpty())
        <div class="summary-box">
            Belum ada peserta yang mendaftar pada kursus ini.
        </div>
        @else
        <table>
            <thead>
                <tr>
                    <th width="4%" class="text-center">No</th>
                    <th width="23%">Nama Peserta</th>
                    <th width="18%">Kontak</th>
                    <th width="12%" class="text-center">Tanggal Daftar</th>
                    <th width="28%">Progres Belajar</th>
                    <th width="10%" class="text-center">Nilai</th>
                </tr>
            </thead>
                <tbody>
            @foreach($pesertaData as $data)
            @php
                $progressPercentage = $data['progress_percentage'];
                $completedMaterials = $data['completed_materials'];
                $totalMaterials = $data['total_materials'];
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td><strong>{{ $data['user']->nama ?? ($data['user']->name ?? 'N/A') }}</strong></td>
                <td>{{ $data['user']->email ?? ($data['user']->username ?? 'N/A') }}</td>
                <td class="text-center">{{ $data['enrollment']->created_at->format('d/m/Y') }}</td>
                <td class="text-center">
                    <span style="font-weight: 600; color: #1f3c88;">
                        {{ $progressPercentage }}% ({{ $completedMaterials }}/{{ $totalMaterials }})
                    </span>
                </td>
                <td class="text-center">
                    @if ($data['nilai'] === null)
                        <span class="badge-nilai text-muted" style="color: #6c757d !important;">
                            Belum ada nilai
                        </span>
                    @else
                        <span class="badge-nilai">{{ number_format($data['nilai'], 1) }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        </table>
        
        <!-- Ringkasan Peserta -->
        @php
            $highProgress = collect($pesertaData)->where('progress_percentage', '>=', 80)->count();
            $mediumProgress = collect($pesertaData)->whereBetween('progress_percentage', [50, 79])->count();
            $lowProgress = collect($pesertaData)->where('progress_percentage', '<', 50)->count();
        @endphp
        
        <!-- <div class="summary-box">
            <div class="summary-item">
                Selesai: {{ $pesertaSelesai }}
            </div>
            <div class="summary-item">
                ≥80%: {{ $highProgress }}
            </div>
            <div class="summary-item">
                50-79%: {{ $mediumProgress }}
            </div>
            <div class="summary-item">
                &lt;50%: {{ $lowProgress }}
            </div>
        </div> -->
        @endif
    </div>
    
    <!-- Daftar Materi -->
    @if($kursus->materials->isNotEmpty())
    <div class="table-section">
        <h3 class="section-title">DAFTAR MATERI</h3>
        
        <table>
            <thead>
                <tr>
                    <th width="5%" class="text-center">No</th>
                    <th width="55%">Judul Materi</th>
                    <th width="20%">Jenis</th>
                    <th width="20%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kursus->materials as $material)
                @php
                    $materialType = $material->material_type ?? $material->tipe_material ?? 'unknown';
                    
                    // Normalisasi tipe
                    $typeMap = [
                        'video' => 'Video',
                        'dokumen' => 'Dokumen', 'document' => 'Dokumen',
                        'kuis' => 'Kuis', 'quiz' => 'Kuis',
                        'theory' => 'Teori', 'teori' => 'Teori',
                        'pre_test' => 'Pre Test',
                        'post_test' => 'Post Test'
                    ];
                    
                    $typeDisplay = $typeMap[strtolower($materialType)] ?? ucfirst($materialType);
                    
                    // Status
                    $isActive = $material->is_active ?? true;
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $material->title ?? $material->judul_material ?? 'N/A' }}</td>
                    <td><span class="badge-type">{{ $typeDisplay }}</span></td>
                    <td>
                        @if($isActive)
                        <span class="badge-status">Aktif</span>
                        @else
                        <span style="display: inline-block; padding: 3px 6px; background-color: #f8d7da; border: 1px solid #721c24; border-radius: 3px; font-size: 8px; color: #721c24;">Nonaktif</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Ringkasan Materi -->
        @php
            $typeCounts = $kursus->materials->groupBy(function($material) use ($typeMap) {
                $type = strtolower($material->material_type ?? $material->tipe_material ?? 'other');
                return $typeMap[$type] ?? 'Lainnya';
            })->map->count();
        @endphp
        
        <!-- <div class="summary-box">
            <strong>Distribusi Materi:</strong>
            @foreach($typeCounts as $type => $count)
            <span class="summary-item">{{ $type }}: {{ $count }}</span>
            @endforeach
        </div> -->
    </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <div>Laporan ini dibuat secara otomatis oleh sistem MOOC BPS</div>
        <div class="footer-info">
            © 2025 MOOC BPS - Hak Cipta Dilindungi Undang-Undang
        </div>
    </div>
</body>
</html>