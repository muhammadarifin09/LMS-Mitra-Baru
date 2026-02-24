<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialProgress;
use App\Models\Kursus;
use App\Models\Materials;
use App\Models\VideoQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // TAMBAH INI
use Illuminate\Support\Str;
use App\Models\UserVideoProgress;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SoalImport;
use FFMpeg\FFProbe;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class MaterialController extends Controller
{
    // File: app/Http/Controllers/Admin/MaterialController.php

public function index(Kursus $kursus, Request $request)
{
    // Tambahkan parameter request untuk menerima query string
    
    // Tambahkan konfigurasi per page
    $perPage = $request->get('per_page', 10);
    
    // Query materials dengan pagination
    $materialsQuery = $kursus->materials()->orderBy('order');
    
    // Jika ada filter/search, bisa ditambahkan di sini
    if ($request->has('search')) {
        $search = $request->get('search');
        $materialsQuery->where(function($query) use ($search) {
            $query->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
        });
    }
    
    // Filter berdasarkan status
    if ($request->has('status')) {
        $status = $request->get('status');
        if ($status === 'active') {
            $materialsQuery->where('is_active', true);
        } elseif ($status === 'inactive') {
            $materialsQuery->where('is_active', false);
        }
    }
    
    // Filter berdasarkan type
    if ($request->has('type')) {
        $type = $request->get('type');
        if (in_array($type, ['material', 'pre_test', 'post_test'])) {
            $materialsQuery->where('type', $type);
        }
    }
    
    $materials = $materialsQuery->paginate($perPage);
    
    // Load progress data untuk semua materials sekaligus
    $materialIds = $materials->pluck('id')->toArray();
    
    // Ambil semua progress sekaligus untuk performa yang lebih baik
    $allProgress = MaterialProgress::whereIn('material_id', $materialIds)
        ->select('material_id', 'material_status', 'video_status')
        ->get()
        ->groupBy('material_id');
    
    // Ambil semua peserta yang enroll
    $totalPeserta = $kursus->enrollments->count();
    
    $materials->each(function ($material) use ($allProgress, $totalPeserta) {
        // Ambil progress untuk material ini
        $progressData = $allProgress->get($material->id) ?? collect();
        
        // ============================================
        // STATISTIK YANG SESUAI DENGAN DATABASE
        // ============================================
        
        // 1. STATISTIK DOWNLOAD (material_status)
        $jumlahDownload = $progressData->where('material_status', 'completed')->count();
        
        // 2. STATISTIK VIDEO (video_status)
        $jumlahTonton = $progressData->where('video_status', 'completed')->count();
        
        // 3. STATISTIK ATTENDANCE
        $jumlahHadir = 0;
        if ($material->attendance_required) {
            if ($progressData->first() && property_exists($progressData->first(), 'attendance_status')) {
                $jumlahHadir = $progressData->where('attendance_status', 'completed')->count();
            } else {
                $jumlahHadir = max($jumlahDownload, $jumlahTonton);
            }
        }
        
        // 4. STATISTIK TEST (pretest dan posttest)
        $jumlahPretest = $progressData->whereNotNull('pretest_score')->count();
        $jumlahPosttest = $progressData->whereNotNull('posttest_score')->count();
        
        $pretestLulus = $progressData->filter(function($progress) use ($material) {
            return isset($progress->pretest_score) && 
                   $progress->pretest_score >= ($material->passing_grade ?? 70);
        })->count();
        
        $posttestLulus = $progressData->filter(function($progress) use ($material) {
            return isset($progress->posttest_score) && 
                   $progress->posttest_score >= ($material->passing_grade ?? 70);
        })->count();
        
        // 5. Hitung persentase
        $persentaseHadir = $totalPeserta > 0 ? round(($jumlahHadir / $totalPeserta) * 100) : 0;
        $persentaseDownload = $totalPeserta > 0 ? round(($jumlahDownload / $totalPeserta) * 100) : 0;
        $persentaseTonton = $totalPeserta > 0 ? round(($jumlahTonton / $totalPeserta) * 100) : 0;
        $persentasePretest = $totalPeserta > 0 ? round(($jumlahPretest / $totalPeserta) * 100) : 0;
        $persentasePosttest = $totalPeserta > 0 ? round(($jumlahPosttest / $totalPeserta) * 100) : 0;
        
        // 6. Rata-rata nilai
        $rataRataPretest = $progressData->whereNotNull('pretest_score')->avg('pretest_score');
        $rataRataPosttest = $progressData->whereNotNull('posttest_score')->avg('posttest_score');
        
        // ============================================
        // SIMPAN KE OBJECT MATERIAL
        // ============================================
        $material->statistics = [
            'total_peserta' => $totalPeserta,
            'jumlah_hadir' => $jumlahHadir,
            'jumlah_download' => $jumlahDownload,
            'jumlah_tonton' => $jumlahTonton,
            'jumlah_pretest' => $jumlahPretest,
            'jumlah_posttest' => $jumlahPosttest,
            'pretest_lulus' => $pretestLulus,
            'posttest_lulus' => $posttestLulus,
            'persentase_hadir' => $persentaseHadir,
            'persentase_download' => $persentaseDownload,
            'persentase_tonton' => $persentaseTonton,
            'persentase_pretest' => $persentasePretest,
            'persentase_posttest' => $persentasePosttest,
            'rata_rata_pretest' => $rataRataPretest,
            'rata_rata_posttest' => $rataRataPosttest,
        ];
        
        // Decode JSON fields untuk keperluan tampilan
        $this->decodeJsonFields($material);
    });
    
    // Pass request parameters untuk view
    $search = $request->get('search', '');
    $status = $request->get('status', '');
    $type = $request->get('type', '');
    
    return view('admin.kursus.materials.index', compact(
        'kursus', 
        'materials', 
        'search', 
        'status', 
        'type', 
        'perPage'
    ));
}

/**
 * Helper method untuk decode JSON fields
 */
/**
 * Helper method untuk decode JSON fields
 */
private function decodeJsonFields($material)
{
    // Decode soal_pretest jika ada
    if (!empty($material->soal_pretest)) {
        try {
            // Jika sudah array, langsung gunakan
            if (is_array($material->soal_pretest)) {
                $material->soal_pretest_array = $material->soal_pretest;
            } 
            // Jika string, decode JSON
            elseif (is_string($material->soal_pretest)) {
                $decoded = json_decode($material->soal_pretest, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $material->soal_pretest_array = $decoded;
                } else {
                    $material->soal_pretest_array = [];
                }
            } else {
                $material->soal_pretest_array = [];
            }
        } catch (\Exception $e) {
            $material->soal_pretest_array = [];
        }
    } else {
        $material->soal_pretest_array = [];
    }
    
    // Decode soal_posttest jika ada
    if (!empty($material->soal_posttest)) {
        try {
            // Jika sudah array, langsung gunakan
            if (is_array($material->soal_posttest)) {
                $material->soal_posttest_array = $material->soal_posttest;
            } 
            // Jika string, decode JSON
            elseif (is_string($material->soal_posttest)) {
                $decoded = json_decode($material->soal_posttest, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $material->soal_posttest_array = $decoded;
                } else {
                    $material->soal_posttest_array = [];
                }
            } else {
                $material->soal_posttest_array = [];
            }
        } catch (\Exception $e) {
            $material->soal_posttest_array = [];
        }
    } else {
        $material->soal_posttest_array = [];
    }
    
    // Decode file_path jika ada
    if (!empty($material->file_path)) {
        try {
            // Jika sudah array, langsung gunakan
            if (is_array($material->file_path)) {
                $material->file_path_array = $material->file_path;
            } 
            // Jika string, decode JSON
            elseif (is_string($material->file_path)) {
                $decoded = json_decode($material->file_path, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $material->file_path_array = $decoded;
                } else {
                    $material->file_path_array = [];
                }
            } else {
                $material->file_path_array = [];
            }
        } catch (\Exception $e) {
            $material->file_path_array = [];
        }
    } else {
        $material->file_path_array = [];
    }
    
    // Decode video_data jika ada
    if (!empty($material->video_file)) {
        try {
            // Jika sudah array, langsung gunakan
            if (is_array($material->video_file)) {
                $material->video_data = $material->video_file;
            } 
            // Jika string, decode JSON
            elseif (is_string($material->video_file)) {
                $decoded = json_decode($material->video_file, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $material->video_data = $decoded;
                } else {
                    $material->video_data = [];
                }
            } else {
                $material->video_data = [];
            }
        } catch (\Exception $e) {
            $material->video_data = [];
        }
    } else {
        $material->video_data = [];
    }
}

    // TAMBAHKAN METHOD UPDATE ORDER INI
    public function updateOrder(Request $request, Kursus $kursus)
    {
        try {
            $request->validate([
                'materials' => 'required|array',
                'materials.*.id' => 'required|exists:materials,id',
                'materials.*.order' => 'required|integer|min:1',
            ]);

            foreach ($request->materials as $item) {
                Materials::where('id', $item['id'])
                    ->where('course_id', $kursus->id)
                    ->update(['order' => $item['order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Urutan materi berhasil diperbarui!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating material order: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui urutan materi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create(Kursus $kursus)
    {
        // Hitung urutan berikutnya
        $lastOrder = Materials::where('course_id', $kursus->id)->max('order');
        $nextOrder = $lastOrder ? $lastOrder + 1 : 1;
        
        // Hitung total materi
        $totalMaterials = Materials::where('course_id', $kursus->id)->count();

        return view('admin.kursus.materials.create', compact('kursus', 'nextOrder', 'totalMaterials'));
    }

    public function store(Request $request, Kursus $kursus)
{
    // TAMBAH INI DI AWAL: Deteksi apakah request AJAX
    $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest';
    
    Log::info('Store Request Debug', [
        'isAjax' => $isAjax,
        'content_types' => $request->content_types ?? []
    ]);

    try {
        // Validasi dasar
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'content_types' => 'required|array|min:1',
            'content_types.*' => 'in:file,video,pretest,posttest',
            'attendance_required' => 'boolean',
            'is_active' => 'boolean',
            
            // Video specific validations
            'video_type' => 'nullable|required_if:content_types,video|in:youtube,hosted,local',
            'video_url' => 'nullable|required_if:video_type,youtube|url',
            'video_file' => 'nullable|required_if:video_type,hosted,local|file|mimes:mp4,webm,avi,mov,wmv,mkv|max:102400',
            'allow_skip' => 'boolean',
            
            // Player config validations
            'disable_forward_seek' => 'boolean',
            'disable_backward_seek' => 'boolean',
            'disable_right_click' => 'boolean',
            'require_completion' => 'boolean',
            'auto_pause_on_question' => 'boolean',
            'require_question_completion' => 'boolean',
            
            // Video questions
            'video_questions' => 'nullable|array',
            'video_questions.*.time_in_seconds' => 'nullable|integer|min:0',
            'video_questions.*.question' => 'nullable|string|max:500',
            'video_questions.*.options' => 'nullable|array|min:2|max:4',
            'video_questions.*.options.*' => 'nullable|string|max:255',
            'video_questions.*.correct_option' => 'nullable|integer|min:0|max:3',
            'video_questions.*.points' => 'nullable|integer|min:10|max:100',
            'video_questions.*.explanation' => 'nullable|string',
            'video_questions.*.required_to_continue' => 'boolean',
        ]);

        // **LOGIKA VALIDASI KOMBINASI CONTENT TYPES**
        $contentTypes = $request->content_types;
        $errorMessage = $this->validateContentTypeCombination($contentTypes);
        
        if ($errorMessage) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }

        // Conditional validasi untuk file
        if (in_array('file', $contentTypes)) {
            $request->validate([
                'file_path' => 'nullable|array',
                'file_path.*' => 'file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png|max:10240',
            ]);
        }

        // Conditional validasi untuk pretest
        if (in_array('pretest', $contentTypes)) {
            $request->validate([
                'durasi_pretest' => 'required|integer|min:1',
                'pretest_soal' => 'required|array|min:1',
                'pretest_soal.*.pertanyaan' => 'required|string',
                'pretest_soal.*.pilihan' => 'required|array|min:4|max:4',
                'pretest_soal.*.pilihan.*' => 'required|string',
                'pretest_soal.*.jawaban_benar' => 'required|integer|min:0|max:3',
            ]);
        }

        // Conditional validasi untuk posttest
        if (in_array('posttest', $contentTypes)) {
            $request->validate([
                'durasi_posttest' => 'required|integer|min:1',
                'posttest_soal' => 'required|array|min:1',
                'posttest_soal.*.pertanyaan' => 'required|string',
                'posttest_soal.*.pilihan' => 'required|array|min:4|max:4',
                'posttest_soal.*.pilihan.*' => 'required|string',
                'posttest_soal.*.jawaban_benar' => 'required|integer|min:0|max:3',
            ]);
        }

        // Handle multiple file uploads
        $filePaths = [];
        if (in_array('file', $contentTypes) && $request->hasFile('file_path')) {
            foreach ($request->file('file_path') as $file) {
                $filePaths[] = $file->store('materials', 'public');
            }
        }

        // ============================================
        // PERBAIKAN BESAR: VIDEO HANDLING SECTION
        // ============================================
        
        $videoData = null;
        $videoDuration = 0;
        $videoFilePath = null;

        if (in_array('video', $contentTypes)) {
            try {
                $videoType = $request->video_type;
                Log::info('Processing video upload', [
                    'video_type' => $videoType,
                    'has_file' => $request->hasFile('video_file')
                ]);
                
                // YouTube videos
                if ($videoType === 'youtube') {
                    // Validasi URL YouTube
                    if (!$this->isValidYouTubeUrl($request->video_url)) {
                        if ($isAjax) {
                            return response()->json([
                                'success' => false,
                                'message' => 'URL YouTube tidak valid.'
                            ], 422);
                        }
                        
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'URL YouTube tidak valid.');
                    }
                    
                    $videoData = null; // YouTube tidak menyimpan video_data
                    Log::info('YouTube video configured', ['url' => $request->video_url]);
                }
                
                // Google Drive (hosted) videos
                elseif ($videoType === 'hosted' && $request->hasFile('video_file')) {
                    $videoFile = $request->file('video_file');
                    
                    // Validasi file
                    if (!$videoFile->isValid()) {
                        throw new \Exception('File video tidak valid atau corrupt');
                    }
                    
                    Log::info('Uploading to Google Drive', [
                        'original_name' => $videoFile->getClientOriginalName(),
                        'size' => $videoFile->getSize(),
                        'mime_type' => $videoFile->getMimeType()
                    ]);
                    
                    // Upload ke Google Drive
                    $driveInfo = $this->uploadToGoogleDrive($videoFile);
                    
                    if (!$driveInfo || !is_array($driveInfo)) {
                        throw new \Exception('Gagal upload video ke Google Drive. Response tidak valid.');
                    }
                    
                    // Validasi response dari Google Drive
                    if (empty($driveInfo['file_id'])) {
                        Log::error('Google Drive response missing file_id', $driveInfo);
                        throw new \Exception('Response dari Google Drive tidak lengkap.');
                    }
                    
                    // **PERBAIKAN: Buat struktur data yang konsisten dan valid JSON**
                    $videoData = [
                        'type' => 'hosted',
                        'file_id' => $driveInfo['file_id'] ?? null,
                        'file_name' => $driveInfo['file_name'] ?? $videoFile->getClientOriginalName(),
                        'original_name' => $driveInfo['original_name'] ?? $videoFile->getClientOriginalName(),
                        'web_view_link' => $driveInfo['web_view_link'] ?? null,
                        'web_content_link' => $driveInfo['web_content_link'] ?? null,
                        'embed_link' => $driveInfo['embed_link'] ?? 'https://drive.google.com/file/d/' . $driveInfo['file_id'] . '/preview',
                        'thumbnail_link' => $driveInfo['thumbnail_link'] ?? null,
                        'size' => $driveInfo['size'] ?? $videoFile->getSize(),
                        'mime_type' => $driveInfo['mime_type'] ?? $videoFile->getMimeType(),
                        'uploaded_at' => now()->toDateTimeString(),
                        'direct_play' => true,
                    ];
                    
                    // Hapus null values
                    $videoData = array_filter($videoData, function($value) {
                        return $value !== null;
                    });
                    
                    // Validasi JSON sebelum disimpan
                    $jsonTest = json_encode($videoData);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('JSON encode error for video_data', [
                            'error' => json_last_error_msg(),
                            'data' => $videoData
                        ]);
                        throw new \Exception('Gagal membuat data JSON video: ' . json_last_error_msg());
                    }
                    
                    Log::info('Google Drive upload successful', [
                        'file_id' => $driveInfo['file_id'],
                        'embed_link' => $videoData['embed_link']
                    ]);
                }
                
                // Local videos
                elseif ($videoType === 'local' && $request->hasFile('video_file')) {
                    $videoFile = $request->file('video_file');
                    
                    // Validasi file
                    if (!$videoFile->isValid()) {
                        throw new \Exception('File video tidak valid atau corrupt');
                    }
                    
                    Log::info('Uploading local video', [
                        'original_name' => $videoFile->getClientOriginalName(),
                        'size' => $videoFile->getSize(),
                        'mime_type' => $videoFile->getMimeType()
                    ]);
                    
                    // Simpan ke storage lokal
                    $videoFilePath = $videoFile->store('videos', 'public');
                    
                    if (!$videoFilePath) {
                        throw new \Exception('Gagal menyimpan video ke storage lokal');
                    }
                    
                    // Dapatkan durasi video
                    $videoDuration = $this->getVideoDuration($videoFile);
                    
                    // **PERBAIKAN: Buat struktur data yang konsisten dan valid JSON**
                    $videoData = [
                        'type' => 'local',
                        'path' => $videoFilePath,
                        'url' => Storage::url($videoFilePath),
                        'size' => Storage::disk('public')->size($videoFilePath),
                        'duration' => $videoDuration,
                        'original_name' => $videoFile->getClientOriginalName(),
                        'uploaded_at' => now()->toDateTimeString(),
                        'direct_play' => true,
                    ];
                    
                    // Validasi JSON sebelum disimpan
                    $jsonTest = json_encode($videoData);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // Hapus file jika JSON invalid
                        Storage::disk('public')->delete($videoFilePath);
                        Log::error('JSON encode error for local video', [
                            'error' => json_last_error_msg(),
                            'data' => $videoData
                        ]);
                        throw new \Exception('Gagal membuat data JSON video lokal: ' . json_last_error_msg());
                    }
                    
                    Log::info('Local video upload successful', [
                        'path' => $videoFilePath,
                        'url' => $videoData['url'],
                        'duration' => $videoDuration
                    ]);
                }
                
                // Video type tidak valid
                else {
                    throw new \Exception('Tipe video tidak didukung atau file tidak diupload');
                }
                
            } catch (\Exception $e) {
                Log::error('Error processing video upload: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                
                // Hapus file yang sudah diupload jika ada error
                if (isset($videoFilePath) && $videoFilePath) {
                    try {
                        Storage::disk('public')->delete($videoFilePath);
                    } catch (\Exception $deleteError) {
                        Log::error('Error deleting video file: ' . $deleteError->getMessage());
                    }
                }
                
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error processing video: ' . $e->getMessage()
                    ], 500);
                }
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Error processing video: ' . $e->getMessage());
            }
        }

        // Hitung total durasi
        $duration = $videoDuration;

        // Tentukan type berdasarkan content_types
        $type = 'material';
        if (in_array('pretest', $contentTypes)) {
            $type = 'pre_test';
        } elseif (in_array('posttest', $contentTypes)) {
            $type = 'post_test';
        }

        // Tentukan material_type berdasarkan content_types yang dipilih
        $materialType = 'theory';
        if (in_array('file', $contentTypes)) {
            $materialType = 'theory';
        } elseif (in_array('video', $contentTypes)) {
            $materialType = 'video';
        } elseif (in_array('pretest', $contentTypes) || in_array('posttest', $contentTypes)) {
            $materialType = 'quiz';
        }

        // Format soal pretest dengan VALIDASI JSON
        $soalPretest = null;
        if (in_array('pretest', $contentTypes) && !empty($request->pretest_soal)) {
            $soalFormatted = [];
            foreach ($request->pretest_soal as $index => $soal) {
                // Validasi bahwa semua pilihan terisi
                if (count(array_filter($soal['pilihan'])) < 4) {
                    $errorMsg = 'Semua pilihan jawaban untuk setiap soal harus diisi.';
                    
                    if ($isAjax) {
                        return response()->json([
                            'success' => false,
                            'message' => $errorMsg
                        ], 422);
                    }
                    
                    return redirect()->back()
                        ->withInput()
                        ->with('error', $errorMsg);
                }

                $soalFormatted[] = [
                    'id' => $index + 1,
                    'pertanyaan' => $soal['pertanyaan'] ?? '',
                    'pilihan' => $soal['pilihan'] ?? [],
                    'jawaban_benar' => (int)($soal['jawaban_benar'] ?? 0),
                    'poin' => (int)($soal['poin'] ?? 1)
                ];
            }
            
            // Validasi JSON sebelum disimpan
            $jsonTest = json_encode($soalFormatted);
            if (json_last_error() !== JSON_ERROR_NONE) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format soal pretest tidak valid: ' . json_last_error_msg()
                    ], 422);
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Format soal pretest tidak valid: ' . json_last_error_msg());
            }
            
            $soalPretest = $soalFormatted;
        }

        // Format soal posttest dengan VALIDASI JSON
        $soalPosttest = null;
        if (in_array('posttest', $contentTypes) && !empty($request->posttest_soal)) {
            $soalFormatted = [];
            foreach ($request->posttest_soal as $index => $soal) {
                // Validasi bahwa semua pilihan terisi
                if (count(array_filter($soal['pilihan'])) < 4) {
                    $errorMsg = 'Semua pilihan jawaban untuk setiap soal harus diisi.';
                    
                    if ($isAjax) {
                        return response()->json([
                            'success' => false,
                            'message' => $errorMsg
                        ], 422);
                    }
                    
                    return redirect()->back()
                        ->withInput()
                        ->with('error', $errorMsg);
                }

                $soalFormatted[] = [
                    'id' => $index + 1,
                    'pertanyaan' => $soal['pertanyaan'] ?? '',
                    'pilihan' => $soal['pilihan'] ?? [],
                    'jawaban_benar' => (int)($soal['jawaban_benar'] ?? 0),
                ];
            }
            
            // Validasi JSON sebelum disimpan
            $jsonTest = json_encode($soalFormatted);
            if (json_last_error() !== JSON_ERROR_NONE) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format soal posttest tidak valid: ' . json_last_error_msg()
                    ], 422);
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Format soal posttest tidak valid: ' . json_last_error_msg());
            }
            
            $soalPosttest = $soalFormatted;
        }

        // ============================================
        // PERBAIKAN: PLAYER CONFIG DENGAN VALIDASI JSON
        // ============================================
        
        $playerConfig = [
            'allow_skip' => $request->boolean('allow_skip'),
            'disable_forward_seek' => $request->boolean('disable_forward_seek', false), // Ubah dari true jadi false
            'disable_backward_seek' => $request->boolean('disable_backward_seek', false),
            'disable_right_click' => $request->boolean('disable_right_click', false), // Ubah dari true jadi false
            'require_completion' => $request->boolean('require_completion', false), // Ubah dari true jadi false
            'auto_pause_on_question' => $request->boolean('auto_pause_on_question', false), // Ubah dari true jadi false
            'require_question_completion' => $request->boolean('require_question_completion', false),
            'auto_detect_duration' => true,
            'player_type' => 'videojs',
            'videojs_options' => [
                'controls' => true,
                'autoplay' => false,
                'preload' => 'auto',
                'fluid' => true,
                'playbackRates' => [0.5, 1, 1.5, 2],
                'controlBar' => [
                    'volumePanel' => true,
                    'currentTimeDisplay' => true,
                    'timeDivider' => true,
                    'durationDisplay' => true,
                    'progressControl' => true,
                    'remainingTimeDisplay' => true,
                    'playbackRateMenuButton' => true,
                    'fullscreenToggle' => true,
                ],
                'html5' => [
                    'nativeTextTracks' => false
                ]
            ]
        ];

        // Validasi JSON untuk player_config
        $playerConfigJson = json_encode($playerConfig);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid player_config JSON: ' . json_last_error_msg());
            $playerConfig = []; // Fallback ke array kosong
        }

        // Calculate video questions stats
        $hasVideoQuestions = false;
        $questionCount = 0;
        $totalVideoPoints = 0;

        if (in_array('video', $contentTypes) && $request->filled('video_questions')) {
            $hasVideoQuestions = true;
            $questionCount = count(array_filter($request->video_questions, function($q) {
                return !empty($q['question']) && !empty($q['options']);
            }));
            
            foreach ($request->video_questions as $question) {
                if (!empty($question['question']) && !empty($question['options'])) {
                    $totalVideoPoints += $question['points'] ?? 1;
                }
            }
        }

        // ============================================
        // PERBAIKAN: PERSIAPAN DATA UNTUK DISIMPAN
        // ============================================
        
        $materialData = [
            'course_id' => $kursus->id,
            'title' => $request->title,
            'description' => $request->description ?? '',
            'order' => $request->order,
            'type' => $type,
            'material_type' => $materialType,
            'duration' => $duration,
            'video_url' => $request->video_url ?? '',
            'video_type' => $request->video_type ?? 'youtube',
            'video_file' => $videoData, // Biarkan model handle JSON encoding
            'allow_skip' => $request->boolean('allow_skip'),
            'player_config' => $playerConfig, // Biarkan model handle JSON encoding
            'has_video_questions' => $hasVideoQuestions,
            'require_video_completion' => $request->boolean('require_completion', true),
            'question_count' => $questionCount,
            'total_video_points' => $totalVideoPoints,
            'is_active' => $request->boolean('is_active'),
            'attendance_required' => $request->boolean('attendance_required'),
            'learning_objectives' => json_encode($contentTypes),
            'auto_duration' => true,
        ];

        // Handle file_path dengan validasi JSON
        if (!empty($filePaths)) {
            $filePathsJson = json_encode($filePaths);
            if (json_last_error() === JSON_ERROR_NONE) {
                $materialData['file_path'] = $filePathsJson;
            } else {
                Log::error('Invalid JSON for file_paths: ' . json_last_error_msg());
                $materialData['file_path'] = null;
            }
        }

        // Handle soal pretest dengan validasi JSON
        if ($soalPretest) {
            $soalPretestJson = json_encode($soalPretest, JSON_UNESCAPED_UNICODE);
            if (json_last_error() === JSON_ERROR_NONE) {
                $materialData['soal_pretest'] = $soalPretestJson;
                $materialData['durasi_pretest'] = $request->durasi_pretest;
            } else {
                Log::error('Invalid JSON for soal_pretest: ' . json_last_error_msg());
            }
        }

        // Handle soal posttest dengan validasi JSON
        if ($soalPosttest) {
            $soalPosttestJson = json_encode($soalPosttest, JSON_UNESCAPED_UNICODE);
            if (json_last_error() === JSON_ERROR_NONE) {
                $materialData['soal_posttest'] = $soalPosttestJson;
                $materialData['durasi_posttest'] = $request->durasi_posttest;
            } else {
                Log::error('Invalid JSON for soal_posttest: ' . json_last_error_msg());
            }
        }

        // Log data sebelum disimpan untuk debugging
        Log::info('Material data prepared for save', [
            'has_video_data' => !empty($videoData),
            'video_data_type' => gettype($videoData),
            'video_data_sample' => is_array($videoData) ? array_keys($videoData) : 'Not array',
            'has_player_config' => !empty($playerConfig),
            'content_types' => $contentTypes
        ]);

        try {
            // Create material - Model akan handle JSON encoding via mutator
            $material = Materials::create($materialData);
            
            Log::info('Material created successfully', [
                'material_id' => $material->id,
                'video_type' => $material->video_type,
                'video_file_exists' => !empty($material->video_file),
                'video_file_type' => gettype($material->video_file),
                'is_video_available' => $material->isVideoAvailable()
            ]);
            
            // Save video questions jika ada
            if ($hasVideoQuestions && $request->filled('video_questions')) {
                foreach ($request->video_questions as $index => $questionData) {
                    if (empty($questionData['question']) || empty($questionData['options'])) {
                        continue;
                    }
                    
                    // Validasi JSON untuk options
                    $optionsJson = json_encode($questionData['options']);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('Invalid JSON for video question options', [
                            'material_id' => $material->id,
                            'question_index' => $index
                        ]);
                        continue;
                    }
                    
                    VideoQuestion::create([
                        'material_id' => $material->id,
                        'order' => $index + 1,
                        'time_in_seconds' => $questionData['time_in_seconds'] ?? 0,
                        'question' => $questionData['question'],
                        'options' => $optionsJson,
                        'correct_option' => $questionData['correct_option'] ?? 0,
                        'points' => $questionData['points'] ?? 1,
                        'explanation' => $questionData['explanation'] ?? null,
                        'required_to_continue' => $questionData['required_to_continue'] ?? true,
                    ]);
                }
            }
            
            // **FIX: SELALU KEMBALIKAN JSON UNTUK AJAX REQUEST**
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Material berhasil ditambahkan!',
                    'redirect' => route('admin.kursus.materials.index', $kursus),
                    'material_id' => $material->id,
                    'video_status' => [
                        'is_available' => $material->isVideoAvailable(),
                        'type' => $material->video_type,
                        'has_video_data' => !empty($material->video_file)
                    ]
                ]);
            }
            
            // Hanya untuk non-AJAX request
            return redirect()->route('admin.kursus.materials.index', $kursus)
                            ->with('success', 'Material berhasil ditambahkan!');
                            
        } catch (\Exception $e) {
            Log::error('Error creating material record: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            // Hapus file yang sudah diupload jika ada error
            foreach ($filePaths as $filePath) {
                Storage::disk('public')->delete($filePath);
            }
            
            // Hapus video file dari Google Drive jika ada error
            if (isset($driveInfo) && isset($driveInfo['file_id'])) {
                try {
                    $this->deleteFromGoogleDrive($driveInfo['file_id']);
                } catch (\Exception $deleteError) {
                    Log::error('Error deleting video from Google Drive: ' . $deleteError->getMessage());
                }
            }
            
            // Hapus video local jika ada error
            if ($videoFilePath) {
                try {
                    Storage::disk('public')->delete($videoFilePath);
                } catch (\Exception $deleteError) {
                    Log::error('Error deleting local video: ' . $deleteError->getMessage());
                }
            }
            
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan material: ' . $e->getMessage(),
                    'error_details' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Gagal menyimpan material: ' . $e->getMessage());
        }
                        
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation Exception:', $e->errors());
        
        if ($isAjax) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
        
        return redirect()->back()
            ->withErrors($e->validator)
            ->withInput();
            
    } catch (\Exception $e) {
        Log::error('Error storing material: ' . $e->getMessage());
        Log::error('Trace: ' . $e->getTraceAsString());
        
        // Hapus file yang sudah diupload jika ada error
        foreach ($filePaths as $filePath) {
            Storage::disk('public')->delete($filePath);
        }
        
        // Hapus video file dari Google Drive jika ada error
        if (isset($driveInfo) && isset($driveInfo['file_id'])) {
            try {
                $this->deleteFromGoogleDrive($driveInfo['file_id']);
            } catch (\Exception $deleteError) {
                Log::error('Error deleting video from Google Drive: ' . $deleteError->getMessage());
            }
        }
        
        // Hapus video local jika ada error
        if (isset($videoFilePath) && $videoFilePath) {
            try {
                Storage::disk('public')->delete($videoFilePath);
            } catch (\Exception $deleteError) {
                Log::error('Error deleting local video: ' . $deleteError->getMessage());
            }
        }
        
        if ($isAjax) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan material: ' . $e->getMessage(),
                'error_details' => $e->getMessage()
            ], 500);
        }
        
        return redirect()->back()
                        ->withInput()
                        ->with('error', 'Gagal menyimpan material: ' . $e->getMessage());
    }
}

public function edit(Kursus $kursus, Materials $material)
{
    $material->load('videoQuestions');
    
    // Decode content types untuk form edit
    $material->content_types_array = json_decode($material->learning_objectives ?? '[]', true);
    
    // ============================================
    // STANDARDISASI DATA DARI DATABASE - DIPINDAHKAN DARI BLADE
    // ============================================
    
    // 1. Content Types (Jenis Konten)
    $contentTypes = [];
    if ($material->learning_objectives) {
        $contentTypes = json_decode($material->learning_objectives, true);
    }
    
    // Jika kosong, tentukan dari data yang ada di material
    if (empty($contentTypes)) {
        if ($material->file_path) $contentTypes[] = 'file';
        if ($material->video_url || $material->video_file) $contentTypes[] = 'video';
        if ($material->soal_pretest) $contentTypes[] = 'pretest';
        if ($material->soal_posttest) $contentTypes[] = 'posttest';
    }
    $contentTypes = is_array($contentTypes) ? $contentTypes : [];
    
    // 2. File Materi (PDF/PPT/DOC)
    $existingFiles = [];
    $existingFilesWithUrls = [];
    if ($material->file_path) {
        $fileData = is_string($material->file_path) ? json_decode($material->file_path, true) : $material->file_path;
        if (is_array($fileData)) {
            $existingFiles = $fileData;
            foreach ($fileData as $index => $filePath) {
                $existingFilesWithUrls[] = [
                    'path' => $filePath,
                    'name' => basename($filePath),
                    'url' => Storage::url($filePath)
                ];
            }
        } elseif (is_string($material->file_path)) {
            $existingFiles = [$material->file_path];
            $existingFilesWithUrls[] = [
                'path' => $material->file_path,
                'name' => basename($material->file_path),
                'url' => Storage::url($material->file_path)
            ];
        }
    }
    
    // 3. Video Data
    $videoData = [];
    $videoType = $material->video_type ?? 'youtube';
    if ($material->video_file) {
        $videoData = is_string($material->video_file) ? json_decode($material->video_file, true) : $material->video_file;
    }
    
    // 4. Player Configuration
    $playerConfig = [];
    if ($material->player_config) {
        $playerConfig = is_string($material->player_config) ? json_decode($material->player_config, true) : $material->player_config;
    }
    $playerConfig = is_array($playerConfig) ? $playerConfig : [];
    
    // 5. Soal Pretest
    $existingPretest = [];
    if ($material->soal_pretest) {
        $pretestData = is_string($material->soal_pretest) ? json_decode($material->soal_pretest, true) : $material->soal_pretest;
        if (is_array($pretestData)) {
            $existingPretest = $pretestData;
        }
    }
    
    // 6. Soal Posttest
    $existingPosttest = [];
    if ($material->soal_posttest) {
        $posttestData = is_string($material->soal_posttest) ? json_decode($material->soal_posttest, true) : $material->soal_posttest;
        if (is_array($posttestData)) {
            $existingPosttest = $posttestData;
        }
    }
    
    // 7. Video Questions
    $existingVideoQuestions = [];
if (method_exists($material, 'videoQuestions') && $material->videoQuestions()->exists()) {
    $existingVideoQuestions = $material->videoQuestions()
        ->orderBy('time_in_seconds', 'asc')
        ->get()
        ->map(function($question) use ($material) { // <-- TAMBAHKAN use ($material)
            // Format options dari database - PASTIKAN INI DIPERBAIKI
            $options = [];
            
            // OPTION 1: Jika options sudah di-cast sebagai array
            if (is_array($question->options)) {
                $options = $question->options;
            } 
            // OPTION 2: Jika masih string JSON
            elseif (is_string($question->options) && !empty($question->options)) {
                $decoded = json_decode($question->options, true);
                if (is_array($decoded)) {
                    $options = $decoded;
                }
            }
            
            // Jika options masih kosong atau invalid, berikan default
            if (empty($options) || !is_array($options)) {
                $options = ['', '', '', ''];
            }
            
            // Pastikan array memiliki 4 elemen
            while (count($options) < 4) {
                $options[] = '';
            }
            
            // Pastikan tidak ada null values
            $options = array_map(function($option) {
                return $option !== null ? (string)$option : '';
            }, $options);
            
            // Debug log untuk melihat data
            Log::info('Video Question Data for Edit:', [
                'question_id' => $question->id,
                'material_id' => $material->id, // <-- SEKARANG $material TERSEDIA
                'time' => $question->time_in_seconds,
                'question_text' => substr($question->question, 0, 50),
                'options_count' => count($options),
                'options' => $options,
                'correct_option' => $question->correct_option
            ]);
            
            return [
                'id' => $question->id,
                'time_in_seconds' => (int)$question->time_in_seconds,
                'question' => $question->question ?? '',
                'options' => $options,
                'correct_option' => (int)$question->correct_option,
                'points' => (int)$question->points,
                'explanation' => $question->explanation ?? '',
                'required_to_continue' => (bool)$question->required_to_continue
            ];
        })
        ->toArray();
}
    
    // 8. Total materials untuk urutan
    $totalMaterials = $kursus->materials()->count();
    
    return view('admin.kursus.materials.edit', compact(
        'kursus', 
        'material',
        'contentTypes',
        'existingFiles',
        'existingFilesWithUrls',
        'existingVideoQuestions',
        'videoData',
        'videoType',
        'playerConfig',
        'existingPretest',
        'existingPosttest',
        'existingVideoQuestions',
        'totalMaterials'
    ));
}

public function update(Request $request, Kursus $kursus, Materials $material)
{
    // Deteksi apakah request AJAX
    $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest';
    
    Log::info('Update Request Debug', [
        'isAjax' => $isAjax,
        'content_types' => $request->content_types ?? [],
        'has_video' => in_array('video', $request->content_types ?? [])
    ]);

    try {
        // Validasi dasar
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'content_types' => 'required|array|min:1',
            'content_types.*' => 'in:file,video,pretest,posttest',
            'attendance_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // **LOGIKA VALIDASI KOMBINASI CONTENT TYPES**
        $contentTypes = $request->content_types;
        $errorMessage = $this->validateContentTypeCombination($contentTypes);
        
        if ($errorMessage) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }

        // ============================================
        // PERBAIKAN: VALIDASI VIDEO HANYA JIKA VIDEO DIPILIH
        // ============================================
        
        if (in_array('video', $contentTypes)) {
            $request->validate([
                // Video specific validations - HANYA jika video dipilih
                'video_type' => 'required|in:youtube,hosted,local',
                'allow_skip' => 'nullable|boolean',
                
                // Player config validations
                'disable_forward_seek' => 'nullable|boolean',
                'disable_backward_seek' => 'nullable|boolean',
                'disable_right_click' => 'nullable|boolean',
                'require_completion' => 'nullable|boolean',
                'auto_pause_on_question' => 'nullable|boolean',
                'require_question_completion' => 'nullable|boolean',
            ]);
            
            // Conditional validation untuk YouTube
            if ($request->video_type === 'youtube') {
                $request->validate([
                    'video_url' => 'nullable|url'
                ]);
            }
            
            // Conditional validation untuk hosted/local
            if (in_array($request->video_type, ['hosted', 'local'])) {
                $request->validate([
                    'video_file' => 'nullable|file|mimes:mp4,webm,avi,mov,wmv,mkv|max:102400'
                ]);
            }
            
            // Validasi video questions jika ada
            if ($request->has('video_questions') && is_array($request->video_questions)) {
                foreach ($request->video_questions as $index => $question) {
                    if (isset($question['question']) || isset($question['options'])) {
                        $request->validate([
                            "video_questions.{$index}.question" => 'nullable|string|max:500',
                            "video_questions.{$index}.time_in_seconds" => 'nullable|integer|min:0',
                            "video_questions.{$index}.correct_option" => 'nullable|integer|min:0|max:3',
                            "video_questions.{$index}.points" => 'nullable|integer|min:10|max:100',
                            "video_questions.{$index}.required_to_continue" => 'nullable|boolean',
                        ]);
                    }
                }
            }
        }
        
        // ============================================
        // VALIDASI CONDITIONAL FIELDS BERDASARKAN CONTENT TYPES
        // ============================================
        
        $validationErrors = $this->validateConditionalFields($request, $contentTypes, $isAjax);
        if ($validationErrors) {
            if ($isAjax && is_array($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validationErrors
                ], 422);
            }
            return $validationErrors;
        }

        // Handle multiple file uploads
        $existingFiles = $material->file_path ? json_decode($material->file_path, true) : [];
        $newFilePaths = $existingFiles;

        // Handle deleted files
        $deletedFiles = $request->deleted_files ? json_decode($request->deleted_files, true) : [];
        if (!empty($deletedFiles) && is_array($deletedFiles)) {
            foreach ($deletedFiles as $filePath) {
                // Hapus dari storage
                Storage::disk('public')->delete($filePath);
                // Hapus dari array newFilePaths
                $key = array_search($filePath, $newFilePaths);
                if ($key !== false) {
                    unset($newFilePaths[$key]);
                }
            }
            $newFilePaths = array_values($newFilePaths); // Reindex array
        }

        if (in_array('file', $contentTypes) && $request->hasFile('file_path')) {
            foreach ($request->file('file_path') as $file) {
                $newFilePaths[] = $file->store('materials', 'public');
            }
        } elseif (!in_array('file', $contentTypes)) {
            // Jika file tidak dipilih, hapus semua file yang ada
            foreach ($existingFiles as $filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $newFilePaths = [];
        }

        // ============================================
        // HANDLE VIDEO UPLOAD
        // ============================================

        $videoInfo = $material->video_file;
        $videoDuration = $material->duration;

        if (in_array('video', $contentTypes)) {
            $videoType = $request->video_type;
            
            // PERBAIKAN: Jika video_type tidak diisi, gunakan existing
            if (empty($videoType) && $this->hasExistingVideoData($material)) {
                $videoType = $material->video_type;
            }
            
            // Jika video_type = youtube
            if ($videoType === 'youtube') {
                // PERBAIKAN: Gunakan existing URL jika tidak ada input baru
                $videoUrl = $request->video_url ?: $material->video_url;
                
                // Validasi hanya jika ada URL baru atau URL sudah ada
                if ($videoUrl && !$this->isValidYouTubeUrl($videoUrl)) {
                    if ($isAjax) {
                        return response()->json([
                            'success' => false,
                            'message' => 'URL YouTube tidak valid.'
                        ], 422);
                    }
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'URL YouTube tidak valid.');
                }
                
                // Reset video file untuk YouTube
                $videoInfo = null;
                $videoDuration = 0;
                
                Log::info('YouTube video updated/kept', [
                    'material_id' => $material->id,
                    'video_url' => $videoUrl,
                    'has_new_url' => $request->filled('video_url')
                ]);
            }
            
            // Jika video_type = hosted dan ada file baru
            elseif ($videoType === 'hosted' && $request->hasFile('video_file')) {
                try {
                    // Delete old video file jika ada
                    if ($material->video_file) {
                        $oldVideoData = is_string($material->video_file) ? json_decode($material->video_file, true) : $material->video_file;
                        if ($oldVideoData && isset($oldVideoData['type']) && $oldVideoData['type'] === 'hosted') {
                            if (isset($oldVideoData['file_id'])) {
                                $this->deleteFromGoogleDrive($oldVideoData['file_id']);
                                Log::info('Deleted old Google Drive video', [
                                    'file_id' => $oldVideoData['file_id']
                                ]);
                            }
                        } elseif ($oldVideoData && isset($oldVideoData['type']) && $oldVideoData['type'] === 'local') {
                            // Hapus file lokal jika ada
                            if (isset($oldVideoData['path'])) {
                                Storage::disk('public')->delete($oldVideoData['path']);
                            }
                        }
                    }
                    
                    $videoFile = $request->file('video_file');
                    $newVideoInfo = $this->uploadToGoogleDrive($videoFile);
                    
                    if ($newVideoInfo) {
                        $videoInfo = $newVideoInfo; // Simpan sebagai array
                        $videoDuration = 0; // Google Drive video duration perlu dihitung terpisah
                        
                        Log::info('Google Drive video uploaded successfully', [
                            'material_id' => $material->id,
                            'file_id' => $newVideoInfo['file_id'] ?? 'unknown'
                        ]);
                    } else {
                        throw new \Exception('Gagal upload video ke Google Drive');
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Error uploading video to Google Drive: ' . $e->getMessage());
                    
                    if ($isAjax) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal mengupload video ke Google Drive: ' . $e->getMessage()
                        ], 500);
                    }
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Gagal mengupload video ke Google Drive: ' . $e->getMessage());
                }
            }
            
            // Jika video_type = local dan ada file baru
            elseif ($videoType === 'local' && $request->hasFile('video_file')) {
                try {
                    // Delete old video file jika ada
                    if ($material->video_file) {
                        $oldVideoData = is_string($material->video_file) ? json_decode($material->video_file, true) : $material->video_file;
                        if ($oldVideoData && isset($oldVideoData['type']) && $oldVideoData['type'] === 'local') {
                            // Hapus file lokal
                            if (isset($oldVideoData['path'])) {
                                Storage::disk('public')->delete($oldVideoData['path']);
                                Log::info('Deleted old local video', [
                                    'path' => $oldVideoData['path']
                                ]);
                            }
                        }
                    }
                    
                    $videoFile = $request->file('video_file');
                    $videoPath = $videoFile->store('videos', 'public');
                    $videoDuration = $this->getVideoDuration($videoFile);
                    
                    $videoInfo = [
                        'type' => 'local',
                        'path' => $videoPath,
                        'url' => Storage::url($videoPath),
                        'size' => Storage::disk('public')->size($videoPath),
                        'duration' => $videoDuration,
                        'original_name' => $videoFile->getClientOriginalName(),
                        'uploaded_at' => now()->toDateTimeString(),
                        'direct_play' => true,
                    ];
                    
                    Log::info('Local video uploaded successfully', [
                        'material_id' => $material->id,
                        'path' => $videoPath,
                        'duration' => $videoDuration
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error('Error uploading video to local storage: ' . $e->getMessage());
                    
                    if ($isAjax) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal mengupload video: ' . $e->getMessage()
                        ], 500);
                    }
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Gagal mengupload video: ' . $e->getMessage());
                }
            }
            
            // Jika tidak ada file baru, tetap pakai existing data
            elseif ($this->hasExistingVideoData($material)) {
                // Tetap pakai data video yang ada
                $videoDuration = $material->duration;
                
                Log::info('Using existing video data', [
                    'material_id' => $material->id,
                    'video_type' => $material->video_type,
                    'has_video_file' => !empty($material->video_file)
                ]);
            }
            
            // Jika video dipilih tapi tidak ada data sama sekali
            else {
                Log::warning('Video selected but no video data provided or exists', [
                    'material_id' => $material->id,
                    'video_type' => $videoType,
                    'has_file' => $request->hasFile('video_file'),
                    'has_existing' => $this->hasExistingVideoData($material)
                ]);
            }
        }

        // ============================================
        // FORMAT DATA UNTUK DISIMPAN
        // ============================================

        // Hitung total durasi
        $duration = $videoDuration;

        // Tentukan type dan material_type
        list($type, $materialType) = $this->determineMaterialTypes($contentTypes);

        // Format soal pretest
        $soalPretest = null;
        if (in_array('pretest', $contentTypes) && !empty($request->pretest_soal)) {
            $soalFormatted = $this->formatSoal($request->pretest_soal, 'pretest');
            if (is_string($soalFormatted)) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => $soalFormatted
                    ], 422);
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', $soalFormatted);
            }
            $soalPretest = $soalFormatted;
        }

        // Format soal posttest
        $soalPosttest = null;
        if (in_array('posttest', $contentTypes) && !empty($request->posttest_soal)) {
            $soalFormatted = $this->formatSoal($request->posttest_soal, 'posttest');
            if (is_string($soalFormatted)) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => $soalFormatted
                    ], 422);
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', $soalFormatted);
            }
            $soalPosttest = $soalFormatted;
        }

        // Player config
        $playerConfig = [
            'allow_skip' => $request->boolean('allow_skip'),
            'disable_forward_seek' => $request->boolean('disable_forward_seek', true),
            'disable_backward_seek' => $request->boolean('disable_backward_seek', false),
            'disable_right_click' => $request->boolean('disable_right_click', true),
            'require_completion' => $request->boolean('require_completion', true),
            'auto_pause_on_question' => $request->boolean('auto_pause_on_question', true),
            'require_question_completion' => $request->boolean('require_question_completion', false),
            'auto_detect_duration' => true,
            'player_type' => 'videojs',
            'videojs_options' => [
                'controls' => true,
                'autoplay' => false,
                'preload' => 'auto',
                'fluid' => true,
                'playbackRates' => [0.5, 1, 1.5, 2],
                'controlBar' => [
                    'volumePanel' => true,
                    'currentTimeDisplay' => true,
                    'timeDivider' => true,
                    'durationDisplay' => true,
                    'progressControl' => true,
                    'remainingTimeDisplay' => true,
                    'playbackRateMenuButton' => true,
                    'fullscreenToggle' => true,
                ],
                'html5' => [
                    'nativeTextTracks' => false
                ]
            ]
        ];

        // Calculate video questions stats
        list($hasVideoQuestions, $questionCount, $totalVideoPoints) = $this->calculateVideoQuestionStats($request, $contentTypes);

        // Update data
        $materialData = [
            'title' => $request->title,
            'description' => $request->description ?? '',
            'order' => $request->order,
            'type' => $type,
            'material_type' => $materialType,
            'duration' => $duration,
            'file_path' => !empty($newFilePaths) ? json_encode($newFilePaths) : null,
            'allow_skip' => $request->boolean('allow_skip'),
            'has_video_questions' => $hasVideoQuestions,
            'require_video_completion' => $request->boolean('require_completion', true),
            'question_count' => $questionCount,
            'total_video_points' => $totalVideoPoints,
            'is_active' => $request->boolean('is_active'),
            'attendance_required' => $request->boolean('attendance_required'),
            'soal_pretest' => $soalPretest ? json_encode($soalPretest, JSON_UNESCAPED_UNICODE) : null,
            'soal_posttest' => $soalPosttest ? json_encode($soalPosttest, JSON_UNESCAPED_UNICODE) : null,
            'learning_objectives' => json_encode($contentTypes),
            'auto_duration' => true,
        ];

        // PERBAIKAN: Update video data hanya jika video dipilih
        // PERBAIKAN: Update video data hanya jika video dipilih
// PERBAIKAN: Update video data hanya jika video dipilih
if (in_array('video', $contentTypes)) {
    // Jika video dipilih, gunakan data dari request atau existing
    $materialData['video_url'] = $request->video_url ?? $material->video_url;
    $materialData['video_type'] = $request->video_type ?? $material->video_type;
    $materialData['video_file'] = $videoInfo ? (is_array($videoInfo) ? $videoInfo : json_encode($videoInfo)) : null;
    $materialData['player_config'] = $playerConfig;
    
    Log::info('Video data included in update', [
        'material_id' => $material->id,
        'video_type' => $materialData['video_type'],
        'has_video_file' => !empty($materialData['video_file'])
    ]);
} else {
    // Jika video TIDAK dipilih, clear data video TANPA mengubah video_type
    $fieldsToClear = [
        'video_url' => null,
        'video_file' => null,
        'player_config' => null,
        'has_video_questions' => false,
        'question_count' => 0,
        'total_video_points' => 0,
    ];
    
    foreach ($fieldsToClear as $field => $value) {
        $materialData[$field] = $value;
    }
    
    // Hapus video questions
    VideoQuestion::where('material_id', $material->id)->delete();
    
    // Hapus file video jika ada
    if ($material->video_file) {
        try {
            $oldVideoData = is_string($material->video_file) ? json_decode($material->video_file, true) : $material->video_file;
            if ($oldVideoData && isset($oldVideoData['type'])) {
                if ($oldVideoData['type'] === 'hosted' && isset($oldVideoData['file_id'])) {
                    $this->deleteFromGoogleDrive($oldVideoData['file_id']);
                    Log::info('Deleted Google Drive video since video not selected', [
                        'file_id' => $oldVideoData['file_id']
                    ]);
                } elseif ($oldVideoData['type'] === 'local' && isset($oldVideoData['path'])) {
                    Storage::disk('public')->delete($oldVideoData['path']);
                    Log::info('Deleted local video since video not selected', [
                        'path' => $oldVideoData['path']
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error deleting video file: ' . $e->getMessage());
        }
    }
    
    Log::info('Video data cleared as video not selected', [
        'material_id' => $material->id
    ]);
    
    // PERBAIKAN PENTING: JANGAN update video_type!
    // Database akan tetap menggunakan default value 'youtube'
}

        // Update field durasi khusus
        if (in_array('pretest', $contentTypes)) {
            $materialData['durasi_pretest'] = $request->durasi_pretest;
        } else {
            $materialData['durasi_pretest'] = null;
            $materialData['soal_pretest'] = null;
        }

        if (in_array('posttest', $contentTypes)) {
            $materialData['durasi_posttest'] = $request->durasi_posttest;
        } else {
            $materialData['durasi_posttest'] = null;
            $materialData['soal_posttest'] = null;
        }

        // ============================================
        // SIMPAN DATA
        // ============================================

        try {
            // Update material
            $material->update($materialData);
            
            // Update video questions
            if ($hasVideoQuestions && $request->filled('video_questions')) {
                VideoQuestion::where('material_id', $material->id)->delete();
                
                foreach ($request->video_questions as $index => $questionData) {
                    if (empty($questionData['question']) || empty($questionData['options'])) {
                        continue;
                    }
                    
                    VideoQuestion::create([
                        'material_id' => $material->id,
                        'order' => $index + 1,
                        'time_in_seconds' => $questionData['time_in_seconds'] ?? 0,
                        'question' => $questionData['question'],
                        'options' => json_encode($questionData['options']),
                        'correct_option' => $questionData['correct_option'] ?? 0,
                        'points' => $questionData['points'] ?? 1,
                        'explanation' => $questionData['explanation'] ?? null,
                        'required_to_continue' => $questionData['required_to_continue'] ?? true,
                    ]);
                }
            } elseif (!in_array('video', $contentTypes)) {
                // Hapus video questions jika video tidak dipilih
                VideoQuestion::where('material_id', $material->id)->delete();
            }

            // ============================================
            // PERBAIKAN: RETURN JSON UNTUK AJAX REQUEST
            // ============================================
            
            if ($isAjax) {
                Log::info('Update successful - returning JSON response for AJAX');
                return response()->json([
                    'success' => true,
                    'message' => 'Material berhasil diperbarui!',
                    'redirect' => route('admin.kursus.materials.index', $kursus)
                ]);
            }
            
            // Untuk non-AJAX request
            return redirect()->route('admin.kursus.materials.index', $kursus)
                            ->with('success', 'Material berhasil diperbarui!');
                            
        } catch (\Exception $e) {
            Log::error('Error updating material: ' . $e->getMessage());
            
            // Hapus file baru yang sudah diupload jika ada error
            if ($request->hasFile('file_path')) {
                foreach ($request->file('file_path') as $file) {
                    $filePath = $file->store('materials', 'public');
                    Storage::disk('public')->delete($filePath);
                }
            }
            
            // Hapus video file baru dari Google Drive jika ada error
            if (isset($newVideoInfo) && isset($newVideoInfo['file_id'])) {
                try {
                    $this->deleteFromGoogleDrive($newVideoInfo['file_id']);
                } catch (\Exception $deleteError) {
                    Log::error('Error deleting new video from Google Drive: ' . $deleteError->getMessage());
                }
            }
            
            // Hapus video file baru dari local storage jika ada error
            if (isset($videoPath)) {
                try {
                    Storage::disk('public')->delete($videoPath);
                } catch (\Exception $deleteError) {
                    Log::error('Error deleting new video from local storage: ' . $deleteError->getMessage());
                }
            }
            
            // ============================================
            // PERBAIKAN: RETURN JSON UNTUK AJAX REQUEST JIKA ERROR
            // ============================================
            
            if ($isAjax) {
                Log::error('Update failed - returning JSON error for AJAX');
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui material: ' . $e->getMessage()
                ], 500);
            }
            
            // Untuk non-AJAX request
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Gagal memperbarui material: ' . $e->getMessage());
        }
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation Exception in update:', $e->errors());
        
        // ============================================
        // PERBAIKAN: RETURN JSON UNTUK VALIDATION ERROR
        // ============================================
        
        if ($isAjax) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
        
        // Untuk non-AJAX request
        return redirect()->back()
            ->withErrors($e->validator)
            ->withInput();
            
    } catch (\Exception $e) {
        Log::error('Error in update method: ' . $e->getMessage());
        Log::error('Trace: ' . $e->getTraceAsString());
        
        // ============================================
        // PERBAIKAN: RETURN JSON UNTUK GENERAL ERROR
        // ============================================
        
        if ($isAjax) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui material: ' . $e->getMessage()
            ], 500);
        }
        
        // Untuk non-AJAX request
        return redirect()->back()
            ->withInput()
            ->with('error', 'Gagal memperbarui material: ' . $e->getMessage());
    }
}

    public function destroy(Kursus $kursus, Materials $material)
{
    try {
        // Simpan order sebelum dihapus untuk reorder
        $deletedOrder = $material->order;
        
        // Delete files (tetap di local)
        if ($material->file_path) {
            // PERBAIKAN: Cek apakah file_path sudah array atau masih string JSON
            $files = [];
            
            if (is_array($material->file_path)) {
                // Jika sudah array, langsung gunakan
                $files = $material->file_path;
            } elseif (is_string($material->file_path) && !empty($material->file_path)) {
                // Jika string, decode JSON
                $decoded = json_decode($material->file_path, true);
                if (is_array($decoded)) {
                    $files = $decoded;
                }
            }
            
            // Hapus file dari storage
            if (is_array($files)) {
                foreach ($files as $filePath) {
                    if (is_string($filePath) && !empty($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                }
            }
        }

        // Delete video file dari Google Drive
        if ($material->video_file) {
            try {
                $videoData = [];
                
                // PERBAIKAN: Handle video_file yang bisa array atau string JSON
                if (is_array($material->video_file)) {
                    $videoData = $material->video_file;
                } elseif (is_string($material->video_file) && !empty($material->video_file)) {
                    $decoded = json_decode($material->video_file, true);
                    if (is_array($decoded)) {
                        $videoData = $decoded;
                    }
                }
                
                if (!empty($videoData)) {
                    if (isset($videoData['type']) && $videoData['type'] === 'hosted' && isset($videoData['file_id'])) {
                        // Hapus dari Google Drive
                        $this->deleteFromGoogleDrive($videoData['file_id']);
                    } elseif (isset($videoData['type']) && $videoData['type'] === 'local' && isset($videoData['path'])) {
                        // Hapus dari local storage
                        Storage::disk('public')->delete($videoData['path']);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error deleting video file: ' . $e->getMessage());
            }
        }

        // Delete video questions
        VideoQuestion::where('material_id', $material->id)->delete();
        
        // Delete video progress
        UserVideoProgress::where('material_id', $material->id)->delete();

        // Delete material progress
        MaterialProgress::where('material_id', $material->id)->delete();

        // Delete material
        $material->delete();

        // Reorder secara manual (backup jika event deleted tidak jalan)
        $this->reorderMaterials($kursus->id);

        return redirect()->route('admin.kursus.materials.index', $kursus)
                        ->with('success', 'Material berhasil dihapus!');
    } catch (\Exception $e) {
        Log::error('Error deleting material: ' . $e->getMessage());
        Log::error('File path data: ' . print_r($material->file_path, true));
        Log::error('Video file data: ' . print_r($material->video_file, true));
        
        return redirect()->back()
                        ->with('error', 'Gagal menghapus material: ' . $e->getMessage());
    }
}

private function isValidYouTubeUrl($url)
    {
        if (empty($url)) {
            return false;
        }
        
        $patterns = [
            '/^(https?\:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/',
            '/^(https?\:\/\/)?(www\.)?youtube\.com\/watch\?v=[\w-]+/',
            '/^(https?\:\/\/)?(www\.)?youtu\.be\/[\w-]+/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }
        
        return false;
    }

    

// Tambahkan method helper untuk reorder
private function reorderMaterials($courseId)
{
    $materials = Materials::where('course_id', $courseId)
        ->orderBy('order')
        ->orderBy('created_at')
        ->get();
    
    $order = 1;
    foreach ($materials as $material) {
        $material->order = $order;
        $material->saveQuietly(); // saveQuietly untuk menghindari event updating
        $order++;
    }
}

    public function updateStatus(Request $request, Kursus $kursus, Materials $material)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        try {
            $material->update(['is_active' => $request->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Status material berhasil diperbarui!',
                'new_status' => $material->is_active ? 'Aktif' : 'Nonaktif'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating material status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status material: ' . $e->getMessage()
            ], 500);
        }
    }

    // NEW METHODS FOR VIDEO MANAGEMENT
    
    public function videoQuestions(Kursus $kursus, Materials $material)
    {
        $material->load('videoQuestions');
        return view('admin.kursus.materials.video-questions', compact('kursus', 'material'));
    }

    public function videoPreview(Kursus $kursus, Materials $material)
    {
        $material->load('videoQuestions');
        
        // Dapatkan URL video dari Google Drive
        $videoUrl = $this->getVideoUrl($material);
        
        $videoData = [
            'type' => $material->video_type,
            'url' => $videoUrl ?: $material->video_url,
            'video_id' => $this->getVideoId($material),
            'embed_url' => $this->getVideoEmbedUrl($material),
            'config' => $material->player_config,
        ];
        
        return view('admin.kursus.materials.video-preview', compact('kursus', 'material', 'videoData'));
    }

    public function videoStats(Kursus $kursus, Materials $material)
    {
        $stats = [
            'total_views' => $material->total_views,
            'total_completions' => $material->total_completions,
            'completion_rate' => $material->total_views > 0 ? 
                round(($material->total_completions / $material->total_views) * 100, 2) : 0,
            'avg_completion_time' => $material->avg_completion_time,
            'question_stats' => [
                'total_questions' => $material->question_count,
                'total_points' => $material->total_video_points,
                'avg_points_earned' => UserVideoProgress::where('material_id', $material->id)
                    ->where('total_points_earned', '>', 0)
                    ->avg('total_points_earned') ?? 0,
            ],
            'progress_data' => UserVideoProgress::where('material_id', $material->id)
                ->selectRaw('progress_percentage, COUNT(*) as count')
                ->groupBy('progress_percentage')
                ->orderBy('progress_percentage')
                ->get(),
        ];
        
        return view('admin.kursus.materials.video-stats', compact('kursus', 'material', 'stats'));
    }

    private function validateContentTypeCombination($contentTypes)
    {
        // Jika memilih pretest
        if (in_array('pretest', $contentTypes)) {
            if (count($contentTypes) > 1) {
                return 'Jika memilih pretest, tidak bisa memilih konten lain.';
            }
        }
        
        // Jika memilih posttest
        if (in_array('posttest', $contentTypes)) {
            if (count($contentTypes) > 1) {
                return 'Jika memilih posttest, tidak bisa memilih konten lain.';
            }
        }
        
        // Jika memilih file (PDF/PPT)
        if (in_array('file', $contentTypes)) {
            $allowedWithFile = ['video']; // File bisa dikombinasikan dengan video
            foreach ($contentTypes as $type) {
                if ($type !== 'file' && !in_array($type, $allowedWithFile)) {
                    if (in_array($type, ['pretest', 'posttest'])) {
                        return 'File tidak bisa dikombinasikan dengan ' . ($type === 'pretest' ? 'pretest' : 'posttest') . '.';
                    }
                }
            }
            
            // Cek apakah kombinasi file dan video valid
            if (count($contentTypes) > 2 || (count($contentTypes) === 2 && !in_array('video', $contentTypes))) {
                return 'File hanya bisa dikombinasikan dengan video.';
            }
        }
        
        // Jika memilih video
        if (in_array('video', $contentTypes)) {
            $allowedWithVideo = ['file']; // Video bisa dikombinasikan dengan file
            foreach ($contentTypes as $type) {
                if ($type !== 'video' && !in_array($type, $allowedWithVideo)) {
                    if (in_array($type, ['pretest', 'posttest'])) {
                        return 'Video tidak bisa dikombinasikan dengan ' . ($type === 'pretest' ? 'pretest' : 'posttest') . '.';
                    }
                }
            }
            
            // Cek apakah kombinasi video valid
            if (count($contentTypes) > 2 || (count($contentTypes) === 2 && !in_array('file', $contentTypes))) {
                return 'Video hanya bisa dikombinasikan dengan file.';
            }
        }

        return null;
    }
    private function validateConditionalFields($request, $contentTypes, $isAjax = false)
{
    // Conditional validasi untuk file
    if (in_array('file', $contentTypes)) {
        // PERBAIKAN: File tidak wajib di edit mode (bisa kosong untuk tetap pakai existing files)
        if ($request->hasFile('file_path')) {
            try {
                // Validasi hanya jika ada file baru
                if ($request->file_path && is_array($request->file_path)) {
                    $fileRules = [];
                    foreach ($request->file_path as $index => $file) {
                        if ($file instanceof \Illuminate\Http\UploadedFile) {
                            $request->validate([
                                "file_path.{$index}" => 'file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png|max:10240'
                            ]);
                        }
                    }
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                if ($isAjax) {
                    // Re-throw exception agar bisa ditangkap di method update
                    throw $e;
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'File tidak valid: ' . implode(', ', array_flatten($e->errors())));
                }
            }
        }
    }

    // Conditional validasi untuk pretest
    if (in_array('pretest', $contentTypes)) {
        try {
            // Validasi durasi
            $request->validate([
                'durasi_pretest' => 'required|integer|min:1|max:300',
            ]);
            
            // Validasi soal pretest jika ada
            if ($request->has('pretest_soal') && is_array($request->pretest_soal)) {
                $pretestSoal = array_filter($request->pretest_soal, function($soal) {
                    return !empty($soal['pertanyaan']) || !empty(array_filter($soal['pilihan'] ?? []));
                });
                
                if (count($pretestSoal) === 0) {
                    $errorMessage = 'Pretest harus memiliki minimal 1 soal';
                    if ($isAjax) {
                        return [
                            'pretest_soal' => [$errorMessage]
                        ];
                    }
                    return redirect()->back()
                        ->withInput()
                        ->with('error', $errorMessage);
                }
                
                foreach ($pretestSoal as $index => $soal) {
                    // Validasi pertanyaan dan jawaban
                    $request->validate([
                        "pretest_soal.{$index}.pertanyaan" => 'required|string|min:5|max:1000',
                        "pretest_soal.{$index}.jawaban_benar" => 'required|integer|min:0|max:3',
                    ]);
                    
                    // Validasi pilihan jika ada
                    if (isset($soal['pilihan']) && is_array($soal['pilihan'])) {
                        $validPilihanCount = 0;
                        foreach ($soal['pilihan'] as $pilihanIndex => $pilihan) {
                            if (!empty(trim($pilihan))) {
                                $validPilihanCount++;
                                $request->validate([
                                    "pretest_soal.{$index}.pilihan.{$pilihanIndex}" => 'required|string|min:1|max:500'
                                ]);
                            }
                        }
                        
                        // Minimal harus ada 2 pilihan valid
                        if ($validPilihanCount < 2) {
                            $errorMessage = "Soal pretest #" . ($index + 1) . " harus memiliki minimal 2 pilihan jawaban";
                            if ($isAjax) {
                                return [
                                    "pretest_soal.{$index}.pilihan" => [$errorMessage]
                                ];
                            }
                            return redirect()->back()
                                ->withInput()
                                ->with('error', $errorMessage);
                        }
                    } else {
                        $errorMessage = "Soal pretest #" . ($index + 1) . " harus memiliki pilihan jawaban";
                        if ($isAjax) {
                            return [
                                "pretest_soal.{$index}.pilihan" => [$errorMessage]
                            ];
                        }
                        return redirect()->back()
                            ->withInput()
                            ->with('error', $errorMessage);
                    }
                }
            } else {
                $errorMessage = 'Pretest harus memiliki minimal 1 soal';
                if ($isAjax) {
                    return [
                        'pretest_soal' => [$errorMessage]
                    ];
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', $errorMessage);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($isAjax) {
                throw $e;
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Validasi pretest gagal: ' . implode(', ', array_flatten($e->errors())));
            }
        }
    }

    // Conditional validasi untuk posttest
    if (in_array('posttest', $contentTypes)) {
        try {
            // Validasi durasi
            $request->validate([
                'durasi_posttest' => 'required|integer|min:1|max:300',
            ]);
            
            // Validasi soal posttest jika ada
            if ($request->has('posttest_soal') && is_array($request->posttest_soal)) {
                $posttestSoal = array_filter($request->posttest_soal, function($soal) {
                    return !empty($soal['pertanyaan']) || !empty(array_filter($soal['pilihan'] ?? []));
                });
                
                if (count($posttestSoal) === 0) {
                    $errorMessage = 'Posttest harus memiliki minimal 1 soal';
                    if ($isAjax) {
                        return [
                            'posttest_soal' => [$errorMessage]
                        ];
                    }
                    return redirect()->back()
                        ->withInput()
                        ->with('error', $errorMessage);
                }
                
                foreach ($posttestSoal as $index => $soal) {
                    // Validasi pertanyaan dan jawaban
                    $request->validate([
                        "posttest_soal.{$index}.pertanyaan" => 'required|string|min:5|max:1000',
                        "posttest_soal.{$index}.jawaban_benar" => 'required|integer|min:0|max:3',
                    ]);
                    
                    // Validasi pilihan jika ada
                    if (isset($soal['pilihan']) && is_array($soal['pilihan'])) {
                        $validPilihanCount = 0;
                        foreach ($soal['pilihan'] as $pilihanIndex => $pilihan) {
                            if (!empty(trim($pilihan))) {
                                $validPilihanCount++;
                                $request->validate([
                                    "posttest_soal.{$index}.pilihan.{$pilihanIndex}" => 'required|string|min:1|max:500'
                                ]);
                            }
                        }
                        
                        // Minimal harus ada 2 pilihan valid
                        if ($validPilihanCount < 2) {
                            $errorMessage = "Soal posttest #" . ($index + 1) . " harus memiliki minimal 2 pilihan jawaban";
                            if ($isAjax) {
                                return [
                                    "posttest_soal.{$index}.pilihan" => [$errorMessage]
                                ];
                            }
                            return redirect()->back()
                                ->withInput()
                                ->with('error', $errorMessage);
                        }
                    } else {
                        $errorMessage = "Soal posttest #" . ($index + 1) . " harus memiliki pilihan jawaban";
                        if ($isAjax) {
                            return [
                                "posttest_soal.{$index}.pilihan" => [$errorMessage]
                            ];
                        }
                        return redirect()->back()
                            ->withInput()
                            ->with('error', $errorMessage);
                    }
                }
            } else {
                $errorMessage = 'Posttest harus memiliki minimal 1 soal';
                if ($isAjax) {
                    return [
                        'posttest_soal' => [$errorMessage]
                    ];
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', $errorMessage);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($isAjax) {
                throw $e;
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Validasi posttest gagal: ' . implode(', ', array_flatten($e->errors())));
            }
        }
    }

    return null;
}

    /**
 * Check if material has existing video data
 */
private function hasExistingVideoData($material)
{
    // Cek apakah material sudah punya video data
    if (empty($material)) {
        return false;
    }
    
    // Untuk YouTube
    if ($material->video_type === 'youtube' && !empty($material->video_url)) {
        return true;
    }
    
    // Untuk hosted/local
    if (in_array($material->video_type, ['hosted', 'local']) && !empty($material->video_file)) {
        try {
            // Cek apakah video_file valid JSON
            if (is_string($material->video_file)) {
                $decoded = json_decode($material->video_file, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return !empty($decoded);
                }
            } elseif (is_array($material->video_file)) {
                return !empty($material->video_file);
            }
        } catch (\Exception $e) {
            Log::error('Error checking existing video data: ' . $e->getMessage());
            return false;
        }
    }
    
    return false;
}

    private function determineMaterialTypes($contentTypes)
    {
        $type = 'material';
        $materialType = 'theory';
        
        if (in_array('pretest', $contentTypes)) {
            $type = 'pre_test';
            $materialType = 'quiz';
        } elseif (in_array('posttest', $contentTypes)) {
            $type = 'post_test';
            $materialType = 'quiz';
        } elseif (in_array('file', $contentTypes) && in_array('video', $contentTypes)) {
            $materialType = 'mixed';
        } elseif (in_array('file', $contentTypes)) {
            $materialType = 'theory';
        } elseif (in_array('video', $contentTypes)) {
            $materialType = 'video';
        }
        
        return [$type, $materialType];
    }

    private function formatSoal($soalData, $type)
    {
        $soalFormatted = [];
        foreach ($soalData as $index => $soal) {
            // Validasi bahwa semua pilihan terisi
            if (count(array_filter($soal['pilihan'])) < 4) {
                return 'Semua pilihan jawaban untuk setiap soal ' . $type . ' harus diisi.';
            }

            $soalFormatted[] = [
                'id' => $index + 1,
                'pertanyaan' => $soal['pertanyaan'] ?? '',
                'pilihan' => $soal['pilihan'] ?? [],
                'jawaban_benar' => (int)($soal['jawaban_benar'] ?? 0)
            ];
        }
        return $soalFormatted;
    }

    private function calculateVideoQuestionStats($request, $contentTypes)
    {
        $hasVideoQuestions = false;
        $questionCount = 0;
        $totalVideoPoints = 0;

        if (in_array('video', $contentTypes) && $request->filled('video_questions')) {
            $hasVideoQuestions = true;
            $questionCount = count(array_filter($request->video_questions, function($q) {
                return !empty($q['question']) && !empty($q['options']);
            }));
            
            foreach ($request->video_questions as $question) {
                if (!empty($question['question']) && !empty($question['options'])) {
                    $totalVideoPoints += $question['points'] ?? 1;
                }
            }
        }
        
        return [$hasVideoQuestions, $questionCount, $totalVideoPoints];
    }

    public function updateVideoQuestions(Request $request, Kursus $kursus, Materials $material)
    {
        $request->validate([
            'questions' => 'nullable|array',
            'questions.*.time_in_seconds' => 'required|integer|min:0',
            'questions.*.question' => 'required|string|max:500',
            'questions.*.options' => 'required|array|min:2|max:4',
            'questions.*.options.*' => 'required|string|max:255',
            'questions.*.correct_option' => 'required|integer|min:0|max:3',
            'questions.*.points' => 'required|integer|min:1|max:10',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.required_to_continue' => 'boolean',
        ]);

        try {
            // Delete existing questions
            VideoQuestion::where('material_id', $material->id)->delete();
            
            // Calculate stats
            $questionCount = count($request->questions);
            $totalVideoPoints = 0;
            
            // Create new questions
            if (!empty($request->questions)) {
                foreach ($request->questions as $index => $questionData) {
                    $totalVideoPoints += $questionData['points'];
                    
                    VideoQuestion::create([
                        'material_id' => $material->id,
                        'order' => $index + 1,
                        'time_in_seconds' => $questionData['time_in_seconds'],
                        'question' => $questionData['question'],
                        'options' => json_encode($questionData['options']),
                        'correct_option' => $questionData['correct_option'],
                        'points' => $questionData['points'],
                        'explanation' => $questionData['explanation'] ?? null,
                        'required_to_continue' => $questionData['required_to_continue'] ?? true,
                    ]);
                }
            }
            
            // Update material stats
            $material->update([
                'has_video_questions' => $questionCount > 0,
                'question_count' => $questionCount,
                'total_video_points' => $totalVideoPoints,
            ]);
            
            return redirect()->back()
                            ->with('success', 'Pertanyaan video berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error updating video questions: ' . $e->getMessage());
            
            return redirect()->back()
                            ->with('error', 'Gagal memperbarui pertanyaan video: ' . $e->getMessage());
        }
    }

    public function updatePlayerConfig(Request $request, Kursus $kursus, Materials $material)
    {
        $request->validate([
            'allow_skip' => 'boolean',
            'disable_forward_seek' => 'boolean',
            'disable_backward_seek' => 'boolean',
            'disable_right_click' => 'boolean',
            'require_completion' => 'boolean',
            'auto_pause_on_question' => 'boolean',
            'require_question_completion' => 'boolean',
        ]);

        try {
            $playerConfig = [
                'allow_skip' => $request->boolean('allow_skip'),
                'disable_forward_seek' => $request->boolean('disable_forward_seek', true),
                'disable_backward_seek' => $request->boolean('disable_backward_seek', false),
                'disable_right_click' => $request->boolean('disable_right_click', true),
                'require_completion' => $request->boolean('require_completion', true),
                'auto_pause_on_question' => $request->boolean('auto_pause_on_question', true),
                'require_question_completion' => $request->boolean('require_question_completion', false),
            ];
            
            $material->update([
                'allow_skip' => $request->boolean('allow_skip'),
                'player_config' => json_encode($playerConfig),
                'require_video_completion' => $request->boolean('require_completion', true),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan player berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating player config: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pengaturan player: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // HELPER METHODS FOR GOOGLE DRIVE INTEGRATION
    // ============================================

    /**
     * Upload file to Google Drive
     */
    // GANTI method uploadToGoogleDrive dengan ini:
    private function uploadToGoogleDrive($file)
{
    try {
        Log::info('Starting Google Drive upload...');
        
        $client = new GoogleClient();
        
        // Load credentials
        $credentialsPath = storage_path('app/google-drive/credentials.json');
        
        if (!file_exists($credentialsPath)) {
            throw new \Exception('Credentials file not found at: ' . $credentialsPath);
        }
        
        $client->setAuthConfig($credentialsPath);
        $client->addScope(GoogleDrive::DRIVE_FILE);
        $client->addScope(GoogleDrive::DRIVE_READONLY);
        
        // Set subject untuk service account
        $serviceAccount = json_decode(file_get_contents($credentialsPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid credentials JSON: ' . json_last_error_msg());
        }
        
        if (!isset($serviceAccount['client_email'])) {
            throw new \Exception('Service account email not found in credentials');
        }
        
        $client->setSubject($serviceAccount['client_email']);
        
        $service = new GoogleDrive($client);
        
        // Generate unique filename
        $filename = 'video_' . Str::random(20) . '_' . time() . '.' . $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        
        // Upload file ke Google Drive
        $fileMetadata = new \Google\Service\Drive\DriveFile([
            'name' => $filename,
            'parents' => [env('GOOGLE_DRIVE_FOLDER_ID')],
            'description' => 'Video materi pembelajaran - ' . $originalName,
            'mimeType' => $file->getMimeType(),
        ]);

        $videoDuration = $this->getVideoDuration($file);
        
        $content = file_get_contents($file->getRealPath());
        
        $uploadedFile = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $file->getMimeType(),
            'uploadType' => 'multipart',
            'fields' => 'id, name, webViewLink, webContentLink, size, mimeType, thumbnailLink',
            'supportsAllDrives' => true
        ]);
        
        // Buat file bisa diakses publik
        try {
            $permission = new \Google\Service\Drive\Permission([
                'type' => 'anyone',
                'role' => 'reader',
                'allowFileDiscovery' => false,
            ]);
            
            $service->permissions->create($uploadedFile->id, $permission);
            
            Log::info('Google Drive permission set successfully', [
                'file_id' => $uploadedFile->id
            ]);
            
        } catch (\Exception $e) {
            Log::warning('Failed to make file public, but continuing: ' . $e->getMessage());
        }
        
        // Buat embed link khusus
        $embedLink = 'https://drive.google.com/file/d/' . $uploadedFile->id . '/preview';
        
        // **PERBAIKAN: Return data yang konsisten dan lengkap**
        $result = [
            'file_id' => $uploadedFile->id,
            'file_name' => $uploadedFile->name,
            'original_name' => $originalName,
            'web_view_link' => $uploadedFile->webViewLink ?? null,
            'web_content_link' => $uploadedFile->webContentLink ?? null,
            'embed_link' => $embedLink,
            'thumbnail_link' => $uploadedFile->thumbnailLink ?? null,
            'size' => $uploadedFile->size ?? null,
            'mime_type' => $uploadedFile->mimeType ?? null,
            'duration' => $videoDuration, // <<< TAMBAHKAN INI
            'uploaded_at' => now()->toDateTimeString(),
            'direct_play' => true,
        ];
        
        // Hapus null values
        $result = array_filter($result, function($value) {
            return $value !== null;
        });
        
        // Validasi JSON
        $jsonTest = json_encode($result);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid JSON from Google Drive upload', [
                'error' => json_last_error_msg(),
                'data' => $result
            ]);
            throw new \Exception('Invalid JSON data from Google Drive: ' . json_last_error_msg());
        }
        
        Log::info('Google Drive upload successful', [
            'file_id' => $uploadedFile->id,
            'embed_link' => $embedLink,
            'json_valid' => true
        ]);
        
        return $result;
        
    } catch (\Exception $e) {
        Log::error('Error in uploadToGoogleDrive: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        throw new \Exception('Google Drive upload failed: ' . $e->getMessage());
    }
}

// app/Http\Controllers\Admin\MaterialController.php

// Tambahkan method baru untuk multi-delete
public function bulkDestroy(Request $request, Kursus $kursus)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'exists:materials,id'
    ]);

    try {
        $materials = Materials::whereIn('id', $request->ids)
            ->where('course_id', $kursus->id)
            ->get();

        $deletedCount = 0;
        $errors = [];

        foreach ($materials as $material) {
            try {
                // Delete files
                if ($material->file_path) {
                    $files = [];
                    
                    if (is_array($material->file_path)) {
                        $files = $material->file_path;
                    } elseif (is_string($material->file_path) && !empty($material->file_path)) {
                        $decoded = json_decode($material->file_path, true);
                        if (is_array($decoded)) {
                            $files = $decoded;
                        }
                    }
                    
                    if (is_array($files)) {
                        foreach ($files as $filePath) {
                            if (is_string($filePath) && !empty($filePath)) {
                                Storage::disk('public')->delete($filePath);
                            }
                        }
                    }
                }

                // Delete video file dari Google Drive
                if ($material->video_file) {
                    try {
                        $videoData = [];
                        
                        if (is_array($material->video_file)) {
                            $videoData = $material->video_file;
                        } elseif (is_string($material->video_file) && !empty($material->video_file)) {
                            $decoded = json_decode($material->video_file, true);
                            if (is_array($decoded)) {
                                $videoData = $decoded;
                            }
                        }
                        
                        if (!empty($videoData)) {
                            if (isset($videoData['type']) && $videoData['type'] === 'hosted' && isset($videoData['file_id'])) {
                                $this->deleteFromGoogleDrive($videoData['file_id']);
                            } elseif (isset($videoData['type']) && $videoData['type'] === 'local' && isset($videoData['path'])) {
                                Storage::disk('public')->delete($videoData['path']);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Error deleting video file for material ' . $material->id . ': ' . $e->getMessage());
                        $errors[] = 'Material ' . $material->title . ': Gagal menghapus video - ' . $e->getMessage();
                    }
                }

                // Delete video questions
                VideoQuestion::where('material_id', $material->id)->delete();
                
                // Delete video progress
                UserVideoProgress::where('material_id', $material->id)->delete();

                // Delete material progress
                MaterialProgress::where('material_id', $material->id)->delete();

                // Delete material
                $material->delete();
                $deletedCount++;

            } catch (\Exception $e) {
                Log::error('Error deleting material ' . $material->id . ': ' . $e->getMessage());
                $errors[] = 'Material ' . $material->title . ': ' . $e->getMessage();
            }
        }

        // Reorder materials yang tersisa
        $this->reorderMaterials($kursus->id);

        if (empty($errors)) {
            return response()->json([
                'success' => true,
                'message' => $deletedCount . ' materi berhasil dihapus!'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $deletedCount . ' materi berhasil dihapus, tetapi ada beberapa error: ' . implode(', ', $errors)
            ], 207);
        }

    } catch (\Exception $e) {
        Log::error('Error in bulk destroy: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus materi: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Get YouTube video duration via API
 */
private function getYouTubeDuration($url)
{
    try {
        $videoId = $this->extractYouTubeId($url);
        if (!$videoId) return 0;
        
        $apiKey = env('YOUTUBE_API_KEY');
        if (!$apiKey) return 0;
        
        $apiUrl = "https://www.googleapis.com/youtube/v3/videos?id={$videoId}&key={$apiKey}&part=contentDetails";
        
        $response = file_get_contents($apiUrl);
        $data = json_decode($response, true);
        
        if (isset($data['items'][0]['contentDetails']['duration'])) {
            $duration = $data['items'][0]['contentDetails']['duration'];
            return $this->parseISODuration($duration);
        }
    } catch (\Exception $e) {
        Log::warning('Error getting YouTube duration: ' . $e->getMessage());
    }
    
    return 0;
}

/**
 * Parse ISO 8601 duration format
 */
private function parseISODuration($duration)
{
    $pattern = '/PT(\d+H)?(\d+M)?(\d+S)?/';
    preg_match($pattern, $duration, $matches);
    
    $hours = isset($matches[1]) ? (int)str_replace('H', '', $matches[1]) : 0;
    $minutes = isset($matches[2]) ? (int)str_replace('M', '', $matches[2]) : 0;
    $seconds = isset($matches[3]) ? (int)str_replace('S', '', $matches[3]) : 0;
    
    return ($hours * 3600) + ($minutes * 60) + $seconds;
}

    /**
 * Fix video data for all materials
 */
public function fixAllVideoData()
{
    try {
        $materials = Materials::whereIn('video_type', ['hosted', 'local'])
            ->get();
        
        $fixed = 0;
        $errors = [];
        
        foreach ($materials as $material) {
            try {
                $videoFile = $material->video_file;
                
                // Jika video_file adalah string tapi bukan JSON valid
                if (is_string($videoFile) && !empty($videoFile)) {
                    // Coba decode
                    $decoded = json_decode($videoFile, true);
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::warning('Fixing invalid JSON for material ' . $material->id, [
                            'error' => json_last_error_msg(),
                            'video_file_sample' => substr($videoFile, 0, 200)
                        ]);
                        
                        // Set ke null
                        $material->video_file = null;
                        $material->save();
                        $fixed++;
                    } elseif (is_array($decoded)) {
                        // JSON valid, simpan ulang untuk memastikan
                        $material->video_file = $decoded;
                        $material->save();
                        $fixed++;
                    }
                }
                // Jika video_file sudah array, simpan ulang untuk validasi
                elseif (is_array($videoFile)) {
                    $material->video_file = $videoFile;
                    $material->save();
                    $fixed++;
                }
                
            } catch (\Exception $e) {
                $errors[] = 'Material ' . $material->id . ': ' . $e->getMessage();
                Log::error('Error fixing video_file for material ' . $material->id . ': ' . $e->getMessage());
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Fixed ' . $fixed . ' materials',
            'errors' => $errors
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in fixAllVideoData: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Check video data status
 */
public function checkVideoDataStatus()
{
    try {
        $materials = Materials::all();
        
        $results = [];
        $invalidCount = 0;
        $validCount = 0;
        
        foreach ($materials as $material) {
            $videoFile = $material->video_file;
            $isValid = true;
            $error = null;
            
            // Check if video_file is valid
            if (is_string($videoFile) && !empty($videoFile)) {
                json_decode($videoFile);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $isValid = false;
                    $error = 'Invalid JSON: ' . json_last_error_msg();
                    $invalidCount++;
                } else {
                    $validCount++;
                }
            } elseif (is_array($videoFile)) {
                $validCount++;
            } elseif (empty($videoFile) || $videoFile === null) {
                // Empty is okay for non-video materials
                $validCount++;
            }
            
            $results[] = [
                'id' => $material->id,
                'title' => $material->title,
                'video_type' => $material->video_type,
                'video_file_type' => gettype($videoFile),
                'is_valid' => $isValid,
                'error' => $error,
                'is_video_available' => $material->isVideoAvailable()
            ];
        }
        
        return response()->json([
            'success' => true,
            'total_materials' => count($materials),
            'valid_video_data' => $validCount,
            'invalid_video_data' => $invalidCount,
            'results' => $results
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error checking video data: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

// Juga perbaiki deleteFromGoogleDrive:
private function deleteFromGoogleDrive($fileId)
    {
        try {
            $client = new GoogleClient();
            
            $credentialsPath = storage_path('app/google-drive/credentials.json');
            $client->setAuthConfig($credentialsPath);
            $client->addScope(GoogleDrive::DRIVE_FILE);
            
            $serviceAccount = json_decode(file_get_contents($credentialsPath), true);
            $client->setSubject($serviceAccount['client_email']);
            
            $service = new GoogleDrive($client);
            $service->files->delete($fileId);
            
            Log::info('File deleted from Google Drive: ' . $fileId);
            
        } catch (\Exception $e) {
            Log::error('Error deleting from Google Drive: ' . $e->getMessage());
        }
    }


    /**
     * Get video URL from Google Drive
     */
    private function getVideoUrl($material)
{
    if ($material->video_type === 'local' && $material->video_file) {
        try {
            $videoData = json_decode($material->video_file, true);
            if ($videoData && isset($videoData['url'])) {
                return $videoData['url'];
            }
        } catch (\Exception $e) {
            Log::error('Error getting local video URL: ' . $e->getMessage());
        }
    }
    
    if ($material->video_type === 'hosted' && $material->video_file) {
        try {
            $videoData = json_decode($material->video_file, true);
            if ($videoData && isset($videoData['embed_link'])) {
                return $videoData['embed_link'];
            }
        } catch (\Exception $e) {
            Log::error('Error getting hosted video URL: ' . $e->getMessage());
        }
    }
    
    // YouTube
    return $material->video_url;
}

    /**
     * Get video ID for embedding
     */
    private function getVideoId($material)
    {
        if ($material->video_type === 'hosted' && $material->video_file) {
            try {
                $videoData = json_decode($material->video_file, true);
                if ($videoData && isset($videoData['file_id'])) {
                    return $videoData['file_id'];
                }
            } catch (\Exception $e) {
                Log::error('Error getting video ID: ' . $e->getMessage());
            }
        }
        
        // Untuk YouTube, extract ID dari URL
        if ($material->video_type === 'youtube') {
            return $this->extractYouTubeId($material->video_url);
        }
        
        return null;
    }

    /**
     * Get video embed URL
     */
    private function getVideoEmbedUrl($material)
{
    if ($material->video_type === 'youtube') {
        $videoId = $this->extractYouTubeId($material->video_url);
        if ($videoId) {
            return 'https://www.youtube.com/embed/' . $videoId . '?rel=0&modestbranding=1&showinfo=0';
        }
    }
    elseif ($material->video_type === 'hosted' && $material->video_file) {
        try {
            $videoData = json_decode($material->video_file, true);
            if ($videoData && isset($videoData['embed_link'])) {
                return $videoData['embed_link'];
            }
        } catch (\Exception $e) {
            Log::error('Error getting hosted embed URL: ' . $e->getMessage());
        }
    }
    
    return null;
}

    private function extractYouTubeId($url)
    {
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]+)/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Import soal dari Excel
     */
    public function importSoal(Request $request, Kursus $kursus)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120', // 5MB max
            'type' => 'required|in:pretest,posttest',
            'preview_only' => 'nullable|boolean'
        ]);

        try {
            $import = new SoalImport();
            
            if ($request->preview_only) {
                // Untuk preview, baca hanya 50 baris pertama
                $data = Excel::toCollection($import, $request->file('file'))->first()->take(50);
            } else {
                $data = Excel::toCollection($import, $request->file('file'))->first();
            }
            
            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Excel kosong atau format tidak sesuai'
                ], 400);
            }
            
            $soalData = $this->processExcelData($data, $request->type);
            
            if (empty($soalData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data soal yang valid ditemukan. Pastikan format sesuai template.'
                ], 400);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diimport',
                'data' => $soalData,
                'count' => count($soalData)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error importing soal: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function prepareVideoData($material)
{
    $videoData = [
        'type' => $material->video_type ?? 'youtube',
        'url' => $material->video_url ?? '',
        'embed_url' => null,
        'direct_link' => null,
        'is_local' => false,
        'is_hosted' => false,
        'video_info' => null,
        'player_config' => $material->player_config ? json_decode($material->player_config, true) : [],
        'duration' => $material->duration,
        'auto_play' => false,
        'controls' => true,
        'player_type' => 'videojs'
    ];
    
    // YouTube
    if ($material->video_type === 'youtube' && $material->video_url) {
        $videoId = $this->extractYouTubeId($material->video_url);
        if ($videoId) {
            $videoData['embed_url'] = "https://www.youtube.com/embed/{$videoId}?rel=0&modestbranding=1&showinfo=0";
            $videoData['direct_link'] = "https://www.youtube.com/watch?v={$videoId}";
        }
    }
    
    // Google Drive (hosted)
    elseif ($material->video_type === 'hosted' && $material->video_file) {
        try {
            $videoInfo = json_decode($material->video_file, true);
            if ($videoInfo) {
                $videoData['is_hosted'] = true;
                $videoData['video_info'] = $videoInfo;
                
                if (isset($videoInfo['embed_link'])) {
                    $videoData['embed_url'] = $videoInfo['embed_link'];
                    $videoData['direct_link'] = $videoInfo['web_view_link'] ?? $videoInfo['embed_link'];
                }
            }
        } catch (\Exception $e) {
            Log::error('Error preparing hosted video data: ' . $e->getMessage());
        }
    }
    
    // Local video
    elseif ($material->video_type === 'local' && $material->video_file) {
        try {
            $videoInfo = json_decode($material->video_file, true);
            if ($videoInfo) {
                $videoData['is_local'] = true;
                $videoData['video_info'] = $videoInfo;
                $videoData['direct_link'] = $videoInfo['url'] ?? Storage::url($videoInfo['path'] ?? '');
                $videoData['player_type'] = 'videojs';
                
                if (isset($videoInfo['size']) && $videoInfo['size'] > 100 * 1024 * 1024) {
                    $videoData['use_hls'] = true;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error preparing local video data: ' . $e->getMessage());
        }
    }
    
    return $videoData;
}

/**
 * Download template Excel untuk soal
 */
public function downloadTemplate()
{
    try {
        // Buat spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set header TANPA POIN
        $headers = ['PERTANYAAN', 'PILIHAN_A', 'PILIHAN_B', 'PILIHAN_C', 'PILIHAN_D', 'JAWABAN_BENAR'];
        
        foreach ($headers as $index => $header) {
            $col = chr(65 + $index);
            $sheet->setCellValue($col . '1', $header);
            
            // Style header
            $sheet->getStyle($col . '1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3C72']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);
        }
        
        // Contoh data TANPA POIN
        $examples = [
            [
                'Apa ibu kota Indonesia?',
                'Jakarta',
                'Bandung',
                'Surabaya',
                'Yogyakarta',
                'A'
            ],
            [
                'Siapa presiden pertama Indonesia?',
                'Soekarno',
                'Soeharto',
                'BJ Habibie',
                'Gus Dur',
                'A'
            ],
            [
                'Tanggal berapa Indonesia merdeka?',
                '17 Agustus 1945',
                '27 Desember 1949',
                '1 Juni 1945',
                '10 November 1945',
                'A'
            ]
        ];
        
        // Tambahkan contoh data
        $row = 2;
        foreach ($examples as $example) {
            foreach ($example as $index => $value) {
                $col = chr(65 + $index);
                $sheet->setCellValue($col . $row, $value);
            }
            $row++;
        }
        
        // Set width columns
        $sheet->getColumnDimension('A')->setWidth(40); // Pertanyaan
        $sheet->getColumnDimension('B')->setWidth(20); // Pilihan A
        $sheet->getColumnDimension('C')->setWidth(20); // Pilihan B
        $sheet->getColumnDimension('D')->setWidth(20); // Pilihan C
        $sheet->getColumnDimension('E')->setWidth(20); // Pilihan D
        $sheet->getColumnDimension('F')->setWidth(15); // Jawaban Benar
        
        // Data validation untuk kolom jawaban
        $validation = $sheet->getCell('F2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setFormula1('"A,B,C,D"');
        $validation->setShowDropDown(true);
        $validation->setAllowBlank(false);
        
        // Terapkan ke semua baris contoh
        for ($i = 2; $i <= 4; $i++) {
            $sheet->getCell('F' . $i)->setDataValidation(clone $validation);
        }
        
        // Simpan ke temporary file
        $filename = 'template-soal-' . date('YmdHis') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);
        
        // Pastikan directory exists
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);
        
        // Download file
        return response()->download($tempPath, 'template-soal.xlsx')->deleteFileAfterSend(true);
        
    } catch (\Exception $e) {
        Log::error('Error creating template: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal membuat template: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Process data dari Excel
     */
    private function processExcelData($rows, string $type): array
{
    $soalData = [];
    
    foreach ($rows as $index => $row) {
        // Skip jika baris kosong
        if (empty($row) || !isset($row['pertanyaan']) || empty(trim($row['pertanyaan']))) {
            continue;
        }
        
        // Format data soal TANPA POIN
        $soal = [
            'id' => count($soalData) + 1,
            'pertanyaan' => trim($row['pertanyaan']),
            'pilihan' => [
                isset($row['pilihan_a']) ? trim($row['pilihan_a']) : '',
                isset($row['pilihan_b']) ? trim($row['pilihan_b']) : '',
                isset($row['pilihan_c']) ? trim($row['pilihan_c']) : '',
                isset($row['pilihan_d']) ? trim($row['pilihan_d']) : '',
            ],
            'jawaban_benar' => $this->convertJawabanToIndex($row['jawaban_benar'] ?? 'A'),
            'row_number' => $index + 2
        ];
        
        // Validasi: pastikan ada minimal 2 pilihan yang tidak kosong
        $validPilihan = array_filter($soal['pilihan'], function($pilihan) {
            return !empty(trim($pilihan));
        });
        
        if (count($validPilihan) < 2) {
            Log::warning("Soal pada baris {$soal['row_number']} diabaikan: kurang dari 2 pilihan yang valid");
            continue;
        }
        
        $soalData[] = $soal;
        
        // Batasi maksimal 500 soal untuk performance
        if (count($soalData) >= 500) {
            Log::info("Mencapai batas maksimal 500 soal, menghentikan proses import");
            break;
        }
    }
    
    Log::info("Berhasil memproses " . count($soalData) . " soal dari Excel");
    return $soalData;
}

    /**
     * Convert jawaban (A/B/C/D) ke index (0/1/2/3)
     */
    private function convertJawabanToIndex($jawaban): int
    {
        if (empty($jawaban)) {
            return 0;
        }
        
        $jawaban = strtoupper(trim($jawaban));
        
        switch ($jawaban) {
            case 'A': return 0;
            case 'B': return 1;
            case 'C': return 2;
            case 'D': return 3;
            default: return 0;
        }
    }

    /**
     * Get video duration (optional - untuk implementasi nanti)
     */
public function getVideoDuration($videoFile)
{
    try {
        if ($videoFile instanceof \Illuminate\Http\UploadedFile) {
            $tempPath = $videoFile->getRealPath();
        } else {
            $tempPath = $videoFile;
        }
        
        $ffprobe = FFProbe::create();
        $duration = $ffprobe
            ->format($tempPath)
            ->get('duration');
            
        return floor($duration);
    } catch (\Exception $e) {
        // PERBAIKAN: Gunakan facade dengan benar
        \Illuminate\Support\Facades\Log::error('Error getting video duration: ' . $e->getMessage());
        return 0;
    }
}
}
