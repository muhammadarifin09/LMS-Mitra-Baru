<style>
    /* ===== FIXED NAVBAR STYLES ===== */
    .main-nav {
        background: rgba(255, 255, 255, 0.98);
        padding: 15px 60px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        border-bottom: 3px solid #1e3c72;
        position: sticky;
        top: 0;
        z-index: 1000;
        backdrop-filter: blur(10px);
    }
    
    .nav-brand {
        font-size: 1.8rem;
        font-weight: 800;
        color: #1e3c72;
        text-decoration: none;
        position: relative;
        padding-right: 25px;
    }

    .nav-brand::after {
        content: "";
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 60px;
        width: 1.5px;
        background: linear-gradient(to bottom, rgba(42, 82, 152, 0.7));
        border-radius: 2px;
    }
    
    .nav-brand span {
        color: #2a5298;
    }
    
    /* Logo MOOC BPS sebagai gambar */
    .logo-image {
        height: 50px;
        width: auto;
        transition: transform 0.3s ease;
    }
    
    .logo-image:hover {
        transform: scale(1.05);
    }
    
    .nav-menu {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    /* Style untuk ikon navigasi */
    .nav-icon {
        position: relative;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1e3c72;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        background: rgba(30, 60, 114, 0.1);
    }

    .nav-icon:hover {
        background: rgba(30, 60, 114, 0.2);
        color: #2a5298;
        transform: scale(1.1);
    }

    /* Badge notifikasi */
    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    
    .nav-item {
        padding: 10px 20px;
        border-radius: 20px;
        font-weight: 600;
        color: #1e3c72;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 1rem;
    }
    
    .nav-item:hover, .nav-item.active {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        transform: translateY(-2px);
    }

    /* Mobile Menu Button - Hidden by default */
    .mobile-menu-btn {
        display: none !important;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #1e3c72;
        padding: 5px 10px;
        cursor: pointer;
        z-index: 1001;
    }

    /* User Profile & Avatar Styles - DIPERBAIKI */
    .user-profile {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 12px;
        border-radius: 20px;
        background: rgba(30, 60, 114, 0.05);
        transition: all 0.3s ease;
        margin-left: 15px;
        text-decoration: none;
        border: 1px solid rgba(30, 60, 114, 0.1);
    }

    .user-profile:hover {
        background: rgba(30, 60, 114, 0.1);
        text-decoration: none;
    }

    .user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        flex-shrink: 0;
        border: 2px solid white;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .avatar-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .avatar-initials {
        color: white;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
    }

    .user-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .user-name {
        font-weight: 600;
        color: #1e3c72;
        font-size: 0.85rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 120px;
        line-height: 1.2;
    }

    .user-status {
        font-size: 0.7rem;
        color: #5a6c7d;
        display: flex;
        align-items: center;
        gap: 4px;
        line-height: 1.2;
    }

    .status-dot {
        width: 6px;
        height: 6px;
        background: #28a745;
        border-radius: 50%;
        display: inline-block;
    }

    /* CSS untuk Fallback Image */
    .avatar-image[src=""],
    .avatar-image:not([src]) {
        opacity: 0;
    }

    .avatar-image:not([src]) + .avatar-initials,
    .avatar-image[src=""] + .avatar-initials {
        display: flex !important;
    }

    /* ===== RESPONSIVE FIXES ===== */
    @media (max-width: 1200px) {
        .main-nav {
            padding: 12px 20px;
        }
        
        .nav-menu {
            gap: 8px;
        }
        
        .nav-item {
            padding: 4px 7px;
            font-size: 0.9rem;
        }
        
        .logo-image {
            height: 45px;
        }
        
        .user-profile {
            margin-left: 10px;
            padding: 5px 10px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
        }
        
        .avatar-initials {
            font-size: 0.75rem;
        }
        
        .user-name {
            font-size: 0.8rem;
            max-width: 100px;
        }
        
        .user-status {
            font-size: 0.65rem;
        }

        .nav-icon {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }
    }

    @media (max-width: 992px) {
        .nav-menu {
            display: none !important;
            position: absolute;
            top: 100%;
            left: -48px;
            right: 0;
            width: 100%; /* Pastikan full width */
            max-width: 100%; /* Pastikan tidak dibatasi */
            background: rgba(255, 255, 255, 0.98);
            flex-direction: column;
            padding: 20px;
            gap: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-top: 1px solid rgba(30, 60, 114, 0.1);
            
            /* TAMBAHKAN INI: */
            align-items: flex-start !important; /* Pastikan item rata kiri */
        }
        
        .nav-menu.show {
            display: flex !important;
        }
        
        .mobile-menu-btn {
            display: block !important;
        }
        
        .nav-item {
            text-align: left;
            padding: 12px 20px;
            border-radius: 10px;
            justify-content: flex-start;
            width: 100%; /* Pastikan item memenuhi lebar */
        }
    }

    @media (max-width: 768px) {
        .nav-menu {
            gap: 5px;
        }
        
        .logo-image {
            height: 45px;
        }
        
        .user-profile {
            margin-left: 10px;
            padding: 5px 10px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
        }
        
        .avatar-initials {
            font-size: 0.75rem;
        }
        
        .user-name {
            font-size: 0.8rem;
            max-width: 100px;
        }
        
        .user-status {
            font-size: 0.65rem;
        }

        .nav-item {
            padding: 8px 15px;
            font-size: 0.9rem;
        }

        .nav-icon {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }
    }

    @media (max-width: 576px) {
        .user-info {
            display: none;
        }
        
        .user-profile {
            padding: 6px;
            background: transparent;
            border: none;
        }
        
        .user-profile:hover {
            background: rgba(30, 60, 114, 0.1);
        }

        .main-nav {
            padding: 10px 15px !important;
        }
        
        .logo-image {
            height: 40px !important;
        }
    }

    /* ===== PERBAIKAN KHUSUS UNTUK 400px KE BAWAH (DEVICE SANGAT KECIL) ===== */
    @media (max-width: 400px) {
        .main-nav {
            padding: 8px 8px !important;
        }
        
        .logo-image {
            height: 28px !important;
            margin-left: 4px !important;
            margin-right: 8px !important;
        }
        
        /* JANGAN SEMBUNYIKAN ICON WORLD - semua icon tetap ada */
        .nav-icon {
            width: 28px !important;
            height: 28px !important;
            font-size: 0.8rem !important;
            margin-right: 4px !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* PERBAIKAN: Lingkaran biru pada icon world dan notif - HAPUS background color */
        .nav-icon {
            background-color: transparent !important; /* HAPUS lingkaran biru */
            width: 12px;
            height: 12px;
        }
        
        /* PERBAIKAN: Icon sendiri yang berwarna biru */
        .nav-icon i {
            font-size: 0.9rem !important;
        }
        
        .notification-badge {
            width: 12px;
            height: 12px;
            font-size: 0.5rem;
            top: 6px;
            right: 6px;
        }
        
        .user-avatar {
            width: 28px !important;
            height: 28px !important;
        }
        
        .avatar-initials {
            font-size: 0.6rem !important;
        }
        
        /* PERBAIKAN: Tambah jarak untuk hamburger menu */
        .mobile-menu-btn {
            font-size: 1.1rem;
            padding: 4px 4px;
            margin-left: 15px !important; /* TAMBAH jarak dari 4px ke 10px */
        }
        
        /* PERBAIKAN: Garis separator - sesuaikan posisi */
        .nav-brand::after {
            height: 28px;
            width: 2px;
            background-color: #d1d5db;
            right: -4px !important; /* PERUBAHAN: dari -8px ke -4px */
            opacity: 0.7;
            position: absolute;
            content: '';
        }
        
        /* PERBAIKAN: Atur posisi container logo */
        .nav-brand {
            position: relative;
            padding-right: 8px !important; /* KURANGI dari 12px ke 8px */
        }
        
        /* Kurangi gap di user profile */
        .user-profile {
            gap: 6px;
            padding: 4px;
        }
    }

    /* Touch-friendly improvements */
    @media (max-width: 768px) {
        .nav-icon, 
        .slider-arrow, 
        .slider-dot,
        .content-card,
        .stat-mini-item {
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        
        .nav-icon,
        .slider-arrow {
            min-width: 44px;
            min-height: 44px;
        }
        
        button, 
        .btn-simple {
            min-height: 44px;
            padding: 12px 20px;
        }
    }

    /* Prevent horizontal scroll */
    html, body {
        overflow-x: hidden;
        max-width: 100%;
    }

    /* Improve text readability on mobile */
    @media (max-width: 768px) {
        body {
            font-size: 14px;
            line-height: 1.5;
        }
        
        .welcome-subtitle,
        .content-description {
            line-height: 1.6;
        }
    }
</style>

<!-- Navigation - Sticky -->
<nav class="main-nav">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <!-- Logo MOOC BPS sebagai gambar -->
            <a href="#" class="nav-brand">
                <img src="{{ asset('img/Logo_E-Learning.png') }}" alt="MOOC BPS Logo" class="logo-image">
            </a>
            <div class="nav-menu ms-5">
                <a href="{{ route('mitra.beranda') }}" 
                class="nav-item {{ request()->routeIs('mitra.beranda') ? 'active' : '' }}">
                    Beranda
                </a>
                <a href="{{ route('mitra.dashboard') }}" 
                class="nav-item {{ request()->routeIs('mitra.dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('mitra.kursus.index') }}" 
                class="nav-item {{ request()->routeIs('mitra.kursus.index') ? 'active' : '' }}">
                    Kursus
                </a>
                <a href="{{ route('mitra.kursus.saya') }}" 
                class="nav-item {{ request()->routeIs('mitra.kursus.saya') ? 'active' : '' }}">
                    Kursus Saya
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <!-- Tambahkan bagian ikon di sini -->
        <div class="d-flex align-items-center">
            <!-- User Profile dengan Foto - VERSI DIPERBAIKI -->
            <a href="{{ route('profil.index') }}" class="user-profile">
                <div class="user-avatar">
                    @auth
                        @php
                            $user = auth()->user();
                            $biodata = $user->biodata ?? null;
                            $initials = strtoupper(substr($user->name, 0, 2));
                        @endphp
                        
                        @if($biodata && $biodata->foto_profil)
                            <img src="{{ asset('storage/' . $biodata->foto_profil) }}" 
                                 alt="Foto Profil" 
                                 class="avatar-image"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="avatar-initials" style="display: none;">{{ $initials }}</div>
                        @else
                            <div class="avatar-initials">{{ $initials }}</div>
                        @endif
                    @endauth
                </div>
                <div class="user-info">
                    <div class="user-name">
                        {{ auth()->user()->biodata->nama_lengkap ?? auth()->user()->name }}
                    </div>
                    <div class="user-status">
                        <span class="status-dot"></span>
                        Online
                    </div>
                </div>
            </a>
        </div>
    </div>
</nav>

<script>
    // Mobile Menu Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const navMenu = document.querySelector('.nav-menu');
        
        if (mobileMenuBtn && navMenu) {
            mobileMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                navMenu.classList.toggle('show');
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!navMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                    navMenu.classList.remove('show');
                }
            });
        }

        // Handle image loading errors
        document.querySelectorAll('.avatar-image').forEach(img => {
            img.addEventListener('error', function() {
                this.style.display = 'none';
                const initials = this.nextElementSibling;
                if (initials && initials.classList.contains('avatar-initials')) {
                    initials.style.display = 'flex';
                }
            });
        });
    });
</script>