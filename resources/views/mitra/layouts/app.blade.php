<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MOOC BPS - Dashboard Mitra')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
        }

        /* Prevent horizontal scroll */
        html, body {
            overflow-x: hidden;
            max-width: 100%;
        }

        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            min-height: calc(100vh - 100px);
        }

        /* Sidebar Styles */
        .sidebar {
            width: 300px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-section {
            margin-bottom: 30px;
        }

        .sidebar-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e3c72;
            padding: 0 25px 15px 25px;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 15px;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: #5a6c7d;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .sidebar-item:hover {
            background: rgba(30, 60, 114, 0.1);
            color: #1e3c72;
            border-left-color: #1e3c72;
        }

        .sidebar-item.active {
            background: rgba(30, 60, 114, 0.15);
            color: #1e3c72;
            border-left-color: #1e3c72;
            font-weight: 600;
        }

        .sidebar-item i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95);
            margin: 20px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        /* ===== RESPONSIVE FIXES ===== */
        @media (max-width: 992px) {
            .dashboard-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                order: 2;
                margin-top: 20px;
            }
            
            .main-content {
                order: 1;
                margin: 10px;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                min-height: calc(100vh - 80px);
            }
            
            .main-content {
                margin: 5px;
                padding: 15px;
                border-radius: 15px;
            }

            .sidebar {
                padding: 20px 0;
            }

            .sidebar-item {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .sidebar-title {
                padding: 0 15px 10px 15px;
                font-size: 1rem;
            }
        }

        /* Improve text readability on mobile */
        @media (max-width: 768px) {
            body {
                font-size: 14px;
                line-height: 1.5;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                margin: 5px;
                padding: 12px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    @include('mitra.layouts.navbar')
    
    <!-- Dashboard Content -->
    <div class="dashboard-container">

        @if(!isset($noSidebar) || $noSidebar === false)
            <!-- Sidebar -->
            @include('mitra.layouts.sidebar')
        @endif
        
        <!-- Main Content -->
        <main class="main-content" 
            style="{{ (isset($noSidebar) && $noSidebar) ? 'margin:20px auto; width:100%; max-width:1200px;' : '' }}">
            @yield('content')
        </main>

    </div>

    
    <!-- Footer -->
    @include('mitra.layouts.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript umum
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar item active state
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.addEventListener('click', function() {
                    document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Nav item active state
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', function() {
                    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });

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
    
    @stack('scripts')
</body>
</html>