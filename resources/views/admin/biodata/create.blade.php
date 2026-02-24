@extends('layouts.admin')

@section('title', 'MOOC BPS - Tambah Biodata Peserta')

@section('styles')
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

    /* Form Styles */
    .form-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        overflow: hidden;
    }

    .form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid #e9ecef;
        background: #f8f9fa;
    }

    .form-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e3c72;
        margin: 0;
    }

    .btn-kembali {
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

    .btn-kembali:hover {
        background: #5a6268;
        color: white;
        transform: translateY(-2px);
    }

    .form-content {
        padding: 30px;
    }

    .form-section {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e9ecef;
    }

    .section-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1e3c72;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: 600;
        color: #1e3c72;
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #fff;
    }

    .form-control:focus {
        border-color: #1e3c72;
        box-shadow: 0 0 0 0.2rem rgba(30, 60, 114, 0.25);
        outline: none;
    }

    .form-select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #fff;
        cursor: pointer;
    }

    .form-select:focus {
        border-color: #1e3c72;
        box-shadow: 0 0 0 0.2rem rgba(30, 60, 114, 0.25);
        outline: none;
    }

    .form-textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #fff;
        resize: vertical;
        min-height: 100px;
    }

    .form-textarea:focus {
        border-color: #1e3c72;
        box-shadow: 0 0 0 0.2rem rgba(30, 60, 114, 0.25);
        outline: none;
    }

    .form-file {
        width: 100%;
        padding: 12px 15px;
        border: 2px dashed #e9ecef;
        border-radius: 8px;
        background: #f8f9fa;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .form-file:hover {
        border-color: #1e3c72;
        background: #f0f4f8;
    }

    .form-help {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 5px;
    }

    .form-group-with-image {
        margin-bottom: 30px;
    }

    .form-group-with-image {
        margin-bottom: 30px;
    }

    .image-preview-container {
        max-height: 200px;
        overflow: hidden;
        margin-bottom: 15px;
    }

    .image-preview {
        text-align: left;
        margin-top: 10px;
    }

    .image-preview img {
        border-radius: 8px;
        border: 2px solid #1e3c72;
        max-width: 180px;
    }

    .btn-submit {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(30, 60, 114, 0.3);
    }

    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid transparent;
    }

    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .alert ul {
        margin: 0;
        padding-left: 20px;
    }

    .alert li {
        margin-bottom: 5px;
    }
</style>
@endsection

@section('content')
<!-- Welcome Section -->
<div class="welcome-section">
    <h1 class="welcome-title">Tambah Biodata Peserta</h1>
    <p class="welcome-subtitle">
        Tambahkan data biodata peserta MOOC baru. Pastikan semua data diisi dengan benar.
    </p>
</div>

<!-- Form Section -->
<div class="form-container">
    <div class="form-header">
        <h2 class="form-title">Form Tambah Biodata Peserta</h2>
    </div>
    
    <div class="form-content">
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Terjadi kesalahan:</strong>
                <ul class="mt-2 mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.biodata.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Data untuk User & Login -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-user-lock me-2"></i>Data Login Peserta
                </h3>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">ID Sobat *</label>
                            <input type="text" name="id_sobat" class="form-control" placeholder="Contoh: 630122090056" value="{{ old('id_sobat') }}" required>
                            <div class="form-help">ID Sobat akan menjadi password default untuk login peserta</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Email/Username *</label>
                            <input type="email" name="username_sobat" class="form-control" placeholder="Contoh: peserta@example.com" value="{{ old('username_sobat') }}" required>
                            <div class="form-help">Email akan menjadi username untuk login peserta</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Biodata Sesuai Tabel -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-user me-2"></i>Data Pribadi
                </h3>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap Peserta" value="{{ old('nama_lengkap') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Tempat, Tanggal Lahir (Umur) *</label>
                            <input type="text" name="tempat_tanggal_lahir" class="form-control" placeholder="Contoh: TANAH LAUT, 04 Juli 2002 (24)" value="{{ old('tempat_tanggal_lahir') }}" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">No Telepon/HP *</label>
                            <input type="text" name="no_telepon" class="form-control" placeholder="Contoh: +6281234567890" value="{{ old('no_telepon') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Jenis Kelamin *</label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Provinsi *</label>
                            <input type="text" name="alamat_prov" class="form-control" placeholder="Masukkan Kode Kecamatan" value="{{ old('alamat_prov') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Kabupaten/Kota *</label>
                            <input type="text" name="alamat_kab" class="form-control" placeholder="Masukkan Kode Kabupaten/Kota" value="{{ old('alamat_kab') }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Kecamatan *</label>
                            <input type="text" name="kecamatan" class="form-control" placeholder="Masukkan Kode Kecamatan" value="{{ old('kecamatan') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Desa/Kelurahan *</label>
                            <input type="text" name="desa" class="form-control" placeholder="Masukkan Kode Desa/Kelurahan" value="{{ old('desa') }}" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Lengkap *</label>
                    <textarea name="alamat" class="form-textarea" placeholder="Alamat lengkap tempat tinggal" required>{{ old('alamat') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Pendidikan Terakhir *</label>
                            <select name="pendidikan" class="form-control" required>
                                <option value="">-- Pilih Pendidikan --</option>
                                <option value="Tamat SD/Sederajat" {{ old('pendidikan')=='Tamat SD/Sederajat' ? 'selected' : '' }}>
                                    Tamat SD / Sederajat
                                </option>
                                <option value="Tamat SMP/Sederajat" {{ old('pendidikan')=='Tamat SMP/Sederajat' ? 'selected' : '' }}>
                                    Tamat SMP / Sederajat
                                </option>
                                <option value="Tamat SMA/Sederajat" {{ old('pendidikan')=='Tamat SMA/Sederajat' ? 'selected' : '' }}>
                                    Tamat SMA / Sederajat
                                </option>
                                <option value="Tamat D1/D2/D3" {{ old('pendidikan')=='Tamat D1/D2/D3' ? 'selected' : '' }}>
                                    Tamat D1 / D2 / D3
                                </option>
                                <option value="Tamat D4/S1" {{ old('pendidikan')=='Tamat D4/S1' ? 'selected' : '' }}>
                                    Tamat D4 / S1
                                </option>
                                <option value="Tamat S2/S3" {{ old('pendidikan')=='Tamat S2/S3' ? 'selected' : '' }}>
                                    Tamat S2 / S3
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Pekerjaan *</label>
                            <select name="pekerjaan" class="form-select" required>
                                <option value="">-- Pilih Pekerjaan --</option>
                                <option value="Pelajar / Mahasiswa" {{ old('pekerjaan') == 'Pelajar / Mahasiswa' ? 'selected' : '' }}>Pelajar / Mahasiswa</option>
                                <option value="Wiraswasta" {{ old('pekerjaan') == 'Wiraswasta' ? 'selected' : '' }}>Wiraswasta</option>
                                <option value="Aparat Desa / Kelurahan" {{ old('pekerjaan') == 'Aparat Desa / Kelurahan' ? 'selected' : '' }}>Aparat Desa / Kelurahan</option>
                                <option value="Pegawai / Guru Honorer" {{ old('pekerjaan') == 'Pegawai / Guru Honorer' ? 'selected' : '' }}>Pegawai / Guru Honorer</option>
                                <option value="Kader PKK / Karang Taruna / Kader Lainnya" {{ old('pekerjaan') == 'Kader PKK / Karang Taruna / Kader Lainnya' ? 'selected' : '' }}>
                                    Kader PKK / Karang Taruna / Kader Lainnya
                                </option>
                                <option value="Mengurus Rumah Tangga" {{ old('pekerjaan') == 'Mengurus Rumah Tangga' ? 'selected' : '' }}>Mengurus Rumah Tangga</option>
                                <option value="Lainnya" {{ old('pekerjaan') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Pekerjaan Lainnya</label>
                    <textarea name="deskripsi_pekerjaan_lain" class="form-textarea">{{ old('deskripsi_pekerjaan_lain') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Posisi *</label>
                            <input type="text" name="posisi" class="form-control" value="{{ old('posisi') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Posisi Daftar *</label>
                            <input type="text" name="posisi_daftar" class="form-control" value="{{ old('posisi_daftar') }}" required>
                        </div>
                    </div>
                </div>


                <!-- Foto Profil Section -->
                <div class="form-group form-group-with-image">
                    <label for="foto_profil" class="form-label">Foto Profil</label>
                    <input type="file" class="form-control" id="foto_profil" name="foto_profil" 
                        accept="image/*" onchange="previewImage(this)">
                    <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB</div>
                    <div class="image-preview-container">
                        <div class="image-preview" id="imagePreview" style="display: none;">
                            <img src="" alt="Preview Foto Profil" style="max-height: 150px;">
                        </div>
                    </div>
                    @error('foto_profil')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ route('admin.biodata.index') }}" class="btn-kembali">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Simpan Biodata Peserta
                </button>
            </div>
        </form>
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
<script>
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

    // Image Preview Function
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const file = input.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.style.display = 'block';
                preview.querySelector('img').src = e.target.result;
            }
            
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            preview.querySelector('img').src = '';
        }
    }

    // Form validation enhancement
    document.querySelector('form').addEventListener('submit', function(e) {
        const requiredFields = this.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.style.borderColor = '#e74c3c';
            } else {
                field.style.borderColor = '#e9ecef';
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Harap lengkapi semua field yang wajib diisi!');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('foto_profil');
        const fotoPreview = document.getElementById('foto-preview');
        
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validasi ukuran file (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal 2MB');
                    this.value = '';
                    return;
                }
                
                // Validasi tipe file
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file harus JPG, PNG, atau GIF');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    fotoPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endsection
