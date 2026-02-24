{{-- resources/views/mitra/kursus-detail.blade.php --}}
@extends('mitra.layouts.app')

@section('title', 'MOOC BPS - Detail Kursus')

@section('content')
<style>
    /* HIDE SIDEBAR */
    .sidebar {
        display: none !important;
    }
    
    /* Adjust main content to full width */
    .main-content {
        margin-left:  !important;
        width: flex;
    }

    .sequential-flow {
        max-width: 3000px;
        margin: 0 auto;
    }

    .flow-step {
        background: white;
        border-radius: 12px;
        padding: rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-left: 4px solid #e9ecef;
        transition: all 0.3s;
    }

    .flow-step.current {
        border-left-color: #1e3c72;
        background: #f8f9fa;
    }

    .flow-step.completed {
        border-left-color: #28a745;
        background: #f8fff9;
    }

    .flow-step.locked {
        border-left-color: #6c757d;
        background: #f8f9fa;
    }

    .step-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 0;
        cursor: pointer;
        padding: 10px;
        border-radius: 8px;
        transition: background-color 0.3s;
    }

    .step-header:hover {
        background-color: rgba(30, 60, 114, 0.05);
    }

    .step-toggle {
        margin-left: 15px;
        color: #5a6c7d;
        transition: transform 0.3s ease;
    }

    .step-toggle.rotated {
        transform: rotate(180deg);
    }

    .step-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e3c72;
        margin: 0;
    }

    .step-status {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-left: auto;
    }

    .status-locked {
        background: #6c757d;
        color: white;
    }

    .status-current {
        background: #1e3c72;
        color: white;
    }

    .status-completed {
        background: #28a745;
        color: white;
    }

    .sub-tasks {
        background: white;
        border-radius: 8px;
        padding: 0;
        border: 1px solid #e9ecef;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, padding 0.3s ease;
    }

    .sub-tasks.expanded {
        max-height: 500px;
        padding: 1rem;
    }

    .sub-task {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        border-bottom: 1px solid #f1f3f4;
    }

    .sub-task:last-child {
        border-bottom: none;
    }

    .task-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .task-icon.current {
        background: #1e3c72;
        color: white;
    }

    .task-info {
        flex: 1;
    }

    .task-name {
        font-weight: 500;
        margin: 0;
        color: #2c3e50;
    }

    .task-description {
        font-size: 0.8rem;
        color: #6c757d;
        margin: 0;
    }

    .task-action {
        margin-left: 1rem;
    }

    .btn-simple {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        font-size: 0.85rem;
    }

    .btn-primary {
        background: #1e3c72;
        color: white;
    }

    .btn-primary:hover {
        background: #2a5298;
        transform: translateY(-2px);
    }

    .btn-success {
        background: #28a745;
        color: white;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn:disabled {
        background: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
    }

    .progress-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 2rem;
    }

    .progress-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 0.85rem;
        color: #5a6c7d;
        font-weight: 500;
    }

    .progress-bar {
        width: 100%;
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        border-radius: 4px;
    }

    /* Test info badges */
    .test-info {
        display: flex;
        gap: 10px;
        margin-top: 10px;
        flex-wrap: wrap;
    }

    .info-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .badge-info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .badge-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    

    /* Material description */
    .material-description {
        font-size: 0.9rem;
        color: #6c757d;
        margin-top: 5px;
        line-height: 1.4;
    }

    /* File list styling */
    .file-list {
        margin-top: 10px;
    }

    .file-item {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 8px;
        border: 1px solid #e9ecef;
    }

    .file-icon {
        font-size: 1.2rem;
        margin-right: 10px;
        width: 24px;
        text-align: center;
    }

    .file-name {
        flex: 1;
        font-size: 0.85rem;
        color: #495057;
        word-break: break-all;
    }

    .no-content-message {
        text-align: center;
        padding: 20px;
        color: #6c757d;
        font-style: italic;
    }

    .score-pass {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .score-fail {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    /* Tambahan styling untuk video info */
    .video-info-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        background: #e3f2fd;
        color: #1565c0;
        border-radius: 12px;
        font-size: 0.75rem;
        margin-top: 5px;
    }
    
    .video-info-badge i {
        margin-right: 5px;
    }
    /* =========================
    RESPONSIVE DESIGN UNTUK MOBILE (<500px)
    ========================= */
    @media (max-width: 500px) {
        .main-content {
            padding: 15px 12px !important; /* Tambah kiri-kanan 12px */
            margin: 10px 10px !important; /* Tambah margin kiri-kanan 8px */
            width: auto !important;
        }

        /* Container utama */
        .container-fluid.py-4 {
            padding: 10px !important;
        }
        
        /* Header dan Progress Bar */
        .progress-section {
            padding: 10px !important;
            border-radius: 8px !important;
            margin-bottom: 15px !important;
        }
        
        .progress-info {
            gap: 5px !important;
            margin-bottom: 8px !important;
            font-size: 0.75rem !important;
            justify-content: space-between;
        }
        
        .progress-bar {
            height: 6px !important;
            border-radius: 3px !important;
        }

        /* Flow Step Container */
        .sequential-flow {
            margin: 0 !important;
        }
        
        /* Flow Step Box */
        .flow-step {
            padding: 12px !important;
            margin-bottom: 12px !important;
            border-radius: 10px !important;
            border-left-width: 3px !important;
        }
        
        /* Step Header */
        .step-header {
            padding: 8px !important;
            flex-direction: row !important;
            align-items: flex-start !important;
            flex-wrap: wrap;
        }
        
        .header-content {
            flex: 1;
            min-width: 0; /* Penting untuk text truncation */
        }
        
        .step-title {
            font-size: 0.9rem !important;
            line-height: 1.2 !important;
            margin-bottom: 3px !important;
            word-break: break-word;
        }
        
        .material-description {
            font-size: 0.75rem !important;
            line-height: 1.3 !important;
            margin-top: 2px !important;
        }
        
        /* Step Status Badge */
        .step-status {
            padding: 3px 8px !important;
            font-size: 0.65rem !important;
            margin-left: 8px !important;
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        /* Test info badges */
        .test-info {
            gap: 5px !important;
            margin-top: 5px !important;
        }
        
        .info-badge {
            padding: 3px 6px !important;
            font-size: 0.65rem !important;
            border-radius: 8px !important;
        }
        
        /* Step Toggle Arrow */
        .step-toggle {
            margin-left: 5px !important;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .step-toggle i {
            font-size: 0.8rem !important;
        }
        
        /* Sub-tasks Container */
        .sub-tasks.expanded {
            padding: 10px !important;
            max-height: 800px !important; /* Lebih tinggi untuk mobile */
        }
        
        /* Sub-task Item */
        .sub-task {
            padding: 8px !important;
            flex-direction: row;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .task-icon {
            width: 28px !important;
            height: 28px !important;
            margin-right: 8px !important;
            flex-shrink: 0;
        }
        
        .task-icon i {
            font-size: 0.9rem !important;
        }
        
        .task-info {
            flex: 1;
            min-width: 0; /* Penting untuk text truncation */
        }
        
        .task-name {
            font-size: 0.8rem !important;
            margin-bottom: 2px !important;
        }
        
        .task-description {
            font-size: 0.7rem !important;
            line-height: 1.2 !important;
        }
        
        /* File list styling untuk mobile */
        .file-list {
            margin-top: 5px !important;
        }
        
        .file-item {
            padding: 6px 8px !important;
            margin-bottom: 5px !important;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
        
        .file-icon {
            margin-right: 6px !important;
            font-size: 1rem !important;
        }
        
        .file-name {
            font-size: 0.7rem !important;
            word-break: break-all;
        }
        
        .file-status {
            font-size: 0.65rem !important;
            align-self: flex-end;
        }
        
        /* Task Action Buttons */
        .task-action {
            margin-left: 0 !important;
            margin-top: 8px !important;
            width: 100%;
            text-align: center;
        }
        
        .btn-simple {
            padding: 6px 12px !important;
            font-size: 0.75rem !important;
            width: 100%;
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-bottom: 5px;
        }
        
        /* Test Action Button Area */
        .flow-step .task-action {
            padding: 10px 0 0 0 !important;
            margin-top: 10px !important;
            border-top: 1px solid #f1f3f4;
        }
        
        /* Score badges untuk test */
        .score-pass, .score-fail {
            padding: 6px 10px !important;
            font-size: 0.75rem !important;
            width: 100%;
            text-align: center;
        }
        
        /* No content message */
        .no-content-message {
            padding: 15px !important;
            font-size: 0.8rem !important;
        }
        
        /* Text truncation untuk judul panjang */
        .step-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Tombol khusus untuk test dan recap */
        .flow-step .task-action .btn-simple {
            margin-top: 5px;
        }
        
        /* Untuk step yang tidak bisa diklik (no-toggle) */
        .step-header.no-toggle {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .step-header.no-toggle .header-content {
            width: 100%;
        }
        
        .step-header.no-toggle .step-status {
            align-self: flex-end;
            margin-top: 5px;
        }
    }

>>>>>>> aeb232a879ee3019e46bd83769f8370d998319e9
</style>

<div class="container-fluid py-4">
    <!-- Header dan Progress Bar -->
    <div class="progress-section">
        <div class="progress-info">
            <span>{{ $completedMaterials }} dari {{ $totalMaterials }} aktivitas selesai</span>
            <span>{{ $progressPercentage }}%</span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width: {{ $progressPercentage }}%"></div>
        </div>
    </div>
    
    <!-- DEBUG INFO (optional, bisa dihilangkan setelah fix) -->
    @php
        // Cek material dengan video untuk debugging
        $videoMaterials = [];
        foreach($materials as $index => $material) {
            if($material['has_video']) {
                $videoMaterials[] = [
                    'index' => $index,
                    'id' => $material['id'],
                    'title' => $material['title'],
                    'video_type' => $material['video_type'],
                    'has_video_data' => !empty($material['video_data']),
                    'video_data_keys' => !empty($material['video_data']) ? array_keys($material['video_data']) : [],
                    'has_embed_url' => !empty($material['video_data']['embed_url'] ?? ''),
                    'embed_url' => $material['video_data']['embed_url'] ?? null,
                    'has_direct_link' => !empty($material['video_data']['direct_link'] ?? ''),
                    'is_hosted' => $material['video_data']['is_hosted'] ?? false,
                    'is_local' => $material['video_data']['is_local'] ?? false,
                ];
            }
        }
    @endphp
    
    @if(!empty($videoMaterials) && false) {{-- Set false untuk disable debug setelah fix --}}
    <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
        <h6 style="color: #1e3c72; margin-bottom: 10px;">
            <i class="fas fa-bug me-2"></i>Debug Info: Video Materials
        </h6>
        @foreach($videoMaterials as $video)
        <div style="font-size: 0.85rem; padding: 8px; border-bottom: 1px solid #eee;">
            <strong>{{ $video['title'] }}</strong><br>
            Type: {{ $video['video_type'] }} | 
            Has Data: {{ $video['has_video_data'] ? '✅' : '❌' }} | 
            Embed URL: {{ $video['has_embed_url'] ? '✅' : '❌' }}<br>
            @if($video['has_video_data'])
            Keys: {{ implode(', ', $video['video_data_keys']) }}
            @endif
        </div>
        @endforeach
    </div>
    @endif
    
    <!-- Sequential Flow -->
    <div class="sequential-flow">
        @foreach($materials as $material)
        <div class="flow-step {{ $material['status_class'] }}" id="material-{{ $material['id'] }}"
             data-material-status="{{ $material['status'] }}"
             data-attendance-status="{{ $material['attendance_status'] ?? 'pending' }}"
             data-material-id="{{ $material['id'] }}"
             data-has-attendance="{{ $material['has_attendance'] ? 'true' : 'false' }}"
             data-has-material="{{ $material['has_file'] ? 'true' : 'false' }}"
             data-has-video="{{ $material['has_video'] ? 'true' : 'false' }}">
            
            @if($material['type'] == 'material')
            <!-- Header untuk material -->
            <div class="step-header" onclick="toggleSubTasks({{ $material['id'] }})">
                <div class="header-content">
                    <h3 class="step-title">
                        {{ $loop->iteration }}. {{ $material['title'] }}
                    </h3>
                    @if($material['description'])
                    <div class="material-description">
                        {{ $material['description'] }}
                    </div>
                    @endif
                    
                    <!-- Tampilkan ikon jenis konten yang tersedia -->
                    @php
                        $availableContent = [];
                        if($material['has_attendance']) $availableContent[] = 'Kehadiran';
                        if($material['has_file']) $availableContent[] = 'Materi';
                        if($material['has_video']) $availableContent[] = 'Video';
                    @endphp
                    
                    @if(!empty($availableContent))
                    <div style="font-size: 0.85rem; color: #6c757d; margin-top: 5px;">
                        <i class="fas fa-list-check me-1"></i>
                        Konten: {{ implode(', ', $availableContent) }}
                    </div>
                    @endif
                </div>

                <span class="step-status status-{{ $material['status'] }}">
                    @if($material['status'] == 'locked') 
                        <i class="fas fa-lock"></i> Terkunci
                    @elseif($material['status'] == 'current') 
                        Sedang Berjalan
                    @elseif($material['status'] == 'completed') 
                        Selesai
                    @endif
                </span>
                
                <div class="step-toggle" id="toggle{{ $material['id'] }}">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>

            <!-- Sub-tasks untuk material -->
            <div class="sub-tasks" id="subTasks{{ $material['id'] }}">
                @php
                    $hasContent = false;
                @endphp

                <!-- Kehadiran -->
                @if($material['has_attendance'])
                    @php $hasContent = true; @endphp
                    <div class="sub-task">
                        <div class="task-icon" id="attendance-icon-{{ $material['id'] }}" 
                             style="background: {{ $material['attendance_status'] == 'completed' ? '#28a745' : '#e9ecef' }}; 
                                    color: {{ $material['attendance_status'] == 'completed' ? 'white' : '#6c757d' }};">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="task-info">
                            <div class="task-name">Kehadiran</div>
                            <div class="task-description">Konfirmasi kehadiran untuk materi ini</div>
                        </div>
                        <div class="task-action">
                            @if($material['attendance_status'] == 'completed')
                            <span class="btn-simple btn-success">
                                <i class="fas fa-check"></i> Selesai
                            </span>
                            @elseif($material['status'] == 'current')
                            <button class="btn-simple btn-primary" onclick="markAttendance({{ $material['id'] }})">
                                <i class="fas fa-check-circle"></i> Tandai Hadir
                            </button>
                            @else
                            <button class="btn-simple btn-secondary" disabled>
                                <i class="fas fa-lock"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Materi Pelatihan -->
                @if($material['has_file'])
                    @php 
                        $hasContent = true; 
                        $filePaths = $material['file_paths'] ?? [];
                        $fileCount = count($filePaths);
                        
                        $canDownload = $material['status'] == 'current' && 
                                      ($material['attendance_status'] == 'completed' || !$material['has_attendance']);
                    @endphp
                    <div class="sub-task">
                        <div class="task-icon" id="material-icon-{{ $material['id'] }}"
                             style="background: {{ $material['material_status'] == 'completed' ? '#28a745' : '#e9ecef' }}; 
                                    color: {{ $material['material_status'] == 'completed' ? 'white' : '#6c757d' }};">
                            <i class="fas fa-file-download"></i>
                        </div>
                        <div class="task-info">
                            <div class="task-name">Materi Pelatihan</div>
                            <div class="task-description">
                                @if($fileCount > 1)
                                    {{ $fileCount }} file tersedia (akan diunduh dalam format ZIP)
                                @else
                                    Download dan pelajari materi
                                @endif
                            </div>
                            
                            @if($fileCount > 0)
                            <div class="file-list">
                                @foreach($filePaths as $index => $filePath)
                                    @php
                                        $fileName = basename($filePath);
                                        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                                        $fileIcon = getFileIcon($fileExtension);
                                        $isDownloaded = $material['material_status'] == 'completed';
                                    @endphp
                                    
                                    <div class="file-item">
                                        <div class="file-icon">
                                            <i class="{{ $fileIcon }}"></i>
                                        </div>
                                        <div class="file-name">
                                            {{ $fileName }}
                                        </div>
                                        <div class="file-status">
                                            @if($isDownloaded)
                                                <i class="fas fa-check text-success"></i>
                                            @elseif($fileCount > 1)
                                                <span class="text-muted small">Akan diunduh dalam ZIP</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <div class="task-action">
                            @if($material['material_status'] == 'completed')
                                <span class="btn-simple btn-success" id="material-button-{{ $material['id'] }}">
                                    <i class="fas fa-check"></i> Selesai
                                </span>
                            @elseif($canDownload)
                                <a href="javascript:void(0)" 
                                   class="btn-simple btn-primary"
                                   id="download-button-{{ $material['id'] }}"
                                   onclick="handleDownload(event, {{ $material['id'] }}, {{ $kursus->id }})">
                                    <i class="fas fa-download"></i> 
                                    @if($fileCount > 1)
                                        Download Semua
                                    @else
                                        Download File
                                    @endif
                                </a>
                            @else
                                <button class="btn-simple btn-secondary" disabled id="locked-material-{{ $material['id'] }}">
                                    <i class="fas fa-lock"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Video Pelatihan - PERBAIKAN UTAMA -->
                @if($material['has_video'])
                    @php 
                        $hasContent = true;
                        
                        // Data video dari controller
                        $videoData = $material['video_data'] ?? [];
                        $videoType = $material['video_type'] ?? null;
                        
                        // Debug info
                        $hasVideoData = !empty($videoData);
                        $hasEmbedUrl = !empty($videoData['embed_url'] ?? '');
                        $hasDirectLink = !empty($videoData['direct_link'] ?? '');
                        $isVideoAvailable = $hasEmbedUrl || $hasDirectLink;
                        
                        // LOGIKA AKSES VIDEO YANG DIPERBAIKI
                        $canWatchVideo = $material['status'] == 'current' && $isVideoAvailable &&
                                        // Jika ada materi, materi harus selesai dulu
                                        (!$material['has_file'] || $material['material_status'] == 'completed') &&
                                        // Jika ada attendance, attendance harus selesai dulu
                                        (!$material['has_attendance'] || $material['attendance_status'] == 'completed');
                        
                        // Tentukan nama platform video
                        $platformName = 'Video';
                        $platformIcon = 'fas fa-video';
                        
                        if($videoData['is_youtube'] ?? false) {
                            $platformName = 'YouTube';
                            $platformIcon = 'fab fa-youtube';
                            $videoType = 'youtube';
                        } elseif($videoData['is_hosted'] ?? false) {
                            $platformName = 'Google Drive';
                            $platformIcon = 'fab fa-google-drive';
                            $videoType = 'hosted';
                        } elseif($videoData['is_local'] ?? false) {
                            $platformName = 'Video Lokal';
                            $platformIcon = 'fas fa-server';
                            $videoType = 'local';
                        } elseif($videoData['is_vimeo'] ?? false) {
                            $platformName = 'Vimeo';
                            $platformIcon = 'fab fa-vimeo';
                            $videoType = 'vimeo';
                        } elseif($videoType == 'youtube') {
                            $platformName = 'YouTube';
                            $platformIcon = 'fab fa-youtube';
                        } elseif($videoType == 'hosted') {
                            $platformName = 'Google Drive';
                            $platformIcon = 'fab fa-google-drive';
                        } elseif($videoType == 'local') {
                            $platformName = 'Video Lokal';
                            $platformIcon = 'fas fa-server';
                        } elseif($videoType == 'vimeo') {
                            $platformName = 'Vimeo';
                            $platformIcon = 'fab fa-vimeo';
                        }
                    @endphp
                    
                    <div class="sub-task">
                        <div class="task-icon" id="video-icon-{{ $material['id'] }}"
                             style="background: {{ $material['video_status'] == 'completed' ? '#28a745' : '#e9ecef' }}; 
                                    color: {{ $material['video_status'] == 'completed' ? 'white' : '#6c757d' }};">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <div class="task-info">
                            <div class="task-name">Video Pelatihan</div>
                            <div class="task-description">Tonton video materi</div>

                        </div>
                        <div class="task-action">
                            @if($material['video_status'] == 'completed')
                                <span class="btn-simple btn-success" id="video-button-{{ $material['id'] }}">
                                    <i class="fas fa-check"></i> Selesai
                                </span>
                            @elseif($canWatchVideo && $isVideoAvailable)
                                <a href="{{ route('mitra.kursus.material.video', ['kursus' => $kursus->id, 'material' => $material['id']]) }}" 
                                   class="btn-simple btn-primary"
                                   id="video-link-{{ $material['id'] }}"
                                   onclick="handleVideoWatch(event, {{ $material['id'] }}, {{ $kursus->id }})">
                                    <i class="fas fa-play"></i> Tonton Video
                                </a>
                            @else
                                <button class="btn-simple btn-secondary" disabled id="locked-video-{{ $material['id'] }}">
                                    <i class="fas fa-lock"></i> 
                                    @if(!$isVideoAvailable)
                                        Video Tidak Tersedia
                                    @else
                                        Video Terkunci
                                    @endif
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                @if(!$hasContent)
                    <div class="no-content-message">
                        <i class="fas fa-info-circle me-2"></i>
                        Tidak ada aktivitas yang tersedia untuk materi ini.
                    </div>
                @endif
            </div>

            @else
            <!-- Header untuk pretest, posttest, dan recap -->
            <div class="step-header no-toggle">
                <div class="header-content">
                    <h3 class="step-title">
                        {{ $loop->iteration }}. {{ $material['title'] }}
                    </h3>
                    
                    @if($material['description'])
                    <div class="material-description">
                        {{ $material['description'] }}
                    </div>
                    @endif
                    
                    @if(in_array($material['type'], ['pre_test', 'post_test']))
                    <div class="test-info">
                        <span class="info-badge badge-warning">
                            <i class="fas fa-clock me-1"></i>
                            {{ $material['type'] == 'pre_test' ? $material['durasi_pretest'] : $material['durasi_posttest'] }} menit
                        </span>
                        <span class="info-badge badge-success">
                            <i class="fas fa-list-ol me-1"></i>
                            @if($material['type'] == 'pre_test')
                                {{ count($material['soal_pretest'] ?? []) }} soal
                            @else
                                {{ count($material['soal_posttest'] ?? []) }} soal
                            @endif
                        </span>
                    </div>
                    @endif
                </div>

                <span class="step-status status-{{ $material['status'] }}">
                    @if($material['status'] == 'locked') 
                        <span style="font-size: 14px;">
                            <i class="fas fa-lock"></i> Terkunci
                        </span>
                    @elseif($material['status'] == 'current') 
                        Sedang Berjalan
                    @elseif($material['status'] == 'completed') 
                        Selesai
                    @endif
                </span>
            </div>

            <!-- Tombol aksi untuk pretest, posttest, dan recap -->
            <div class="task-action" style="padding: 15px 0 0 0; border-top: 1px solid #f1f3f4; margin-top: 15px;">
                @if($material['type'] == 'pre_test' || $material['type'] == 'post_test')
                    @if($material['status'] == 'locked')
                        <button class="btn-simple btn-secondary" disabled>
                            <span style="font-size: 14px;">
                                <i class="fas fa-lock"></i> Materi Terkunci
                            </span>
                        </button>
                    @elseif($material['is_test_completed'])
                        @php
                            $score = $material['test_score'];
                            $scoreClass = $score >= 60 ? 'score-pass' : 'score-fail';
                        @endphp

                        <span class="btn-simple {{ $scoreClass }}">
                            Nilai: {{ number_format($score, 2) }}%
                        </span>
                    @else
                        <a href="{{ route('mitra.kursus.test.show', ['kursus' => $kursus->id, 'material' => $material['id'], 'testType' => $material['type']]) }}" 
                           class="btn-simple btn-primary">
                            <i class="fas fa-play"></i> Mulai {{ $material['type'] == 'pre_test' ? 'Pre Test' : 'Post Test' }}
                        </a>
                    @endif

                @elseif($material['type'] == 'recap')
                    @if($material['status'] == 'locked')
                        <button class="btn-simple btn-secondary" disabled>
                            <span style="font-size: 14px;">
                                <i class="fas fa-lock"></i> Materi Terkunci
                            </span>
                        </button>
                    @else
                        <a href="{{ route('mitra.kursus.recap.show', ['kursus' => $kursus->id, 'material' => $material['id']]) }}" 
                           class="btn-simple btn-primary">
                            <i class="fas fa-chart-bar"></i> Lihat Rekap Nilai
                        </a>
                    @endif
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ==============================================
// FUNGSI UTAMA YANG DIPERBAIKI
// ==============================================

/**
 * Deteksi apakah semua sub-tasks dalam material sudah selesai
 */
function checkAllSubTasksCompleted(materialId) {
    const materialStep = document.getElementById(`material-${materialId}`);
    if (!materialStep) return false;
    
    // Dapatkan status dari data attributes
    const hasAttendance = materialStep.getAttribute('data-has-attendance') === 'true';
    const hasMaterial = materialStep.getAttribute('data-has-material') === 'true';
    const hasVideo = materialStep.getAttribute('data-has-video') === 'true';
    
    let allCompleted = true;
    
    // Periksa kehadiran
    if (hasAttendance) {
        const attendanceIcon = document.getElementById(`attendance-icon-${materialId}`);
        const attendanceCompleted = attendanceIcon && 
                                   (attendanceIcon.style.backgroundColor === 'rgb(40, 167, 69)' || 
                                    attendanceIcon.style.backgroundColor === '#28a745');
        
        if (!attendanceCompleted) allCompleted = false;
    }
    
    // Periksa materi
    if (hasMaterial) {
        const materialIcon = document.getElementById(`material-icon-${materialId}`);
        const materialCompleted = materialIcon && 
                                 (materialIcon.style.backgroundColor === 'rgb(40, 167, 69)' || 
                                  materialIcon.style.backgroundColor === '#28a745');
        
        if (!materialCompleted) allCompleted = false;
    }
    
    // Periksa video
    if (hasVideo) {
        const videoIcon = document.getElementById(`video-icon-${materialId}`);
        const videoCompleted = videoIcon && 
                              (videoIcon.style.backgroundColor === 'rgb(40, 167, 69)' || 
                               videoIcon.style.backgroundColor === '#28a745');
        
        if (!videoCompleted) allCompleted = false;
    }
    
    return allCompleted;
}

/**
 * Update status material menjadi selesai
 */
function updateMaterialToCompleted(materialId) {
    const materialStep = document.getElementById(`material-${materialId}`);
    if (!materialStep || !materialStep.classList.contains('current')) return;
    
    // Update UI
    materialStep.classList.remove('current');
    materialStep.classList.add('completed');
    materialStep.style.borderLeftColor = '#28a745';
    materialStep.style.background = '#f8fff9';
    
    // Update status text
    const statusSpan = materialStep.querySelector('.step-status');
    if (statusSpan) {
        statusSpan.innerHTML = '<i class="fas fa-check"></i> Selesai';
        statusSpan.className = 'step-status status-completed';
    }
    
    // Update data attribute
    materialStep.setAttribute('data-material-status', 'completed');
    
    console.log(`✅ Material ${materialId} selesai`);
    
    // Unlock material berikutnya
    unlockNextMaterial(materialId);
    
    // Update progress
    updateProgressBar();
    
    // Notifikasi ke server
    notifyServerMaterialCompleted(materialId);
}

/**
 * Membuka material berikutnya yang terkunci
 */
function unlockNextMaterial(currentMaterialId) {
    const allMaterials = document.querySelectorAll('.flow-step');
    let foundCurrent = false;
    let nextMaterial = null;
    
    allMaterials.forEach(material => {
        const materialId = material.getAttribute('id').replace('material-', '');
        
        if (materialId == currentMaterialId) {
            foundCurrent = true;
            return;
        }
        
        if (foundCurrent && material.classList.contains('locked')) {
            nextMaterial = material;
            foundCurrent = false;
        }
    });
    
    if (nextMaterial) {
        const nextMaterialId = nextMaterial.getAttribute('id').replace('material-', '');
        
        // Update UI
        nextMaterial.classList.remove('locked');
        nextMaterial.classList.add('current');
        nextMaterial.style.borderLeftColor = '#1e3c72';
        nextMaterial.style.background = '#f8f9fa';
        
        // Update status
        const statusSpan = nextMaterial.querySelector('.step-status');
        if (statusSpan) {
            statusSpan.innerHTML = 'Sedang Berjalan';
            statusSpan.className = 'step-status status-current';
        }
        
        // Update data attribute
        nextMaterial.setAttribute('data-material-status', 'current');
        
        console.log(`🔓 Material ${nextMaterialId} terbuka`);
        
        // Auto-expand material baru
        setTimeout(() => {
            const header = nextMaterial.querySelector('.step-header');
            if (header && !header.classList.contains('no-toggle')) {
                const onclickAttr = header.getAttribute('onclick');
                if (onclickAttr && onclickAttr.includes('toggleSubTasks')) {
                    const newMaterialId = onclickAttr.match(/toggleSubTasks\((\d+)\)/)[1];
                    toggleSubTasks(newMaterialId);
                }
            }
        }, 500);
    }
}

/**
 * Notifikasi ke server
 */
function notifyServerMaterialCompleted(materialId) {
    const kursusId = {{ $kursus->id }};
    
    fetch(`/mitra/kursus/${kursusId}/materials/${materialId}/complete`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log(`📡 Server mencatat material ${materialId} selesai`);
        }
    })
    .catch(error => {
        console.error('❌ Error notifying server:', error);
    });
}

/**
 * Alternative: Gunakan event listener untuk deteksi download selesai
 */
function handleDownloadWithEvent(event, materialId, kursusId) {
    event.preventDefault();
    
    Swal.fire({
        title: 'Menyiapkan file...',
        text: 'Sedang mempersiapkan file untuk didownload.',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Buat iframe untuk download
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = `/mitra/kursus/${kursusId}/materials/${materialId}/download`;
    document.body.appendChild(iframe);
    
    // Deteksi ketika download selesai
    iframe.onload = function() {
        setTimeout(() => {
            Swal.close();
            
            // Update UI
            updateMaterialStatus(materialId, kursusId);
            
            // Cek dan unlock material berikutnya
            checkAndUnlockNextMaterial(materialId);
            
            Swal.fire({
                title: 'Berhasil!',
                text: 'File berhasil didownload. Materi berikutnya sekarang terbuka!',
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });
            
            // Hapus iframe
            document.body.removeChild(iframe);
        }, 1000);
    };
}

/**
 * Cek dan unlock material berikutnya via AJAX
 */
function checkAndUnlockNextMaterial(currentMaterialId) {
    const kursusId = {{ $kursus->id }};
    
    fetch(`/mitra/kursus/${kursusId}/materials/${currentMaterialId}/check-unlock`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.next_material_id) {
            // Unlock material berikutnya
            unlockNextMaterial(currentMaterialId);
        }
    })
    .catch(error => {
        console.error('Error checking unlock:', error);
    });
}

/**
 * Handle download file
 */
function handleDownload(event, materialId, kursusId) {
    event.preventDefault();
    
    Swal.fire({
        title: 'Menyiapkan file...',
        text: 'Sedang mempersiapkan file untuk didownload.',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    const downloadUrl = `/mitra/kursus/${kursusId}/materials/${materialId}/download`;
    
    fetch(downloadUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Gagal mendownload file');
        }
        
        const contentDisposition = response.headers.get('Content-Disposition');
        let fileName = 'materi.zip';
        
        if (contentDisposition && contentDisposition.includes('filename=')) {
            fileName = contentDisposition.split('filename=')[1].replace(/"/g, '');
        }
        
        return response.blob().then(blob => ({ blob, fileName }));
    })
    .then(({ blob, fileName }) => {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = fileName;
        link.style.display = 'none';
        
        document.body.appendChild(link);
        link.click();
        
        window.URL.revokeObjectURL(url);
        document.body.removeChild(link);
        
        Swal.close();
        
        Swal.fire({
            title: 'Berhasil!',
            text: 'File berhasil didownload.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
        
        updateMaterialStatus(materialId, kursusId);
    })
    .catch(error => {
        console.error('Download error:', error);
        
        Swal.fire({
            title: 'Error!',
            text: 'Gagal mendownload file: ' . error.message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}

/**
 * Update status material setelah download
 */
function updateMaterialStatus(materialId, kursusId) {
    // Update material icon
    const materialIcon = document.getElementById(`material-icon-${materialId}`);
    if (materialIcon) {
        materialIcon.style.background = '#28a745';
        materialIcon.style.color = 'white';
    }
    
    // Update button
    const downloadButton = document.getElementById(`download-button-${materialId}`);
    if (downloadButton) {
        downloadButton.innerHTML = '<i class="fas fa-check"></i> Selesai';
        downloadButton.className = 'btn-simple btn-success';
        downloadButton.onclick = null;
        downloadButton.href = '#';
    }
    
    // Update file status
    const fileItems = document.querySelectorAll(`#material-${materialId} .file-item`);
    fileItems.forEach(item => {
        const fileStatus = item.querySelector('.file-status');
        if (fileStatus) {
            fileStatus.innerHTML = '<i class="fas fa-check text-success"></i>';
        }
    });
    
    // Update material progress
    const materialStep = document.getElementById(`material-${materialId}`);
    if (materialStep) {
        materialStep.setAttribute('data-material-status', 'completed');
    }
    
    // Cek semua sub-tasks
    if (checkAllSubTasksCompleted(materialId)) {
        updateMaterialToCompleted(materialId);
    } else {
        updateProgressBar();
    }
}

/**
 * Handle video watch
 */
function handleVideoWatch(event, materialId, kursusId) {
    event.preventDefault();
    
    Swal.fire({
        title: 'Membuka video...',
        text: 'Video akan segera dibuka.',
        icon: 'info',
        timer: 1000,
        showConfirmButton: false
    });
    
    setTimeout(() => {
        window.location.href = `/mitra/kursus/${kursusId}/materials/${materialId}/video`;
    }, 500);
    
    // Record video progress
    fetch(`/mitra/kursus/${kursusId}/materials/${materialId}/record-video`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .catch(error => {
        console.error('Error recording video:', error);
    });
}

// ==============================================
// LISTENER UNTUK UPDATE DARI VIDEO PLAYER
// ==============================================

// Listen for messages from video player
window.addEventListener('message', function(event) {
    if (event.data.type === 'video_completed') {
        if (event.data.materialId && event.data.kursusId === {{ $kursus->id }}) {
            console.log(`🎬 Video ${event.data.materialId} completed, updating UI...`);
            updateVideoProgress(event.data.materialId);
            updateProgressBar();
        }
    }
});

// Auto-refresh saat kembali dari video player
window.addEventListener('pageshow', function(event) {
    if (event.persisted || performance.getEntriesByType("navigation")[0].type === 'back_forward') {
        console.log('🔄 Page restored from bfcache, refreshing progress...');
        setTimeout(() => {
            updateProgressBar();
            refreshMaterialStatus();
        }, 500);
    }
});

// Refresh material status
function refreshMaterialStatus() {
    const currentMaterials = document.querySelectorAll('.flow-step.current');
    
    currentMaterials.forEach(materialStep => {
        const materialId = materialStep.getAttribute('id').replace('material-', '');
        
        fetch(`/mitra/kursus/{{ $kursus->id }}/material/${materialId}/status`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateMaterialUI(materialId, data);
            }
        })
        .catch(error => {
            console.error('Error refreshing material status:', error);
        });
    });
}

function updateMaterialUI(materialId, data) {
    const materialStep = document.getElementById(`material-${materialId}`);
    if (!materialStep) return;
    
    // Update video status jika ada
    if (data.video_status === 'completed') {
        updateVideoProgress(materialId);
    }
    
    // Update attendance status jika ada
    if (data.attendance_status === 'completed') {
        updateAttendanceUI(materialId);
    }
    
    // Update material status jika ada
    if (data.material_status === 'completed') {
        const materialIcon = document.getElementById(`material-icon-${materialId}`);
        if (materialIcon) {
            materialIcon.style.background = '#28a745';
            materialIcon.style.color = 'white';
        }
    }
    
    // Cek apakah semua selesai
    if (checkAllSubTasksCompleted(materialId)) {
        updateMaterialToCompleted(materialId);
    }
}

/**
 * Update video progress (dipanggil dari halaman video)
 */
function updateVideoProgress(materialId) {
    const videoIcon = document.getElementById(`video-icon-${materialId}`);
    if (videoIcon) {
        videoIcon.style.background = '#28a745';
        videoIcon.style.color = 'white';
    }
    
    const videoButton = document.getElementById(`video-button-${materialId}`);
    const videoLink = document.getElementById(`video-link-${materialId}`);
    
    if (videoButton) {
        videoButton.innerHTML = '<i class="fas fa-check"></i> Selesai';
        videoButton.className = 'btn-simple btn-success';
    }
    
    if (videoLink) {
        videoLink.innerHTML = '<i class="fas fa-check"></i> Selesai';
        videoLink.className = 'btn-simple btn-success';
        videoLink.onclick = null;
        videoLink.href = '#';
    }
    
    // Update material progress
    const materialStep = document.getElementById(`material-${materialId}`);
    if (materialStep) {
        materialStep.setAttribute('data-video-status', 'completed');
    }
    
    // Cek semua sub-tasks
    if (checkAllSubTasksCompleted(materialId)) {
        updateMaterialToCompleted(materialId);
    } else {
        updateProgressBar();
    }
}

/**
 * Mark attendance
 */
function markAttendance(materialId) {
    const kursusId = {{ $kursus->id }};
    
    Swal.fire({
        title: 'Konfirmasi Kehadiran',
        text: 'Apakah Anda yakin ingin menandai kehadiran?',
        icon: 'question',
        showConfirmButton: true,
        confirmButtonColor: '#1e3c72',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Tandai Hadir',
        cancelButtonText: 'Batal',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang mencatat kehadiran',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(`/mitra/kursus/${kursusId}/materials/${materialId}/attendance`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateAttendanceUI(materialId);
                    unlockDownloadButton(materialId, kursusId);
                    
                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#1e3c72',
                        timer: 2000
                    });
                    
                    if (checkAllSubTasksCompleted(materialId)) {
                        updateMaterialToCompleted(materialId);
                    } else {
                        updateProgressBar();
                    }
                } else {
                    throw new Error(data.message || 'Gagal mencatat kehadiran');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan: ' + error.message,
                    icon: 'error'
                });
            });
        }
    });
}

/**
 * Update attendance UI
 */
function updateAttendanceUI(materialId) {
    const materialStep = document.getElementById(`material-${materialId}`);
    const attendanceIcon = document.getElementById(`attendance-icon-${materialId}`);
    
    if (attendanceIcon) {
        attendanceIcon.style.background = '#28a745';
        attendanceIcon.style.color = 'white';
    }
    
    if (materialStep) {
        materialStep.setAttribute('data-attendance-status', 'completed');
    }
    
    const attendanceButton = document.querySelector(`button[onclick="markAttendance(${materialId})"]`);
    if (attendanceButton) {
        attendanceButton.outerHTML = `
            <span class="btn-simple btn-success">
                <i class="fas fa-check"></i> Selesai
            </span>
        `;
    }
}

/**
 * Unlock download button setelah attendance
 */
function unlockDownloadButton(materialId, kursusId) {
    const lockedButton = document.getElementById(`locked-material-${materialId}`);
    if (lockedButton) {
        const materialStep = document.getElementById(`material-${materialId}`);
        const fileList = materialStep.querySelector('.file-list');
        const fileItems = fileList ? fileList.querySelectorAll('.file-item') : [];
        const fileCount = fileItems.length;
        
        const newButton = `
            <a href="javascript:void(0)" 
               class="btn-simple btn-primary"
               id="download-button-${materialId}"
               onclick="handleDownload(event, ${materialId}, ${kursusId})">
                <i class="fas fa-download"></i> 
                ${fileCount > 1 ? 'Download Semua' : 'Download File'}
            </a>
        `;
        
        lockedButton.outerHTML = newButton;
    }
}

/**
 * Update progress bar
 */
function updateProgressBar() {
    fetch(`/mitra/kursus/{{ $kursus->id }}/progress`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const progressFill = document.querySelector('.progress-fill');
            const progressInfo = document.querySelector('.progress-info span:first-child');
            const progressPercentageSpan = document.querySelector('.progress-info span:last-child');
            
            if (progressFill) {
                progressFill.style.width = `${data.progress_percentage}%`;
            }
            
            if (progressInfo) {
                progressInfo.textContent = `${data.completed_materials} dari ${data.total_materials} aktivitas selesai`;
            }
            
            if (progressPercentageSpan) {
                progressPercentageSpan.textContent = `${data.progress_percentage}%`;
            }
        }
    })
    .catch(error => {
        console.error('Error updating progress:', error);
    });
}

/**
 * Toggle sub-tasks
 */
function toggleSubTasks(materialId) {
    const subTasks = document.getElementById('subTasks' + materialId);
    const toggleIcon = document.getElementById('toggle' + materialId);
    
    if (subTasks && toggleIcon) {
        document.querySelectorAll('.sub-tasks').forEach(task => {
            if (task.id !== 'subTasks' + materialId) {
                task.classList.remove('expanded');
            }
        });
        
        document.querySelectorAll('.step-toggle').forEach(icon => {
            if (icon.id !== 'toggle' + materialId) {
                icon.classList.remove('rotated');
            }
        });
        
        subTasks.classList.toggle('expanded');
        toggleIcon.classList.toggle('rotated');
    }
}

/**
 * Cek status material saat halaman dimuat
 */
function checkInitialMaterialStatus() {
    const currentMaterials = document.querySelectorAll('.flow-step.current');
    
    currentMaterials.forEach(materialStep => {
        const materialId = materialStep.getAttribute('id').replace('material-', '');
        
        if (checkAllSubTasksCompleted(materialId)) {
            updateMaterialToCompleted(materialId);
        }
    });
}

// ==============================================
// INISIALISASI
// ==============================================

document.addEventListener('DOMContentLoaded', function() {
    // Auto-expand current material
    const currentMaterials = document.querySelectorAll('.flow-step.current');
    currentMaterials.forEach(step => {
        const header = step.querySelector('.step-header');
        if (header && !header.classList.contains('no-toggle')) {
            const onclickAttr = header.getAttribute('onclick');
            if (onclickAttr && onclickAttr.includes('toggleSubTasks')) {
                const materialId = onclickAttr.match(/toggleSubTasks\((\d+)\)/)[1];
                setTimeout(() => {
                    toggleSubTasks(materialId);
                }, 300);
            }
        }
    });
    
    // Cek status material saat halaman dimuat
    setTimeout(() => {
        checkInitialMaterialStatus();
        updateProgressBar();
    }, 1000);
});

// Auto-refresh progress setiap 10 detik
setInterval(() => {
    updateProgressBar();
}, 10000);
</script>

@endsection

@php
function getFileIcon($extension) {
    $icons = [
        'pdf' => 'fa-file-pdf text-danger',
        'doc' => 'fa-file-word text-primary',
        'docx' => 'fa-file-word text-primary',
        'ppt' => 'fa-file-powerpoint text-warning',
        'pptx' => 'fa-file-powerpoint text-warning',
        'xls' => 'fa-file-excel text-success',
        'xlsx' => 'fa-file-excel text-success',
        'jpg' => 'fa-file-image text-info',
        'jpeg' => 'fa-file-image text-info',
        'png' => 'fa-file-image text-info',
        'gif' => 'fa-file-image text-info',
        'txt' => 'fa-file-alt text-secondary',
        'zip' => 'fa-file-archive text-secondary',
        'rar' => 'fa-file-archive text-secondary'
    ];
    
    return $icons[strtolower($extension)] ?? 'fa-file text-secondary';
}
@endphp