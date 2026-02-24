@extends('layouts.admin')

@section('title', 'MOOC BPS - Edit Biodata Peserta')

@section('styles')
<style>
    /* MAIN CONTENT STYLE */
    body {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
    }
    
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

    /* FORM STYLE */
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

    .form-control:read-only {
        background-color: #f8f9fa;
        cursor: not-allowed;
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

    .current-photo {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .current-photo img {
        max-width: 150px;
        max-height: 150px;
        border-radius: 8px;
        object-fit: cover;
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

    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }

    .debug-section {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content') 
<!-- Welcome Section -->
<div class="welcome-section">
    <h1 class="welcome-title">Edit Biodata Peserta</h1>
    <p class="welcome-subtitle">
        Perbarui data biodata peserta. Pastikan semua data diisi dengan benar.
    </p>
</div>

<!-- Form Section -->
<div class="form-container">
    <div class="form-header">
        <h2 class="form-title">Form Edit Biodata Peserta</h2>
    </div>
    
    <div class="form-content">
        <!-- DEBUG SECTION -->
        <!-- <div class="debug-section">
            <h5>🛠️ Debug Information</h5>
            <p><strong>Biodata Variable:</strong> {{ isset($biodata) ? '✅ SET' : '❌ NOT SET' }}</p>
            <p><strong>Biodata Data:</strong> {{ $biodata ? '✅ EXISTS' : '❌ NULL' }}</p>
            @if($biodata)
                <p><strong>ID Sobat:</strong> {{ $biodata->id_sobat }}</p>
                <p><strong>Nama:</strong> {{ $biodata->nama_lengkap }}</p>
                <p><strong>Kecamatan:</strong> {{ $biodata->kecamatan }}</p>
            @else
                <p><strong>Error:</strong> Data tidak tersedia di view</p>
            @endif
        </div> -->

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

        @if(isset($biodata) && $biodata !== null)
            <form action="{{ route('admin.biodata.update', $biodata->id_sobat) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Data untuk User & Login -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user-lock me-2"></i>Data Login Peserta
                    </h3>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">ID Sobat</label>
                                <input type="text" name="id_sobat" class="form-control" value="{{ old('id_sobat', $biodata->id_sobat) }}" readonly>
                                <div class="form-help">ID Sobat tidak dapat diubah</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Email/Username</label>
                                <input type="email" name="username_sobat" class="form-control" value="{{ old('username_sobat', $biodata->username_sobat) }}" readonly>
                                <div class="form-help">Email/Username tidak dapat diubah</div>
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
                                <input type="text" name="nama_lengkap" class="form-control"
                                    value="{{ old('nama_lengkap', $biodata->nama_lengkap) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Tempat, Tanggal Lahir</label>
                                <input type="text" name="tempat_tanggal_lahir" class="form-control"
                                    value="{{ old('tempat_tanggal_lahir', $biodata->tempat_tanggal_lahir) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">No Telepon *</label>
                                <input type="text" name="no_telepon" class="form-control"
                                    value="{{ old('no_telepon', $biodata->no_telepon) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select">
                                    <option value="">-- Pilih --</option>
                                    <option value="Laki-laki" {{ old('jenis_kelamin', $biodata->jenis_kelamin)=='Laki-laki'?'selected':'' }}>Laki-laki</option>
                                    <option value="Perempuan" {{ old('jenis_kelamin', $biodata->jenis_kelamin)=='Perempuan'?'selected':'' }}>Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Provinsi</label>
                                <input type="text" name="alamat_prov" class="form-control"
                                    value="{{ old('alamat_prov', $biodata->alamat_prov) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Kabupaten/Kota</label>
                                <input type="text" name="alamat_kab" class="form-control"
                                    value="{{ old('alamat_kab', $biodata->alamat_kab) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Kecamatan *</label>
                                <input type="text" name="kecamatan" class="form-control"
                                    value="{{ old('kecamatan', $biodata->kecamatan) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Desa *</label>
                                <input type="text" name="desa" class="form-control"
                                    value="{{ old('desa', $biodata->desa) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat Lengkap *</label>
                        <textarea name="alamat" class="form-textarea" required>{{ old('alamat', $biodata->alamat) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Pendidikan</label>
                                <select name="pendidikan" class="form-control">
                                    @foreach(['Tamat SD/Sederajat','Tamat SMP/Sederajat','Tamat SMA/Sederajat','Tamat D1/D2/D3','Tamat D4/S1','Tamat S2/S3'] as $p)
                                        <option value="{{ $p }}" {{ old('pendidikan', $biodata->pendidikan)==$p?'selected':'' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Pekerjaan</label>
                                <select name="pekerjaan" class="form-select">
                                    @foreach(['Pelajar / Mahasiswa','Wiraswasta','Aparat Desa / Kelurahan','Pegawai / Guru Honorer','Kader PKK / Karang Taruna / Kader Lainnya','Mengurus Rumah Tangga','Lainnya'] as $p)
                                        <option value="{{ $p }}" {{ old('pekerjaan', $biodata->pekerjaan)==$p?'selected':'' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi Pekerjaan Lain</label>
                        <textarea name="deskripsi_pekerjaan_lain" class="form-textarea">{{ old('deskripsi_pekerjaan_lain', $biodata->deskripsi_pekerjaan_lain) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Posisi</label>
                            <input type="text" name="posisi" class="form-control" value="{{ old('posisi', $biodata->posisi) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Posisi Daftar</label>
                            <input type="text" name="posisi_daftar" class="form-control" value="{{ old('posisi_daftar', $biodata->posisi_daftar) }}">
                        </div>
                    </div>
                </div>

                <!-- Foto Profil Section -->
                <div class="form-group form-group-with-image">
                    <label for="foto_profil" class="form-label">Foto Profil</label>

                    <input type="file" class="form-control" id="foto_profil" name="foto_profil" 
                        accept="image/*" onchange="previewImage(this)">

                    <div class="form-text">
                        Klik choose file untuk mengubah foto. Format: JPG, PNG, GIF. Maksimal 2MB
                    </div>

                    <div class="image-preview-container">
                        <div class="image-preview" id="imagePreview">
                            <img 
                                src="{{ $biodata->foto_profil ? asset('storage/' . $biodata->foto_profil) : asset('img/default-avatar.png') }}" 
                                alt="Preview Foto Profil"
                                style="max-height: 150px;"
                            >
                        </div>
                    </div>

                    @error('foto_profil')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Submit Button -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="{{ route('admin.biodata.index') }}" class="btn-kembali">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </a>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i>
                        Perbarui Biodata Peserta
                    </button>
                </div>
            </form>
        @else
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error:</strong> Data biodata tidak ditemukan atau tidak tersedia!
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('biodata.index') }}" class="btn-kembali">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Daftar Biodata
                </a>
            </div>
        @endif
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
        // Kalo cancel upload, balikin ke foto sebelumnya
        preview.style.display = 'block';
        preview.querySelector('img').src = "{{ $biodata->foto_profil ? asset('storage/' . $biodata->foto_profil) : asset('img/default-avatar.png') }}";
    }
}
</script>
@endsection