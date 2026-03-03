@extends('layouts.admin')

@section('title', 'Pengaturan KKM Global - MOOC BPS')

@section('styles')
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
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .table-title .emoji {
        font-size: 1.8rem;
    }
    
    .badge-kkm {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .badge-kkm i {
        font-size: 1.1rem;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(30, 60, 114, 0.15);
    }
    
    .stat-card.primary {
        border-left: 4px solid #1e3c72;
    }
    
    .stat-card.success {
        border-left: 4px solid #198754;
    }
    
    .stat-card.danger {
        border-left: 4px solid #dc3545;
    }
    
    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .stat-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .stat-icon.primary {
        background: rgba(30, 60, 114, 0.1);
        color: #1e3c72;
    }
    
    .stat-icon.success {
        background: rgba(25, 135, 84, 0.1);
        color: #198754;
    }
    
    .stat-icon.danger {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1e3c72;
        line-height: 1.2;
        margin-bottom: 5px;
    }
    
    .stat-desc {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .form-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        overflow: hidden;
    }
    
    .form-header {
        padding: 20px 25px;
        border-bottom: 1px solid #e9ecef;
        background: #f8f9fa;
    }
    
    .form-header h5 {
        margin: 0;
        font-weight: 700;
        color: #1e3c72;
    }
    
    .form-body {
        padding: 25px;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }
    
    .input-group-custom {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .input-group-custom:focus-within {
        border-color: #1e3c72;
        box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
    }
    
    .input-group-custom .input-group-text {
        background: #f8f9fa;
        border: none;
        color: #1e3c72;
        font-size: 1.1rem;
    }
    
    .input-group-custom .form-control {
        border: none;
        padding: 12px 15px;
        font-size: 1rem;
    }
    
    .input-group-custom .form-control:focus {
        box-shadow: none;
    }
    
    .info-alert {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .info-alert i {
        color: #1e3c72;
    }
    
    .info-alert h6 {
        color: #1e3c72;
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    .info-alert ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .info-alert li {
        margin-bottom: 10px;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .info-alert li:last-child {
        margin-bottom: 0;
    }
    
    .info-alert li i {
        color: #198754;
        font-size: 0.9rem;
    }
    
    .visual-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        height: 100%;
    }
    
    .visual-title {
        font-weight: 700;
        color: #1e3c72;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .progress-custom {
        height: 40px;
        border-radius: 20px;
        background: #e9ecef;
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .progress-bar-custom {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 600;
        color: white;
    }
    
    .progress-bar-custom.danger {
        background: linear-gradient(135deg, #dc3545, #c82333);
    }
    
    .progress-bar-custom.success {
        background: linear-gradient(135deg, #198754, #157347);
    }
    
    .legend {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 4px;
    }
    
    .legend-color.danger {
        background: linear-gradient(135deg, #dc3545, #c82333);
    }
    
    .legend-color.success {
        background: linear-gradient(135deg, #198754, #157347);
    }
    
    .legend-text {
        font-size: 0.9rem;
        color: #495057;
    }
    
    .stats-mini-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-top: 20px;
    }
    
    .stats-mini-item {
        background: white;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }
    
    .stats-mini-item small {
        display: block;
        color: #6c757d;
        margin-bottom: 5px;
    }
    
    .stats-mini-item span {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1e3c72;
    }
    
    .btn-simpan {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-simpan:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(30, 60, 114, 0.3);
        color: white;
    }
    
    .btn-reset {
        background: #6c757d;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-reset:hover {
        background: #5a6268;
        transform: translateY(-2px);
        color: white;
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .table-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
        
        .badge-kkm {
            align-self: flex-start;
        }
    }
</style>
@endsection

@section('content')
<!-- WELCOME SECTION -->
<div class="welcome-section">
    <h1 class="welcome-title">
        <span class="emoji">⚙️</span>
        Pengaturan Nilai KKM
    </h1>
    <p class="welcome-subtitle">
        Atur nilai minimal kelulusan (KKM) yang akan diterapkan untuk seluruh kursus di sistem MOOC BPS.
    </p>
</div>

<!-- STATS CARDS -->
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-header">
            <span class="stat-title">KKM Global</span>
            <div class="stat-icon primary">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-value">{{ $kkm }}</div>
        <div class="stat-desc">Nilai minimal kelulusan</div>
    </div>
    
    <div class="stat-card success">
        <div class="stat-header">
            <span class="stat-title">Rentang Lulus</span>
            <div class="stat-icon success">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>
        <div class="stat-value">{{ $kkm }} - 100</div>
        <div class="stat-desc">Nilai di atas atau sama dengan KKM</div>
    </div>
    
    <div class="stat-card danger">
        <div class="stat-header">
            <span class="stat-title">Rentang Tidak Lulus</span>
            <div class="stat-icon danger">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
        <div class="stat-value">0 - {{ $kkm - 1 }}</div>
        <div class="stat-desc">Nilai di bawah KKM</div>
    </div>
</div>

<!-- ALERT SUCCESS -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- MAIN FORM CARD -->
<div class="form-container">
    <div class="form-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-sliders-h me-2 text-primary"></i>
                Form Pengaturan KKM
            </h5>
            <span class="badge-kkm">
                <i class="fas fa-info-circle"></i>
                KKM Saat Ini: {{ $kkm }}
            </span>
        </div>
    </div>
    
    <div class="form-body">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            
            <div class="row">
                <div class="col-lg-6">
                    <!-- Input KKM -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-pencil-alt me-1 text-primary"></i>
                            Nilai Minimal Lulus (KKM)
                        </label>
                        <div class="input-group-custom">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-percent"></i>
                                </span>
                                <input type="number" 
                                       name="kkm" 
                                       class="form-control @error('kkm') is-invalid @enderror"
                                       value="{{ old('kkm', $kkm) }}" 
                                       min="0" 
                                       max="100" 
                                       step="1"
                                       id="kkmInput"
                                       required>
                            </div>
                        </div>
                        @error('kkm')
                            <div class="invalid-feedback d-block">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-text mt-2">
                            <i class="fas fa-info-circle me-1 text-info"></i>
                            Masukkan nilai antara 0 - 100. KKM akan diterapkan ke semua kursus.
                        </div>
                    </div>
                    
                    <!-- Informasi Tambahan -->
                    <div class="info-alert">
                        <i class="fas fa-lightbulb fa-2x mb-3 d-block"></i>
                        <h6>Pengaruh Perubahan KKM:</h6>
                        <ul>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                Status kelulusan peserta akan menyesuaikan
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                Perhitungan statistik nilai akan diperbarui
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                Laporan nilai akan menggunakan KKM terbaru
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <!-- Visual Preview KKM -->
                    <div class="visual-card">
                        <h6 class="visual-title">
                            <i class="fas fa-chart-line"></i>
                            Visualisasi KKM
                        </h6>
                        
                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small text-muted">0</span>
                                <span class="small text-muted">100</span>
                            </div>
                            <div class="progress-custom">
                                <div class="progress-bar-custom danger" 
                                     id="progressDanger"
                                     role="progressbar" 
                                     style="width: {{ $kkm }}%; height: 100%; float: left;">
                                    Tidak Lulus
                                </div>
                                <div class="progress-bar-custom success" 
                                     id="progressSuccess"
                                     role="progressbar" 
                                     style="width: {{ 100 - $kkm }}%; height: 100%; float: left;">
                                    Lulus
                                </div>
                            </div>
                        </div>
                        
                        <!-- Legend -->
                        <div class="legend">
                            <div class="legend-item">
                                <div class="legend-color danger"></div>
                                <span class="legend-text">Tidak Lulus (&lt; <span id="legendDanger">{{ $kkm }}</span>)</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color success"></div>
                                <span class="legend-text">Lulus (≥ <span id="legendSuccess">{{ $kkm }}</span>)</span>
                            </div>
                        </div>
                        
                        <!-- Statistik Cepat -->
                        <hr class="my-3">
                        
                        <div class="stats-mini-grid">
                            <div class="stats-mini-item">
                                <small>Nilai Minimum</small>
                                <span>0</span>
                            </div>
                            <div class="stats-mini-item">
                                <small>Nilai Maksimum</small>
                                <span>100</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tombol Aksi -->
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="reset" class="btn-reset">
                    <i class="fas fa-undo-alt"></i>
                    Reset
                </button>
                <button type="submit" class="btn-simpan">
                    <i class="fas fa-save"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const kkmInput = document.getElementById('kkmInput');
    const progressDanger = document.getElementById('progressDanger');
    const progressSuccess = document.getElementById('progressSuccess');
    const legendDanger = document.getElementById('legendDanger');
    const legendSuccess = document.getElementById('legendSuccess');
    const badgeKkm = document.querySelector('.badge-kkm');
    
    // Function to format text with current KKM value
    function formatLabels(value) {
        // Update progress bars
        progressDanger.style.width = value + '%';
        progressSuccess.style.width = (100 - value) + '%';
        
        // Update progress bar text (hide if too narrow)
        progressDanger.textContent = value >= 20 ? 'Tidak Lulus' : '';
        progressSuccess.textContent = (100 - value) >= 20 ? 'Lulus' : '';
        
        // Update legend
        legendDanger.textContent = value;
        legendSuccess.textContent = value;
        
        // Update stat cards
        document.querySelector('.stat-card.primary .stat-value').textContent = value;
        document.querySelector('.stat-card.success .stat-value').textContent = value + ' - 100';
        document.querySelector('.stat-card.danger .stat-value').textContent = '0 - ' + (value - 1);
        
        // Update badge
        badgeKkm.innerHTML = `<i class="fas fa-info-circle"></i> KKM Saat Ini: ${value}`;
    }
    
    if (kkmInput) {
        // Real-time preview
        kkmInput.addEventListener('input', function() {
            let value = parseInt(this.value) || 0;
            
            // Batasi nilai
            if (value < 0) value = 0;
            if (value > 100) value = 100;
            
            // Update tampilan
            formatLabels(value);
        });
        
        // Validate on blur
        kkmInput.addEventListener('blur', function() {
            let value = parseInt(this.value) || 0;
            
            if (value < 0) value = 0;
            if (value > 100) value = 100;
            
            if (this.value != value) {
                this.value = value;
                formatLabels(value);
            }
        });
    }
});
</script>
@endsection