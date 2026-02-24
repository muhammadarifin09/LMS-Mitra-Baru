<?php

use App\Models\Enrollment;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\KursusController;
use App\Http\Controllers\Admin\BiodataController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Mitra\BerandaController;
use App\Http\Controllers\Mitra\KursusController as MitraKursusController;
use App\Http\Controllers\Mitra\DashboardController as MitraDashboardController;
use App\Http\Controllers\Mitra\CertificateController;
use App\Http\Controllers\Admin\LaporanController;

// Login
Route::get('/', fn() => view('login'))->name('login.page');
Route::post('/', [AuthController::class, 'login'])->name('login');

// Forgot Password Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/dashboard/refresh', [DashboardController::class, 'refresh'])->name('admin.dashboard.refresh');
    
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        
        // Biodata Routes
        Route::get('/biodata', [BiodataController::class, 'index'])->name('biodata.index');
        Route::get('/biodata/create', [BiodataController::class, 'create'])->name('biodata.create');
        Route::post('/biodata', [BiodataController::class, 'store'])->name('biodata.store');
        Route::get('/biodata/{id_sobat}/edit', [BiodataController::class, 'edit'])->name('biodata.edit');
        Route::put('/biodata/{id_sobat}', [BiodataController::class, 'update'])->name('biodata.update');
        Route::delete('/biodata/{id_sobat}', [BiodataController::class, 'destroy'])->name('biodata.destroy');

        // Kursus Routes
        Route::get('kursus', [KursusController::class, 'index'])->name('kursus.index');
        Route::get('kursus/create', [KursusController::class, 'create'])->name('kursus.create');
        Route::post('kursus', [KursusController::class, 'store'])->name('kursus.store');
        Route::get('kursus/{kursus}', [KursusController::class, 'show'])->name('kursus.show');
        Route::get('kursus/{kursus}/edit', [KursusController::class, 'edit'])->name('kursus.edit');
        Route::put('kursus/{kursus}', [KursusController::class, 'update'])->name('kursus.update');
        Route::delete('kursus/{kursus}', [KursusController::class, 'destroy'])->name('kursus.destroy');
        Route::post('kursus/{kursus}/status', [KursusController::class, 'updateStatus'])->name('kursus.updateStatus');
        
        // Material Routes
        Route::prefix('kursus/{kursus}')->name('kursus.materials.')->group(function () {
            // CRUD Materials
            Route::get('materials', [MaterialController::class, 'index'])->name('index');
            Route::get('materials/create', [MaterialController::class, 'create'])->name('create');
            Route::post('materials', [MaterialController::class, 'store'])->name('store');
            Route::get('materials/{material}/edit', [MaterialController::class, 'edit'])->name('edit');
            Route::put('materials/{material}', [MaterialController::class, 'update'])->name('update');
            Route::delete('materials/{material}', [MaterialController::class, 'destroy'])->name('destroy');
            
            // Status & Ordering
            Route::post('materials/{material}/status', [MaterialController::class, 'updateStatus'])->name('status');
            
            // NEW: Drag and Drop Routes
            Route::post('materials/update-order', [MaterialController::class, 'updateOrder'])->name('update-order');
            Route::post('materials/bulk-destroy', [MaterialController::class, 'bulkDestroy'])->name('bulk-destroy'); // Nama route akan menjadi: admin.kursus.materials.bulk-destroy
            Route::get('materials/{material}/progress-stats', [MaterialController::class, 'getProgressStats'])->name('progress-stats');
            
            // Video Related Routes
            Route::get('materials/{material}/video-questions', [MaterialController::class, 'videoQuestions'])->name('video-questions');
            Route::get('materials/{material}/video-preview', [MaterialController::class, 'videoPreview'])->name('video-preview');
            Route::get('materials/{material}/video-stats', [MaterialController::class, 'videoStats'])->name('video-stats');
            
            // Update Video Questions
            Route::post('materials/{material}/video-questions/update', [MaterialController::class, 'updateVideoQuestions'])->name('video-questions.update');
            
            // Update Player Config
            Route::post('materials/{material}/player-config/update', [MaterialController::class, 'updatePlayerConfig'])->name('player-config.update');
            
            // Download & Import
            Route::get('materials/{material}/download', [MaterialController::class, 'downloadMaterialFile'])->name('download');
            Route::post('materials/import-soal', [MaterialController::class, 'importSoal'])->name('import-soal');
            
            // Video View
            Route::get('materials/{material}/video', [MaterialController::class, 'viewMaterialVideo'])->name('video.view');
            
            // Video Direct Link
            Route::get('video/{material}/direct-link', [MaterialController::class, 'getDirectVideoLink'])->name('video.direct-link');
        });
        
        // Template soal route
        Route::get('template-soal', [MaterialController::class, 'downloadTemplate'])->name('kursus.materials.download-template');

        // Notification Routes
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    });
});

// Routes untuk Mitra - DIPERBAIKI DENGAN SEMUA ROUTE VIDEO
Route::middleware(['auth', 'role:mitra'])->group(function () {
    // Route tanpa prefix (untuk kompatibilitas)
    Route::get('/beranda', [BerandaController::class, 'index'])->name('mitra.beranda');
    Route::get('/dashboard', [MitraDashboardController::class, 'index'])->name('mitra.dashboard');
    
    // Route dengan prefix mitra
    Route::prefix('mitra')->name('mitra.')->group(function () {
        // Kursus Routes
        Route::get('/kursus', [MitraKursusController::class, 'index'])->name('kursus.index');
        Route::get('/kursus/{kursus}', [MitraKursusController::class, 'show'])->name('kursus.show');
        Route::post('/kursus/{kursus}/enroll', [MitraKursusController::class, 'enroll'])->name('kursus.enroll');
        Route::get('/kursus-saya', [MitraKursusController::class, 'kursusSaya'])->name('kursus.saya');
        
        // Route untuk myCourses (alternatif)
        Route::get('/my-courses', [MitraKursusController::class, 'myCourses'])->name('kursus.my');
        
        // ============================================
        // VIDEO & PROGRESS ROUTES - FIXED VERSION
        // ============================================
        Route::prefix('kursus/{kursus}')->group(function () {
            // 1. Save video question answer - ROUTE YANG PERNAH HILANG
            Route::post('/material/{material}/save-video-question', [MitraKursusController::class, 'saveVideoQuestionAnswer'])
                ->name('kursus.material.save.video.question');
            
            // 2. Mark video as completed - ROUTE YANG PERNAH HILANG
            Route::post('/materials/{material}/complete-video', [MitraKursusController::class, 'completeVideo'])
                ->name('kursus.material.video.complete');
            
            // 3. Get material status (API)
            Route::get('/materials/{material}/status', [MitraKursusController::class, 'getMaterialStatus'])
                ->name('kursus.material.status');

            Route::post('/materials/{material}/force-complete-video', [MitraKursusController::class, 'forceCompleteVideo'])
                ->name('kursus.material.video.force-complete');
            
            // 4. Get progress
            Route::get('/progress', [MitraKursusController::class, 'getProgress'])
                ->name('kursus.progress');
            
            // 5. Refresh all materials status
            Route::get('/materials/refresh-all-status', [MitraKursusController::class, 'refreshAllMaterialsStatus'])
                ->name('kursus.materials.refresh-all-status');
            
            // 6. Get subtasks
            Route::get('/materials/{material}/subtasks', [MitraKursusController::class, 'getMaterialSubtasks'])
                ->name('kursus.material.subtasks');
            
            // 7. Refresh single material status
            Route::get('/materials/{material}/refresh-status', [MitraKursusController::class, 'refreshMaterialStatus'])
                ->name('kursus.material.refresh-status');
            
            // 8. Complete material (non-video)
            Route::post('/materials/{material}/complete', [MitraKursusController::class, 'completeMaterial'])
                ->name('kursus.material.complete');
            
            // 9. Attendance
            Route::post('/materials/{material}/attendance', [MitraKursusController::class, 'markAttendance'])
                ->name('kursus.material.attendance');
            
            // 10. Download material file
            Route::get('/materials/{material}/download', [MitraKursusController::class, 'downloadMaterialFile'])
                ->name('kursus.material.download');
            
            // 11. Video view
            Route::get('/materials/{material}/video', [MitraKursusController::class, 'viewMaterialVideo'])
                ->name('kursus.material.video');
            
            // 12. Video stream
            Route::get('/materials/{material}/video/stream/{token}', [MitraKursusController::class, 'streamVideo'])
                ->name('kursus.material.video.stream');
            
            // 13. Video progress update
            Route::post('/materials/{material}/video/progress', [MitraKursusController::class, 'updateVideoProgress'])
                ->name('kursus.material.video.progress');
            
            // 14. Mark video as watched
            Route::post('/materials/{material}/video/watched', [MitraKursusController::class, 'recordVideoWatched'])
                ->name('kursus.material.video.watched');
            
            // 15. Get video info
            Route::get('/materials/{material}/video-info', [MitraKursusController::class, 'getVideoInfo'])
                ->name('kursus.material.video.info');

            Route::post('/materials/{material}/check-unlock', [MitraKursusController::class, 'checkUnlock'])
                ->name('kursus.material.check-unlock');
            
            // 16. Test Routes
            Route::get('/test/{material}/{testType}', [MitraKursusController::class, 'showTest'])
                ->name('kursus.test.show');
            Route::post('/test/{material}/{testType}/submit', [MitraKursusController::class, 'submitTest'])
                ->name('kursus.test.submit');
            
            // 17. Recap Routes
            Route::get('/recap/{material}', [MitraKursusController::class, 'showRecap'])
                ->name('kursus.recap.show');
            
            // 18. Show material files (optional)
            Route::get('/materials/{material}/files', [MitraKursusController::class, 'showMaterialFiles'])
                ->name('kursus.material.files');
            
            // 19. Quick fix video (debug)
            Route::get('/materials/{material}/quick-fix-video', [MitraKursusController::class, 'quickFixVideo'])
                ->name('kursus.material.quick.fix.video');
            
            // 20. Update enrollment progress
            Route::post('/refresh-progress', function($kursus) {
                $controller = app()->make(MitraKursusController::class);
                return $controller->updateEnrollmentProgress(auth()->id(), $kursus);
            })->name('kursus.refresh.progress');
        });
        
        // Debug routes (optional)
        Route::get('/debug/material/{material}/video', [MitraKursusController::class, 'debugMaterialVideo'])
            ->name('debug.material.video');
            
        Route::post('/fix-material-videos', [MitraKursusController::class, 'fixMaterialVideos'])
            ->name('fix.material.videos');
    });
});

// Profil Routes (untuk semua user yang terautentikasi)
Route::middleware(['auth'])->prefix('profil')->group(function () {
    Route::get('/', [ProfilController::class, 'index'])->name('profil.index');
    Route::get('/edit', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/update', [ProfilController::class, 'update'])->name('profil.update');
    Route::delete('/hapus-foto', [ProfilController::class, 'hapusFoto'])->name('profil.hapus-foto');
});

// Laporan Routes
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

        Route::prefix('laporan')->name('laporan.')->group(function () {

            // ======================
            // LIST KURSUS
            // ======================
            Route::get('/kursus', [LaporanController::class, 'kursusIndex'])
                ->name('kursus');
            Route::get('/mitra', [LaporanController::class, 'mitraIndex'])
                ->name('mitra');

            // ======================
            // DETAIL KURSUS
            // ======================
            Route::get('/kursus/{kursus}', [LaporanController::class, 'kursusDetail'])
                ->name('kursus.detail');
            Route::get('/mitra/{mitra}', [LaporanController::class, 'mitraDetail'])
                ->name('mitra.detail');

            // ======================
            // EXPORT PDF RINGKAS (dari halaman index)
            // ======================
            Route::get('/kursus/{kursus}/pdf-ringkas', [LaporanController::class, 'exportKursusPdfRingkas'])
                ->name('kursus.pdf.ringkas');
            Route::get('/mitra/{mitra}/pdf-ringkas', [LaporanController::class, 'exportMitraPdfRingkas'])
                ->name('mitra.pdf.ringkas');

            // ======================
            // EXPORT PDF DETAIL (dari halaman detail)
            // ======================
            Route::get('/kursus/{kursus}/pdf-detail', [LaporanController::class, 'exportKursusPdfDetail'])
                ->name('kursus.pdf.detail');
            Route::get('/mitra/{mitra}/pdf-detail', [LaporanController::class, 'exportMitraPdfDetail'])
                ->name('mitra.pdf.detail');

            // ======================
            // EXPORT PDF (LEGACY - bisa dihapus jika tidak diperlukan)
            // ======================
            Route::get('/kursus/{kursus}/pdf', [LaporanController::class, 'exportKursusPdf'])
                ->name('kursus.pdf');
            Route::get('/mitra/{mitra}/pdf', [LaporanController::class, 'exportMitraPdf'])
                ->name('mitra.pdf');

            // ======================
            // TEST PDF (SEMENTARA)
            // ======================
            Route::get('/test-pdf', function () {
                return Pdf::loadHTML('
                    <h2 style="font-family: DejaVu Sans, sans-serif;">
                        ✅ DomPDF LMS BERHASIL
                    </h2>
                    <p>Export PDF sudah aktif di modul laporan.</p>
                    <p><strong>Versi:</strong> Dual PDF (Ringkas & Detail)</p>
                    <ul>
                        <li><strong>/admin/laporan/kursus/{id}/pdf-ringkas</strong> - PDF Ringkas</li>
                        <li><strong>/admin/laporan/kursus/{id}/pdf-detail</strong> - PDF Detail Lengkap</li>
                    </ul>
                ')->stream('test-laporan.pdf');
            })->name('test.pdf');

        });
    });

Route::prefix('admin/laporan')->name('admin.laporan.')->group(function () {

    // =====================
    // CSV (HARUS DI ATAS)
    // =====================
    Route::get('/kursus/export-csv', [LaporanController::class, 'exportKursusCsv'])
        ->name('kursus.csv');
    Route::get('/mitra/export-csv', [LaporanController::class, 'exportMitraCsv'])
        ->name('mitra.csv');

    Route::get('/kursus/{kursus}/export-csv', [LaporanController::class, 'exportKursusDetailCsv'])
        ->name('kursus.detail.csv');
    Route::get('/mitra/{mitra}/export-csv', [LaporanController::class, 'exportMitraDetailCsv'])
        ->name('mitra.detail.csv');

    // =====================
    // VIEW (DI BAWAH)
    // =====================
    Route::get('/kursus', [LaporanController::class, 'kursusIndex'])
        ->name('kursus');
    Route::get('/mitra', [LaporanController::class, 'mitraIndex'])
        ->name('mitra');

    Route::get('/kursus/{kursus}', [LaporanController::class, 'kursusDetail'])
        ->name('kursus.detail');
    Route::get('/mitra/{mitra}', [LaporanController::class, 'mitraDetail'])
        ->name('mitra.detail');
});

Route::get('/test-csv', [LaporanController::class, 'exportKursusCsv'])
    ->name('test.csv');
Route::post(
    '/admin/laporan/kursus/{kursus}/generate',
    [LaporanController::class, 'generateLaporanKursus']
)->name('admin.laporan.kursus.generate');
Route::post(
    '/admin/laporan/mitra/{mitra}/generate',
    [LaporanController::class, 'generateLaporanMitra']
)->name('admin.laporan.mitra.generate');

// Sertifikat Routes
Route::middleware(['auth'])->prefix('sertifikat')->name('sertifikat.')->group(function () {
    Route::get('/', [CertificateController::class, 'index'])->name('index');
    Route::get('/{certificate}/unduh', [CertificateController::class, 'download'])->name('download');
});

Route::middleware(['auth'])->prefix('dashboard/sertifikat')->name('sertifikat.')->group(function () {
    Route::get('/', [CertificateController::class, 'index'])->name('index');
    Route::get('/{certificate}/unduh', [CertificateController::class, 'download'])->name('download');
});

// Test certificate QR
Route::get('/test-certificate-qr', function () {
    $certificate = \App\Models\Certificate::with(['user', 'kursus', 'enrollment'])
        ->whereNotNull('id_kredensial')
        ->first();
    
    if (!$certificate) {
        return 'No certificate with id_kredensial found';
    }
    
    return view('mitra.sertifikat.template', [
        'certificate' => $certificate,
        'user' => $certificate->user,
        'kursus' => $certificate->kursus,
        'enrollment' => $certificate->enrollment,
    ]);
});

// Validasi sertifikat via QR code
Route::get('/sertifikat/{id_kredensial}', [CertificateController::class, 'validateCertificate'])
    ->name('certificates.validate');
    
Route::get('/sertifikat/{id_kredensial}/pdf', 
    [CertificateController::class, 'publicPdf']
)->name('certificates.publicPdf');

// Nilai Routes
Route::middleware(['auth', 'role:mitra'])
    ->prefix('mitra')
    ->name('mitra.')
    ->group(function () {

        Route::get('/nilai', [NilaiController::class, 'index'])
            ->name('nilai');
        Route::post('/nilai/simpan', [NilaiController::class, 'simpan'])
            ->name('nilai.simpan');
    });


// Route::get(
//     '/admin/kursus/{kursus}/pdf-detail',
//     [\App\Http\Controllers\Admin\KursusPdfController::class, 'detail']
// )->name('admin.kursus.pdf.detail');



// Test routes untuk video functionality
if (app()->environment('local')) {
    Route::get('/test-video-routes', function() {
        $routes = [
            'saveVideoQuestionAnswer' => route('mitra.kursus.material.save.video.question', ['kursus' => 1, 'material' => 1]),
            'completeVideo' => route('mitra.kursus.material.video.complete', ['kursus' => 1, 'material' => 1]),
            'getMaterialStatus' => route('mitra.kursus.material.status', ['kursus' => 1, 'material' => 1]),
            'getProgress' => route('mitra.kursus.progress', ['kursus' => 1]),
            'viewMaterialVideo' => route('mitra.kursus.material.video', ['kursus' => 1, 'material' => 1]),
        ];
        
        return response()->json([
            'message' => 'Video routes are properly configured',
            'routes' => $routes
        ]);
    });
}