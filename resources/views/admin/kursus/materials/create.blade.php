@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.admin')

@section('title', 'MOOC BPS - Tambah Materi')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">
<style>
    .page-title-box {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        border: none;
        border-radius: 8px;
        font-weight: 600;
        padding: 12px 24px;
    }
    
    .btn-secondary {
        background: linear-gradient(135deg, #95a5a6, #7f8c8d);
        border: none;
        border-radius: 8px;
        font-weight: 600;
        padding: 12px 24px;
    }
    
    .soal-item {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background: #f8f9fa;
    }
    
    .pretest-section, .posttest-section {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .posttest-section {
        background: #d1ecf1;
        border: 1px solid #bee5eb;
    }
    
    .content-type-option {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .content-type-option:hover {
        border-color: #1e3c72;
    }
    
    .content-type-option.selected {
        border-color: #1e3c72;
        background-color: #f8f9fa;
    }
    
    .content-type-checkbox {
        display: none;
    }
    
    .order-info {
        background: #e3f2fd;
        border: 1px solid #bbdefb;
        border-radius: 8px;
        padding: 10px 15px;
        margin-top: 5px;
    }
    
    .navigation-header {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        border-left: 4px solid #1e3c72;
    }
    
    .order-display {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px 15px;
        font-weight: 600;
        color: #1e3c72;
    }
    
    .info-icon {
        cursor: pointer;
        color: #6c757d;
        transition: color 0.3s ease;
    }
    
    .info-icon:hover {
        color: #1e3c72;
    }
    
    .tooltip-inner {
        max-width: 300px;
        padding: 12px;
        background: #1e3c72;
        color: white;
        border-radius: 8px;
        text-align: left;
    }
    
    .bs-tooltip-auto[data-popper-placement^=top] .tooltip-arrow::before,
    .bs-tooltip-top .tooltip-arrow::before {
        border-top-color: #1e3c72;
    }
    
    .form-check-label {
        display: flex;
        align-items: center;
    }
    
    .file-preview {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-top: 10px;
    }
    
    .file-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .file-item:last-child {
        border-bottom: none;
    }
    
    .file-actions {
        display: flex;
        gap: 5px;
    }
    
    .file-input-wrapper {
        position: relative;
    }
    
    .file-input-wrapper input[type="file"] {
        position: absolute;
        left: -9999px;
    }
    
    /* Video Section Styles */
    .video-control-section {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .video-question-item {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .player-config-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    
    .config-item {
        background: white;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .config-item label {
        font-weight: 500;
        margin-bottom: 5px;
        display: block;
    }
    
    .config-item small {
        color: #6c757d;
        font-size: 12px;
    }
    
    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .fade-in {
        animation: fadeIn 0.3s ease;
    }
    
    /* Style untuk YouTube preview */
    .youtube-preview-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-top: 10px;
    }
    
    .youtube-preview-box h6 {
        color: #ff0000;
    }
    
    /* Style untuk progress bars */
    .progress-bar-google {
        background: linear-gradient(135deg, #4285f4, #34a853, #fbbc05, #ea4335);
        background-size: 400% 400%;
        animation: gradient 3s ease infinite;
    }
    
    .progress-bar-local {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
    }
    
    @keyframes gradient {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    .google-drive-info {
        background: #e8f5e9;
        border: 1px solid #c8e6c9;
        border-radius: 8px;
        padding: 15px;
        margin-top: 10px;
    }
    
    .google-drive-info h6 {
        color: #2e7d32;
    }
    
    .upload-progress {
        background: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-top: 10px;
        display: none;
    }
    
    .file-size-info {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
    
    .upload-status {
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 5px;
    }
    
    .status-uploading {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .status-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .video-preview-thumbnail {
        width: 100%;
        height: 200px;
        background: #f8f9fa;
        border: 1px dashed #dee2e6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        margin-top: 10px;
    }
    
    .video-preview-thumbnail i {
        font-size: 48px;
    }
    
    /* Import Excel Styles */
    .excel-preview-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    
    .excel-preview-table th {
        background: #1e3c72;
        color: white;
        padding: 8px;
        text-align: left;
        font-size: 12px;
    }
    
    .excel-preview-table td {
        border: 1px solid #dee2e6;
        padding: 6px;
        font-size: 11px;
    }
    
    .excel-preview-table tr:nth-child(even) {
        background: #f8f9fa;
    }
    
    .question-imported {
        background: #e8f5e9 !important;
        border-left: 4px solid #34a853 !important;
        animation: pulse 0.5s ease;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
    
    .disabled-option {
        opacity: 0.5;
        cursor: not-allowed !important;
    }
    
    .disabled-option:hover {
        border-color: #e9ecef !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .player-config-grid {
            grid-template-columns: 1fr;
        }
        
        .content-type-option {
            padding: 10px;
        }
        
        .soal-item {
            padding: 10px;
        }
        
        .btn-primary, .btn-secondary {
            padding: 10px 20px;
            font-size: 14px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Navigation Header dengan Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="navigation-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Tambah Materi Baru</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.kursus.index') }}">Kursus</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.kursus.show', $kursus) }}">{{ Str::limit($kursus->judul_kursus, 30) }}</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.kursus.materials.index', $kursus) }}">Materi</a>
                                </li>
                                <li class="breadcrumb-item active">Tambah Materi</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="{{ route('admin.kursus.materials.index', $kursus) }}" class="btn btn-secondary">
                        <i class="mdi mdi-arrow-left me-2"></i> Kembali ke Daftar Materi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <!-- Kosongkan bagian ini atau tambahkan elemen lain jika perlu -->
                </div>
                <h4 class="page-title">Tambah Materi untuk: {{ $kursus->judul_kursus }}</h4>
                <p class="mb-0 text-white-50">Lengkapi form berikut untuk menambahkan materi baru</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.kursus.materials.store', $kursus) }}" method="POST" enctype="multipart/form-data" id="materialForm">
                        @csrf

                        <!-- Informasi Dasar Materi -->
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="title" class="form-label">Judul Materi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title') }}" required
                                       placeholder="Contoh: Materi 1 - Pengenalan Dasar">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">
                                    Urutan Tampilan 
                                    <i class="mdi mdi-information-outline info-icon ms-1" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Urutan akan disesuaikan otomatis oleh sistem. Urutan dapat diubah nanti melalui fitur sortir materi."></i>
                                </label>
                                <!-- Input hidden untuk order -->
                                <input type="hidden" id="order" name="order" value="{{ $totalMaterials + 1 }}">
                                
                                <div class="order-display">
                                    <i class="mdi mdi-sort-numeric-asc me-2"></i>
                                    Urutan ke-{{ $totalMaterials + 1 }}
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="description" class="form-label">Deskripsi Materi</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3"
                                          placeholder="Deskripsi singkat tentang materi ini">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Tipe Konten Materi (Multiple Selection) -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Jenis Konten Materi <span class="text-danger">*</span></label>
                                <small class="text-muted d-block mb-2">Pilih satu atau lebih jenis konten yang akan dimasukkan dalam materi ini</small>
                                
                                <div class="row">
                                    <!-- Materi PDF/PPT -->
                                    <div class="col-md-6 mb-3">
                                        <div class="content-type-option" onclick="toggleContentType('file')">
                                            <div class="form-check">
                                                <input class="form-check-input content-type-checkbox" type="checkbox" 
                                                       id="content_type_file" name="content_types[]" value="file"
                                                       {{ in_array('file', old('content_types', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="content_type_file">
                                                    <i class="mdi mdi-file-pdf-box mdi-24px text-danger me-2"></i>
                                                    Materi PDF/PPT
                                                </label>
                                            </div>
                                            <small class="text-muted">Upload file materi dalam format PDF, PPT, atau dokumen (disimpan di server lokal)</small>
                                        </div>
                                    </div>

                                    <!-- Video -->
                                    <div class="col-md-6 mb-3">
                                        <div class="content-type-option" onclick="toggleContentType('video')">
                                            <div class="form-check">
                                                <input class="form-check-input content-type-checkbox" type="checkbox" 
                                                       id="content_type_video" name="content_types[]" value="video"
                                                       {{ in_array('video', old('content_types', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="content_type_video">
                                                    <i class="mdi mdi-video mdi-24px text-primary me-2"></i>
                                                    Video Pembelajaran
                                                </label>
                                            </div>
                                            <small class="text-muted">Video akan diupload ke Google Drive atau Local Storage</small>
                                        </div>
                                    </div>

                                    <!-- Pretest -->
                                    <div class="col-md-6 mb-3">
                                        <div class="content-type-option" onclick="toggleContentType('pretest')">
                                            <div class="form-check">
                                                <input class="form-check-input content-type-checkbox" type="checkbox" 
                                                       id="content_type_pretest" name="content_types[]" value="pretest"
                                                       {{ in_array('pretest', old('content_types', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="content_type_pretest">
                                                    <i class="mdi mdi-clipboard-text mdi-24px text-warning me-2"></i>
                                                    Pretest
                                                </label>
                                            </div>
                                            <small class="text-muted">Tes awal untuk mengukur pengetahuan sebelum belajar</small>
                                        </div>
                                    </div>

                                    <!-- Posttest -->
                                    <div class="col-md-6 mb-3">
                                        <div class="content-type-option" onclick="toggleContentType('posttest')">
                                            <div class="form-check">
                                                <input class="form-check-input content-type-checkbox" type="checkbox" 
                                                       id="content_type_posttest" name="content_types[]" value="posttest"
                                                       {{ in_array('posttest', old('content_types', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="content_type_posttest">
                                                    <i class="mdi mdi-clipboard-check mdi-24px text-success me-2"></i>
                                                    Posttest
                                                </label>
                                            </div>
                                            <small class="text-muted">Tes akhir untuk mengukur pemahaman setelah belajar</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kehadiran -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="attendance_required" 
                                           name="attendance_required" value="1" {{ old('attendance_required') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="attendance_required">
                                        Wajib Kehadiran
                                        <i class="mdi mdi-information-outline info-icon ms-1" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Jika diaktifkan, peserta wajib mengkonfirmasi kehadiran untuk materi ini sebelum dapat melanjutkan ke materi berikutnya."></i>
                                    </label>
                                </div>
                                <small class="text-muted">Centang jika peserta wajib mengkonfirmasi kehadiran untuk materi ini</small>
                            </div>
                        </div>

                        <!-- Konten Materi: File PDF/PPT -->
                        <div id="file-content-section" class="row mb-3" style="display: none;">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="mdi mdi-file-pdf-box text-danger me-2"></i>Materi File</h6>
                                        <div class="row">
                                            <div class="col-12">
                                            <div class="mb-3">
    <label class="form-label">Tambah File Materi</label>
    <div class="d-flex gap-2 mb-2">
        <div class="file-input-wrapper">
            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('file_input').click()">
                <i class="mdi mdi-plus me-2"></i> Tambah File
            </button>
            <input type="file" id="file_input" 
                   accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png" 
                   style="display: none;" multiple
                   onchange="handleFileSelection(this.files)">
        </div>
        
        <button type="button" class="btn btn-outline-danger" 
                onclick="clearAllFiles()" 
                id="clear-all-btn">
            <i class="mdi mdi-delete-sweep me-2"></i> Hapus Semua
        </button>
    </div>
    
    <small class="text-muted">
        Klik "Tambah File" berkali-kali untuk menambahkan file satu per satu.
        Format: PDF, DOC, PPT, atau gambar. Maksimal 10MB per file.
    </small>
    
    <!-- File Preview Area -->
    <div id="file-preview" class="file-preview mt-3" style="display: none;">
        <h6 class="mb-3">File yang akan diupload:</h6>
        <div id="file-list"></div>
    </div>
</div>

                                                <!-- Hidden input untuk menyimpan file paths -->
                                                <div id="file-inputs-container">
                                                    <!-- File inputs akan ditambahkan di sini via JavaScript -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Konten Materi: Video -->
                        <div id="video-content-section" class="row mb-3" style="display: none;">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="mdi mdi-video text-primary me-2"></i>Materi Video</h6>
                                        
                                        <!-- Video Type Selection -->
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label for="video_type" class="form-label">Jenis Video <span class="text-danger">*</span></label>
                                                <select class="form-select @error('video_type') is-invalid @enderror" 
                                                        id="video_type" name="video_type" onchange="toggleVideoType()">
                                                    <option value="">Pilih Jenis Video</option>
                                                    <option value="youtube" {{ old('video_type') == 'youtube' ? 'selected' : '' }}>YouTube</option>
                                                    <option value="hosted" {{ old('video_type') == 'hosted' ? 'selected' : '' }}>Google Drive</option>
                                                    <option value="local" {{ old('video_type') == 'local' ? 'selected' : '' }}>Local Storage</option>
                                                </select>
                                                @error('video_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Video URL (for YouTube only) -->
                                        <div class="row mb-3" id="video-url-section" style="display: none;">
                                            <div class="col-12">
                                                <label for="video_url" class="form-label">URL Video YouTube</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="mdi mdi-youtube text-danger"></i>
                                                    </span>
                                                    <input type="url" class="form-control @error('video_url') is-invalid @enderror" 
                                                           id="video_url" name="video_url" value="{{ old('video_url') }}" 
                                                           placeholder="Contoh: https://youtube.com/watch?v=VIDEO_ID atau https://youtu.be/VIDEO_ID">
                                                </div>
                                                <small class="text-muted" id="url-help-text">
                                                    Masukkan URL YouTube lengkap. Video akan ditampilkan menggunakan embed player YouTube.
                                                </small>
                                                @error('video_url')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                
                                                <!-- YouTube URL Preview -->
                                                <div id="youtube-preview" class="mt-3" style="display: none;">
                                                    <div class="alert alert-info">
                                                        <i class="mdi mdi-youtube me-2"></i>
                                                        <strong>YouTube Video Detected</strong>
                                                        <p class="mb-0 small" id="youtube-info"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Video File Upload (for Google Drive) -->
                                        <div class="row mb-3" id="video-file-hosted-section" style="display: none;">
                                            <div class="col-12">
                                                <div class="alert alert-warning">
                                                    <i class="mdi mdi-google-drive me-2"></i>
                                                    <strong>Upload ke Google Drive</strong>
                                                    <p class="mb-0 small">Video akan diupload ke Google Drive untuk penyimpanan cloud yang aman.</p>
                                                </div>
                                                
                                                <label for="video_file_hosted" class="form-label">Upload Video ke Google Drive</label>
                                                <input type="file" class="form-control @error('video_file') is-invalid @enderror" 
                                                       id="video_file_hosted" name="video_file" accept=".mp4,.webm,.avi,.mov,.wmv,.mkv" 
                                                       onchange="previewVideoFile(this, 'hosted')">
                                                <small class="text-muted">
                                                    Format: MP4, WebM, AVI, MOV, WMV, MKV. Maksimal 100MB<br>
                                                    Video akan diupload ke Google Drive secara otomatis.
                                                </small>
                                                @error('video_file')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Video File Upload (for Local Storage) -->
                                        <div class="row mb-3" id="video-file-local-section" style="display: none;">
                                            <div class="col-12">
                                                <div class="alert alert-info">
                                                    <i class="mdi mdi-server me-2"></i>
                                                    <strong>Simpan di Local Storage</strong>
                                                </div>
                                                
                                                <label for="video_file_local" class="form-label">Upload Video ke Local Storage</label>
                                                <input type="file" class="form-control @error('video_file') is-invalid @enderror" 
                                                       id="video_file_local" name="video_file" accept=".mp4,.webm,.avi,.mov,.wmv,.mkv" 
                                                       onchange="previewVideoFile(this, 'local')">
                                                <small class="text-muted">
                                                    Format: MP4, WebM, AVI, MOV, WMV, MKV. Maksimal 100MB<br>
                                                    Video akan disimpan di server lokal dan diputar menggunakan <strong>video.js</strong>.
                                                </small>
                                                @error('video_file')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                
                                                <!-- Video Preview untuk Local -->
                                                <div id="video-preview-local" style="display: none;">
                                                    <div class="video-preview-thumbnail mt-3">
                                                        <i class="mdi mdi-video"></i>
                                                    </div>
                                                    <div id="video-file-info-local" class="file-size-info"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Upload Progress -->
                                        <div id="upload-progress" class="upload-progress">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span id="upload-message">Uploading video...</span>
                                                <span id="upload-percentage">0%</span>
                                            </div>
                                            <div class="progress">
                                                <div id="upload-progress-bar" class="progress-bar" 
                                                     role="progressbar" style="width: 0%"></div>
                                            </div>
                                            <div id="upload-status" class="upload-status mt-2"></div>
                                        </div>

                                        <!-- Durasi Video (Otomatis) -->
                                        <div class="row mb-3" id="duration-info-section" style="display: none;">
                                            <div class="col-12">
                                                <div class="alert alert-info">
                                                    <i class="mdi mdi-timer-sand me-2"></i>
                                                    <strong>Durasi Video:</strong> 
                                                    <span id="duration-display">Akan terdeteksi otomatis setelah upload</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Player Configuration -->
                                        <div class="video-control-section fade-in" id="player-config-section" style="display: none;">
                                            <h6><i class="mdi mdi-cog me-2"></i>Pengaturan Video Player</h6>
                                            <small class="text-muted d-block mb-3">Kontrol seperti platform Digitalent - video tidak bisa di-skip</small>
                                            
                                            <div class="player-config-grid">
                                               <!-- Izinkan Skip -->
<div class="config-item">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="allow_skip" 
               name="allow_skip" value="1" {{ old('allow_skip') ? 'checked' : '' }}>
        <label class="form-check-label fw-bold" for="allow_skip">
            Izinkan Skip Video
        </label>
    </div>
    <small class="text-muted">
        Aktifkan jika peserta boleh langsung lanjut tanpa menonton video
    </small>
</div>

<div class="config-item">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="disable_forward_seek" 
               name="disable_forward_seek" value="1" {{ old('disable_forward_seek') ? 'checked' : '' }}>
        <label class="form-check-label fw-bold" for="disable_forward_seek">
            Kunci Gerak Maju
        </label>
    </div>
    <small class="text-muted">
        Aktifkan untuk mencegah peserta mempercepat video ke bagian yang belum ditonton
    </small>
</div>

<div class="config-item">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="disable_backward_seek" 
               name="disable_backward_seek" value="1" {{ old('disable_backward_seek') ? 'checked' : '' }}>
        <label class="form-check-label fw-bold" for="disable_backward_seek">
            Kunci Gerak Mundur
        </label>
    </div>
    <small class="text-muted">
        Aktifkan untuk mencegah peserta mengulang bagian video yang sudah lewat
    </small>
</div>

<div class="config-item">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="disable_right_click" 
               name="disable_right_click" value="1" {{ old('disable_right_click') ? 'checked' : '' }}>
        <label class="form-check-label fw-bold" for="disable_right_click">
            Larang Klik Kanan
        </label>
    </div>
    <small class="text-muted">
        Aktifkan untuk melindungi video dari download dengan menonaktifkan klik kanan
    </small>
</div>

<!-- Completion Requirements -->
<div class="config-item">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="require_completion" 
               name="require_completion" value="1" {{ old('require_completion') ? 'checked' : '' }}>
        <label class="form-check-label fw-bold" for="require_completion">
            Wajib Tuntas Nonton
        </label>
    </div>
    <small class="text-muted">
        Aktifkan jika peserta harus menyelesaikan video 100% untuk bisa lanjut
    </small>
</div>

<!-- Video Questions -->
<div class="config-item">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="auto_pause_on_question" 
               name="auto_pause_on_question" value="1" {{ old('auto_pause_on_question') ? 'checked' : '' }}>
        <label class="form-check-label fw-bold" for="auto_pause_on_question">
            Jeda Otomatis
        </label>
    </div>
    <small class="text-muted">
        Aktifkan agar video berhenti otomatis saat ada pertanyaan
    </small>
</div>

<div class="config-item">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="require_question_completion" 
               name="require_question_completion" value="1" {{ old('require_question_completion') ? 'checked' : '' }}>
        <label class="form-check-label fw-bold" for="require_question_completion">
            Wajib Jawab Pertanyaan
        </label>
    </div>
    <small class="text-muted">
        Aktifkan jika peserta harus menjawab pertanyaan untuk lanjut video
    </small>
</div>
                                            </div>
                                        </div>

                                        <!-- Video Questions -->
                                        <div class="video-control-section fade-in" id="video-questions-section" style="display: none;">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0"><i class="mdi mdi-help-circle me-2"></i>Pertanyaan Video</h6>
                                                <button type="button" class="btn btn-success btn-sm" onclick="addVideoQuestion()">
                                                    <i class="mdi mdi-plus"></i> Tambah Pertanyaan
                                                </button>
                                            </div>
                                            <small class="text-muted d-block mb-3">Tambahkan pertanyaan yang muncul di waktu tertentu dalam video</small>
                                            
                                            <div id="video-questions-container">
                                                <!-- Video questions akan ditambahkan di sini -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FORM PRETEST -->
                        <div id="pretest-content-section" class="pretest-section" style="display: none;">
                            <h5 class="mb-3 text-warning"><i class="mdi mdi-clipboard-text me-2"></i>Pengaturan Pretest</h5>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="durasi_pretest" class="form-label">Durasi Pretest (menit) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('durasi_pretest') is-invalid @enderror" 
                                           id="durasi_pretest" name="durasi_pretest" 
                                           value="{{ old('durasi_pretest', 60) }}" min="1">
                                    @error('durasi_pretest')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" class="form-check-input" id="is_pretest" 
                                               name="is_pretest" value="1" {{ old('is_pretest', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="is_pretest">Tandai sebagai Pretest</label>
                                    </div>
                                </div>
                            </div>

                            <!-- IMPORT SOAL EXCEL -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="card-title mb-0">
                                                    <i class="mdi mdi-microsoft-excel me-2"></i>Import Soal dari Excel
                                                </h6>
                                                <button type="button" class="btn btn-sm btn-light" onclick="downloadTemplate()">
                                                    <i class="mdi mdi-download me-1"></i> Download Template
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info">
                                                <strong>Format Excel:</strong> Gunakan template di atas. Kolom wajib: PERTANYAAN, PILIHAN_A, PILIHAN_B, PILIHAN_C, PILIHAN_D, JAWABAN_BENAR
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Upload File Excel</label>
                                                        <div class="input-group">
                                                            <input type="file" class="form-control" 
                                                                   id="excel_file" accept=".xlsx,.xls,.csv"
                                                                   onchange="previewExcelFile(this)">
                                                            <button class="btn btn-success" type="button" 
                                                                    onclick="importSoal('pretest')" id="import-btn-pretest">
                                                                <i class="mdi mdi-upload me-2"></i> Import Soal
                                                            </button>
                                                        </div>
                                                        <small class="text-muted">
                                                            Format: .xlsx, .xls, .csv | Maksimal: 5MB | Maksimal: 500 soal
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check mt-4">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="replace_existing_pretest" checked>
                                                        <label class="form-check-label fw-bold" for="replace_existing_pretest">
                                                            Ganti soal yang ada
                                                        </label>
                                                        <small class="text-muted d-block">
                                                            Jika dicentang, semua soal lama akan diganti
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Preview Area -->
                                            <div id="excel-preview-pretest" class="mt-3" style="display: none;">
                                                <h6><i class="mdi mdi-eye me-2"></i>Preview Data</h6>
                                                <div id="preview-content-pretest"></div>
                                            </div>
                                            
                                            <!-- Result Area -->
                                            <div id="import-result-pretest" class="mt-3"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Soal Pretest -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Soal Pretest</h6>
                                    <button type="button" class="btn btn-success btn-sm" onclick="addSoal('pretest')">
                                        <i class="mdi mdi-plus"></i> Tambah Soal
                                    </button>
                                </div>
                                <small class="text-muted">Tambahkan soal pretest untuk mengukur pengetahuan awal peserta</small>
                            </div>

                            <div id="soal-pretest-container">
                                <!-- Soal pretest akan ditambahkan di sini via JavaScript -->
                            </div>
                        </div>

                        <!-- FORM POSTTEST -->
                        <div id="posttest-content-section" class="posttest-section" style="display: none;">
                            <h5 class="mb-3 text-success"><i class="mdi mdi-clipboard-check me-2"></i>Pengaturan Posttest</h5>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="durasi_posttest" class="form-label">Durasi Posttest (menit) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('durasi_posttest') is-invalid @enderror" 
                                           id="durasi_posttest" name="durasi_posttest" 
                                           value="{{ old('durasi_posttest', 60) }}" min="1">
                                    @error('durasi_posttest')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" class="form-check-input" id="is_posttest" 
                                               name="is_posttest" value="1" {{ old('is_posttest', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="is_posttest">Tandai sebagai Posttest</label>
                                    </div>
                                </div>
                            </div>

                            <!-- IMPORT SOAL EXCEL -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="card-title mb-0">
                                                    <i class="mdi mdi-microsoft-excel me-2"></i>Import Soal dari Excel
                                                </h6>
                                                <button type="button" class="btn btn-sm btn-light" onclick="downloadTemplate()">
                                                    <i class="mdi mdi-download me-1"></i> Download Template
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info">
                                                <strong>Format Excel:</strong> Gunakan template di atas. Kolom wajib: PERTANYAAN, PILIHAN_A, PILIHAN_B, PILIHAN_C, PILIHAN_D, JAWABAN_BENAR
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Upload File Excel</label>
                                                        <div class="input-group">
                                                            <input type="file" class="form-control" 
                                                                   id="excel_file_posttest" accept=".xlsx,.xls,.csv"
                                                                   onchange="previewExcelFilePosttest(this)">
                                                            <button class="btn btn-success" type="button" 
                                                                    onclick="importSoal('posttest')" id="import-btn-posttest">
                                                                <i class="mdi mdi-upload me-2"></i> Import Soal
                                                            </button>
                                                        </div>
                                                        <small class="text-muted">
                                                            Format: .xlsx, .xls, .csv | Maksimal: 5MB | Maksimal: 500 soal
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check mt-4">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="replace_existing_posttest" checked>
                                                        <label class="form-check-label fw-bold" for="replace_existing_posttest">
                                                            Ganti soal yang ada
                                                        </label>
                                                        <small class="text-muted d-block">
                                                            Jika dicentang, semua soal lama akan diganti
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Preview Area -->
                                            <div id="excel-preview-posttest" class="mt-3" style="display: none;">
                                                <h6><i class="mdi mdi-eye me-2"></i>Preview Data</h6>
                                                <div id="preview-content-posttest"></div>
                                            </div>
                                            
                                            <!-- Result Area -->
                                            <div id="import-result-posttest" class="mt-3"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Soal Posttest -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Soal Posttest</h6>
                                    <button type="button" class="btn btn-success btn-sm" onclick="addSoal('posttest')">
                                        <i class="mdi mdi-plus"></i> Tambah Soal
                                    </button>
                                </div>
                                <small class="text-muted">Tambahkan soal posttest untuk mengukur pemahaman peserta setelah belajar</small>
                            </div>

                            <div id="soal-posttest-container">
                                <!-- Soal posttest akan ditambahkan di sini via JavaScript -->
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="is_active" 
                                           name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_active">
                                        Aktifkan materi ini
                                        <i class="mdi mdi-information-outline info-icon ms-1" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Materi yang nonaktif tidak akan ditampilkan ke peserta dan tidak dapat diakses. Gunakan fitur ini untuk menyembunyikan materi sementara."></i>
                                    </label>
                                </div>
                                <small class="text-muted">Materi yang nonaktif tidak akan ditampilkan ke peserta</small>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="mdi mdi-content-save me-2"></i> Simpan Materi
                                        </button>
                                        <a href="{{ route('admin.kursus.materials.index', $kursus) }}" class="btn btn-secondary">
                                             Batal
                                        </a>
                                    </div>
                                    <div id="form-status"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Variabel untuk counter soal
let soalPretestCounter = 0;
let soalPosttestCounter = 0;
let videoQuestionCounter = 0;
let currentExcelFilePretest = null;
let currentExcelFilePosttest = null;

// Variabel untuk menyimpan file yang dipilih
let selectedFiles = [];
let currentVideoFile = null;
let currentVideoType = null;

// Helper untuk escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Fungsi untuk validasi URL sederhana
function isValidUrl(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}

// Preview file Excel untuk pretest
function previewExcelFile(input) {
    const file = input.files[0];
    if (!file) return;
    
    currentExcelFilePretest = file;
    
    // Validasi file
    const validExtensions = ['xlsx', 'xls', 'csv'];
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    if (!validExtensions.includes(fileExtension)) {
        Swal.fire({
            title: 'Format Tidak Valid',
            text: 'Hanya file Excel (.xlsx, .xls, .csv) yang didukung',
            icon: 'error',
            confirmButtonColor: '#1e3c72'
        });
        input.value = '';
        currentExcelFilePretest = null;
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        Swal.fire({
            title: 'File Terlalu Besar',
            text: 'Maksimal ukuran file adalah 5MB',
            icon: 'error',
            confirmButtonColor: '#1e3c72'
        });
        input.value = '';
        currentExcelFilePretest = null;
        return;
    }
    
    // Show preview
    showFilePreview(file, 'pretest');
}

// Preview file Excel untuk posttest
function previewExcelFilePosttest(input) {
    const file = input.files[0];
    if (!file) return;
    
    currentExcelFilePosttest = file;
    
    // Validasi file
    const validExtensions = ['xlsx', 'xls', 'csv'];
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    if (!validExtensions.includes(fileExtension)) {
        Swal.fire({
            title: 'Format Tidak Valid',
            text: 'Hanya file Excel (.xlsx, .xls, .csv) yang didukung',
            icon: 'error',
            confirmButtonColor: '#1e3c72'
        });
        input.value = '';
        currentExcelFilePosttest = null;
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        Swal.fire({
            title: 'File Terlalu Besar',
            text: 'Maksimal ukuran file adalah 5MB',
            icon: 'error',
            confirmButtonColor: '#1e3c72'
        });
        input.value = '';
        currentExcelFilePosttest = null;
        return;
    }
    
    // Show preview
    showFilePreview(file, 'posttest');
}

// Tampilkan preview file
function showFilePreview(file, type) {
    const previewDiv = document.getElementById(`excel-preview-${type}`);
    const previewContent = document.getElementById(`preview-content-${type}`);
    
    if (!previewDiv || !previewContent) return;
    
    const fileSize = (file.size / (1024 * 1024)).toFixed(2);
    
    previewContent.innerHTML = `
        <div class="alert alert-success">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-file-excel mdi-24px me-3 text-success"></i>
                <div>
                    <strong>${escapeHtml(file.name)}</strong><br>
                    <small>Size: ${fileSize} MB | Type: ${file.type || 'Excel file'}</small>
                </div>
            </div>
        </div>
        <p class="text-muted">
            <i class="mdi mdi-information me-1"></i>
            File siap untuk diimport. Klik "Import Soal" untuk melanjutkan.
        </p>
    `;
    
    previewDiv.style.display = 'block';
}

// Download template
function downloadTemplate() {
    Swal.fire({
        title: 'Mengunduh Template',
        text: 'Sedang menyiapkan template Excel...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Create download link
    const link = document.createElement('a');
    link.href = "/admin/template-soal";
    link.download = 'template-soal.xlsx';
    link.style.display = 'none';
    document.body.appendChild(link);
    
    // Trigger download
    setTimeout(() => {
        link.click();
        document.body.removeChild(link);
        
        Swal.close();
        
        Swal.fire({
            title: 'Template Berhasil Diunduh',
            html: `
                <div class="text-center">
                    <i class="mdi mdi-check-circle text-success mdi-48px mb-3"></i>
                    <p>Template Excel telah berhasil diunduh.</p>
                    <p class="text-muted small">
                        Silakan buka file dan isi dengan soal-soal Anda sesuai format.
                    </p>
                </div>
            `,
            icon: 'success',
            confirmButtonColor: '#1e3c72',
            confirmButtonText: 'Mengerti'
        });
    }, 1000);
}

// Import soal dari Excel
async function importSoal(type) {
    let currentExcelFile;
    let importBtn;
    let replaceExisting;
    
    if (type === 'pretest') {
        currentExcelFile = currentExcelFilePretest;
        importBtn = document.getElementById('import-btn-pretest');
        replaceExisting = document.getElementById('replace_existing_pretest').checked;
    } else {
        currentExcelFile = currentExcelFilePosttest;
        importBtn = document.getElementById('import-btn-posttest');
        replaceExisting = document.getElementById('replace_existing_posttest').checked;
    }
    
    if (!currentExcelFile) {
        Swal.fire({
            title: 'Peringatan',
            text: 'Pilih file Excel terlebih dahulu',
            icon: 'warning',
            confirmButtonColor: '#1e3c72'
        });
        return;
    }
    
    // Disable import button
    if (importBtn) {
        importBtn.disabled = true;
        importBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-2"></i> Memproses...';
    }
    
    // Show loading
    Swal.fire({
        title: 'Mengimport Soal',
        html: 'Sedang membaca dan memproses soal dari Excel...<br><small>Harap tunggu, ini mungkin memerlukan waktu beberapa saat</small>',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    try {
        // Prepare form data
        const formData = new FormData();
        formData.append('file', currentExcelFile);
        formData.append('type', type);
        formData.append('_token', getCsrfToken());
        
        // Send to server dengan timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 detik timeout
        
        const response = await fetch("{{ route('admin.kursus.materials.import-soal', $kursus) }}", {
            method: 'POST',
            body: formData,
            signal: controller.signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });
        
        clearTimeout(timeoutId);
        
        const result = await response.json();
        
        Swal.close();
        
        if (response.ok && result.success) {
            // Tampilkan konfirmasi
            const actionText = replaceExisting ? 'mengganti' : 'menambahkan';
            
            const confirmResult = await Swal.fire({
                title: 'Import Berhasil!',
                html: `
                    <div class="text-center">
                        <i class="mdi mdi-check-circle text-success mdi-48px mb-3"></i>
                        <h5 class="mt-2">${result.count} Soal Ditemukan</h5>
                        <p>Berhasil membaca ${result.count} soal dari file Excel.</p>
                        <p class="mb-0">Apakah Anda ingin ${actionText} soal yang ada?</p>
                    </div>
                `,
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#1e3c72',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
                reverseButtons: true
            });
            
            if (confirmResult.isConfirmed) {
                // Proses data hasil import
                processImportedData(result.data, replaceExisting, type);
                
                // Tampilkan notifikasi sukses
                showImportSuccess(result.count, replaceExisting, type);
            }
            
        } else {
            throw new Error(result.message || 'Gagal mengimport soal');
        }
        
    } catch (error) {
        Swal.close();
        
        console.error('Import error:', error);
        
        let errorMessage = 'Terjadi kesalahan saat mengimport soal';
        
        if (error.name === 'AbortError') {
            errorMessage = 'Request timeout - proses terlalu lama. Silakan coba lagi dengan file yang lebih kecil.';
        } else if (error.message) {
            errorMessage = error.message;
        }
        
        Swal.fire({
            title: 'Import Gagal',
            html: `
                <p>${escapeHtml(errorMessage)}</p>
                <small class="text-muted">
                    <strong>Pastikan:</strong>
                    <ul class="text-start small mt-2">
                        <li>File menggunakan format template yang benar</li>
                        <li>Kolom header sesuai (PERTANYAAN, PILIHAN_A, dll)</li>
                        <li>Jawaban Benar berisi A, B, C, atau D</li>
                        <li>Ukuran file tidak melebihi 5MB</li>
                    </ul>
                </small>
            `,
            icon: 'error',
            confirmButtonColor: '#1e3c72'
        });
    } finally {
        // Re-enable import button
        if (importBtn) {
            importBtn.disabled = false;
            importBtn.innerHTML = '<i class="mdi mdi-upload me-2"></i> Import Soal';
        }
    }
}

// Proses data hasil import
function processImportedData(data, replaceExisting, type) {
    if (replaceExisting) {
        // Ganti semua soal
        replaceAllSoal(type, data);
    } else {
        // Tambahkan saja
        addImportedSoal(type, data);
    }
}

// Ganti semua soal
function replaceAllSoal(type, data) {
    const container = document.getElementById(`soal-${type}-container`);
    if (!container) return;
    
    // Hapus semua soal yang ada
    container.innerHTML = '';
    
    // Reset counter
    if (type === 'pretest') {
        soalPretestCounter = 0;
    } else {
        soalPosttestCounter = 0;
    }
    
    // Tambahkan soal baru
    addImportedSoal(type, data);
}

// Tambahkan soal dari data import
function addImportedSoal(type, data) {
    if (!data || !Array.isArray(data) || data.length === 0) {
        console.error('Data import kosong atau tidak valid');
        return;
    }
    
    const container = document.getElementById(`soal-${type}-container`);
    if (!container) return;
    
    data.forEach((soal, index) => {
        const counter = type === 'pretest' ? soalPretestCounter : soalPosttestCounter;
        
        // Buat elemen soal baru dengan animasi
        const newSoal = document.createElement('div');
        newSoal.className = 'soal-item card mb-3 question-imported';
        newSoal.innerHTML = `
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-10">
                        <label class="form-label">Pertanyaan ${counter + 1}</label>
                        <textarea class="form-control" name="${type}_soal[${counter}][pertanyaan]" 
                                  required>${escapeHtml(soal.pertanyaan)}</textarea>
                    </div>
                    <div class="col-2 text-end">
                        <div class="mt-4">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeSoal(this, '${type}')" title="Hapus Soal">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    ${soal.pilihan.map((pilihan, i) => `
                    <div class="col-md-6 mb-2">
                        <div class="input-group">
                            <div class="input-group-text">
                                <input type="radio" name="${type}_soal[${counter}][jawaban_benar]" 
                                       value="${i}" ${i == soal.jawaban_benar ? 'checked' : ''} required>
                            </div>
                            <input type="text" class="form-control" name="${type}_soal[${counter}][pilihan][]" 
                                   value="${escapeHtml(pilihan)}" placeholder="Pilihan ${String.fromCharCode(65 + i)}" required>
                        </div>
                    </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        container.appendChild(newSoal);
        
        if (type === 'pretest') {
            soalPretestCounter++;
        } else {
            soalPosttestCounter++;
        }
    });
    
    // Hapus class animasi setelah beberapa detik
    setTimeout(() => {
        container.querySelectorAll('.question-imported').forEach(el => {
            el.classList.remove('question-imported');
        });
    }, 2000);
}

// Tampilkan notifikasi sukses
function showImportSuccess(count, replaced, type) {
    const resultDiv = document.getElementById(`import-result-${type}`);
    if (!resultDiv) return;
    
    const actionText = replaced ? 'mengganti' : 'menambahkan';
    
    resultDiv.innerHTML = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-check-circle me-3 mdi-24px"></i>
                <div>
                    <h5 class="alert-heading mb-1">Import Berhasil!</h5>
                    <p class="mb-0">Berhasil ${actionText} <strong>${count} soal</strong> dari file Excel.</p>
                    <small class="text-muted">Soal telah ditambahkan ke form. Anda dapat mengeditnya jika diperlukan.</small>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Auto hide after 10 seconds
    setTimeout(() => {
        const alert = resultDiv.querySelector('.alert');
        if (alert) {
            alert.classList.remove('show');
            setTimeout(() => {
                resultDiv.innerHTML = '';
            }, 300);
        }
    }, 10000);
}

// Validasi kombinasi content types
function validateContentTypes(selectedTypes) {
    const errors = [];
    
    // Jika memilih pretest
    if (selectedTypes.includes('pretest')) {
        if (selectedTypes.length > 1) {
            errors.push('Jika memilih Pretest, tidak bisa memilih konten lain');
        }
    }
    
    // Jika memilih posttest
    if (selectedTypes.includes('posttest')) {
        if (selectedTypes.length > 1) {
            errors.push('Jika memilih Posttest, tidak bisa memilih konten lain');
        }
    }
    
    // Jika memilih file (PDF/PPT)
    if (selectedTypes.includes('file')) {
        const allowedWithFile = ['video']; // File bisa dikombinasikan dengan video
        selectedTypes.forEach(type => {
            if (type !== 'file' && !allowedWithFile.includes(type)) {
                if (type === 'pretest' || type === 'posttest') {
                    errors.push(`File tidak bisa dikombinasikan dengan ${type === 'pretest' ? 'Pretest' : 'Posttest'}`);
                }
            }
        });
        
        // Cek apakah kombinasi file dan video valid
        if (selectedTypes.length > 2 || (selectedTypes.length === 2 && !selectedTypes.includes('video'))) {
            errors.push('File hanya bisa dikombinasikan dengan Video');
        }
    }
    
    // Jika memilih video
    if (selectedTypes.includes('video')) {
        const allowedWithVideo = ['file']; // Video bisa dikombinasikan dengan file
        selectedTypes.forEach(type => {
            if (type !== 'video' && !allowedWithVideo.includes(type)) {
                if (type === 'pretest' || type === 'posttest') {
                    errors.push(`Video tidak bisa dikombinasikan dengan ${type === 'pretest' ? 'Pretest' : 'Posttest'}`);
                }
            }
        });
        
        // Cek apakah kombinasi video valid
        if (selectedTypes.length > 2 || (selectedTypes.length === 2 && !selectedTypes.includes('file'))) {
            errors.push('Video hanya bisa dikombinasikan dengan File');
        }
    }
    
    return errors;
}

// Fungsi untuk mengupdate UI berdasarkan pilihan content types
function updateContentTypeUI() {
    const contentTypes = Array.from(document.querySelectorAll('input[name="content_types[]"]:checked'))
        .map(cb => cb.value);
    
    // Reset semua checkbox enable
    document.querySelectorAll('input[name="content_types[]"]').forEach(cb => {
        cb.disabled = false;
        if (cb.closest('.content-type-option')) {
            cb.closest('.content-type-option').classList.remove('disabled-option');
        }
    });
    
    // Jika pretest dipilih
    if (contentTypes.includes('pretest')) {
        document.querySelectorAll('input[name="content_types[]"]').forEach(cb => {
            if (cb.value !== 'pretest') {
                cb.disabled = true;
                if (cb.closest('.content-type-option')) {
                    cb.closest('.content-type-option').classList.add('disabled-option');
                }
            }
        });
    }
    // Jika posttest dipilih
    else if (contentTypes.includes('posttest')) {
        document.querySelectorAll('input[name="content_types[]"]').forEach(cb => {
            if (cb.value !== 'posttest') {
                cb.disabled = true;
                if (cb.closest('.content-type-option')) {
                    cb.closest('.content-type-option').classList.add('disabled-option');
                }
            }
        });
    }
    // Jika file dipilih
    else if (contentTypes.includes('file')) {
        // Nonaktifkan pretest dan posttest
        document.querySelectorAll('input[name="content_types[]"]').forEach(cb => {
            if (cb.value === 'pretest' || cb.value === 'posttest') {
                cb.disabled = true;
                if (cb.closest('.content-type-option')) {
                    cb.closest('.content-type-option').classList.add('disabled-option');
                }
            }
        });
    }
    // Jika video dipilih
    else if (contentTypes.includes('video')) {
        // Nonaktifkan pretest dan posttest
        document.querySelectorAll('input[name="content_types[]"]').forEach(cb => {
            if (cb.value === 'pretest' || cb.value === 'posttest') {
                cb.disabled = true;
                if (cb.closest('.content-type-option')) {
                    cb.closest('.content-type-option').classList.add('disabled-option');
                }
            }
        });
    }
}

// Toggle content type selection
function toggleContentType(type) {
    const checkbox = document.getElementById(`content_type_${type}`);
    const option = checkbox.closest('.content-type-option');
    
    // Dapatkan semua content types yang sudah dipilih
    const selectedTypes = Array.from(document.querySelectorAll('input[name="content_types[]"]:checked'))
        .map(cb => cb.value);
    
    // Jika checkbox akan dicentang
    if (!checkbox.checked) {
        const newSelectedTypes = [...selectedTypes, type];
        const errors = validateContentTypes(newSelectedTypes);
        
        if (errors.length > 0) {
            Swal.fire({
                title: 'Kombinasi Tidak Diperbolehkan',
                html: errors.join('<br>'),
                icon: 'warning',
                confirmButtonColor: '#1e3c72'
            });
            return;
        }
    }
    
    // Toggle checkbox jika valid
    checkbox.checked = !checkbox.checked;
    
    if (checkbox.checked) {
        option.classList.add('selected');
    } else {
        option.classList.remove('selected');
    }
    
    // Update UI
    updateContentTypeUI();
    
    // Tampilkan/sembunyikan section yang sesuai
    toggleContentSection(type, checkbox.checked);
}

// Toggle content section berdasarkan pilihan
function toggleContentSection(type, isVisible) {
    const section = document.getElementById(`${type}-content-section`);
    
    if (section) {
        section.style.display = isVisible ? 'block' : 'none';
        
        // Jika video dipilih, tampilkan config
        if (type === 'video' && isVisible) {
            document.getElementById('player-config-section').style.display = 'block';
            document.getElementById('video-questions-section').style.display = 'block';
            document.getElementById('duration-info-section').style.display = 'block';
        } else if (type === 'video' && !isVisible) {
            document.getElementById('player-config-section').style.display = 'none';
            document.getElementById('video-questions-section').style.display = 'none';
            document.getElementById('duration-info-section').style.display = 'none';
        }
        
        // Jika pretest/posttest dipilih, tambahkan soal default
        if ((type === 'pretest' || type === 'posttest') && isVisible) {
            const container = document.getElementById(`soal-${type}-container`);
            if (container.children.length === 0) {
                addSoal(type);
            }
        }
    }
}

// ============================================
// FUNGSI VIDEO
// ============================================

// Toggle video type sections
function toggleVideoType() {
    const videoType = document.getElementById('video_type').value;
    const urlSection = document.getElementById('video-url-section');
    const fileHostedSection = document.getElementById('video-file-hosted-section');
    const fileLocalSection = document.getElementById('video-file-local-section');
    const urlHelp = document.getElementById('url-help-text');
    const videoUrlInput = document.getElementById('video_url');
    const youtubePreview = document.getElementById('youtube-preview');
    const uploadProgress = document.getElementById('upload-progress');
    const playerConfig = document.getElementById('player-config-section');
    const videoQuestions = document.getElementById('video-questions-section');
    const durationInfo = document.getElementById('duration-info-section');
    setupVideoField();
    
    // Reset semua section
    if (urlSection) urlSection.style.display = 'none';
    if (fileHostedSection) fileHostedSection.style.display = 'none';
    if (fileLocalSection) fileLocalSection.style.display = 'none';
    if (youtubePreview) youtubePreview.style.display = 'none';
    if (uploadProgress) uploadProgress.style.display = 'none';
    
    // Reset input files
    const hostedInput = document.getElementById('video_file_hosted');
    const localInput = document.getElementById('video_file_local');
    if (hostedInput) hostedInput.value = '';
    if (localInput) localInput.value = '';
    
    // Reset preview
    const previewLocal = document.getElementById('video-preview-local');
    if (previewLocal) previewLocal.style.display = 'none';
    
    // Reset current video file
    currentVideoFile = null;
    currentVideoType = null;
    
    // Tampilkan section sesuai jenis video
    if (videoType === 'youtube') {
        if (urlSection) urlSection.style.display = 'block';
        if (urlHelp) urlHelp.textContent = 'Format: https://youtube.com/watch?v=VIDEO_ID atau https://youtu.be/VIDEO_ID';
        if (videoUrlInput) {
            videoUrlInput.placeholder = 'Contoh: https://youtube.com/watch?v=dQw4w9WgXcQ';
            videoUrlInput.oninput = checkYouTubeUrl;
        }
        if (playerConfig) playerConfig.style.display = 'block';
        if (videoQuestions) videoQuestions.style.display = 'block';
        if (durationInfo) durationInfo.style.display = 'block';
    } else if (videoType === 'hosted') {
        if (fileHostedSection) fileHostedSection.style.display = 'block';
        if (playerConfig) playerConfig.style.display = 'block';
        if (videoQuestions) videoQuestions.style.display = 'block';
        if (durationInfo) durationInfo.style.display = 'block';
        // Progress bar khusus Google Drive
        const progressBar = document.getElementById('upload-progress-bar');
        if (progressBar) {
            progressBar.className = 'progress-bar progress-bar-google';
        }
    } else if (videoType === 'local') {
        if (fileLocalSection) fileLocalSection.style.display = 'block';
        if (playerConfig) playerConfig.style.display = 'block';
        if (videoQuestions) videoQuestions.style.display = 'block';
        if (durationInfo) durationInfo.style.display = 'block';
        // Progress bar untuk local
        const progressBar = document.getElementById('upload-progress-bar');
        if (progressBar) {
            progressBar.className = 'progress-bar progress-bar-local';
        }
    } else {
        // Jika tidak ada pilihan, sembunyikan semua
        if (playerConfig) playerConfig.style.display = 'none';
        if (videoQuestions) videoQuestions.style.display = 'none';
        if (durationInfo) durationInfo.style.display = 'none';
    }
}

function checkYouTubeUrl() {
    const url = this.value;
    const youtubePreview = document.getElementById('youtube-preview');
    const youtubeInfo = document.getElementById('youtube-info');
    
    if (!url) {
        if (youtubePreview) youtubePreview.style.display = 'none';
        return;
    }
    
    // Pattern untuk detect YouTube URL
    const patterns = [
        /(?:youtube\.com\/watch\?v=)([a-zA-Z0-9_-]+)/,
        /(?:youtu\.be\/)([a-zA-Z0-9_-]+)/,
        /(?:youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/
    ];
    
    let videoId = null;
    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match && match[1]) {
            videoId = match[1];
            break;
        }
    }
    
    if (videoId) {
        if (youtubePreview) youtubePreview.style.display = 'block';
        if (youtubeInfo) {
            youtubeInfo.innerHTML = `
                Video ID: <strong>${videoId}</strong><br>
                Preview: <a href="https://www.youtube.com/embed/${videoId}" target="_blank">https://www.youtube.com/embed/${videoId}</a>
            `;
        }
    } else {
        if (youtubePreview) youtubePreview.style.display = 'none';
    }
}

// Preview video file sebelum upload
function previewVideoFile(input, type) {
    const preview = document.getElementById(`video-preview-${type}`);
    const fileInfo = document.getElementById(`video-file-info-${type}`);
    const uploadProgress = document.getElementById('upload-progress');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        currentVideoFile = file;
        currentVideoType = type;
        
        // Show preview
        if (preview) preview.style.display = 'block';
        if (uploadProgress) uploadProgress.style.display = 'none';
        
        // Show file info
        const fileSize = (file.size / (1024 * 1024)).toFixed(2);
        if (fileInfo) {
            fileInfo.innerHTML = `
                <strong>${escapeHtml(file.name)}</strong><br>
                Size: ${fileSize} MB | Type: ${file.type}<br>
                Video akan diupload ke ${type === 'hosted' ? 'Google Drive' : 'Local Storage'} secara otomatis.
            `;
        }
        
        // Reset progress bar
        const progressBar = document.getElementById('upload-progress-bar');
        const percentage = document.getElementById('upload-percentage');
        const status = document.getElementById('upload-status');
        const uploadMessage = document.getElementById('upload-message');
        
        if (progressBar) progressBar.style.width = '0%';
        if (percentage) percentage.textContent = '0%';
        if (status) {
            status.className = 'upload-status';
            status.innerHTML = '';
        }
        if (uploadMessage) {
            uploadMessage.textContent = type === 'hosted' 
                ? 'Uploading to Google Drive...' 
                : 'Uploading to Local Storage...';
        }
    } else {
        if (preview) preview.style.display = 'none';
        currentVideoFile = null;
        currentVideoType = null;
    }
}

// Tambah soal baru
function addSoal(type) {
    const container = document.getElementById(`soal-${type}-container`);
    if (!container) return;
    
    const counter = type === 'pretest' ? soalPretestCounter : soalPosttestCounter;
    const soalId = counter + 1;
    
    const newSoal = document.createElement('div');
    newSoal.className = 'soal-item card mb-3';
    newSoal.innerHTML = `
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-10">
                    <label class="form-label">Pertanyaan ${soalId}</label>
                    <textarea class="form-control" name="${type}_soal[${counter}][pertanyaan]" 
                              placeholder="Tulis pertanyaan di sini..." required></textarea>
                </div>
                <div class="col-2 text-end">
                    <div class="mt-4">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeSoal(this, '${type}')" title="Hapus Soal">
                            <i class="mdi mdi-delete"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="row">
                ${Array.from({length: 4}, (_, i) => `
                <div class="col-md-6 mb-2">
                    <div class="input-group">
                        <div class="input-group-text">
                            <input type="radio" name="${type}_soal[${counter}][jawaban_benar]" value="${i}" required>
                        </div>
                        <input type="text" class="form-control" name="${type}_soal[${counter}][pilihan][]" 
                               placeholder="Pilihan ${String.fromCharCode(65 + i)}" required>
                    </div>
                </div>
                `).join('')}
            </div>
            
            <small class="text-muted mt-2">Pilih jawaban yang benar dengan mencentang radio button</small>
        </div>
    `;
    container.appendChild(newSoal);
    
    if (type === 'pretest') {
        soalPretestCounter++;
    } else {
        soalPosttestCounter++;
    }
}

// Tambah pertanyaan video baru
function addVideoQuestion() {
    const container = document.getElementById('video-questions-container');
    if (!container) return;
    
    const questionId = videoQuestionCounter;
    
    const newQuestion = document.createElement('div');
    newQuestion.className = 'video-question-item fade-in';
    newQuestion.innerHTML = `
        <div class="row mb-3">
            <div class="col-11">
                <h6 class="mb-3">Pertanyaan Video #${videoQuestionCounter + 1}</h6>
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeVideoQuestion(this)" title="Hapus Pertanyaan">
                    <i class="mdi mdi-delete"></i>
                </button>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Waktu Muncul (detik)</label>
                <input type="number" class="form-control" name="video_questions[${questionId}][time_in_seconds]" 
                       min="0" value="0" placeholder="0">
                <small class="text-muted">Detik ke berapa pertanyaan muncul</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Poin</label>
                <input type="number" class="form-control" name="video_questions[${questionId}][points]" 
                       min="10" max="100" value="1">
                <small class="text-muted">Poin untuk jawaban benar (10-100)</small>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-12">
                <label class="form-label">Pertanyaan</label>
                <textarea class="form-control" name="video_questions[${questionId}][question]" 
                          rows="2" placeholder="Tulis pertanyaan di sini..." required></textarea>
            </div>
        </div>
        
        <div class="row mb-3">
            ${Array.from({length: 4}, (_, i) => `
            <div class="col-md-6 mb-2">
                <div class="input-group">
                    <div class="input-group-text">
                        <input type="radio" name="video_questions[${questionId}][correct_option]" value="${i}" required>
                    </div>
                    <input type="text" class="form-control" name="video_questions[${questionId}][options][]" 
                           placeholder="Pilihan ${String.fromCharCode(65 + i)}" required>
                </div>
            </div>
            `).join('')}
        </div>
        
        <div class="row">
            <div class="col-12">
                <label class="form-label">Penjelasan (Opsional)</label>
                <textarea class="form-control" name="video_questions[${questionId}][explanation]" 
                          rows="2" placeholder="Penjelasan jawaban yang benar"></textarea>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="required_${questionId}" 
                           name="video_questions[${questionId}][required_to_continue]" value="1" checked>
                    <label class="form-check-label" for="required_${questionId}">
                        Wajib dijawab untuk melanjutkan video
                    </label>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(newQuestion);
    videoQuestionCounter++;
}

// Hapus soal
function removeSoal(button, type) {
    const container = document.getElementById(`soal-${type}-container`);
    if (!container) return;
    
    if (container.children.length > 1) {
        button.closest('.soal-item').remove();
        reindexSoal(type);
    } else {
        Swal.fire({
            title: 'Peringatan',
            text: 'Minimal harus ada 1 soal',
            icon: 'warning',
            confirmButtonColor: '#1e3c72'
        });
    }
}

// Hapus pertanyaan video
function removeVideoQuestion(button) {
    const container = document.getElementById('video-questions-container');
    if (!container) return;
    
    if (container.children.length > 0) {
        button.closest('.video-question-item').remove();
        reindexVideoQuestions();
    }
}

// Reindex soal setelah hapus
function reindexSoal(type) {
    const container = document.getElementById(`soal-${type}-container`);
    if (!container) return;
    
    const soalItems = container.querySelectorAll('.soal-item');
    
    soalItems.forEach((item, index) => {
        // Update label pertanyaan
        const label = item.querySelector('label');
        if (label) label.textContent = `Pertanyaan ${index + 1}`;
        
        // Update semua input names
        const inputs = item.querySelectorAll('[name]');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            const newName = name.replace(new RegExp(`${type}_soal\\[\\d+\\]`), `${type}_soal[${index}]`);
            input.setAttribute('name', newName);
        });
    });
    
    if (type === 'pretest') {
        soalPretestCounter = soalItems.length;
    } else {
        soalPosttestCounter = soalItems.length;
    }
}

// Reindex video questions
function reindexVideoQuestions() {
    const container = document.getElementById('video-questions-container');
    if (!container) return;
    
    const questions = container.querySelectorAll('.video-question-item');
    
    questions.forEach((item, index) => {
        // Update title
        const title = item.querySelector('h6');
        if (title) {
            title.textContent = `Pertanyaan Video #${index + 1}`;
        }
        
        // Update all input names
        const inputs = item.querySelectorAll('[name]');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            const newName = name.replace(/video_questions\[\d+\]/, `video_questions[${index}]`);
            input.setAttribute('name', newName);
        });
        
        // Update checkbox ID
        const checkbox = item.querySelector('[id^="required_"]');
        if (checkbox) {
            checkbox.id = `required_${index}`;
            const label = checkbox.nextElementSibling;
            if (label && label.tagName === 'LABEL') {
                label.setAttribute('for', `required_${index}`);
            }
        }
    });
    
    videoQuestionCounter = questions.length;
}

// ============================================
// FUNGSI FILE HANDLING
// ============================================

function handleFileSelection(files) {
    if (files.length > 0) {
        // TAMBAHKAN file ke array, jangan reset!
        Array.from(files).forEach(file => {
            // Cek apakah file sudah ada
            const isDuplicate = selectedFiles.some(existingFile => 
                existingFile.name === file.name && existingFile.size === file.size
            );
            
            if (!isDuplicate) {
                selectedFiles.push(file);
            } else {
                alert(`File "${file.name}" sudah dipilih sebelumnya.`);
            }
        });
        
        // Update preview
        updateFilePreview();
        
        // Update hidden file inputs
        updateFileInputs();
        
        // Reset input agar bisa pilih file yang sama lagi
        document.getElementById('file_input').value = '';
    }
}

// Update file preview
function updateFilePreview() {
    const previewContainer = document.getElementById('file-preview');
    const fileList = document.getElementById('file-list');
    const clearAllBtn = document.getElementById('clear-all-btn');
    
    if (!previewContainer || !fileList) return;
    
    if (selectedFiles.length > 0) {
        fileList.innerHTML = '';
        
        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <div class="flex-grow-1">
                    <span class="badge bg-primary me-2">${index + 1}</span>
                    <i class="mdi mdi-file-document me-2"></i>
                    ${escapeHtml(file.name)} (${(file.size / 1024 / 1024).toFixed(2)} MB)
                </div>
                <div class="file-actions">
                    <button type="button" class="btn btn-sm btn-danger" 
                            onclick="removeFile(${index})" 
                            title="Hapus File">
                        <i class="mdi mdi-delete"></i>
                    </button>
                </div>
            `;
            fileList.appendChild(fileItem);
        });
        
        // Show/Hide clear button
        if (clearAllBtn) {
            clearAllBtn.style.display = selectedFiles.length > 0 ? 'inline-block' : 'none';
        }
        
        previewContainer.style.display = 'block';
    } else {
        previewContainer.style.display = 'none';
        if (clearAllBtn) clearAllBtn.style.display = 'none';
    }
}

// Hapus file dari daftar
function removeFile(index) {
    if (confirm(`Yakin hapus file "${selectedFiles[index].name}"?`)) {
        selectedFiles.splice(index, 1);
        updateFilePreview();
        updateFileInputs();
    }
}

// Hapus semua file
function clearAllFiles() {
    if (selectedFiles.length === 0) return;
    
    if (confirm(`Hapus semua ${selectedFiles.length} file?`)) {
        selectedFiles = [];
        updateFilePreview();
        updateFileInputs();
    }
}

// Update hidden file inputs untuk form submission
function updateFileInputs() {
    const container = document.getElementById('file-inputs-container');
    if (!container) return;
    
    container.innerHTML = '';
    
    // Buat DataTransfer object untuk multiple files
    const dataTransfer = new DataTransfer();
    
    selectedFiles.forEach(file => {
        dataTransfer.items.add(file);
    });
    
    // Buat input file baru dengan semua file yang dipilih
    const newInput = document.createElement('input');
    newInput.type = 'file';
    newInput.name = 'file_path[]';
    newInput.multiple = true;
    newInput.style.display = 'none';
    
    // Set files menggunakan DataTransfer
    newInput.files = dataTransfer.files;
    
    container.appendChild(newInput);
}

// Helper untuk escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================
// FUNGSI HELPER
// ============================================

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || 
           document.querySelector('input[name="_token"]')?.value ||
           '{{ csrf_token() }}';
}

// Helper untuk menampilkan loading progress
function showUploadProgress(percentage, message) {
    const progressBar = document.getElementById('upload-progress-bar');
    const percentageElement = document.getElementById('upload-percentage');
    const statusElement = document.getElementById('upload-status');
    
    if (progressBar) {
        progressBar.style.width = percentage + '%';
    }
    if (percentageElement) {
        percentageElement.textContent = percentage + '%';
    }
    if (statusElement) {
        statusElement.textContent = message;
        
        // Update class berdasarkan status
        if (percentage === 100) {
            statusElement.className = 'upload-status status-success';
        } else if (percentage > 0) {
            statusElement.className = 'upload-status status-uploading';
        }
    }
}

// Helper untuk mengecek apakah request AJAX
function isAjaxRequest() {
    return window.XMLHttpRequest && 
           (new XMLHttpRequest()).responseType === 'json' || 
           navigator.userAgent.indexOf('XMLHttpRequest') !== -1;
}

// Fungsi untuk simulasi progress bar
function simulateProgressBar() {
    let progress = 0;
    const progressBar = document.getElementById('upload-progress-bar');
    const percentage = document.getElementById('upload-percentage');
    const status = document.getElementById('upload-status');
    
    if (!progressBar || !percentage || !status) return;
    
    const interval = setInterval(() => {
        progress += 1;
        progressBar.style.width = progress + '%';
        percentage.textContent = progress + '%';
        
        if (progress >= 100) {
            clearInterval(interval);
            status.className = 'upload-status status-success';
            status.innerHTML = 'Upload berhasil!';
        }
    }, 100); // Update setiap 100ms
}

function setupVideoField() {
    const videoType = document.getElementById('video_type').value;
    
    // Sembunyikan/sembunyikan input file yang tidak aktif
    const hostedInput = document.getElementById('video_file_hosted');
    const localInput = document.getElementById('video_file_local');
    
    if (hostedInput && localInput) {
        // Nonaktifkan semua input file
        hostedInput.disabled = true;
        localInput.disabled = true;
        hostedInput.required = false;
        localInput.required = false;
        
        // Aktifkan input sesuai tipe video
        if (videoType === 'hosted') {
            hostedInput.disabled = false;
            hostedInput.required = true;
            hostedInput.name = 'video_file'; // Set nama ke 'video_file'
            localInput.name = 'video_file_disabled'; // Ubah nama untuk local
        } else if (videoType === 'local') {
            localInput.disabled = false;
            localInput.required = true;
            localInput.name = 'video_file'; // Set nama ke 'video_file'
            hostedInput.name = 'video_file_disabled'; // Ubah nama untuk hosted
        } else {
            // Untuk YouTube, kosongkan nama
            hostedInput.name = '';
            localInput.name = '';
        }
    }
}

// Validasi URL YouTube
function isValidYouTubeUrl(url) {
    if (!url) return false;
    
    const patterns = [
        /^(https?\:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/,
        /^(https?\:\/\/)?(www\.)?youtube\.com\/watch\?v=[\w-]+/,
        /^(https?\:\/\/)?(www\.)?youtu\.be\/[\w-]+/
    ];
    
    for (const pattern of patterns) {
        if (pattern.test(url)) {
            return true;
        }
    }
    
    return false;
}

// ============================================
// FUNGSI SUBMIT FORM YANG DIPERBAIKI
// ============================================

async function submitFormWithProgress() {
    const form = document.getElementById('materialForm');
    if (!form) return;
    
    console.log('Starting form submission...');
    
    const formData = new FormData(form);
    
    // Debug: Log form data
    console.log('Form data entries:');
    for (let [key, value] of formData.entries()) {
        if (key === 'video_file' || key === 'file_path[]') {
            console.log(`${key}: [File]`);
        } else {
            console.log(`${key}:`, value);
        }
    }
    
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 300000); // 5 menit timeout
    
    try {
        console.log('Sending request to:', form.action);
        
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            signal: controller.signal,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });
        
        clearTimeout(timeoutId);
        
        console.log('Response status:', response.status);
        console.log('Response OK?', response.ok);
        
        // Dapatkan response sebagai text dulu
        const responseText = await response.text();
        console.log('Response received, length:', responseText.length);
        
        // Coba parse sebagai JSON
        try {
            const result = JSON.parse(responseText);
            console.log('JSON parsed successfully:', result);
            
            if (result.success) {
                console.log('Success! Redirecting to:', result.redirect);
                
                // Tampilkan success message
                Swal.fire({
                    title: 'Berhasil!',
                    text: result.message,
                    icon: 'success',
                    confirmButtonColor: '#1e3c72',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    // Redirect setelah alert ditutup
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    } else {
                        window.location.href = "{{ route('admin.kursus.materials.index', $kursus) }}";
                    }
                });
                
                return result;
            } else {
                // Server mengembalikan success: false
                console.error('Server returned error:', result);
                throw new Error(result.message || 'Gagal menyimpan data');
            }
            
        } catch (jsonError) {
            console.error('JSON parse error:', jsonError);
            console.log('Response preview (first 500 chars):', responseText.substring(0, 500));
            
            // Jika bukan JSON, berarti server mengembalikan HTML
            if (responseText.includes('<!DOCTYPE html>') || responseText.includes('<html')) {
                // Coba extract error dari HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(responseText, 'text/html');
                
                let errorMessage = 'Server mengembalikan halaman HTML';
                
                // Cari error message
                const errorElement = doc.querySelector('.error-message, .alert-danger, .invalid-feedback, .alert');
                if (errorElement) {
                    errorMessage = errorElement.textContent.trim();
                }
                
                throw new Error(`HTTP ${response.status}: ${errorMessage}`);
            } else {
                // Jika bukan HTML juga, tampilkan raw text
                throw new Error(`Server error: ${responseText.substring(0, 200)}`);
            }
        }
        
    } catch (error) {
        console.error('Fetch error:', error);
        
        if (error.name === 'AbortError') {
            throw new Error('Request timeout - proses terlalu lama. Silakan coba lagi dengan file yang lebih kecil.');
        }
        
        throw error;
    }
}

// Function untuk submit form (yang utama)
async function submitForm() {
    const submitBtn = document.getElementById('submitBtn');
    const formStatus = document.getElementById('form-status');
    const originalText = submitBtn ? submitBtn.innerHTML : '';
    
    // Disable button dan ubah text
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-2"></i> Menyimpan...';
    }
    
    // Show loading indicator
    if (formStatus) {
        formStatus.innerHTML = `
            <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <div>Menyimpan materi, harap tunggu...</div>
            </div>
        `;
    }
    
    // Jika ada video hosted, tampilkan progress (hanya visual)
    const videoType = document.getElementById('video_type');
    if (videoType && videoType.value === 'hosted' && currentVideoFile) {
        const uploadProgress = document.getElementById('upload-progress');
        const uploadStatus = document.getElementById('upload-status');
        
        if (uploadProgress) {
            uploadProgress.style.display = 'block';
            uploadProgress.innerHTML = `
                <div class="d-flex justify-content-between mb-2">
                    <span>Mengupload video ke Google Drive...</span>
                    <span id="upload-percentage">0%</span>
                </div>
                <div class="progress">
                    <div id="upload-progress-bar" class="progress-bar progress-bar-google" 
                         role="progressbar" style="width: 0%"></div>
                </div>
                <div id="upload-status" class="upload-status status-uploading mt-2">
                    Sedang memproses upload...
                </div>
            `;
        }
    }
    
    try {
        // Submit form dengan Fetch API
        await submitFormWithProgress();
        
    } catch (error) {
        console.error('Error submitting form:', error);
        
        // Restore button
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
        
        // Clear loading indicator
        if (formStatus) {
            formStatus.innerHTML = '';
        }
        
        // Hide progress bar jika ada
        const uploadProgress = document.getElementById('upload-progress');
        if (uploadProgress) {
            uploadProgress.style.display = 'none';
        }
        
        // Show error message dengan detail
        let errorMessage = error.message;
        
        // Tambahkan saran berdasarkan error
        let suggestion = '';
        if (errorMessage.includes('Google Drive')) {
            suggestion = 'Coba gunakan YouTube atau Local Storage sebagai alternatif.';
        } else if (errorMessage.includes('timeout')) {
            suggestion = 'File mungkin terlalu besar. Coba dengan file yang lebih kecil.';
        } else if (errorMessage.includes('validation') || errorMessage.includes('Validasi')) {
            suggestion = 'Periksa kembali data yang Anda masukkan.';
        }
        
        Swal.fire({
            title: 'Gagal Menyimpan',
            html: `
                <div style="text-align:left;">
                    <p><strong>Error:</strong> ${escapeHtml(errorMessage)}</p>
                    ${suggestion ? `<p class="text-muted small mt-2">${suggestion}</p>` : ''}
                </div>
            `,
            icon: 'error',
            confirmButtonColor: '#1e3c72',
            confirmButtonText: 'Mengerti'
        });
        
        // Scroll ke atas untuk melihat error
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// ============================================
// EVENT LISTENERS & INITIALIZATION
// ============================================

// Update percentage value display
document.addEventListener('DOMContentLoaded', function() { 
    // Initialize selected options
    const contentTypes = ['file', 'video', 'pretest', 'posttest'];
    contentTypes.forEach(type => {
        const checkbox = document.getElementById(`content_type_${type}`);
        if (checkbox && checkbox.checked) {
            const option = checkbox.closest('.content-type-option');
            if (option) option.classList.add('selected');
            toggleContentSection(type, true);
            
            // Jika video sudah dipilih, init video type
            if (type === 'video') {
                setTimeout(() => {
                    const videoType = document.getElementById('video_type');
                    if (videoType && videoType.value) {
                        toggleVideoType();
                    }
                }, 100);
            }
        }
    });

    // Update UI
    updateContentTypeUI();

    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Form submit event listener
    const materialForm = document.getElementById('materialForm');
    if (materialForm) {
        materialForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Form submit triggered');
            
            const submitBtn = document.getElementById('submitBtn');
            const originalBtnText = submitBtn.innerHTML;
            
            // Validasi client-side
            const contentTypes = Array.from(document.querySelectorAll('input[name="content_types[]"]:checked'))
                .map(cb => cb.value);
            
            if (contentTypes.length === 0) {
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Pilih minimal satu jenis konten materi',
                    icon: 'warning',
                    confirmButtonColor: '#1e3c72'
                });
                return false;
            }
            
            // Validasi kombinasi content types
            const typeErrors = validateContentTypes(contentTypes);
            if (typeErrors.length > 0) {
                Swal.fire({
                    title: 'Kombinasi Konten Tidak Valid',
                    html: typeErrors.join('<br>'),
                    icon: 'error',
                    confirmButtonColor: '#1e3c72'
                });
                return false;
            }
            
            // Validasi: tidak boleh pilih pretest dan posttest bersamaan
            if (contentTypes.includes('pretest') && contentTypes.includes('posttest')) {
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Tidak dapat memilih pretest dan posttest bersamaan dalam satu materi',
                    icon: 'warning',
                    confirmButtonColor: '#1e3c72'
                });
                return false;
            }
            
            // Validasi untuk setiap konten yang dipilih
            let warnings = [];
            
            if (contentTypes.includes('file') && selectedFiles.length === 0) {
                warnings.push('File materi untuk konten PDF/PPT belum dipilih');
            }
            
            if (contentTypes.includes('video')) {
                const videoType = document.getElementById('video_type').value;
                if (!videoType) {
                    warnings.push('Jenis video harus dipilih');
                } else {
                    if (videoType === 'youtube') {
                        const videoUrl = document.getElementById('video_url').value;
                        if (!videoUrl) {
                            warnings.push('URL YouTube harus diisi');
                        } else if (!isValidYouTubeUrl(videoUrl)) {
                            warnings.push('URL YouTube tidak valid');
                        }
                    } else if (videoType === 'hosted') {
                        const videoFile = document.getElementById('video_file_hosted');
                        if (!videoFile || !videoFile.value) {
                            warnings.push('File video harus diupload untuk Google Drive');
                        }
                    } else if (videoType === 'local') {
                        const videoFile = document.getElementById('video_file_local');
                        if (!videoFile || !videoFile.value) {
                            warnings.push('File video harus diupload untuk Local Storage');
                        }
                    }
                }
            }
            
            if (contentTypes.includes('pretest')) {
                const soalPretestContainer = document.getElementById('soal-pretest-container');
                if (soalPretestContainer && soalPretestContainer.children.length === 0) {
                    warnings.push('Pretest harus memiliki minimal 1 soal');
                }
                
                const durasiPretest = document.getElementById('durasi_pretest');
                if (durasiPretest && (!durasiPretest.value || durasiPretest.value < 1)) {
                    warnings.push('Durasi pretest harus diisi (minimal 1 menit)');
                }
            }
            
            if (contentTypes.includes('posttest')) {
                const soalPosttestContainer = document.getElementById('soal-posttest-container');
                if (soalPosttestContainer && soalPosttestContainer.children.length === 0) {
                    warnings.push('Posttest harus memiliki minimal 1 soal');
                }
                
                const durasiPosttest = document.getElementById('durasi_posttest');
                if (durasiPosttest && (!durasiPosttest.value || durasiPosttest.value < 1)) {
                    warnings.push('Durasi posttest harus diisi (minimal 1 menit)');
                }
            }
            
            // Jika ada warnings, tampilkan konfirmasi
            if (warnings.length > 0) {
                const warningMessage = 'Perhatikan hal berikut:\n\n' + warnings.join('\n');
                
                const result = await Swal.fire({
                    title: 'Data Belum Lengkap',
                    text: warningMessage,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#1e3c72',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Simpan',
                    cancelButtonText: 'Perbaiki Data',
                    reverseButtons: true
                });
                
                if (result.isConfirmed) {
                    // Jika user memilih untuk lanjut, submit form
                    await submitForm();
                }
            } else {
                // Jika tidak ada warning, langsung submit
                await submitForm();
            }
            
            return false;
        });
    }
    
    // Juga tambahkan event listener untuk tombol submit biasa (fallback)
    const submitButton = document.querySelector('button[type="submit"]');
    if (submitButton && !materialForm) {
        submitButton.addEventListener('click', function(e) {
            e.preventDefault();
            submitForm();
        });
    }
});
</script>
@endsection 