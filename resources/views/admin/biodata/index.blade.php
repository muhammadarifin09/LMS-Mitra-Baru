@extends('layouts.admin')

@section('title', 'MOOC BPS - Manajemen Biodata Peserta')

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

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-edit {
        background: rgba(52, 152, 219, 0.1);
        color: #3498db;
    }

    .btn-edit:hover {
        background: #3498db;
        color: white;
        transform: translateY(-2px);
    }

    .btn-delete {
        background: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
    }

    .btn-delete:hover {
        background: #e74c3c;
        color: white;
        transform: translateY(-2px);
    }

    /* Pagination Styles */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 25px;
        border-top: 1px solid #e9ecef;
        background: #f8f9fa;
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

    /* Responsive */
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
    }

    .search-input {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.9rem;
        min-width: 150px;
    }
</style>
@endsection

@section('content')
<!-- WELCOME SECTION -->
<div class="welcome-section">
    <h1 class="welcome-title">Manajemen Biodata Peserta</h1>
    <p class="welcome-subtitle">
        Kelola data biodata peserta MOOC dengan mudah. Lihat, edit, atau hapus data peserta sesuai kebutuhan.
    </p>
</div>

<!-- TABLE SECTION -->
<div class="table-container">
    <div class="table-header">
        <div class="d-flex align-items-center gap-3">
            <h2 class="table-title mb-0">Daftar Biodata Peserta</h2>

            <div class="search-box">
                <form method="GET" action="{{ url()->current() }}">
                    <input 
                        type="text" 
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search..."
                        class="search-input"
                    >
                </form>
            </div>
        </div>

        <a href="{{ route('admin.biodata.create') }}" class="btn-tambah">
            <i class="fas fa-plus-circle"></i>
            Tambah Biodata
        </a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Sobat</th>
                    <th>Nama</th>
                    <th>Kecamatan</th>
                    <th>Desa/Kelurahan</th>
                    <th>Posisi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($biodata) && $biodata->count() > 0)
                    @foreach($biodata as $item)
                    <tr>
                        <td>{{ ($biodata->currentPage() - 1) * $biodata->perPage() + $loop->iteration }}</td>
                        <td>{{ $item->id_sobat }}</td>
                        <td>{{ $item->nama_lengkap }}</td>
                        <td>{{ $item->kecamatan }}</td>
                        <td>{{ $item->desa }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($item->posisi, 50) }}</td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.biodata.edit', $item->id_sobat) }}" class="btn-action btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.biodata.destroy', $item->id_sobat) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete" title="Hapus" 
                                            onclick="confirmDelete(event, this.closest('form'))">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-database me-2"></i>
                            @if(!isset($biodata))
                                Variabel $biodata tidak terdefinisi
                            @else
                                Tidak ada data biodata peserta
                            @endif
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- PAGINATION SECTION -->
    @if($biodata->count() > 0)
    <div class="pagination-container">

        <!-- Info -->
        <div class="pagination-info">
            Menampilkan {{ $biodata->firstItem() }} – {{ $biodata->lastItem() }}
            dari {{ $biodata->total() }} data
        </div>

        <div class="pagination-controls">

            <!-- Per Page -->
            <div class="per-page-selector">
                <label>Per halaman:</label>
                <select onchange="changePerPage(this.value)">
                    <option value="5" {{ $biodata->perPage() == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ $biodata->perPage() == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $biodata->perPage() == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $biodata->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $biodata->perPage() == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>

            <!-- Pagination -->
            <nav>
                <ul class="pagination">

                    @php
                        $current = $biodata->currentPage();
                        $last = $biodata->lastPage();

                        $prev = max($current - 1, 1);
                        $next = min($current + 1, $last);
                    @endphp

                    <!-- First Page -->
                    <li class="page-item {{ $current == 1 ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $biodata->url(1) }}">«</a>
                    </li>

                    <!-- Previous Number -->
                    @if($current > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $biodata->url($prev) }}">{{ $prev }}</a>
                        </li>
                    @endif

                    <!-- Current -->
                    <li class="page-item active">
                        <span class="page-link">{{ $current }}</span>
                    </li>

                    <!-- Next Number -->
                    @if($current < $last)
                        <li class="page-item">
                            <a class="page-link" href="{{ $biodata->url($next) }}">{{ $next }}</a>
                        </li>
                    @endif

                    <!-- Last Page -->
                    <li class="page-item {{ $current == $last ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $biodata->url($last) }}">»</a>
                    </li>

                </ul>
            </nav>
        </div>
    </div>
    @endif

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
    // Delete Confirmation dengan SweetAlert
    function confirmDelete(event, form) {
        event.preventDefault();
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data biodata dan akun akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
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
</script>   
@endsection