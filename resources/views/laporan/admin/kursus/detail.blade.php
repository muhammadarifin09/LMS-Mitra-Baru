@extends('layouts.admin')

@section('title', 'Detail Laporan Kursus')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-alt mr-2"></i>
                        Detail Laporan Kursus: {{ $kursus->judul_kursus }}
                    </h3>
                    <div class="card-tools">
                        {{-- 🔵 TOMBOL BARU --}}
                        <a href="{{ route('admin.laporan.kursus') }}" 
                           class="btn btn-sm btn-secondary mr-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <form action="{{ route('admin.laporan.kursus.generate', $kursus->id) }}"
                            method="POST"
                            class="d-inline"
                            onsubmit="return confirm('Generate dan simpan laporan kursus ini?')">
                            @csrf
                            <button class="btn btn-sm btn-primary mr-2">
                                <i class="fas fa-database"></i> Simpan ke Arsip
                            </button>
                        </form>
                        <!-- PERBAIKAN: route ke list kursus, bukan pdf ringkas -->
                    
                        <!-- TAMBAH: tombol import excel -->
                        <a href="{{ route('admin.laporan.kursus.detail.csv', $kursus->id) }}" 
                        class="btn btn-sm btn-success mr-2">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>

                        <!-- PERBAIKAN: route ke pdf detail, bukan detail view -->
                        <a href="{{ route('admin.laporan.kursus.pdf.detail', $kursus->id) }}" 
                           class="btn btn-sm btn-danger" target="_blank">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>
             

                <div class="card-body">
                    <!-- Course Information Card -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Informasi Kursus</h3>
                                </div>
                                <div class="card-body">
                                   <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="40%">Judul Kursus</th>
                                                    <td>{{ $kursus->judul_kursus }}</td>
                                                </tr>
                                                <tr>
                                                    <th width="40%">Total Materi</th>
                                                    <td>{{ $kursus->materials->count() }} Materi</td>
                                                </tr>
                                            </table>
                                        </div>

                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th>Total Peserta</th>
                                                    <td>{{ $kursus->enrollments->count() }} Peserta</td>
                                                </tr>
                                                <tr>
                                                    <th>Tanggal Dibuat</th>
                                                    <td>{{ $kursus->created_at->format('d M Y H:i') }}</td>
                                                </tr>
                                                <!-- <tr>
                                                    <th>Rata-rata Nilai</th>
                                                    <td>
                                                        <strong>
                                                            @if (optional($laporan)->rata_rata_nilai === null)
                                                                <span class="text-muted fst-italic">Belum ada nilai</span>
                                                            @else
                                                                {{ number_format(optional($laporan)->rata_rata_nilai, 1) }}
                                                            @endif
                                                        </strong>
                                                    </td>
                                                </tr> -->
                                            </table>
                                        </div>
                                    </div>

                                    @if($kursus->deskripsi_singkat)
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <h6>Deskripsi:</h6>
                                            <p>{{ $kursus->deskripsi_singkat }}</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Participants List -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-users mr-2"></i>
                                        Daftar Peserta 
                                    </h3>
                                </div>
                                <div class="card-body">
                                    @if($kursus->enrollments->isEmpty())
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        Belum ada peserta yang mendaftar pada kursus ini.
                                    </div>
                                    @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="pesertaTable">
                                            <thead>
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th width="25%">Nama Peserta</th>
                                                    <th width="20%">Email/Username</th>
                                                    <th width="15%">Tanggal Daftar</th>
                                                    <th width="25%">Progress</th>
                                                    <th width="15%">Nilai Rata-rata</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pesertaData as $data)
                                                @php
                                                    // Data sudah dihitung di controller
                                                    $progressPercentage = $data['progress_percentage'];
                                                    $completedMaterials = $data['completed_materials'];
                                                    $totalMaterials = $data['total_materials'];
                                                    
                                                    // Beri warna berdasarkan progress
                                                    $progressColor = 'bg-danger'; // default merah
                                                    if ($progressPercentage >= 80) {
                                                        $progressColor = 'bg-success';
                                                    } elseif ($progressPercentage >= 50) {
                                                        $progressColor = 'bg-primary';
                                                    } elseif ($progressPercentage > 0) {
                                                        $progressColor = 'bg-info';
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                    <td>
                                                        <strong>{{ $data['user']->nama ?? ($data['user']->nama ?? 'N/A') }}</strong>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ $data['user']->email ?? ($data['user']->username ?? 'N/A') }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span style="font-size: 0.9rem; font-weight: 500; color: #000;">
                                                            {{ $data['enrollment']->created_at->format('d M Y') }}
                                                        </span>
                                                    </td>
                                                    @php
                                                        $progressWidth = max($progressPercentage, 5);
                                                    @endphp

                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-grow-1">
                                                                <div class="progress" style="height: 20px; border-radius: 10px; overflow: hidden;">
                                                                    <div class="progress-bar {{ $progressColor }}"
                                                                        role="progressbar"
                                                                        style="--progress-width: {{ $progressWidth }}%;"
                                                                        aria-valuenow="{{ $progressPercentage }}"
                                                                        aria-valuemin="0"
                                                                        aria-valuemax="100">
                                                                        <span style="font-size: 11px; font-weight: 600; color: white;">
                                                                            {{ $progressPercentage }}%
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <small class="ml-2" style="min-width: 40px; font-weight: 600; color: #000;">
                                                                ({{ $completedMaterials }}/{{ $totalMaterials }})
                                                            </small>
                                                        </div>
                                                    </td>

                                                    <td class="text-center">
                                                        <span style="
                                                            font-size: 0.9rem; 
                                                            padding: 6px 12px; 
                                                            min-width: 60px;
                                                            font-weight: 600;
                                                            color: #000 !important;
                                                            background-color: #f8f9fa;
                                                            border: 1px solid #dee2e6;
                                                            border-radius: 50px;
                                                            display: inline-block;
                                                        ">
                                                            @if ($data['nilai'] === null)
                                                                <span class="text-muted fst-italic">Belum ada nilai</span>
                                                            @else
                                                                {{ number_format($data['nilai'], 1) }}
                                                            @endif
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    

                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Materials List -->
                    @if($kursus->materials->isNotEmpty())
                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="card card-warning">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-book mr-2"></i>
                                        Daftar Materi 
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="materiTable">
                                            <thead>
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th width="40%">Judul Materi</th>
                                                    <th width="15%">Tipe</th>
                                                    <th width="15%">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($kursus->materials as $material)
                                                @php
                                                    $materialType = $material->material_type ?? $material->tipe_material ?? 'unknown';
                                                    // Normalisasi nama tipe
                                                    if (strtolower($materialType) == 'video') {
                                                        $typeDisplay = 'Video';
                                                    } elseif (strtolower($materialType) == 'dokumen' || strtolower($materialType) == 'document') {
                                                        $typeDisplay = 'Dokumen';
                                                    } elseif (strtolower($materialType) == 'kuis' || strtolower($materialType) == 'quiz') {
                                                        $typeDisplay = 'Kuis';
                                                    } elseif (strtolower($materialType) == 'theory' || strtolower($materialType) == 'teori') {
                                                        $typeDisplay = 'Teori';
                                                    } else {
                                                        $typeDisplay = ucfirst($materialType);
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                    <td>{{ $material->title ?? $material->judul_material ?? 'N/A' }}</td>
                                                    <td>
                                                        <span style="
                                                            display: inline-block;
                                                            padding: 4px 12px;
                                                            border-radius: 4px;
                                                            font-size: 0.85rem;
                                                            font-weight: 500;
                                                            background-color: #f8f9fa;
                                                            border: 1px solid #dee2e6;
                                                            color: #212529 !important;
                                                            text-transform: capitalize;
                                                        ">
                                                            {{ $typeDisplay }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span style="
                                                            display: inline-block;
                                                            padding: 4px 12px;
                                                            border-radius: 4px;
                                                            font-size: 0.85rem;
                                                            font-weight: 500;
                                                            background-color: #f8f9fa;
                                                            border: 1px solid #28a745;
                                                            color: #212529 !important;
                                                        ">
                                                            <i class="fas fa-check-circle mr-1" style="color: #28a745;"></i>
                                                            Aktif
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Laporan ini dibuat pada: <span id="realtimeTimestamp">{{ now()->format('d M Y H:i:s') }}</span>
                                <span id="realtimeIndicator" class="badge badge-success ml-2" 
                                    style="font-size: 8px; padding: 2px 6px; animation: pulse 2s infinite;">Live</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        confirmButtonText: 'OK',
        confirmButtonColor: '#3085d6',
        allowOutsideClick: false
    });
</script>
@endif

<script>
    $(document).ready(function() {
        // Inisialisasi DataTable untuk tabel peserta
        $('#pesertaTable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "pageLength": 10,
            "order": [[3, "desc"]], // Urutkan berdasarkan tanggal daftar
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            }
        });
        
        // Inisialisasi DataTable untuk tabel materi
        $('#materiTable').DataTable({
            "paging": false,
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true,
            "order": [[0, "asc"]] // Urutkan berdasarkan urutan
        });
        
        // Konfirmasi sebelum export PDF Ringkas (tombol kuning/warning)
        $(document).on('click', '.btn-warning[href*="pdf"]', function(e) {
            e.preventDefault();
            const pdfUrl = $(this).attr('href');
            const kursusName = '{{ $kursus->judul_kursus }}';
            
            Swal.fire({
                title: 'Export Ringkasan PDF?',
                html: `Apakah Anda yakin ingin export <strong>Ringkasan PDF</strong> untuk:<br>"${kursusName}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Export Ringkasan',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            window.open(pdfUrl, '_blank');
                            resolve();
                        }, 500);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Ringkasan PDF Sedang Diproses!',
                        text: 'Laporan ringkasan sedang dibuat...',
                        icon: 'info',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        });
        
        // Konfirmasi sebelum export PDF Detail (tombol merah/danger)
        $(document).on('click', '.btn-danger[href*="pdf"]', function(e) {
            e.preventDefault();
            const pdfUrl = $(this).attr('href');
            const kursusName = '{{ $kursus->judul_kursus }}';
            
            Swal.fire({
                title: 'Export Detail PDF?',
                html: `Apakah Anda yakin ingin export <strong>Detail PDF Lengkap</strong> untuk:<br>"${kursusName}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Export Detail',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            window.open(pdfUrl, '_blank');
                            resolve();
                        }, 500);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Detail PDF Sedang Diproses!',
                        text: 'Laporan detail sedang dibuat (mungkin butuh waktu lebih lama)...',
                        icon: 'info',
                        timer: 2500,
                        showConfirmButton: false
                    });
                }
            });
        });
        
        // Fungsi untuk update timestamp realtime
        function updateRealtimeTimestamp() {
            const now = new Date();
            
            const monthNames = [
                'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
            ];
            
            const day = now.getDate().toString().padStart(2, '0');
            const month = monthNames[now.getMonth()];
            const year = now.getFullYear();
            let hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            
            const timestamp = `${day} ${month} ${year} ${hours}:${minutes}:${seconds}`;
            document.getElementById('realtimeTimestamp').textContent = timestamp;
            
            const indicator = document.getElementById('realtimeIndicator');
            indicator.style.opacity = indicator.style.opacity === '0.5' ? '1' : '0.5';
        }
        
        setInterval(updateRealtimeTimestamp, 1000);
        updateRealtimeTimestamp();
    });
</script>
@endsection

@section('styles')
<style>
    /* Animasi untuk indikator Live */
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    /* Gaya untuk statistik card */
    .stat-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    
    /* Gaya untuk progress bar */
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
    
    /* Gaya untuk tabel */
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .table tbody tr:hover {
        background-color: #f7fafc;
    }
    
    /* Gaya untuk badge nilai */
    .badge-pill {
        border-radius: 50px;
        font-weight: 600;
    }

    .progress-bar {
    width: var(--progress-width);
    min-width: 30px;
    }

</style>
@endsection