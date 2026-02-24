@extends('layouts.admin')

@section('title', 'MOOC BPS - Materi Kursus: ' . $kursus->judul_kursus)

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">
<!-- TAMBAHKAN SORTABLE JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.css">
<style>
    .page-title-box {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #1e3c72;
        font-weight: 600;
    }

    .material-actions {
        display: flex;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .btn-sm-action {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        border: none;
        transition: all 0.3s ease;
    }

    .material-content {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-top: 10px;
    }

    .content-item {
        margin-bottom: 10px;
        padding: 10px;
        background: white;
        border-radius: 6px;
        border-left: 4px solid #1e3c72;
    }

    .stats-item {
        background: white;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        border-left: 4px solid;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* ANIMASI DRAG & DROP YANG LEBIH BAGUS */
    .sortable-ghost {
        opacity: 0.4;
        background: linear-gradient(135deg, rgba(30, 60, 114, 0.1) 0%, rgba(42, 82, 152, 0.1) 100%);
        border: 2px dashed #1e3c72 !important;
        transform: scale(0.98);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sortable-dragging {
        opacity: 0.9 !important;
        transform: rotate(0deg) scale(1.02) !important;
        box-shadow: 0 15px 30px rgba(30, 60, 114, 0.2), 
                    0 5px 15px rgba(0, 0, 0, 0.1) !important;
        z-index: 9999 !important;
        border: 2px solid #1e3c72 !important;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        animation: pulse-drag 2s infinite alternate;
    }

    @keyframes pulse-drag {
        0% {
            box-shadow: 0 15px 30px rgba(30, 60, 114, 0.2), 
                        0 5px 15px rgba(0, 0, 0, 0.1);
        }
        100% {
            box-shadow: 0 20px 40px rgba(30, 60, 114, 0.3), 
                        0 8px 20px rgba(0, 0, 0, 0.15),
                        0 0 0 3px rgba(30, 60, 114, 0.1);
        }
    }

    .sortable-placeholder {
        background: linear-gradient(135deg, rgba(30, 60, 114, 0.05) 0%, rgba(42, 82, 152, 0.05) 100%) !important;
        border: 3px dashed #1e3c72 !important;
        margin: 8px 0 !important;
        border-radius: 12px !important;
        min-height: 85px !important;
        animation: placeholder-pulse 2s ease-in-out infinite;
    }

    @keyframes placeholder-pulse {
        0%, 100% {
            border-color: #1e3c72;
            background: linear-gradient(135deg, rgba(30, 60, 114, 0.05) 0%, rgba(42, 82, 152, 0.05) 100%);
        }
        50% {
            border-color: #2a5298;
            background: linear-gradient(135deg, rgba(30, 60, 114, 0.1) 0%, rgba(42, 82, 152, 0.1) 100%);
        }
    }

    /* Handle drag yang lebih menarik */
    .sortable-handle {
        cursor: move !important;
        color: #6c757d;
        padding: 0 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: rgba(0, 0, 0, 0.03);
    }

    .sortable-handle:hover {
        color: #1e3c72 !important;
        background: rgba(30, 60, 114, 0.1);
        transform: scale(1.1);
    }

    .sortable-handle i {
        font-size: 20px;
    }

    /* Efek saat mode drag aktif */
    .accordion-item.sortable-item {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin: 5px 0;
    }

    .accordion-item.sortable-item:hover:not(.sortable-dragging) {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    /* Order badge yang lebih menarik */
    .order-badge {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        font-weight: bold;
        min-width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 14px;
        box-shadow: 0 4px 8px rgba(30, 60, 114, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sortable-dragging .order-badge {
        transform: scale(1.2);
        box-shadow: 0 6px 12px rgba(30, 60, 114, 0.4);
        animation: badge-pulse 1.5s infinite alternate;
    }

    @keyframes badge-pulse {
        0% {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }
        100% {
            background: linear-gradient(135deg, #2a5298 0%, #3a6fd8 100%);
        }
    }

    /* Panel kontrol yang lebih menarik */
    .sort-control-panel {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        border: 2px solid #1e3c72;
        box-shadow: 0 8px 25px rgba(30, 60, 114, 0.15);
        animation: panel-appear 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes panel-appear {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .sort-info {
        background: white;
        padding: 15px;
        border-radius: 10px;
        font-size: 0.95rem;
        color: #495057;
        border-left: 4px solid #1e3c72;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    /* Smooth transition untuk semua elemen */
    .sortable-item * {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    /* Feedback visual saat berhasil disimpan */
    .save-success {
        animation: save-success 2s ease-out;
    }

    @keyframes save-success {
        0% {
            background-color: #d4edda;
            transform: scale(1);
        }
        50% {
            background-color: #c3e6cb;
            transform: scale(1.02);
        }
        100% {
            background-color: #d4edda;
            transform: scale(1);
        }
    }

    /* STYLE UNTUK CHECKBOX SELECT */
    .material-checkbox {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
        width: 18px;
        height: 18px;
        display: none;
    }

    .select-mode-active .material-checkbox {
        display: block !important;
    }

    .select-mode-active .accordion-button {
        padding-left: 45px !important;
    }

    .select-mode-active .sortable-handle {
        display: none !important;
    }

    .select-mode-active .order-badge {
        display: none !important;
    }

    .bulk-action-panel {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border: 2px solid #ffc107;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.2);
        animation: slide-down 0.3s ease-out;
    }

    @keyframes slide-down {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .bulk-selected-count {
        background: #ffc107;
        color: #000;
        font-weight: bold;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
    }

    .selected-item {
        background: rgba(255, 193, 7, 0.1) !important;
        border-left: 4px solid #ffc107 !important;
    }

    /* PERBAIKAN: Style untuk tombol yang lebih kecil */
    .btn-sm-compact {
        padding: 6px 12px !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
    }

    .btn-sm-compact i {
        font-size: 16px !important;
        margin-right: 4px !important;
    }

    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .action-buttons .btn {
        margin-bottom: 4px;
    }

    /* Checkbox hover effect */
    .accordion-button:hover .material-checkbox {
        display: block;
        opacity: 0.5;
    }

    .select-mode-active .accordion-button:hover .material-checkbox {
        opacity: 1;
    }

    .stats-item.attendance {
        border-left-color: #28a745;
    }

    .stats-item.material {
        border-left-color: #17a2b8;
    }

    .stats-item.video {
        border-left-color: #ffc107;
    }

    .stats-item.pretest {
        border-left-color: #6f42c1;
    }

    .stats-item.posttest {
        border-left-color: #e83e8c;
    }

    .stats-number {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .stats-label {
        font-size: 0.85rem;
        color: #6c757d;
    }

    /* Responsive stats grid */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
        
        .stats-item {
            margin-bottom: 15px;
        }
        
        .action-buttons {
            flex-direction: column;
            align-items: stretch;
        }
        
        .action-buttons .btn {
            width: 100%;
            margin-bottom: 8px;
        }
        
        .material-checkbox {
            left: 10px;
        }
        
        .select-mode-active .accordion-button {
            padding-left: 35px !important;
        }
    }

    /* File info styling */
    .file-info {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 10px;
        margin-top: 8px;
        border-left: 3px solid #17a2b8;
    }

    .file-info small {
        font-size: 0.75rem;
    }

    /* Badge untuk video type */
    .badge-video-type {
        font-size: 0.7rem;
        padding: 2px 8px;
    }

    .progress-container {
        margin-top: 8px;
    }

    .progress {
        height: 6px;
        margin-bottom: 5px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .test-results {
        background: white;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
        border: 1px solid #e9ecef;
    }

    .result-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .result-item:last-child {
        border-bottom: none;
    }

    .score-badge {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
    }

    .score-passed {
        background: #d4edda;
        color: #155724;
    }

    .score-failed {
        background: #f8d7da;
        color: #721c24;
    }
    
    /* STYLE UNTUK DRAG & DROP */
    .sortable-handle {
        cursor: move;
        color: #6c757d;
        padding: 0 10px;
        transition: color 0.3s;
    }
    
    .sortable-handle:hover {
        color: #1e3c72;
    }
    
    .accordion-button .drag-indicator {
        margin-right: 10px;
        opacity: 0.6;
        transition: opacity 0.3s;
    }
    
    .accordion-button:hover .drag-indicator {
        opacity: 1;
    }
    
    .sortable-placeholder {
        border: 2px dashed #1e3c72;
        background-color: rgba(30, 60, 114, 0.1);
        margin: 5px 0;
        border-radius: 8px;
        min-height: 80px;
    }
    
    .sortable-dragging {
        opacity: 0.8;
        transform: rotate(2deg);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .order-badge {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        font-weight: bold;
        min-width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }
    
    .swal2-popup {
        border-radius: 12px !important;
        padding: 20px !important;
    }
    
    .swal2-title {
        font-size: 1.3rem !important;
        color: #1e3c72 !important;
    }
    
    .swal2-html-container {
        font-size: 1rem !important;
        color: #6c757d !important;
    }
    
    .swal2-confirm {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
        border: none !important;
        padding: 10px 25px !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
    }
    
    .swal2-cancel {
        background: #6c757d !important;
        border: none !important;
        padding: 10px 25px !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
    }
    
    .status-badge {
        font-size: 0.8rem;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 600;
    }
    
    .status-active {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-inactive {
        background-color: #e2e3e5;
        color: #383d41;
        border: 1px solid #d6d8db;
    }
    
    /* Control Panel untuk Sortable */
    .sort-control-panel {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid #e9ecef;
    }
    
    .sort-info {
        background: white;
        padding: 10px;
        border-radius: 6px;
        font-size: 0.9rem;
        color: #6c757d;
        border-left: 3px solid #1e3c72;
    }

    /* ============ STYLE BARU UNTUK PAGINATION & FILTER ============ */
    
    /* Table Header */
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid #e9ecef;
        background: #f8f9fa;
    }
    
    .table-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e3c72;
        margin: 0;
    }
    
    .btn-tambah {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-tambah:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(30, 60, 114, 0.3);
        color: white;
    }
    
    /* Filter Section */
    .filter-section {
        padding: 15px 25px;
        border-bottom: 1px solid #e9ecef;
        background: #f8f9fa;
    }
    
    .filter-container {
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .search-box {
        position: relative;
        flex: 1;
        max-width: 300px;
    }
    
    .search-box input {
        width: 100%;
        padding: 10px 15px 10px 40px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        font-size: 0.9rem;
    }
    
    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }
    
    .filter-select {
        padding: 10px 15px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: white;
        font-size: 0.9rem;
        color: #495057;
    }
    
    .btn-reset {
        padding: 10px 15px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: white;
        font-size: 0.9rem;
        color: #495057;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-reset:hover {
        background: #f8f9fa;
        color: #495057;
    }
    
    /* Pagination Section */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 25px;
        border-top: 1px solid #e9ecef;
        background: #f8f9fa;
        margin-top: 20px;
    }
    
    .pagination-info {
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .per-page-selector {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .per-page-selector label {
        font-size: 0.9rem;
        color: #495057;
        margin: 0;
    }
    
    .per-page-selector select {
        padding: 5px 10px;
        border: 1px solid #ced4da;
        border-radius: 5px;
        background: white;
        font-size: 0.9rem;
        color: #495057;
        cursor: pointer;
    }
    
    .per-page-selector select:focus {
        outline: none;
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .pagination {
        margin: 0;
        display: flex;
        gap: 5px;
    }
    
    .page-item .page-link {
        border-radius: 5px;
        padding: 6px 12px;
        border: 1px solid #dee2e6;
        color: #1e3c72;
        font-size: 0.9rem;
        transition: all 0.2s;
        text-decoration: none;
    }
    
    .page-item.active .page-link {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        border-color: #1e3c72;
        color: white;
    }
    
    .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
    
    .page-item .page-link:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    
    .page-item.active .page-link:hover {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
    }
    
    /* Responsive Pagination */
    @media (max-width: 768px) {
        .pagination-container {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
        
        .pagination-controls {
            width: 100%;
            justify-content: space-between;
        }
        
        .pagination {
            flex-wrap: wrap;
        }
        
        .filter-container {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .per-page-selector {
            margin-left: 0;
            width: 100%;
            justify-content: flex-start;
        }
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        color: #dee2e6;
    }
    
    .empty-state h4 {
        margin-bottom: 10px;
        color: #495057;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="page-title mb-0">
                            <i class="mdi mdi-book-open-variant me-2"></i>
                            Materi Kursus: {{ $kursus->judul_kursus }}
                        </h4>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="{{ route('admin.kursus.index') }}" class="btn btn-secondary btn-sm-compact">
                            <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Kursus
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <!-- Action Bar -->
                    <div class="table-header">
                        <h2 class="table-title">Daftar Materi</h2>
                        <div class="d-flex align-items-center gap-2">
                            <!-- TOMBOL UNTUK MENGUBAH MODE DRAG & DROP -->
                            <button type="button" id="toggleSortMode" class="btn btn-outline-primary btn-sm-compact">
                                <i class="mdi mdi-drag me-1"></i> Ubah Urutan
                            </button>
                            
                            <!-- TOMBOL UNTUK MODE SELECT (HAPUS BANYAK) -->
                            <button type="button" id="toggleSelectMode" class="btn btn-outline-warning btn-sm-compact">
                                <i class="mdi mdi-checkbox-multiple-marked me-1"></i> Pilih Materi
                            </button>
                            
                            <a href="{{ route('admin.kursus.materials.create', $kursus) }}" class="btn-tambah">
                                <i class="mdi mdi-plus-circle"></i>
                                Tambah Materi Baru
                            </a>
                        </div>
                    </div>

                    <!-- FILTER SECTION -->
                    <div class="filter-section">
                        <form method="GET" action="{{ route('admin.kursus.materials.index', $kursus) }}" id="filterForm">
                            <div class="filter-container">
                                <!-- Search Input -->
                                <div class="search-box">
                                    <i class="mdi mdi-magnify"></i>
                                    <input type="text" name="search" placeholder="Cari judul materi..." 
                                           value="{{ $search }}" class="search-input" id="searchInput">
                                </div>

                                <!-- Status Filter -->
                                <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">Semua Status</option>
                                    <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                                </select>

                                <!-- Type Filter -->
                                <select name="type" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">Semua Tipe</option>
                                    <option value="material" {{ $type == 'material' ? 'selected' : '' }}>Materi</option>
                                    <option value="pre_test" {{ $type == 'pre_test' ? 'selected' : '' }}>Pretest</option>
                                    <option value="post_test" {{ $type == 'post_test' ? 'selected' : '' }}>Posttest</option>
                                </select>

                                <!-- Reset Filter -->
                                @if($search || $status || $type)
                                    <a href="{{ route('admin.kursus.materials.index', $kursus) }}" class="btn-reset">
                                        <i class="mdi mdi-close-circle-outline"></i> Reset
                                    </a>
                                @endif

                            </div>
                        </form>
                    </div>

                    <!-- PANEL AKSI HAPUS BANYAK (HIDDEN DEFAULT) -->
                    <div id="bulkActionPanel" class="bulk-action-panel" style="display: none;">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="sort-info">
                                    <i class="mdi mdi-information-outline me-2"></i>
                                    <strong>Mode Pilih Aktif</strong> - Pilih materi yang ingin dihapus. 
                                    <span class="bulk-selected-count ms-2">
                                        <i class="mdi mdi-check-circle me-1"></i>
                                        <span id="selectedCount">0</span> materi terpilih
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <button type="button" id="deleteSelected" class="btn btn-danger btn-sm-compact" disabled>
                                    <i class="mdi mdi-delete me-1"></i> Hapus Terpilih
                                </button>
                                <button type="button" id="cancelSelect" class="btn btn-secondary btn-sm-compact ms-2">
                                    <i class="mdi mdi-close me-1"></i> Batal
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- PANEL KONTROL SORTABLE (HIDDEN DEFAULT) -->
                    <div id="sortControlPanel" class="sort-control-panel" style="display: none;">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="sort-info">
                                    <i class="mdi mdi-information-outline me-2"></i>
                                    <strong>Mode Drag & Drop Aktif</strong> - Seret materi untuk mengubah urutan. Klik "Simpan Urutan" untuk menyimpan perubahan.
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <button type="button" id="saveOrder" class="btn btn-success btn-sm-compact">
                                    <i class="mdi mdi-content-save me-1"></i> Simpan Urutan
                                </button>
                                <button type="button" id="cancelSort" class="btn btn-secondary btn-sm-compact ms-2">
                                    <i class="mdi mdi-close me-1"></i> Batal
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Materials Content -->
                    <div class="p-3">
                        @if($materials->count() > 0)
                            <!-- Materials Accordion -->
                            <div id="materialsAccordion" class="accordion">
                                @foreach($materials as $material)
                                    @php
                                        // Gunakan statistics dari controller
                                        $stats = $material->statistics ?? [];
                                        $totalPeserta = $stats['total_peserta'] ?? 0;
                                        $jumlahHadir = $stats['jumlah_hadir'] ?? 0;
                                        $jumlahDownload = $stats['jumlah_download'] ?? 0;
                                        $jumlahTonton = $stats['jumlah_tonton'] ?? 0;
                                        $jumlahPretest = $stats['jumlah_pretest'] ?? 0;
                                        $jumlahPosttest = $stats['jumlah_posttest'] ?? 0;
                                        $pretestLulus = $stats['pretest_lulus'] ?? 0;
                                        $posttestLulus = $stats['posttest_lulus'] ?? 0;
                                        
                                        // Persentase
                                        $persentaseHadir = $stats['persentase_hadir'] ?? 0;
                                        $persentaseDownload = $stats['persentase_download'] ?? 0;
                                        $persentaseTonton = $stats['persentase_tonton'] ?? 0;
                                        $persentasePretest = $stats['persentase_pretest'] ?? 0;
                                        $persentasePosttest = $stats['persentase_posttest'] ?? 0;
                                        
                                        // Rata-rata nilai
                                        $rataRataPretest = $stats['rata_rata_pretest'] ?? 0;
                                        $rataRataPosttest = $stats['rata_rata_posttest'] ?? 0;
                                        
                                        // Decode JSON fields yang sudah dilakukan di controller
                                        $soalPretestArray = $material->soal_pretest_array ?? [];
                                        $soalPosttestArray = $material->soal_posttest_array ?? [];
                                        $filePathArray = $material->file_path_array ?? [];
                                        $videoData = $material->video_data ?? [];
                                        
                                        // Tentukan apakah material memiliki konten tertentu
                                        $hasFile = !empty($filePathArray) && count($filePathArray) > 0;
                                        $hasVideo = !empty($material->video_url) || !empty($videoData);
                                        $hasPretest = !empty($soalPretestArray) && count($soalPretestArray) > 0;
                                        $hasPosttest = !empty($soalPosttestArray) && count($soalPosttestArray) > 0;
                                        $hasAttendance = $material->attendance_required ?? false;
                                    @endphp

                                    <div class="accordion-item sortable-item" data-id="{{ $material->id }}" data-order="{{ $material->order }}">
                                        <!-- HANYA SATU CHECKBOX - DI HAPUS DUPLICATE -->
                                        <input type="checkbox" 
                                               class="material-checkbox form-check-input" 
                                               name="selected_materials[]" 
                                               value="{{ $material->id }}"
                                               style="display: none;">
                                        
                                        <h2 class="accordion-header" id="heading{{ $material->id }}">
                                            <button class="accordion-button collapsed" type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#collapse{{ $material->id }}" 
                                                    aria-expanded="false" 
                                                    aria-controls="collapse{{ $material->id }}">
                                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                    <div class="d-flex align-items-center">
                                                        <!-- HANDLE UNTUK DRAG & DROP -->
                                                        <span class="sortable-handle me-2" style="display: none;">
                                                            <i class="mdi mdi-drag-vertical"></i>
                                                        </span>
                                                        
                                                        <!-- BADGE NOMOR URUT -->
                                                        <span class="order-badge">
                                                            {{ $material->order }}
                                                        </span>
                                                        
                                                        <div>
                                                            <h6 class="mb-0">{{ $material->title }}</h6>
                                                            <small class="text-muted">
                                                                Tipe: 
                                                                @if($material->type == 'pre_test')
                                                                    <span class="badge bg-warning">Pretest</span>
                                                                @elseif($material->type == 'post_test')
                                                                    <span class="badge bg-info">Posttest</span>
                                                                @else
                                                                    <span class="badge bg-success">Materi</span>
                                                                @endif
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        @if($material->attendance_required)
                                                        <span class="badge bg-warning me-2">
                                                            <i class="mdi mdi-clipboard-check me-1"></i>Wajib Hadir
                                                        </span>
                                                        @endif
                                                        <span class="badge bg-{{ $material->is_active ? 'success' : 'secondary' }} me-2">
                                                            {{ $material->is_active ? 'Aktif' : 'Nonaktif' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $material->id }}" class="accordion-collapse collapse" 
                                             aria-labelledby="heading{{ $material->id }}" 
                                             data-bs-parent="#materialsAccordion">
                                            <div class="accordion-body">
                                                <!-- Statistik Progress Peserta -->
                                                <div class="stats-grid">
                                                    <!-- Statistik Kehadiran -->
                                                    @if($hasAttendance)
                                                    <div class="stats-item attendance">
                                                        <div class="stats-number text-success">
                                                            {{ $jumlahHadir }}/{{ $totalPeserta }}
                                                        </div>
                                                        <div class="stats-label">
                                                            <i class="mdi mdi-clipboard-check me-1"></i>
                                                            Peserta Sudah Hadir
                                                        </div>
                                                        <div class="progress-container">
                                                            <div class="progress">
                                                                <div class="progress-bar bg-success" role="progressbar" 
                                                                     style="width: {{ $persentaseHadir }}%"
                                                                     aria-valuenow="{{ $persentaseHadir }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">{{ $persentaseHadir }}%</small>
                                                        </div>
                                                        @if($persentaseHadir == 0)
                                                        <small class="text-warning d-block mt-1">
                                                            <i class="mdi mdi-alert-circle-outline me-1"></i>
                                                            Belum ada peserta yang hadir
                                                        </small>
                                                        @endif
                                                    </div>
                                                    @endif

                                                    <!-- Statistik Download Materi -->
                                                    @if($hasFile)
                                                    <div class="stats-item material">
                                                        <div class="stats-number text-info">
                                                            {{ $jumlahDownload }}/{{ $totalPeserta }}
                                                        </div>
                                                        <div class="stats-label">
                                                            <i class="mdi mdi-download me-1"></i>
                                                            Peserta Sudah Download Materi
                                                        </div>
                                                        <div class="progress-container">
                                                            <div class="progress">
                                                                <div class="progress-bar bg-info" role="progressbar" 
                                                                     style="width: {{ $persentaseDownload }}%"
                                                                     aria-valuenow="{{ $persentaseDownload }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">{{ $persentaseDownload }}%</small>
                                                        </div>
                                                        
                                                        <!-- Tampilkan file yang tersedia -->
                                                        @if(!empty($filePathArray) && count($filePathArray) > 0)
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block">
                                                                <i class="mdi mdi-file-multiple me-1"></i>
                                                                File tersedia:
                                                            </small>
                                                            @foreach($filePathArray as $index => $filePath)
                                                                @php
                                                                    $fileName = basename($filePath);
                                                                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                                                    $fileIcons = [
                                                                        'pdf' => 'mdi-file-pdf-box text-danger',
                                                                        'doc' => 'mdi-file-word-box text-primary',
                                                                        'docx' => 'mdi-file-word-box text-primary',
                                                                        'ppt' => 'mdi-file-powerpoint-box text-warning',
                                                                        'pptx' => 'mdi-file-powerpoint-box text-warning',
                                                                        'xls' => 'mdi-file-excel-box text-success',
                                                                        'xlsx' => 'mdi-file-excel-box text-success',
                                                                        'jpg' => 'mdi-file-image-box text-info',
                                                                        'jpeg' => 'mdi-file-image-box text-info',
                                                                        'png' => 'mdi-file-image-box text-info',
                                                                        'zip' => 'mdi-zip-box text-secondary',
                                                                        'rar' => 'mdi-zip-box text-secondary',
                                                                    ];
                                                                    $fileIcon = $fileIcons[$fileExt] ?? 'mdi-file-box text-secondary';
                                                                @endphp
                                                                <small class="d-block text-truncate">
                                                                    <i class="mdi {{ $fileIcon }} me-1"></i>
                                                                    {{ $fileName }}
                                                                </small>
                                                            @endforeach
                                                        </div>
                                                        @endif
                                                        
                                                        @if($persentaseDownload == 0)
                                                        <small class="text-warning d-block mt-1">
                                                            <i class="mdi mdi-alert-circle-outline me-1"></i>
                                                            Belum ada peserta yang download
                                                        </small>
                                                        @endif
                                                    </div>
                                                    @endif

                                                    <!-- Statistik Menonton Video -->
                                                    @if($hasVideo)
                                                    <div class="stats-item video">
                                                        <div class="stats-number text-warning">
                                                            {{ $jumlahTonton }}/{{ $totalPeserta }}
                                                        </div>
                                                        <div class="stats-label">
                                                            <i class="mdi mdi-play-circle me-1"></i>
                                                            Peserta Sudah Menonton Video
                                                        </div>
                                                        <div class="progress-container">
                                                            <div class="progress">
                                                                <div class="progress-bar bg-warning" role="progressbar" 
                                                                     style="width: {{ $persentaseTonton }}%"
                                                                     aria-valuenow="{{ $persentaseTonton }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">{{ $persentaseTonton }}%</small>
                                                        </div>
                                                        
                                                        <!-- Informasi video -->
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block">
                                                                <i class="mdi mdi-information-outline me-1"></i>
                                                                Tipe video: 
                                                                <span class="badge bg-{{ $material->video_type == 'youtube' ? 'danger' : ($material->video_type == 'hosted' ? 'success' : 'primary') }}">
                                                                    {{ $material->video_type == 'youtube' ? 'YouTube' : ($material->video_type == 'hosted' ? 'Google Drive' : 'Lokal') }}
                                                                </span>
                                                            </small>
                                                            
                                                            @if(!empty($material->video_url) && $material->video_type == 'youtube')
                                                            <small class="d-block text-truncate">
                                                                <i class="mdi mdi-youtube me-1 text-danger"></i>
                                                                {{ Str::limit($material->video_url, 50) }}
                                                            </small>
                                                            @endif
                                                            
                                                            @if(!empty($videoData) && isset($videoData['original_name']))
                                                            <small class="d-block text-truncate">
                                                                <i class="mdi mdi-video me-1"></i>
                                                                {{ $videoData['original_name'] }}
                                                            </small>
                                                            @endif
                                                        </div>
                                                        
                                                        @if($persentaseTonton == 0)
                                                        <small class="text-warning d-block mt-1">
                                                            <i class="mdi mdi-alert-circle-outline me-1"></i>
                                                            Belum ada peserta yang menonton
                                                        </small>
                                                        @endif
                                                    </div>
                                                    @endif

                                                    <!-- Statistik Pretest -->
                                                    @if($hasPretest)
                                                    <div class="stats-item pretest">
                                                        <div class="stats-number text-purple">
                                                            {{ $jumlahPretest }}/{{ $totalPeserta }}
                                                        </div>
                                                        <div class="stats-label">
                                                            <i class="mdi mdi-clipboard-text me-1"></i>
                                                            Peserta Sudah Mengerjakan Pretest
                                                        </div>
                                                        <div class="progress-container">
                                                            <div class="progress">
                                                                <div class="progress-bar bg-purple" role="progressbar" 
                                                                     style="width: {{ $persentasePretest }}%"
                                                                     aria-valuenow="{{ $persentasePretest }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">{{ $persentasePretest }}%</small>
                                                        </div>
                                                        @if($jumlahPretest > 0)
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block">
                                                                <i class="mdi mdi-trophy me-1"></i>
                                                                Lulus: {{ $pretestLulus }} dari {{ $jumlahPretest }} peserta
                                                            </small>
                                                            <small class="text-muted d-block">
                                                                <i class="mdi mdi-chart-line me-1"></i>
                                                                Rata-rata nilai: {{ round($rataRataPretest, 1) }}%
                                                            </small>
                                                            @if($material->passing_grade)
                                                            <small class="text-muted d-block">
                                                                <i class="mdi mdi-flag-checkered me-1"></i>
                                                                Passing grade: {{ $material->passing_grade }}%
                                                            </small>
                                                            @endif
                                                        </div>
                                                        @else
                                                        <small class="text-warning d-block mt-1">
                                                            <i class="mdi mdi-alert-circle-outline me-1"></i>
                                                            Belum ada peserta yang mengerjakan
                                                        </small>
                                                        @endif
                                                    </div>
                                                    @endif

                                                    <!-- Statistik Posttest -->
                                                    @if($hasPosttest)
                                                    <div class="stats-item posttest">
                                                        <div class="stats-number text-pink">
                                                            {{ $jumlahPosttest }}/{{ $totalPeserta }}
                                                        </div>
                                                        <div class="stats-label">
                                                            <i class="mdi mdi-clipboard-check me-1"></i>
                                                            Peserta Sudah Mengerjakan Posttest
                                                        </div>
                                                        <div class="progress-container">
                                                            <div class="progress">
                                                                <div class="progress-bar bg-pink" role="progressbar" 
                                                                     style="width: {{ $persentasePosttest }}%"
                                                                     aria-valuenow="{{ $persentasePosttest }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">{{ $persentasePosttest }}%</small>
                                                        </div>
                                                        @if($jumlahPosttest > 0)
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block">
                                                                <i class="mdi mdi-trophy me-1"></i>
                                                                Lulus: {{ $posttestLulus }} dari {{ $jumlahPosttest }} peserta
                                                            </small>
                                                            <small class="text-muted d-block">
                                                                <i class="mdi mdi-chart-line me-1"></i>
                                                                Rata-rata nilai: {{ round($rataRataPosttest, 1) }}%
                                                            </small>
                                                            @if($material->passing_grade)
                                                            <small class="text-muted d-block">
                                                                <i class="mdi mdi-flag-checkered me-1"></i>
                                                                Passing grade: {{ $material->passing_grade }}%
                                                            </small>
                                                            @endif
                                                        </div>
                                                        @else
                                                        <small class="text-warning d-block mt-1">
                                                            <i class="mdi mdi-alert-circle-outline me-1"></i>
                                                            Belum ada peserta yang mengerjakan
                                                        </small>
                                                        @endif
                                                    </div>
                                                    @endif
                                                </div>

                                                <!-- Detail Hasil Test -->
                                                @if(($hasPretest && $jumlahPretest > 0) || ($hasPosttest && $jumlahPosttest > 0))
                                                <div class="test-results">
                                                    <h6 class="mb-3"><i class="mdi mdi-chart-bar me-2"></i>Detail Hasil Test</h6>
                                                    
                                                    @if($hasPretest && $jumlahPretest > 0)
                                                    <div class="result-item">
                                                        <div>
                                                            <strong>Pretest</strong>
                                                            <small class="text-muted d-block">
                                                                {{ $pretestLulus }} dari {{ $jumlahPretest }} peserta lulus
                                                                @if($totalPeserta > 0)
                                                                ({{ round(($pretestLulus / $totalPeserta) * 100) }}% dari total peserta)
                                                                @endif
                                                            </small>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="score-badge {{ $pretestLulus >= $jumlahPretest * 0.7 ? 'score-passed' : 'score-failed' }}">
                                                                {{ $persentasePretest }}% Selesai
                                                            </span>
                                                            <div class="text-muted small mt-1">
                                                                Rata-rata: {{ round($rataRataPretest, 1) }}%
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    @if($hasPosttest && $jumlahPosttest > 0)
                                                    <div class="result-item">
                                                        <div>
                                                            <strong>Posttest</strong>
                                                            <small class="text-muted d-block">
                                                                {{ $posttestLulus }} dari {{ $jumlahPosttest }} peserta lulus
                                                                @if($totalPeserta > 0)
                                                                ({{ round(($posttestLulus / $totalPeserta) * 100) }}% dari total peserta)
                                                                @endif
                                                            </small>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="score-badge {{ $posttestLulus >= $jumlahPosttest * 0.7 ? 'score-passed' : 'score-failed' }}">
                                                                {{ $persentasePosttest }}% Selesai
                                                            </span>
                                                            <div class="text-muted small mt-1">
                                                                Rata-rata: {{ round($rataRataPosttest, 1) }}%
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                                @endif

                                                <!-- Informasi Tambahan Materi -->
                                                <div class="material-content mt-3">
                                                    <div class="row text-muted small">
                                                        <!-- Tampilkan info file -->
                                                        @if($hasFile)
                                                        <div class="col-md-6 mb-2">
                                                            <i class="mdi mdi-file-multiple me-1"></i>
                                                            File: {{ count($filePathArray) }} file
                                                            @if($material->duration_file)
                                                            • {{ $material->duration_file }} menit
                                                            @endif
                                                        </div>
                                                        @endif
                                                        
                                                        <!-- Tampilkan info video -->
                                                        @if($hasVideo)
                                                        <div class="col-md-6 mb-2">
                                                            <i class="mdi mdi-video me-1"></i>
                                                            Video: 
                                                            @if($material->video_type == 'youtube')
                                                                YouTube
                                                            @elseif($material->video_type == 'hosted')
                                                                Google Drive
                                                            @else
                                                                Lokal
                                                            @endif
                                                            @if($material->duration_video)
                                                            • {{ $material->duration_video }} menit
                                                            @endif
                                                        </div>
                                                        @endif
                                                        
                                                        <!-- Tampilkan info test -->
                                                        @if($hasPretest || $hasPosttest)
                                                        <div class="col-md-6 mb-2">
                                                            <i class="mdi mdi-timer me-1"></i>
                                                            @if($hasPretest)
                                                            Pretest: {{ $material->durasi_pretest ?? 0 }} menit
                                                            @endif
                                                            @if($hasPosttest)
                                                            @if($hasPretest)<br>@endif
                                                            Posttest: {{ $material->durasi_posttest ?? 0 }} menit
                                                            @endif
                                                        </div>
                                                        @endif
                                                        
                                                        <!-- Info pertanyaan video -->
                                                        @if($hasVideo && $material->has_video_questions)
                                                        <div class="col-md-6 mb-2">
                                                            <i class="mdi mdi-comment-question me-1"></i>
                                                            Pertanyaan video: {{ $material->question_count ?? 0 }} soal
                                                        </div>
                                                        @endif
                                                    </div>

                                                    <!-- Informasi Soal Test -->
                                                    @if($hasPretest)
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="mdi mdi-clipboard-text me-1"></i>
                                                            Jumlah Soal Pretest: {{ count($soalPretestArray) }}
                                                        </small>
                                                    </div>
                                                    @endif

                                                    @if($hasPosttest)
                                                    <div class="mt-1">
                                                        <small class="text-muted">
                                                            <i class="mdi mdi-clipboard-check me-1"></i>
                                                            Jumlah Soal Posttest: {{ count($soalPosttestArray) }}
                                                        </small>
                                                    </div>
                                                    @endif
                                                    
                                                    <!-- Info status - UBAH FORMAT JAM KE WIB -->
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="mdi mdi-calendar-clock me-1"></i>
                                                            @php
                                                                // Buat Carbon instance dan set timezone ke WIB
                                                                $createdAt = \Carbon\Carbon::parse($material->created_at)->timezone('Asia/Jakarta');
                                                                $updatedAt = \Carbon\Carbon::parse($material->updated_at)->timezone('Asia/Jakarta');
                                                            @endphp
                                                            Dibuat: {{ $createdAt->format('d M Y H:i') }} WIB
                                                            @if($material->created_at != $material->updated_at)
                                                            • Diupdate: {{ $updatedAt->format('d M Y H:i') }} WIB
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>

                                                <!-- Action Buttons -->
                                                <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                                    <div class="material-actions">
                                                        <a href="{{ route('admin.kursus.materials.edit', [$kursus, $material]) }}" 
                                                           class="btn btn-warning btn-sm">
                                                            <i class="mdi mdi-pencil me-1"></i> Edit
                                                        </a>
                                                        
                                                        <!-- Tombol Status yang sudah diperbaiki -->
                                                        <form action="{{ route('admin.kursus.materials.status', [$kursus, $material]) }}" 
                                                              method="POST" class="d-inline status-form">
                                                            @csrf
                                                            <input type="hidden" name="is_active" value="{{ $material->is_active ? 0 : 1 }}">
                                                            <button type="submit" class="btn btn-{{ $material->is_active ? 'secondary' : 'success' }} btn-sm status-button">
                                                                <i class="mdi mdi-{{ $material->is_active ? 'eye-off' : 'eye' }} me-1"></i>
                                                                {{ $material->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                    
                                                    <form action="{{ route('admin.kursus.materials.destroy', [$kursus, $material]) }}" 
                                                          method="POST" class="d-inline" 
                                                          onsubmit="return confirmDelete(event, {{ $material->id }})">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="mdi mdi-delete me-1"></i> Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- PAGINATION SECTION -->
                            <div class="pagination-container">
                                <!-- Info -->
                                <div class="pagination-info">
                                    Menampilkan {{ $materials->firstItem() }} – {{ $materials->lastItem() }}
                                    dari {{ $materials->total() }} data
                                </div>

                                <div class="pagination-controls">
                                    <!-- Per Page -->
                                    <div class="per-page-selector">
                                        <label>Per halaman:</label>
                                        <select onchange="changePerPage(this.value)">
                                            <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </div>

                                    <!-- Pagination -->
                                    <nav>
                                        <ul class="pagination">
                                            @php
                                                $current = $materials->currentPage();
                                                $last = $materials->lastPage();

                                                $prev = max($current - 1, 1);
                                                $next = min($current + 1, $last);
                                            @endphp

                                            <!-- First Page -->
                                            <li class="page-item {{ $current == 1 ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $materials->url(1) }}{{ request()->getQueryString() ? '&' . http_build_query(request()->except(['page'])) : '' }}">«</a>
                                            </li>

                                            <!-- Previous Number -->
                                            @if($current > 1)
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $materials->url($prev) }}{{ request()->getQueryString() ? '&' . http_build_query(request()->except(['page'])) : '' }}">{{ $prev }}</a>
                                                </li>
                                            @endif

                                            <!-- Current -->
                                            <li class="page-item active">
                                                <span class="page-link">{{ $current }}</span>
                                            </li>

                                            <!-- Next Number -->
                                            @if($current < $last)
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $materials->url($next) }}{{ request()->getQueryString() ? '&' . http_build_query(request()->except(['page'])) : '' }}">{{ $next }}</a>
                                                </li>
                                            @endif

                                            <!-- Last Page -->
                                            <li class="page-item {{ $current == $last ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $materials->url($last) }}{{ request()->getQueryString() ? '&' . http_build_query(request()->except(['page'])) : '' }}">»</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        @else
                            <!-- Empty State -->
                            <div class="text-center py-5">
                                @if($search || $status || $type)
                                    <i class="mdi mdi-magnify-close mdi-48px text-muted mb-3"></i>
                                    <h4 class="text-muted">Tidak Ditemukan</h4>
                                    <p class="text-muted mb-4">
                                        Tidak ada materi yang sesuai dengan filter pencarian.
                                    </p>
                                    <a href="{{ route('admin.kursus.materials.index', $kursus) }}" class="btn btn-secondary">
                                        <i class="mdi mdi-filter-remove me-2"></i> Reset Filter
                                    </a>
                                @else
                                    <i class="mdi mdi-book-open-variant mdi-48px text-muted mb-3"></i>
                                    <h4 class="text-muted">Belum Ada Materi</h4>
                                    <p class="text-muted mb-4">
                                        Mulai dengan menambahkan materi pertama untuk kursus ini.
                                    </p>
                                    <a href="{{ route('admin.kursus.materials.create', $kursus) }}" class="btn btn-primary">
                                        <i class="mdi mdi-plus-circle me-2"></i> Tambah Materi Pertama
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- TAMBAHKAN SORTABLE JS LIBRARY -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Fungsi confirmDelete yang benar
    function confirmDelete(event, materialId) {
        event.preventDefault();
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Materi akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                const submitBtn = event.target.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Menghapus...';
                submitBtn.disabled = true;
                
                event.target.submit();
            }
        });
    }

    // Fungsi untuk mengubah jumlah data per halaman
    function changePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.set('page', 1); // reset ke page 1
        window.location.href = url.toString();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // ============================================
        // VARIABEL GLOBAL
        // ============================================
        let sortable = null;
        let isSortMode = false;
        let isSelectMode = false;
        const selectedMaterials = new Set();
        const accordion = document.getElementById('materialsAccordion');
        const sortControlPanel = document.getElementById('sortControlPanel');
        const bulkActionPanel = document.getElementById('bulkActionPanel');
        const toggleSortModeBtn = document.getElementById('toggleSortMode');
        const toggleSelectModeBtn = document.getElementById('toggleSelectMode');
        const saveOrderBtn = document.getElementById('saveOrder');
        const cancelSortBtn = document.getElementById('cancelSort');
        const deleteSelectedBtn = document.getElementById('deleteSelected');
        const cancelSelectBtn = document.getElementById('cancelSelect');
        const selectedCountSpan = document.getElementById('selectedCount');
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');
        
        // ============================================
        // DEBOUNCE SEARCH INPUT
        // ============================================
        let searchTimeout;
        if (searchInput && filterForm) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    // Reset ke halaman 1 saat search
                    const pageInput = filterForm.querySelector('input[name="page"]');
                    if (pageInput) {
                        pageInput.value = 1;
                    }
                    filterForm.submit();
                }, 500);
            });
        }
        
        // ============================================
        // FUNGSI UNTUK SELECT MODE (HAPUS BANYAK) - BUG FIXED
        // ============================================
        
        // Toggle select mode
        if (toggleSelectModeBtn) {
            toggleSelectModeBtn.addEventListener('click', function() {
                if (!isSelectMode) {
                    enableSelectMode();
                } else {
                    disableSelectMode();
                }
            });
        }
        
        // Cancel select mode
        if (cancelSelectBtn) {
            cancelSelectBtn.addEventListener('click', disableSelectMode);
        }
        
        // Delete selected materials
        if (deleteSelectedBtn) {
            deleteSelectedBtn.addEventListener('click', deleteSelectedMaterials);
        }
        
        function enableSelectMode() {
            // Exit sort mode if active
            if (isSortMode) {
                disableSortMode();
            }
            
            isSelectMode = true;
            
            // Show bulk action panel
            if (bulkActionPanel) bulkActionPanel.style.display = 'block';
            
            // Change button text
            if (toggleSelectModeBtn) {
                toggleSelectModeBtn.innerHTML = '<i class="mdi mdi-close me-1"></i> Keluar Mode Pilih';
                toggleSelectModeBtn.className = 'btn btn-outline-danger btn-sm-compact';
            }
            
            // Add class to body for CSS styling
            document.body.classList.add('select-mode-active');
            
            // Show checkboxes - HANYA SATU YANG AKTIF
            document.querySelectorAll('.material-checkbox').forEach(checkbox => {
                checkbox.style.display = 'block';
            });
            
            // Hide order badges and drag handles in select mode
            document.querySelectorAll('.order-badge').forEach(badge => {
                badge.style.display = 'none';
            });
            
            document.querySelectorAll('.sortable-handle').forEach(handle => {
                handle.style.display = 'none';
            });
            
            // Add event listeners to checkboxes
            document.querySelectorAll('.material-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', handleCheckboxChange);
                
                // Juga tambahkan click listener untuk accordion header
                const accordionItem = checkbox.closest('.accordion-item');
                const accordionButton = accordionItem.querySelector('.accordion-button');
                
                accordionButton.addEventListener('click', function(e) {
                    if (isSelectMode && !e.target.closest('.material-checkbox')) {
                        e.preventDefault();
                        e.stopPropagation();
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                });
            });
            
            // Disable drag & drop button
            if (toggleSortModeBtn) {
                toggleSortModeBtn.disabled = true;
                toggleSortModeBtn.style.opacity = '0.6';
            }
            
            // Collapse all accordions in select mode
            const collapses = document.querySelectorAll('.accordion-collapse');
            collapses.forEach(collapse => {
                if (collapse.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(collapse, {
                        toggle: false
                    });
                    bsCollapse.hide();
                }
            });
        }
        
        function disableSelectMode() {
            isSelectMode = false;
            selectedMaterials.clear();
            updateSelectedCount();
            
            // Hide bulk action panel
            if (bulkActionPanel) bulkActionPanel.style.display = 'none';
            
            // Reset button
            if (toggleSelectModeBtn) {
                toggleSelectModeBtn.innerHTML = '<i class="mdi mdi-checkbox-multiple-marked me-1"></i> Pilih Materi';
                toggleSelectModeBtn.className = 'btn btn-outline-warning btn-sm-compact';
            }
            
            // Remove class from body
            document.body.classList.remove('select-mode-active');
            
            // Hide checkboxes
            document.querySelectorAll('.material-checkbox').forEach(checkbox => {
                checkbox.style.display = 'none';
                checkbox.checked = false;
            });
            
            // Show order badges again
            document.querySelectorAll('.order-badge').forEach(badge => {
                badge.style.display = 'inline-flex';
            });
            
            // Remove selected class from items
            document.querySelectorAll('.accordion-item').forEach(item => {
                item.classList.remove('selected-item');
            });
            
            // Remove event listeners
            document.querySelectorAll('.material-checkbox').forEach(checkbox => {
                checkbox.removeEventListener('change', handleCheckboxChange);
                
                // Remove click listener dari accordion button
                const accordionItem = checkbox.closest('.accordion-item');
                const accordionButton = accordionItem.querySelector('.accordion-button');
                const newButton = accordionButton.cloneNode(true);
                accordionButton.parentNode.replaceChild(newButton, accordionButton);
                
                // Re-init bootstrap accordion
                newButton.addEventListener('click', function(e) {
                    if (isSelectMode) {
                        e.preventDefault();
                        e.stopPropagation();
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                });
            });
            
            // Enable drag & drop button
            if (toggleSortModeBtn) {
                toggleSortModeBtn.disabled = false;
                toggleSortModeBtn.style.opacity = '1';
            }
            
            // Reset delete button
            if (deleteSelectedBtn) {
                deleteSelectedBtn.disabled = true;
            }
        }
        
        function handleCheckboxChange(event) {
            const checkbox = event.target;
            const materialId = checkbox.value;
            const accordionItem = checkbox.closest('.accordion-item');
            
            if (checkbox.checked) {
                selectedMaterials.add(materialId);
                accordionItem.classList.add('selected-item');
            } else {
                selectedMaterials.delete(materialId);
                accordionItem.classList.remove('selected-item');
            }
            
            updateSelectedCount();
        }
        
        function updateSelectedCount() {
            if (!selectedCountSpan) return;
            
            const count = selectedMaterials.size;
            selectedCountSpan.textContent = count;
            
            // Enable/disable delete button
            if (deleteSelectedBtn) {
                deleteSelectedBtn.disabled = count === 0;
                
                if (count > 0) {
                    deleteSelectedBtn.classList.remove('btn-secondary');
                    deleteSelectedBtn.classList.add('btn-danger');
                } else {
                    deleteSelectedBtn.classList.remove('btn-danger');
                    deleteSelectedBtn.classList.add('btn-secondary');
                }
            }
        }
        
        async function deleteSelectedMaterials() {
            const count = selectedMaterials.size;
            
            if (count === 0) {
                Swal.fire({
                    title: 'Tidak ada materi terpilih',
                    text: 'Silakan pilih materi yang ingin dihapus.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
            
            const result = await Swal.fire({
                title: 'Apakah Anda yakin?',
                html: `Anda akan menghapus <strong>${count} materi</strong> secara permanen.<br><br> 
                       <small class="text-muted">Aksi ini tidak dapat dibatalkan!</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: `Ya, Hapus ${count} Materi`,
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                    try {
                        const response = await fetch('{{ route("admin.kursus.materials.bulk-destroy", $kursus) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                ids: Array.from(selectedMaterials)
                            })
                        });
                        
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        
                        return await response.json();
                    } catch (error) {
                        Swal.showValidationMessage(`Request failed: ${error.message}`);
                        return null;
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            });
            
            if (result.isConfirmed && result.value) {
                const response = result.value;
                
                if (response && response.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        html: `<strong>${response.message}</strong>`,
                        icon: 'success',
                        confirmButtonColor: '#3085d6',
                        timer: 3000,
                        timerProgressBar: true,
                        didClose: () => {
                            // Reload halaman untuk update data
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        title: response ? 'Perhatian!' : 'Error!',
                        html: `<strong>${response ? response.message : 'Terjadi kesalahan pada server'}</strong>`,
                        icon: response && response.success === false && response.message.includes('berhasil') ? 'warning' : 'error',
                        confirmButtonColor: '#3085d6'
                    });
                }
            }
        }
        
        // ============================================
        // KODE UNTUK DRAG & DROP FUNCTIONALITY
        // ============================================
        
        // Toggle sort mode
        if (toggleSortModeBtn) {
            toggleSortModeBtn.addEventListener('click', function() {
                if (!isSortMode) {
                    enableSortMode();
                } else {
                    disableSortMode();
                }
            });
        }
        
        // Save order
        if (saveOrderBtn) {
            saveOrderBtn.addEventListener('click', saveOrder);
        }
        
        // Cancel sort
        if (cancelSortBtn) {
            cancelSortBtn.addEventListener('click', disableSortMode);
        }
        
        function enableSortMode() {
            // Exit select mode if active
            if (isSelectMode) {
                disableSelectMode();
            }
            
            isSortMode = true;
            
            // Show control panel
            if (sortControlPanel) sortControlPanel.style.display = 'block';
            
            // Change button text
            if (toggleSortModeBtn) {
                toggleSortModeBtn.innerHTML = '<i class="mdi mdi-close me-1"></i> Keluar Mode Drag';
                toggleSortModeBtn.className = 'btn btn-outline-danger btn-sm-compact';
            }
            
            // Disable select button
            if (toggleSelectModeBtn) {
                toggleSelectModeBtn.disabled = true;
                toggleSelectModeBtn.style.opacity = '0.6';
            }
            
            // Show drag handles
            document.querySelectorAll('.sortable-handle').forEach(handle => {
                handle.style.display = 'inline-block';
            });
            
            // Collapse all accordions
            const collapses = document.querySelectorAll('.accordion-collapse');
            collapses.forEach(collapse => {
                if (collapse.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(collapse, {
                        toggle: false
                    });
                    bsCollapse.hide();
                }
            });
            
            // Disable all action buttons inside accordion
            document.querySelectorAll('.accordion-collapse .material-actions a, .accordion-collapse .material-actions button, .accordion-collapse form button[type="submit"]').forEach(btn => {
                if (!btn.classList.contains('sortable-handle')) {
                    btn.disabled = true;
                    btn.style.pointerEvents = 'none';
                    btn.style.opacity = '0.6';
                }
            });
            
            // Initialize Sortable
            if (accordion) {
                sortable = new Sortable(accordion, {
                    animation: 150,
                    handle: '.sortable-handle',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-dragging',
                    dragClass: 'sortable-drag',
                    filter: '.accordion-collapse',
                    preventOnFilter: false,
                    onStart: function() {
                        // Update all badges when dragging starts
                        updateOrderBadges();
                    },
                    onEnd: function() {
                        // Update badges after dragging
                        updateOrderBadges();
                    }
                });
            }
        }
        
        function disableSortMode() {
            if (sortable) {
                sortable.destroy();
                sortable = null;
            }
            
            isSortMode = false;
            
            // Hide control panel
            if (sortControlPanel) sortControlPanel.style.display = 'none';
            
            // Reset button
            if (toggleSortModeBtn) {
                toggleSortModeBtn.innerHTML = '<i class="mdi mdi-drag me-1"></i> Ubah Urutan';
                toggleSortModeBtn.className = 'btn btn-outline-primary btn-sm-compact';
            }
            
            // Enable select button
            if (toggleSelectModeBtn) {
                toggleSelectModeBtn.disabled = false;
                toggleSelectModeBtn.style.opacity = '1';
            }
            
            // Hide drag handles
            document.querySelectorAll('.sortable-handle').forEach(handle => {
                handle.style.display = 'none';
            });
            
            // Enable all action buttons inside accordion
            document.querySelectorAll('.accordion-collapse .material-actions a, .accordion-collapse .material-actions button, .accordion-collapse form button[type="submit"]').forEach(btn => {
                btn.disabled = false;
                btn.style.pointerEvents = '';
                btn.style.opacity = '';
            });
            
            // Reset to original order (reload page or reset from stored data)
            // For simplicity, we'll just reload the page
            location.reload();
        }

        function updateAllOrderNumbers() {
            const items = document.querySelectorAll('.sortable-item');
            
            items.forEach((item, index) => {
                const badge = item.querySelector('.order-badge');
                const orderInput = item.querySelector('input[name="order"]');
                
                if (badge) {
                    badge.textContent = index + 1;
                }
                
                if (orderInput) {
                    orderInput.value = index + 1;
                }
                
                // Update data attribute
                item.setAttribute('data-order', index + 1);
            });
        }
        
        function updateOrderBadges() {
            const items = document.querySelectorAll('.sortable-item');
            items.forEach((item, index) => {
                const badge = item.querySelector('.order-badge');
                if (badge) {
                    badge.textContent = index + 1;
                }
                // Update data-order attribute
                item.setAttribute('data-order', index + 1);
            });
        }
        
        async function saveOrder() {
            const items = document.querySelectorAll('.sortable-item');
            const materials = [];
            
            // Update UI terlebih dahulu
            updateAllOrderNumbers();
            
            items.forEach((item, index) => {
                const id = item.getAttribute('data-id');
                const order = index + 1;
                
                materials.push({
                    id: id,
                    order: order
                });
            });
            
            // Show loading
            if (saveOrderBtn) {
                const originalText = saveOrderBtn.innerHTML;
                saveOrderBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Menyimpan...';
                saveOrderBtn.disabled = true;
                
                try {
                    const response = await fetch('{{ route("admin.kursus.materials.update-order", $kursus) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ materials: materials })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Show success message
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            timer: 2000,
                            timerProgressBar: true,
                            didClose: () => {
                                // Exit sort mode and reload
                                disableSortMode();
                                location.reload();
                            }
                        });
                    } else {
                        throw new Error(data.message || 'Terjadi kesalahan');
                    }
                    
                } catch (error) {
                    console.error('Error saving order:', error);
                    
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat menyimpan urutan: ' + error.message,
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                    
                    // Reset button
                    saveOrderBtn.innerHTML = originalText;
                    saveOrderBtn.disabled = false;
                }
            }
        }
        
        // ============================================
        // SWEETALERT FOR STATUS CHANGES
        // ============================================
        
        const statusForms = document.querySelectorAll('form[action*="status"]');
        statusForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const url = this.action;
                const method = 'POST';
                const button = this.querySelector('button[type="submit"]');
                const originalText = button.innerHTML;
                
                // Get current status for confirmation message
                const currentStatus = this.querySelector('input[name="is_active"]').value == 1 ? 'Aktif' : 'Nonaktif';
                const newStatus = currentStatus === 'Aktif' ? 'Nonaktif' : 'Aktif';
                
                Swal.fire({
                    title: 'Konfirmasi Perubahan Status',
                    html: `Apakah Anda yakin ingin mengubah status materi dari <strong>${currentStatus}</strong> menjadi <strong>${newStatus}</strong>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Ubah!',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    preConfirm: async () => {
                        try {
                            const response = await fetch(url, {
                                method: method,
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                }
                            });
                            
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            
                            return await response.json();
                        } catch (error) {
                            Swal.showValidationMessage(`Request failed: ${error}`);
                        }
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        const response = result.value;
                        
                        if (response.success) {
                            // Tampilkan notifikasi sukses
                            Swal.fire({
                                title: 'Berhasil!',
                                html: `<strong>${response.message}</strong><br>Status berhasil diubah menjadi <span class="badge bg-${response.new_status === 'Aktif' ? 'success' : 'secondary'}">${response.new_status}</span>`,
                                icon: 'success',
                                confirmButtonColor: '#3085d6',
                                timer: 3000,
                                timerProgressBar: true,
                                didClose: () => {
                                    // Update UI tanpa reload
                                    const badge = form.closest('.accordion-item').querySelector('.badge.bg-success, .badge.bg-secondary');
                                    const button = form.querySelector('button[type="submit"]');
                                    const statusInput = form.querySelector('input[name="is_active"]');
                                    
                                    // Update badge
                                    if (response.new_status === 'Aktif') {
                                        badge.className = 'badge bg-success me-2';
                                        badge.textContent = 'Aktif';
                                        button.className = 'btn btn-secondary btn-sm';
                                        button.innerHTML = '<i class="mdi mdi-eye-off me-1"></i> Nonaktifkan';
                                        statusInput.value = 0;
                                    } else {
                                        badge.className = 'badge bg-secondary me-2';
                                        badge.textContent = 'Nonaktif';
                                        button.className = 'btn btn-success btn-sm';
                                        button.innerHTML = '<i class="mdi mdi-eye me-1"></i> Aktifkan';
                                        statusInput.value = 1;
                                    }
                                }
                            });
                        } else {
                            // Tampilkan error
                            Swal.fire({
                                title: 'Gagal!',
                                text: response.message || 'Terjadi kesalahan saat mengubah status',
                                icon: 'error',
                                confirmButtonColor: '#d33'
                            });
                        }
                    }
                });
            });
        });

        // ============================================
        // AUTO-SUBMIT FILTER SELECT
        // ============================================
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', function() {
                // Reset ke halaman 1 saat filter berubah
                const pageInput = filterForm.querySelector('input[name="page"]');
                if (pageInput) {
                    pageInput.value = 1;
                }
                filterForm.submit();
            });
        });

        // ============================================
        // RESET FORM KE HALAMAN 1 SAAT SEARCH
        // ============================================
        if (filterForm) {
            // Tambahkan hidden input untuk page jika belum ada
            if (!filterForm.querySelector('input[name="page"]')) {
                const pageInput = document.createElement('input');
                pageInput.type = 'hidden';
                pageInput.name = 'page';
                pageInput.value = '1';
                filterForm.appendChild(pageInput);
            }

            // Saat form di-submit, pastikan page = 1 untuk search dan filter baru
            filterForm.addEventListener('submit', function(e) {
                // Untuk submit yang bukan dari pagination links
                if (!e.submitter || !e.submitter.closest('.pagination')) {
                    const pageInput = this.querySelector('input[name="page"]');
                    if (pageInput) {
                        pageInput.value = '1';
                    }
                }
            });
        }

        // ============================================
        // PRESERVE FILTER PARAMS PADA PAGINATION LINKS
        // ============================================
        // Fungsi ini akan memastikan semua parameter filter tetap ada saat klik pagination
        document.querySelectorAll('.pagination a.page-link').forEach(link => {
            const originalHref = link.getAttribute('href');
            if (originalHref) {
                const url = new URL(originalHref, window.location.origin);
                
                // Preserve semua query parameters dari URL saat ini
                const currentParams = new URLSearchParams(window.location.search);
                
                // Update hanya parameter page, jangan ganti yang lain
                const pageParam = url.searchParams.get('page');
                if (pageParam) {
                    currentParams.set('page', pageParam);
                }
                
                // Set href baru dengan semua parameter
                const newHref = `${url.pathname}?${currentParams.toString()}`;
                link.setAttribute('href', newHref);
            }
        });

        // ============================================
        // FIX UNTUK BOOTSTRAP COLLAPSE DI SELECT MODE
        // ============================================
        // Mencegah collapse terbuka saat mode select aktif
        document.querySelectorAll('.accordion-button').forEach(button => {
            button.addEventListener('click', function(e) {
                if (isSelectMode) {
                    e.preventDefault();
                    e.stopPropagation();
                    const checkbox = this.closest('.accordion-item').querySelector('.material-checkbox');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                }
            });
        });
    });
</script>
@endsection