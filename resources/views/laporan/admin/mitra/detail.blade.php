@extends('layouts.admin')

@section('title', 'MOOC BPS - Detail Laporan Mitra')

@section('styles')

<!-- SweetAlert CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* Main Content Area */
    .main-content {
        flex: 1;
        padding: 40px;
        background: rgba(255, 255, 255, 0.95);
        margin: 20px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .welcome-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 25px;
    }

    .welcome-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .welcome-subtitle {
        font-size: 1.1rem;
        line-height: 1.6;
        opacity: 0.9;
    }

    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        overflow: hidden;
        margin-bottom: 25px;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid #e9ecef;
        background: #f8f9fa;
    }

    .table-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1e3c72;
        margin: 0;
    }

    .btn-export {
        background: linear-gradient(135deg, #217346, #2e8b57);
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

    .btn-export:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(30, 60, 114, 0.3);
        color: white;
    }

    .btn-danger-custom {
        background: linear-gradient(135deg, #dc3545, #c82333);
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

    .btn-danger-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        color: white;
    }

    /* Info Mitra Card */
    .info-mitra-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        padding: 25px;
        margin-bottom: 25px;
        font-family: inherit;
    }

    .info-mitra-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1e3c72;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .info-item {
        margin-bottom: 15px;
    }

    .info-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .info-value {
        color: #34495e;
        font-size: 1.1rem;
    }

    .info-row {
        margin-bottom: 8px;
    }

    .info-inline {
        display: grid;
        grid-template-columns: 230px 1fr;
        column-gap: 16px;
        font-size: 14px;
    }

    .info-inline .label {
        color: #1e3c72;
        white-space: nowrap;
        font-weight: 500;
    }

    .info-inline .value {
        color: #111827;
    }


    /* Progress Bar Styling */
    .progress {
        height: 20px;
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar {
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Badge Styling */
    .badge-custom {
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn-back {
        background: #6c757d;
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

    .btn-back:hover {
        background: #5a6268;
        color: white;
        transform: translateY(-2px);
    }

    .btn-save {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }

    /* Statistik Cards */
    .stat-card {
        border-radius: 10px;
        padding: 15px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.7;
        margin-bottom: 10px;
    }

    .stat-number {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    /* Header Section Styling */
    .page-title-box {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid #e9ecef;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: white;
        margin: 0;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-secondary:hover {
        background: #5a6268;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(108, 117, 125, 0.2);
    }
</style>
@endsection

@section('content')
    <!-- HEADER SECTION -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="page-title mb-0">
                            <i class="fas fa-user-circle me-2"></i>
                            Detail Laporan: {{ $mitra->biodata->nama_lengkap }}
                        </h4>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="{{ route('admin.laporan.mitra') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- INFO MITRA -->
    <div class="info-mitra-card">
        <h3 class="info-mitra-title">
            <i class="fas fa-user-circle me-2"></i>Informasi Mitra
        </h3>

        <!-- BARIS 1 -->
        <div class="row info-row">
            <div class="col-md-6">
                <div class="info-inline">
                    <span class="label">ID Sobat</span>
                    <span class="value">{{ $mitra->biodata->id_sobat ?? '-' }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-inline">
                    <span class="label">Total Kursus Diikuti</span>
                    <span class="value">{{ count($kursusData) }}</span>
                </div>
            </div>
        </div>

        <!-- BARIS 2 -->
        <div class="row info-row">
            <div class="col-md-6">
                <div class="info-inline">
                    <span class="label">Nama Lengkap</span>
                    <span class="value">{{ $mitra->biodata->nama_lengkap }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-inline">
                    <span class="label">Kursus Selesai</span>
                    <span class="value">
                        {{ collect($kursusData)->where('progress', 100)->count() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- BARIS 3 -->
        <div class="row info-row">
            <div class="col-md-6">
                <div class="info-inline">
                    <span class="label">Kecamatan, Desa/Kelurahan</span>
                    <span class="value">
                        {{ $mitra->biodata->kecamatan ?? '-' }},
                        {{ $mitra->biodata->desa ?? '-' }}
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-inline">
                    <span class="label">Rata-rata Progress</span>
                    <span class="value">
                        @php
                            $avgProgress = collect($kursusData)->avg('progress');
                            echo $avgProgress ? round($avgProgress, 1) . '%' : '0%';
                        @endphp
                    </span>
                </div>
            </div>
        </div>

        <!-- BARIS 4 -->
        <div class="row info-row">
            <div class="col-md-6">
                <div class="info-inline">
                    <span class="label">No Telepon</span>
                    <span class="value">{{ $mitra->biodata->no_telepon ?? '-' }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-inline">
                    <span class="label">Rata-rata Nilai</span>
                    <span class="value">
                        {{ $rataNilai !== null ? $rataNilai : '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>



    <!-- STATISTIK
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number">{{ count($kursusData) }}</div>
                <div class="stat-label">Total Kursus yang Diikuti</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number">
                    {{ collect($kursusData)->where('progress', 100)->count() }}
                </div>
                <div class="stat-label">Kursus Selesai</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-number">
                    @php
                        $avgProgress = collect($kursusData)->avg('progress');
                        echo $avgProgress ? round($avgProgress, 1) . '%' : '0%';
                    @endphp
                </div>
                <div class="stat-label">Rata-Rata Progress</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-number">
                    @php
                        $nilaiData = collect($kursusData)->where('progress', 100)->pluck('nilai')->filter();
                        echo $nilaiData->isNotEmpty() ? round($nilaiData->avg(), 2) : '-';
                    @endphp
                </div>
                <div class="stat-label">Rata-Rata Nilai</div>
            </div>
        </div>
    </div> -->

    <!-- Tabel Kursus -->
    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">
                <i class="fas fa-book-open me-2"></i>Daftar Kursus yang Diikuti
            </h2>
            <div>
                <a href="{{ route('admin.laporan.mitra.detail.csv', $mitra) }}" 
                class="btn-export mr-2">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
                <a href="{{ route('admin.laporan.mitra.pdf.detail', $mitra) }}" 
                class="btn-danger-custom"
                target="_blank">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
                <form id="arsipForm"
                    action="{{ route('admin.laporan.mitra.generate', $mitra) }}"
                    method="POST"
                    target="_blank"
                    style="display:inline;">
                    @csrf

                    <button type="button" id="btnSimpanArsip" class="btn-save">
                        <i class="fas fa-database"></i> Simpan ke Arsip
                    </button>
                </form>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th width="40%">Judul Kursus</th>
                        <th>Tanggal Daftar</th>
                        <th>Tanggal Selesai</th>
                        <th>Progress</th>
                        <th style="text-align: center;">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kursusData as $data)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div style="font-weight: 600; color: #2c3e50; margin-bottom: 3px;">
                                {{ $data['kursus']->judul_kursus }}
                            </div>
                            @if($data['kursus']->deskripsi_singkat)
                            <div style="font-size: 0.85rem; color: #7f8c8d;">
                                {{ Str::limit($data['kursus']->deskripsi_singkat, 60) }}
                            </div>
                            @endif
                        </td>
                        <td>{{ $data['tanggal_daftar'] }}</td>
                        <td>{{ $data['tanggal_selesai'] }}</td>
                        <td>
                            <!-- Progress Bar Manual -->
                            <div style="margin-bottom: 5px;">
                                <span style="font-size: 0.85rem; color: #495057;">
                                    {{ $data['completed_materials'] }} dari {{ $data['total_materials'] }} materi
                                </span>
                            </div>
                            @php
                                $progress = (int) $data['progress'];
                                $color = $progress === 100 ? '#28a745' : '#007bff';
                            @endphp
                            <div style="width:100%; height:10px; background:#e9ecef; border-radius:5px; overflow:hidden;">
                                <div style="width: <?= $progress ?>%; height:100%; background-color: <?= $color ?>; border-radius:5px;"></div>
                            </div>

                            <div style="margin-top:5px; text-align:center;">
                                <span style="font-size:0.9rem; font-weight:600; color: <?= $color ?>;">
                                    <?= $progress ?>%
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($data['nilai'] !== null)
                                <span class="badge-custom bg-success" style="color: white;">
                                    {{ $data['nilai'] }}
                                </span>
                            @else
                                <span class="badge-custom bg-secondary" style="color: white;">
                                    -
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="fas fa-book me-2"></i>
                            Belum mengikuti kursus
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Copyright -->
    <div class="text-center mt-5 pt-4 border-top">
        <p style="color: #5a6c7d; font-size: 0.9rem;">
            Copyright © 2025 | MOOC BPS
        </p>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Inisialisasi DataTable
    $(document).ready(function() {
        $('.table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "pageLength": 10,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json",
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    });
</script> 

<script>
document.getElementById('btnSimpanArsip').addEventListener('click', function () {
    Swal.fire({
        title: 'Simpan laporan?',
        text: 'Simpan laporan ke arsip database?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {

            // Optional notif sukses (sebelum submit)
            Swal.fire({
                title: 'Menyimpan...',
                text: 'Laporan sedang disimpan ke arsip',
                icon: 'success',
                timer: 1200,
                showConfirmButton: false
            });

            // Submit form POST
            setTimeout(() => {
                document.getElementById('arsipForm').submit();
            }, 1200);
        }
    });
});
</script>

@endsection