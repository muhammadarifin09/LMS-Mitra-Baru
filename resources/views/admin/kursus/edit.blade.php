@extends('layouts.admin')

@section('title', 'Edit Kursus - MOOC BPS')

@section('styles')
<style>
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

    .btn-back {
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

    .btn-back:hover {
        background: #5a6268;
        color: white;
    }

    .btn-primary {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(30, 60, 114, 0.3);
    }

    .form-body {
        padding: 30px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        font-weight: 600;
        color: #1e3c72;
        margin-bottom: 8px;
        display: block;
    }

    .form-control, .form-select {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #1e3c72;
        box-shadow: 0 0 0 0.2rem rgba(30, 60, 114, 0.25);
    }

    .form-text {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }

    .required::after {
        content: " *";
        color: #e74c3c;
    }

    .image-preview {
        max-width: 200px;
        max-height: 150px;
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 10px;
        display: block;
        margin-top: 10px;
    }

    .image-preview img {
        max-width: 100%;
        max-height: 100%;
        border-radius: 4px;
    }

    .form-group-with-image {
        margin-bottom: 30px;
    }

    .image-preview-container {
        max-height: 200px;
        overflow: hidden;
        margin-bottom: 15px;
    }

    /* Responsif */
    @media (max-width: 768px) {
        .form-body {
            padding: 20px;
        }

        .form-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .form-header .d-flex {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>
@endsection

@section('content')
<!-- WELCOME SECTION -->
<div class="welcome-section">
    <h1 class="welcome-title">Edit Kursus</h1>
    <p class="welcome-subtitle">
        Perbarui informasi kursus yang sudah ada di platform MOOC BPS.
    </p>
</div>

<!-- FORM SECTION -->
<div class="form-container">
    <div class="form-header">
        <h2 class="form-title">Form Edit Kursus</h2>
        <a href="{{ route('admin.kursus.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>
    
    <div class="form-body">
        <form action="{{ route('admin.kursus.update', $kursus->id) }}" method="POST" enctype="multipart/form-data" id="kursusForm">
            @csrf
            @method('PUT')
            
            <div class="row">
                <!-- Kolom Kiri -->
                <div class="col-md-6">
                    <!-- Judul Kursus -->
                    <div class="form-group">
                        <label for="judul_kursus" class="form-label required">Judul Kursus</label>
                        <input type="text" class="form-control" id="judul_kursus" name="judul_kursus" 
                               value="{{ old('judul_kursus', $kursus->judul_kursus) }}" required 
                               placeholder="Masukkan judul kursus">
                        @error('judul_kursus')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Pelaksana -->
                    <div class="form-group">
                        <label for="pelaksana" class="form-label required">Pelaksana</label>
                        <input type="text" class="form-control" id="pelaksana" name="pelaksana" 
                               value="{{ old('pelaksana', $kursus->pelaksana) }}" required 
                               placeholder="Masukkan nama pelaksana">
                        @error('pelaksana')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Kategori -->
                    <div class="form-group">
                        <label for="kategori" class="form-label required">Kategori</label>
                        <select class="form-select" id="kategori" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="pemula" {{ old('kategori', $kursus->kategori) == 'pemula' ? 'selected' : '' }}>Pemula</option>
                            <option value="menengah" {{ old('kategori', $kursus->kategori) == 'menengah' ? 'selected' : '' }}>Menengah</option>
                            <option value="lanjutan" {{ old('kategori', $kursus->kategori) == 'lanjutan' ? 'selected' : '' }}>Lanjutan</option>
                        </select>
                        @error('kategori')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Durasi -->
                    <div class="form-group">
                        <label for="durasi_jam" class="form-label required">Jam Pelajaran (JP)</label>
                        <input type="number" class="form-control" id="durasi_jam" name="durasi_jam" 
                               value="{{ old('durasi_jam', $kursus->durasi_jam) }}" min="0" required 
                               placeholder="Masukkan durasi dalam jam">
                        <div class="form-text">1 JP adalah 45 menit</div>
                        @error('durasi_jam')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Kuota Peserta -->
                    <div class="form-group">
                        <label for="kuota_peserta" class="form-label">Kuota Peserta</label>
                        <input type="number" class="form-control" id="kuota_peserta" name="kuota_peserta" 
                               value="{{ old('kuota_peserta', $kursus->kuota_peserta) }}" min="1" 
                               placeholder="Kosongkan untuk tidak terbatas">
                        <div class="form-text">Biarkan kosong jika kuota tidak terbatas</div>
                        @error('kuota_peserta')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                        <!-- Kode Enroll Kursus -->
                    <div class="form-group">
                        <label for="enroll_code" class="form-label">
                            Kode Enroll Kursus
                        </label>
                        <input type="text"
                            class="form-control"
                            id="enroll_code"
                            name="enroll_code"
                            value="{{ old('enroll_code', $kursus->enroll_code) }}"
                            placeholder="Contoh: BPS-TALA-2025">
                        <div class="form-text">
                            Isi jika kursus hanya boleh diikuti mitra tertentu.
                            Kosongkan jika kursus terbuka untuk semua.
                        </div>
                        @error('enroll_code')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>


                    <!-- Tanggal Mulai & Selesai -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" 
                                       value="{{ old('tanggal_mulai', $kursus->tanggal_mulai ? $kursus->tanggal_mulai->format('Y-m-d') : '') }}">
                                @error('tanggal_mulai')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" 
                                       value="{{ old('tanggal_selesai', $kursus->tanggal_selesai ? $kursus->tanggal_selesai->format('Y-m-d') : '') }}">
                                @error('tanggal_selesai')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="col-md-6">
                    <!-- Status -->
                    <div class="form-group">
                        <label for="status" class="form-label required">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Pilih Status</option>
                            <option value="draft" {{ old('status', $kursus->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="aktif" {{ old('status', $kursus->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status', $kursus->status) == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Gambar Kursus -->
                    <div class="form-group form-group-with-image">
                        <label for="gambar_kursus" class="form-label">Gambar Kursus</label>
                        <input type="file" class="form-control" id="gambar_kursus" name="gambar_kursus" 
                               accept="image/*" onchange="previewImage(this)">
                        <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB</div>
                        <div class="image-preview-container">
                            <div class="image-preview" id="imagePreview">
                                @if($kursus->gambar_kursus)
                                    <img src="{{ asset('storage/' . $kursus->gambar_kursus) }}" alt="Preview Gambar" style="max-height: 150px;">
                                @else
                                    <img src="" alt="Preview Gambar" style="display: none; max-height: 150px;">
                                    <div class="text-muted text-center">Tidak ada gambar</div>
                                @endif
                            </div>
                        </div>
                        @error('gambar_kursus')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Deskripsi Kursus -->
            <div class="form-group">
                <label for="deskripsi_kursus" class="form-label required">Deskripsi Kursus</label>
                <textarea class="form-control" id="deskripsi_kursus" name="deskripsi_kursus" 
                          rows="4" required placeholder="Masukkan deskripsi lengkap kursus">{{ old('deskripsi_kursus', $kursus->deskripsi_kursus) }}</textarea>
                @error('deskripsi_kursus')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Output Pelatihan -->
            <div class="form-group">
                <label for="output_pelatihan" class="form-label">Output Pelatihan</label>
                <textarea class="form-control" id="output_pelatihan" name="output_pelatihan" 
                          rows="3" placeholder="Masukkan output yang akan didapat peserta (pisahkan dengan enter)">{{ old('output_pelatihan', $kursus->output_pelatihan) }}</textarea>
                <div class="form-text">Pisahkan setiap poin dengan enter (baris baru)</div>
                @error('output_pelatihan')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Persyaratan -->
            <div class="form-group">
                <label for="persyaratan" class="form-label">Persyaratan</label>
                <textarea class="form-control" id="persyaratan" name="persyaratan" 
                          rows="3" placeholder="Masukkan persyaratan untuk mengikuti kursus">{{ old('persyaratan', $kursus->persyaratan) }}</textarea>
                @error('persyaratan')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Fasilitas -->
            <div class="form-group">
                <label for="fasilitas" class="form-label">Fasilitas</label>
                <textarea class="form-control" id="fasilitas" name="fasilitas" 
                          rows="3" placeholder="Masukkan fasilitas yang didapat peserta">{{ old('fasilitas', $kursus->fasilitas) }}</textarea>
                @error('fasilitas')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="form-group text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Perbarui Kursus
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Image Preview Function
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const file = input.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.style.display = 'block';
                const img = preview.querySelector('img');
                img.src = e.target.result;
                img.style.display = 'block';
                const noImageText = preview.querySelector('.text-muted');
                if (noImageText) noImageText.style.display = 'none';
            }
            
            reader.readAsDataURL(file);
        }
    }

    // Form Validation
    document.getElementById('kursusForm').addEventListener('submit', function(e) {
        const judul = document.getElementById('judul_kursus').value.trim();
        const pelaksana = document.getElementById('pelaksana').value.trim();
        const kategori = document.getElementById('kategori').value;
        const status = document.getElementById('status').value;
        
        if (!judul || !pelaksana || !kategori || !status) {
            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Lengkap!',
                text: 'Harap isi semua field yang wajib diisi.',
                icon: 'warning',
                confirmButtonColor: '#1e3c72'
            });
        }
    });

    // Initialize image preview on page load
    document.addEventListener('DOMContentLoaded', function() {
        const imagePreview = document.getElementById('imagePreview');
        const img = imagePreview.querySelector('img');
        if (img && img.src && !img.src.includes('data:')) {
            img.style.display = 'block';
            const noImageText = imagePreview.querySelector('.text-muted');
            if (noImageText) noImageText.style.display = 'none';
        }
    });
</script>
@endsection