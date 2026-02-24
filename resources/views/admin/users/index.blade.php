@extends('layouts.admin')

@section('title', 'Manajemen User - MOOC BPS')

@section('styles')
<!-- SweetAlert CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
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
    
    .table-responsive {
        padding: 0;
    }
    
    .table {
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    
    .table thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        padding: 15px 12px;
        font-weight: 700;
        color: #1e3c72;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table tbody td {
        padding: 12px;
        vertical-align: middle;
        border-bottom: 1px solid #e9ecef;
    }
    
    .table tbody tr:hover {
        background-color: rgba(30, 60, 114, 0.03);
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
        text-decoration: none;
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
    
    .btn-delete:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    
    .btn-delete:disabled:hover {
        background: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
        transform: none;
    }
    
    .badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .badge-admin {
        background: #dc3545;
        color: white;
    }
    
    .badge-mitra {
        background: #198754;
        color: white;
    }
    
    .badge-instruktur {
        background: #0d6efd;
        color: white;
    }
    
    .badge-moderator {
        background: #fd7e14;
        color: white;
    }
    
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
    
    /* Pagination Styles - Sama dengan Biodata */
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
    
    /* Filter Section - Tetap seperti semula */
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
    
    /* Per Page Selector - Tetap di filter section */
    .per-page-container {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
    }
    
    .per-page-selector select {
        padding: 8px 15px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        background: white;
        font-size: 0.85rem;
        color: #495057;
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
        
        .filter-container {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .per-page-container {
            margin-left: 0;
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>
@endsection

@section('content')
<!-- WELCOME SECTION -->
<div class="welcome-section">
    <h1 class="welcome-title">Manajemen User</h1>
    <p class="welcome-subtitle">
        Kelola data user sistem MOOC BPS dengan mudah. Lihat, edit, atau hapus data user sesuai kebutuhan.
    </p>
</div>

<!-- TABLE SECTION -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">Daftar User</h2>
        <a href="{{ route('admin.users.create') }}" class="btn-tambah">
            <i class="fas fa-plus-circle"></i>
            Tambah User
        </a>
    </div>

    <!-- FILTER SECTION -->
    <div class="filter-section">
        <form method="GET" action="{{ route('admin.users.index') }}" id="filterForm">
            <div class="filter-container">
                <!-- Search Input -->
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Cari nama atau username..." 
                           value="{{ request('search') }}" class="search-input">
                </div>

                <!-- Role Filter -->
                <select name="role" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Role</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="mitra" {{ request('role') == 'mitra' ? 'selected' : '' }}>Mitra</option>
                    <option value="instruktur" {{ request('role') == 'instruktur' ? 'selected' : '' }}>Instruktur</option>
                    <option value="moderator" {{ request('role') == 'moderator' ? 'selected' : '' }}>Moderator</option>
                </select>

                <!-- Reset Filter -->
                @if(request('search') || request('role'))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($users) && $users->count() > 0)
                    @foreach($users as $index => $user)
                    <tr>
                        <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                        <td>{{ $user->nama ?? $user->name }}</td>
                        <td>{{ $user->username ?? $user->email }}</td>
                        <td>********</td>
                        <td>
                            @if($user->role == 'admin')
                                <span class="badge badge-admin">Admin</span>
                            @elseif($user->role == 'mitra')
                                <span class="badge badge-mitra">Mitra</span>
                            @elseif($user->role == 'instruktur')
                                <span class="badge badge-instruktur">Instruktur</span>
                            @elseif($user->role == 'moderator')
                                <span class="badge badge-moderator">Moderator</span>
                            @else
                                <span class="badge bg-secondary">{{ $user->role }}</span>
                            @endif
                            
                            {{-- Tampilkan badge jika user memiliki biodata --}}
                            @if($user->biodata)
                                <span class="badge bg-info ms-1" title="Memiliki data biodata">
                                    <i class="fas fa-id-card"></i>
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-action btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                {{-- Tombol hapus: nonaktif hanya untuk user sendiri --}}
                                @if(auth()->user()->id == $user->id)
                                    {{-- User sendiri - nonaktif --}}
                                    <button class="btn-action btn-delete" title="Tidak dapat menghapus akun sendiri" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @else
                                    {{-- User lain - aktif (biodata tidak ikut terhapus) --}}
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete" title="Hapus" >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="empty-state">
                                <i class="fas fa-database"></i>
                                <h4>Belum ada data user</h4>
                                <p>
                                    @if(request('search') || request('role'))
                                        Tidak ada user yang sesuai dengan filter pencarian
                                    @else
                                        Silakan tambah user baru untuk memulai
                                    @endif
                                </p>
                                <a href="{{ route('admin.users.create') }}" class="btn-tambah mt-3">
                                    <i class="fas fa-plus-circle"></i>
                                    Tambah User
                                </a>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- PAGINATION SECTION - SAMA DENGAN BIODATA -->
    @if($users->count() > 0)
    <div class="pagination-container">

        <!-- Info -->
        <div class="pagination-info">
            Menampilkan {{ $users->firstItem() }} – {{ $users->lastItem() }}
            dari {{ $users->total() }} data
        </div>

        <div class="pagination-controls">

            <!-- Per Page -->
            <div class="per-page-selector">
                <label>Per halaman:</label>
                <select onchange="changePerPage(this.value)">
                    <option value="5" {{ $users->perPage() == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ $users->perPage() == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $users->perPage() == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $users->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $users->perPage() == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>

            <!-- Pagination -->
            <nav>
                <ul class="pagination">

                    @php
                        $current = $users->currentPage();
                        $last = $users->lastPage();

                        $prev = max($current - 1, 1);
                        $next = min($current + 1, $last);
                    @endphp

                    <!-- First Page -->
                    <li class="page-item {{ $current == 1 ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $users->url(1) }}{{ request()->getQueryString() ? '&' . http_build_query(request()->except(['page'])) : '' }}">«</a>
                    </li>

                    <!-- Previous Number -->
                    @if($current > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $users->url($prev) }}{{ request()->getQueryString() ? '&' . http_build_query(request()->except(['page'])) : '' }}">{{ $prev }}</a>
                        </li>
                    @endif

                    <!-- Current -->
                    <li class="page-item active">
                        <span class="page-link">{{ $current }}</span>
                    </li>

                    <!-- Next Number -->
                    @if($current < $last)
                        <li class="page-item">
                            <a class="page-link" href="{{ $users->url($next) }}{{ request()->getQueryString() ? '&' . http_build_query(request()->except(['page'])) : '' }}">{{ $next }}</a>
                        </li>
                    @endif

                    <!-- Last Page -->
                    <li class="page-item {{ $current == $last ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $users->url($last) }}{{ request()->getQueryString() ? '&' . http_build_query(request()->except(['page'])) : '' }}">»</a>
                    </li>

                </ul>
            </nav>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // SweetAlert for delete confirmation
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const userName = this.closest('tr').querySelector('td:nth-child(2)').textContent;
                
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Apakah Anda yakin ingin menghapus user ${userName}?`,
                    html: `Apakah Anda yakin ingin menghapus user <strong>${userName}</strong>?<br><br>
                          <small class="text-muted">Data biodata terkait akan tetap tersimpan.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });

        // Debounce search input
        let searchTimeout;
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    document.getElementById('filterForm').submit();
                }, 500);
            });
        }

        // Fungsi untuk mengubah jumlah data per halaman
        window.changePerPage = function(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            url.searchParams.set('page', 1); // reset ke page 1
            window.location.href = url.toString();
        };
    });
</script>
@endsection