<nav class="main-nav">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.dashboard') }}" class="nav-brand">
                <img src="{{ asset('img/Logo_E-Learning.png') }}" alt="MOOC BPS Logo" class="logo-image">
            </a>
        </div>
        
        <div class="d-flex align-items-center">
            
            
            
            
            <!-- User Profile dengan Dropdown -->
            <div class="user-profile" id="userProfileDropdown">
                <div class="user-avatar">
                    @auth
                        @php
                           $user = auth()->user();
                            // Ambil nama dari biodata jika ada, atau dari user nama
                            $displayNama = $user->biodata->nama_lengkap ?? $user->nama;
                            
                            // Generate inisial dari nama
                            $namaParts = explode(' ', $displayNama);
                            $initials = '';
                            
                            if (count($namaParts) >= 2) {
                                // Ambil huruf pertama dari kata pertama dan kedua
                                $initials = strtoupper(substr($namaParts[0], 0, 1) . substr($namaParts[1], 0, 1));
                            } else {
                                // Jika hanya satu kata, ambil 2 huruf pertama
                                $initials = strtoupper(substr($displayNama, 0, 2));
                            }
                        @endphp
                        
                        <!-- Tampilkan inisial saja -->
                        <div class="avatar-initials">{{ $initials }}</div>
                    @endauth
                </div>
                <div class="user-info">
                    <div class="user-nama">
                       {{ $user->biodata->nama_lengkap ?? $user->nama ?? $user->name }}
                    </div>
                    <div class="user-status">
                        <span class="status-dot"></span>
                        {{ ucfirst($user->role) }}
                    </div>
                </div>
                <i class="fas fa-chevron-down ms-2" style="font-size: 0.8rem; color: #6c757d;"></i>
            </div>

            <!-- Dropdown Menu -->
            <div class="user-dropdown" id="userDropdownMenu">
                <div class="dropdown-header">
                    <div class="dropdown-user-info">
                        <div class="dropdown-avatar">
                            @auth
                                @php
                                    $user = auth()->user();
                                    $displayNama = $user->biodata->nama_lengkap ?? $user->nama;
                                    
                                    // Generate inisial yang sama untuk dropdown
                                    $namaParts = explode(' ', $displayNama);
                                    $initials = '';
                                    
                                    if (count($namaParts) >= 2) {
                                        $initials = strtoupper(substr($namaParts[0], 0, 1) . substr($namaParts[1], 0, 1));
                                    } else {
                                        $initials = strtoupper(substr($displayNama, 0, 2));
                                    }
                                @endphp
                                
                                <!-- Tampilkan inisial saja di dropdown -->
                                <div class="dropdown-avatar-initials">{{ $initials }}</div>
                            @endauth
                        </div>
                        <div class="dropdown-user-details">
                            <div class="dropdown-user-nama">{{ $displayNama }}</div>
<div class="dropdown-user-email">{{ $user->email }}</div>
    <div class="dropdown-user-role">{{ ucfirst($user->role) }}</div>

                        </div>
                    </div>
                </div>
                
                <div class="dropdown-divider"></div>
                
                <div class="dropdown-divider"></div>
                
                <a href="#" class="dropdown-item text-danger" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Backdrop untuk mobile -->
<div class="dropdown-backdrop" id="dropdownBackdrop"></div>

<style>
    /* Dropdown Menu Styles */
    .user-profile {
        position: relative;
        cursor: pointer;
    }

    .user-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 10px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        border: 1px solid #e9ecef;
        min-width: 280px;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
    }

    .user-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .dropdown-header {
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px 12px 0 0;
    }

    .dropdown-user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .dropdown-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .dropdown-avatar-image {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .dropdown-avatar-initials {
        color: white;
        font-weight: 700;
        font-size: 16px;
        text-transform: uppercase;
    }

    .dropdown-user-details {
        flex: 1;
        min-width: 0;
    }

    .dropdown-user-nama {
        font-weight: 700;
        color: #1e3c72;
        font-size: 1rem;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dropdown-user-email {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dropdown-user-role {
        font-size: 0.75rem;
        color: #28a745;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .dropdown-divider {
        height: 1px;
        background: #e9ecef;
        margin: 8px 0;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: #5a6c7d;
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }

    .dropdown-item:hover {
        background: rgba(30, 60, 114, 0.05);
        color: #1e3c72;
        border-left-color: #1e3c72;
    }

    .dropdown-item i {
        width: 20px;
        margin-right: 12px;
        font-size: 1rem;
    }

    .dropdown-item.text-danger {
        color: #dc3545;
    }

    .dropdown-item.text-danger:hover {
        background: rgba(220, 53, 69, 0.05);
        color: #dc3545;
        border-left-color: #dc3545;
    }

    /* Backdrop untuk mobile */
    .dropdown-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.1);
        z-index: 999;
        display: none;
    }

    /* Responsif */
    @media (max-width: 768px) {
        .user-dropdown {
            position: fixed;
            top: auto;
            bottom: 0;
            left: 0;
            right: 0;
            border-radius: 12px 12px 0 0;
            margin-top: 0;
            min-width: auto;
        }
        
        .dropdown-backdrop {
            display: block;
        }
    }
</style>

<script>
    // Dropdown Menu Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const userProfile = document.getElementById('userProfileDropdown');
        const dropdownMenu = document.getElementById('userDropdownMenu');
        const dropdownBackdrop = document.getElementById('dropdownBackdrop');

        // Toggle dropdown
        function toggleDropdown() {
            const isShowing = dropdownMenu.classList.contains('show');
            
            if (isShowing) {
                hideDropdown();
            } else {
                showDropdown();
            }
        }

        function showDropdown() {
            dropdownMenu.classList.add('show');
            dropdownBackdrop.style.display = 'block';
            document.addEventListener('click', handleClickOutside);
        }

        function hideDropdown() {
            dropdownMenu.classList.remove('show');
            dropdownBackdrop.style.display = 'none';
            document.removeEventListener('click', handleClickOutside);
        }

        function handleClickOutside(event) {
            if (!userProfile.contains(event.target) && !dropdownMenu.contains(event.target)) {
                hideDropdown();
            }
        }

        // Event listeners
        userProfile.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown();
        });

        dropdownBackdrop.addEventListener('click', hideDropdown);

        // Close dropdown when clicking on dropdown items (except logout)
        dropdownMenu.addEventListener('click', function(e) {
            if (e.target.closest('.dropdown-item') && !e.target.closest('.dropdown-item.text-danger')) {
                hideDropdown();
            }
        });

        // Close dropdown on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideDropdown();
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                dropdownBackdrop.style.display = 'none';
            } else if (dropdownMenu.classList.contains('show')) {
                dropdownBackdrop.style.display = 'block';
            }
        });
    });

    // Logout Confirmation
    function confirmLogout() {
        Swal.fire({
            title: 'Konfirmasi Logout',
            text: 'Apakah Anda yakin ingin keluar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1e3c72',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Keluar',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit();
            }
        });
    }
</script>

<!-- Hidden Logout Form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>