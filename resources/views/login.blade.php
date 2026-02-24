<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | LMS MOOC Mitra</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


    <style>
        body {
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        .left-panel {
            background-color: rgb(1, 76, 187);
            color: white;
            box-shadow: 8px 0 25px rgba(0, 0, 0, 0.2);
            z-index: 2;
        }

        @media (max-width: 500px) {
            /* Layout utama */
            body {
                padding: 0;
                margin: 0;
                display: block;
                overflow-x: hidden;
            }
            
            
            .d-flex.flex-column.flex-md-row {
                height: auto !important;
                justify-content: flex-start !important;
            }
            
            .left-panel {
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
                border-radius: 0 0 20px 20px;
            }
        }
        
        /* Tambahan CSS untuk perbaikan */
        .mooc-full-text {
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .description-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Animasi untuk alert */
        .alert-danger {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="min-vh-100 d-flex flex-column flex-md-row align-items-stretch justify-content-center">

    <!-- Kolom Kiri -->
    <div class="col-12 col-md-6 d-flex flex-column justify-content-center align-items-center p-5 left-panel">
        <img src="{{ asset('img/logo-bps.png') }}" alt="Logo BPS Tanah Laut" class="mb-3" style="width: 120px;">
        <h1 class="fw-bold text-center fs-3 fs-md-2 mooc-full-text">Badan Pusat Statistik Tanah Laut</h1>
        <p class="mt-2 text-center opacity-75 fs-6 mooc-full-text">LMS Massive Open Online Course Mitra</p>
        <div class="description-container text-center mt-3">
            <p class="mb-0">
                Platform pembelajaran mandiri bagi Mitra Statistik untuk mempersiapkan diri sebelum pelatihan tatap muka dan penugasan di lapangan dengan sistem belajar adaptif.
            </p>
        </div>
    </div>

    <!-- Kolom Kanan -->
    <div class="col-12 col-md-6 d-flex flex-column justify-content-center bg-white p-4 p-md-5">
        <div class="container" style="max-width: 400px;">
            <h2 class="fw-semibold text-dark mb-4 text-center text-md-start fs-3">Login ke Akun Anda</h2>

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <!-- Alert untuk menampilkan error -->
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->has('login'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $errors->first('login') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" 
                           class="form-control @error('username') is-invalid @enderror" 
                           placeholder="Masukkan username Anda" 
                           value="{{ old('username') }}"
                           required>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>

                    <div class="input-group">
                        <input type="password" name="password" id="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            placeholder="Masukkan password"
                            required>

                        <span class="input-group-text bg-white" style="cursor:pointer;" onclick="togglePassword()">
                            <i id="toggleIcon" class="bi bi-eye"></i>
                        </span>
                    </div>

                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>


                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
                    <a href="{{ route('password.request') }}" class="text-primary small text-decoration-none">Lupa kata sandi?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script untuk auto-hide alert setelah beberapa detik -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alert setelah 5 detik
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
            
            // Menambahkan efek pada form saat error
            const errorAlert = document.querySelector('.alert-danger');
            if (errorAlert) {
                // Fokus ke field username jika ada error
                document.getElementById('username').focus();
                
                // Tambahkan efek shake pada form
                const form = document.querySelector('form');
                form.classList.add('animate__animated', 'animate__shakeX');
                setTimeout(() => {
                    form.classList.remove('animate__animated', 'animate__shakeX');
                }, 1000);
            }
        });
    </script>

    <script>
    function togglePassword() {
        const password = document.getElementById("password");
        const icon = document.getElementById("toggleIcon");

        if (password.type === "password") {
            password.type = "text";
            icon.classList.remove("bi-eye");
            icon.classList.add("bi-eye-slash");
        } else {
            password.type = "password";
            icon.classList.remove("bi-eye-slash");
            icon.classList.add("bi-eye");
        }
    }
    </script>

</body>
</html>