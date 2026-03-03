<div class="sidebar">
    <!-- Admin Menu Section -->
    <div class="sidebar-section">
        <div class="sidebar-title">Menu Admin</div>

        <a href="{{ route('admin.dashboard') }}"
           class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('admin.users.index') }}"
           class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Manajemen User</span>
        </a>

        <a href="{{ route('admin.biodata.index') }}"
           class="sidebar-item {{ request()->routeIs('admin.biodata.*') ? 'active' : '' }}">
            <i class="fas fa-id-card"></i>
            <span>Manajemen Biodata</span>
        </a>

            <a href="{{ route('admin.settings') }}"
        class="sidebar-item {{ request()->routeIs('admin.settings') || request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="fas fa-cog"></i>
            <span>Pengaturan Nilai</span>
        </a>



        {{-- MENU LAPORAN DROPDOWN --}}
        <div class="sidebar-dropdown-wrapper">
            <div class="sidebar-dropdown-toggle 
                {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}"
                data-bs-toggle="collapse"
                data-bs-target="#laporanMenu"
                aria-expanded="{{ request()->routeIs('admin.laporan.*') ? 'true' : 'false' }}">
                
                <div class="sidebar-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Laporan</span>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </div>
            </div>
            
            <div class="collapse sidebar-dropdown-content 
                {{ request()->routeIs('admin.laporan.*') ? 'show' : '' }}"
                id="laporanMenu">
                
                <div class="dropdown-items">
                    <!-- PERBAIKAN: Menggunakan route yang benar -->
                    <a href="{{ route('admin.laporan.kursus') }}"  
                       class="sidebar-subitem 
                       {{ request()->routeIs('admin.laporan.kursus') || 
                          request()->routeIs('admin.laporan.kursus.*') ? 'active' : '' }}">
                        <i class="fas fa-book-open"></i>
                        <span>Laporan Kursus</span>
                    </a>
                    <a href="{{ route('admin.laporan.mitra') }}"  
                       class="sidebar-subitem 
                       {{ request()->routeIs('admin.laporan.mitra') || 
                          request()->routeIs('admin.laporan.mitra.*') ? 'active' : '' }}">
                        <i class="fas fa-book-open"></i>
                        <span>Laporan Mitra</span>
                    </a>
                    
                 
                </div>
            </div>
        </div>
    </div>

    <!-- Account Section -->    
    <div class="sidebar-section">
        <a href="#" class="sidebar-item text-danger" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt"></i>
            <span>Keluar</span>
        </a>
    </div>
</div>

<style>
    /* CSS hanya untuk struktur dan layout */
    .sidebar {
        width: 250px;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .sidebar-section {
        margin-bottom: 20px;
        padding: 0 15px;
    }

    .sidebar-title {
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        text-decoration: none;
        border-radius: 6px;
        margin-bottom: 5px;
        transition: all 0.2s ease;
    }

    .sidebar-item:hover {
        transform: translateX(3px);
    }

    .sidebar-item i:first-child {
        width: 20px;
        margin-right: 12px;
        font-size: 1rem;
    }

    .sidebar-item span {
        flex-grow: 1;
        font-size: 0.95rem;
    }

    /* Dropdown Structure */
    .sidebar-dropdown-wrapper {
        position: relative;
        margin-bottom: 5px;
    }

    .sidebar-dropdown-toggle {
        cursor: pointer;
    }

    .sidebar-dropdown-toggle.active .sidebar-item {
        font-weight: 500;
    }

    .dropdown-icon {
        font-size: 0.8rem;
        transition: transform 0.3s ease;
        margin-left: 8px;
    }

    .sidebar-dropdown-toggle[aria-expanded="true"] .dropdown-icon {
        transform: rotate(180deg);
    }

    .sidebar-dropdown-content {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .dropdown-items {
        margin-left: 20px;
        padding: 5px 0;
        border-left: 2px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-subitem {
        display: flex;
        align-items: center;
        padding: 10px 15px 10px 30px;
        text-decoration: none;
        border-radius: 6px;
        margin: 2px 0;
        transition: all 0.2s ease;
        font-size: 0.9rem;
        position: relative;
        left: -2px;
    }

    .sidebar-subitem:hover {
        padding-left: 33px;
    }

    .sidebar-subitem i {
        width: 18px;
        margin-right: 10px;
        font-size: 0.9rem;
    }

    .sidebar-subitem.active {
        font-weight: 500;
        border-left: 3px solid;
    }

    /* Animasi dropdown */
    .sidebar-dropdown-content.collapsing,
    .sidebar-dropdown-content.show {
        border-radius: 0 0 6px 6px;
    }

    /* Responsive Structure */
    @media (max-width: 768px) {
        .sidebar {
            width: 60px;
        }
        
        .sidebar-item span,
        .sidebar-title {
            display: none;
        }
        
        .dropdown-icon {
            display: none;
        }
        
        .sidebar-item {
            justify-content: center;
            padding: 15px 10px;
        }
        
        .sidebar-item i:first-child {
            margin-right: 0;
        }
        
        .sidebar-dropdown-content {
            position: absolute;
            left: 60px;
            top: 0;
            min-width: 180px;
            z-index: 1000;
            border-radius: 8px;
        }
        
        .dropdown-items {
            margin-left: 0;
            padding: 8px;
            border-left: none;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-subitem {
            padding: 10px 15px;
        }
        
        .sidebar-subitem:hover {
            padding-left: 18px;
        }
    }
</style>

<script>
    // Inisialisasi dropdown
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggles = document.querySelectorAll('.sidebar-dropdown-toggle');
        
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
            });
        });
    });

    function confirmLogout() {
        if (confirm('Apakah Anda yakin ingin keluar?')) {
            // Implementasi logout sesuai sistem Anda
            window.location.href = "{{ route('logout') }}";
        }
    }
</script>