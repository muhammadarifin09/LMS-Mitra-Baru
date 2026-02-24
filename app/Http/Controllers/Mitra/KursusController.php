<?php

namespace App\Http\Controllers\Mitra;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Kursus;
use App\Models\Materials;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use App\Models\UserVideoQuestionAnswer;
use App\Models\VideoQuestion;
use App\Models\MaterialProgress;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;


class KursusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $kursus = Kursus::where('status', 'aktif')
                        ->orderBy('created_at', 'desc')
                        ->get();
        
        return view('mitra.kursus', compact('kursus'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kursus = Kursus::where('status', 'aktif')
                        ->where('id', $id)
                        ->with(['materials' => function($query) {
                            // HANYA ambil materi yang AKTIF
                            $query->where('is_active', true)
                                  ->orderBy('order');
                        }])
                        ->firstOrFail();

        $user = Auth::user();
        
        // Cek enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
                            ->where('kursus_id', $id)
                            ->firstOrFail();

        // Process materials dengan status (hanya yang aktif)
        $materials = $this->getMaterialsWithStatus($kursus->materials, $user, $id);
        
        // Calculate progress
        $totalMaterials = count($materials);
        $completedMaterials = 0;
        
        foreach ($materials as $material) {
            if ($material['status'] === 'completed') {
                $completedMaterials++;
            }
        }
        
        $progressPercentage = $totalMaterials > 0 ? round(($completedMaterials / $totalMaterials) * 100) : 0;

        return view('mitra.kursus-detail', compact(
            'kursus',
            'enrollment', 
            'materials',
            'progressPercentage',
            'completedMaterials',
            'totalMaterials'
        ));
    }

    // Untuk halaman kursus saya (yang sudah di-enroll) + FILTER
    public function kursusSaya(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $userId = Auth::id();

        $enrollments = Enrollment::where('user_id', $userId)
            ->with(['kursus' => function($query) {
                $query->where('status', 'aktif')
                    ->with(['materials' => function($q) {
                        $q->where('is_active', true)
                            ->orderBy('order');
                    }]);
            }])
            ->whereHas('kursus', function($query) {
                $query->where('status', 'aktif');
            });

        // Apply filter
        if ($filter === 'in_progress') {
            $enrollments->where('progress_percentage', '<', 100);
        }

        if ($filter === 'completed') {
            $enrollments->where('progress_percentage', '=', 100);
        }

        $enrollments = $enrollments->orderBy('updated_at', 'desc')
                                ->get();

        // Hitung progress untuk setiap enrollment
        foreach ($enrollments as $enrollment) {
            if ($enrollment->kursus && $enrollment->kursus->materials) {
                // Gunakan method yang sama dengan halaman detail
                $materials = $this->getMaterialsWithStatus($enrollment->kursus->materials, Auth::user(), $enrollment->kursus_id);
                
                $totalMaterials = count($materials);
                $completedMaterials = collect($materials)->where('status', 'completed')->count();
                $progressPercentage = $totalMaterials > 0 ? round(($completedMaterials / $totalMaterials) * 100) : 0;
                
                // Update enrollment
                $enrollment->completed_activities = $completedMaterials;
                $enrollment->total_activities = $totalMaterials;
                $enrollment->progress_percentage = $progressPercentage;
                $enrollment->status = $progressPercentage == 100 ? 'completed' : 'in_progress';
            }
        }

        return view('mitra.kursus-saya', compact('enrollments', 'filter'));
    }

    private function getDurationWithFFprobe($filePath)
{
    try {
        // Cari ffprobe di berbagai lokasi umum
        $possiblePaths = [
            'ffprobe',
            '/usr/bin/ffprobe',
            '/usr/local/bin/ffprobe',
            '/opt/homebrew/bin/ffprobe',
            '/opt/local/bin/ffprobe',
            'C:\ffmpeg\bin\ffprobe.exe',
            'C:\Program Files\ffmpeg\bin\ffprobe.exe',
            storage_path('ffmpeg/bin/ffprobe.exe'),
            base_path('vendor/bin/ffprobe')
        ];
        
        $ffprobePath = null;
        foreach ($possiblePaths as $path) {
            $testCommand = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' 
                ? "where $path 2>nul" 
                : "command -v $path 2>/dev/null";
            
            $result = shell_exec($testCommand);
            if ($result && trim($result)) {
                $ffprobePath = $path;
                break;
            }
        }
        
        if (!$ffprobePath) {
            Log::warning('FFprobe not found in any common location');
            return 0;
        }
        
        Log::info('Found ffprobe at:', ['path' => $ffprobePath]);
        
        // Eksekusi ffprobe
        $command = escapeshellcmd($ffprobePath) . 
                  ' -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 ' . 
                  escapeshellarg($filePath) . ' 2>&1';
        
        Log::debug('Executing ffprobe command:', ['command' => $command]);
        
        $output = shell_exec($command);
        Log::debug('FFprobe output:', ['output' => $output]);
        
        if ($output && is_numeric(trim($output))) {
            $duration = (float)trim($output);
            Log::info('✅ FFprobe duration found:', ['duration' => $duration]);
            return $duration;
        }
        
    } catch (\Exception $e) {
        Log::error('FFprobe error:', ['error' => $e->getMessage()]);
    }
    
    return 0;
}

private function getDurationWithPHPFFmpeg($filePath)
{
    try {
        if (class_exists('\FFMpeg\FFMpeg')) {
            $ffmpeg = \FFMpeg\FFMpeg::create();
            $video = $ffmpeg->open($filePath);
            $duration = $video->getStreams()->first()->get('duration');
            
            if ($duration) {
                Log::info('✅ PHP-FFMpeg duration found:', ['duration' => $duration]);
                return (float)$duration;
            }
        }
    } catch (\Exception $e) {
        Log::warning('PHP-FFMpeg not available or error:', ['error' => $e->getMessage()]);
    }
    
    return 0;
}

private function getWebMDuration($filePath)
{
    try {
        // WebM menggunakan EBML format, kompleks untuk parsing manual
        // Lebih baik gunakan library atau shell command
        return 0; // Return 0 untuk trigger metode lain
    } catch (\Exception $e) {
        return 0;
    }
}

private function getMP4DurationManually($filePath)
{
    try {
        $file = fopen($filePath, 'rb');
        if (!$file) {
            Log::warning('Cannot open MP4 file for reading');
            return 0;
        }
        
        $size = filesize($filePath);
        $offset = 0;
        
        while ($offset < $size) {
            fseek($file, $offset);
            
            // Baca atom size (4 bytes big-endian)
            $atomSizeData = fread($file, 4);
            if (strlen($atomSizeData) < 4) break;
            
            $atomSize = unpack('N', $atomSizeData)[1];
            if ($atomSize < 1) break;
            
            // Baca atom type (4 bytes)
            $atomType = fread($file, 4);
            
            Log::debug('MP4 Atom:', [
                'offset' => $offset,
                'size' => $atomSize,
                'type' => $atomType
            ]);
            
            // Cari 'moov' atom
            if ($atomType == 'moov') {
                $moovStart = $offset + 8;
                $moovEnd = $moovStart + $atomSize - 8;
                
                // Cari 'mvhd' atom di dalam 'moov'
                fseek($file, $moovStart);
                while (ftell($file) < $moovEnd) {
                    $innerSize = unpack('N', fread($file, 4))[1];
                    $innerType = fread($file, 4);
                    
                    if ($innerType == 'mvhd') {
                        // Skip version (1 byte) dan flags (3 bytes)
                        fread($file, 4);
                        
                        // Baca creation time dan modification time (masing-masing 4 bytes)
                        fread($file, 8);
                        
                        // Baca timescale (4 bytes)
                        $timescale = unpack('N', fread($file, 4))[1];
                        
                        // Baca duration (4 bytes)
                        $duration = unpack('N', fread($file, 4))[1];
                        
                        fclose($file);
                        
                        if ($timescale > 0) {
                            $result = $duration / $timescale;
                            Log::info('✅ MP4 manual duration found:', [
                                'duration' => $result,
                                'timescale' => $timescale
                            ]);
                            return $result;
                        }
                    } else {
                        // Skip ke atom berikutnya
                        fseek($file, ftell($file) + $innerSize - 8);
                    }
                }
            }
            
            $offset += $atomSize;
        }
        
        fclose($file);
    } catch (\Exception $e) {
        Log::error('Error reading MP4 manually:', ['error' => $e->getMessage()]);
    }
    
    return 0;
}

    /**
 * Helper untuk membaca durasi video dari file dengan beberapa metode
 */
private function getVideoDurationFromFile($filePath)
{
    if (!file_exists($filePath)) {
        Log::warning('Video file does not exist:', ['path' => $filePath]);
        return 0;
    }
    
    // Log info file
    Log::info('Reading video duration:', [
        'file_path' => $filePath,
        'file_size' => filesize($filePath),
        'file_exists' => true,
        'mime_type' => mime_content_type($filePath)
    ]);
    
    // METODE 1: getID3 (Library PHP terbaik)
    if (class_exists('\getID3')) {
        try {
            $getID3 = new \getID3();
            $fileInfo = $getID3->analyze($filePath);
            
            Log::info('getID3 analysis result:', [
                'playtime_seconds' => $fileInfo['playtime_seconds'] ?? null,
                'fileformat' => $fileInfo['fileformat'] ?? null,
                'mime_type' => $fileInfo['mime_type'] ?? null,
                'has_video' => isset($fileInfo['video']),
                'has_audio' => isset($fileInfo['audio'])
            ]);
            
            if (isset($fileInfo['playtime_seconds'])) {
                $duration = (float)$fileInfo['playtime_seconds'];
                Log::info('✅ getID3 duration found:', ['duration' => $duration]);
                return $duration;
            }
        } catch (\Exception $e) {
            Log::error('getID3 error:', ['error' => $e->getMessage()]);
        }
    } else {
        Log::warning('getID3 class not available');
    }
    
    // METODE 2: FFprobe (jika terinstall di server)
    $ffprobeDuration = $this->getDurationWithFFprobe($filePath);
    if ($ffprobeDuration > 0) {
        return $ffprobeDuration;
    }
    
    // METODE 3: PHP FFmpeg (library alternatif)
    $ffmpegDuration = $this->getDurationWithPHPFFmpeg($filePath);
    if ($ffmpegDuration > 0) {
        return $ffmpegDuration;
    }
    
    // METODE 4: Untuk MP4 files - baca manual
    $mimeType = mime_content_type($filePath);
    if (strpos($mimeType, 'mp4') !== false) {
        $mp4Duration = $this->getMP4DurationManually($filePath);
        if ($mp4Duration > 0) {
            return $mp4Duration;
        }
    }
    
    // METODE 5: Untuk WebM files
    if (strpos($mimeType, 'webm') !== false) {
        $webmDuration = $this->getWebMDuration($filePath);
        if ($webmDuration > 0) {
            return $webmDuration;
        }
    }
    
    Log::warning('❌ Could not determine video duration with any method');
    return 0;
}


    /**
     * Helper method untuk mendapatkan content types dari learning_objectives
     */
    private function getContentTypes($learningObjectives)
    {
        if (empty($learningObjectives)) {
            return [];
        }
        
        // Jika sudah array, langsung return
        if (is_array($learningObjectives)) {
            return $learningObjectives;
        }
        
        // Jika string, coba decode JSON
        if (is_string($learningObjectives)) {
            try {
                $decoded = json_decode($learningObjectives, true);
                return is_array($decoded) ? $decoded : [];
            } catch (\Exception $e) {
                Log::error('Error decoding learning_objectives: ' . $e->getMessage());
                return [];
            }
        }
        
        return [];
    }

    /**
     * Helper method untuk parse JSON data dengan aman
     */
    private function safeJsonDecode($data, $default = null)
    {
        if (is_null($data)) {
            return $default;
        }
        
        if (is_array($data)) {
            return $data;
        }
        
        if (is_string($data)) {
            try {
                $decoded = json_decode($data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('JSON decode error:', [
                        'error' => json_last_error_msg(),
                        'data' => substr($data, 0, 200)
                    ]);
                    return $default;
                }
                return $decoded ?? $default;
            } catch (\Exception $e) {
                Log::error('Error decoding JSON: ' . $e->getMessage());
                return $default;
            }
        }
        
        return $default;
    }

    /**
     * Helper method untuk parse file_path dengan aman
     */
    private function parseFilePath($filePath)
    {
        if (empty($filePath)) {
            return [];
        }
        
        // Jika sudah array, langsung return
        if (is_array($filePath)) {
            return array_filter($filePath);
        }
        
        // Jika string, coba decode JSON
        if (is_string($filePath)) {
            try {
                $decoded = json_decode($filePath, true);
                if (is_array($decoded)) {
                    return array_filter($decoded);
                }
                // Jika decode menghasilkan string (bukan array), kembalikan sebagai array
                return array_filter([$filePath]);
            } catch (\Exception $e) {
                Log::error('Error parsing file_path: ' . $e->getMessage());
                return array_filter([$filePath]);
            }
        }
        
        return [];
    }

    private function detectContentTypesFromMaterial($material)
    {
        $types = [];
        
        // Deteksi berdasarkan kolom yang ada
        if (!empty($material->file_path)) {
            $types[] = 'file';
        }
        
        if (!empty($material->video_url) || !empty($material->video_file)) {
            $types[] = 'video';
        }
        
        if ($material->attendance_required ?? true) {
            $types[] = 'attendance';
        }
        
        // Deteksi test types
        if (!empty($material->soal_pretest)) {
            $types[] = 'pretest';
        }
        
        if (!empty($material->soal_posttest)) {
            $types[] = 'posttest';
        }
        
        // Deteksi recap
        if ($material->type === 'recap') {
            $types[] = 'recap';
        }
        
        return $types;
    }
    
    /**
     * Helper method untuk cek apakah material memiliki konten file
     */
    private function hasFileContent($material, $contentTypes = null)
    {
        // Jika ada contentTypes yang ditentukan, gunakan itu
        if ($contentTypes !== null) {
            return in_array('file', $contentTypes);
        }
        
        // Jika tidak ada contentTypes, cek langsung dari material
        return !empty($material->file_path);
    }
    
    /**
     * Helper method untuk cek apakah material memiliki konten video
     */
    private function hasVideoContent($material, $contentTypes = null)
    {
        // Jika ada contentTypes yang ditentukan, gunakan itu
        if ($contentTypes !== null) {
            return in_array('video', $contentTypes);
        }
        
        // Jika tidak ada contentTypes, cek langsung dari material
        return !empty($material->video_url) || !empty($material->video_file);
    }
    
    /**
     * Debug video data dari database
     */
    private function debugVideoData($material)
    {
        Log::debug('Video Database Raw Data:', [
            'material_id' => $material->id,
            'title' => $material->title,
            'video_type' => $material->video_type,
            'video_url' => $material->video_url,
            'video_file_raw_length' => strlen($material->video_file ?? ''),
            'video_file_sample' => substr($material->video_file ?? '', 0, 200),
            'json_valid' => json_decode($material->video_file ?? '') !== null,
            'json_last_error' => json_last_error(),
            'json_last_error_msg' => json_last_error_msg(),
            'learning_objectives' => $material->learning_objectives,
            'is_active' => $material->is_active
        ]);
    }
    
    /**
     * Cek apakah video hosted (Google Drive) tersedia
     */
    private function isHostedVideoAvailable($videoData, $materialId = null)
    {
        if (!$videoData || !is_array($videoData)) {
            Log::warning('Google Drive video data not array or empty', [
                'material_id' => $materialId,
                'video_data_type' => gettype($videoData)
            ]);
            return false;
        }
        
        // Cek berbagai format data Google Drive
        $hasEmbedLink = isset($videoData['embed_link']) && !empty($videoData['embed_link']);
        $hasWebViewLink = isset($videoData['web_view_link']) && !empty($videoData['web_view_link']);
        $hasFileId = isset($videoData['file_id']) && !empty($videoData['file_id']);
        $hasId = isset($videoData['id']) && !empty($videoData['id']);
        $hasDirectLink = isset($videoData['direct_link']) && !empty($videoData['direct_link']);
        
        // Jika ada minimal salah satu, video tersedia
        $isAvailable = $hasEmbedLink || $hasWebViewLink || $hasFileId || $hasId || $hasDirectLink;
        
        Log::info('Google Drive Video Availability Check:', [
            'material_id' => $materialId,
            'has_embed_link' => $hasEmbedLink,
            'has_web_view_link' => $hasWebViewLink,
            'has_file_id' => $hasFileId,
            'has_id' => $hasId,
            'has_direct_link' => $hasDirectLink,
            'available_keys' => array_keys($videoData),
            'is_available' => $isAvailable
        ]);
        
        return $isAvailable;
    }
    
    /**
     * Cek apakah video lokal tersedia
     */
    private function isLocalVideoAvailable($videoData, $materialId = null)
    {
        if (!$videoData || !is_array($videoData)) {
            Log::warning('Local video data not array or empty', [
                'material_id' => $materialId,
                'video_data_type' => gettype($videoData)
            ]);
            return false;
        }
        
        // Cek berbagai format data video lokal
        $hasPath = isset($videoData['path']) && !empty($videoData['path']);
        $hasUrl = isset($videoData['url']) && !empty($videoData['url']);
        
        // Jika ada path, cek apakah file benar-benar ada
        if ($hasPath) {
            $fileExists = Storage::disk('public')->exists($videoData['path']);
            Log::info('Local Video File Check:', [
                'material_id' => $materialId,
                'path' => $videoData['path'],
                'file_exists' => $fileExists
            ]);
            return $fileExists;
        }
        
        // Jika ada URL langsung
        if ($hasUrl) {
            return true;
        }
        
        Log::warning('Local video data incomplete:', [
            'material_id' => $materialId,
            'video_data' => $videoData
        ]);
        return false;
    }

    /**
     * Helper method untuk cek semua materi sebelumnya sudah selesai
     */
    private function allPreviousMaterialsCompleted($userId, $kursusId, $currentOrder)
    {
        if ($currentOrder <= 1) {
            return true;
        }
        
        // Ambil semua materi sebelumnya yang AKTIF
        $previousMaterials = Materials::where('course_id', $kursusId)
            ->where('is_active', true)
            ->where('order', '<', $currentOrder)
            ->orderBy('order')
            ->get();
        
        foreach ($previousMaterials as $material) {
            $progress = MaterialProgress::where('user_id', $userId)
                ->where('material_id', $material->id)
                ->first();
            
            if (!$this->isMaterialCompleted($progress, $material)) {
                return false;
            }
        }
        
        return true;
    }

    private function isMaterialCompleted($progress, $material)
{
    if (!$progress) {
        return false;
    }

    $contentTypes = $this->getContentTypes($material->learning_objectives);
    $isPretest = in_array('pretest', $contentTypes);
    $isPosttest = in_array('posttest', $contentTypes);
    $isRecap = $material->type === 'recap';

    // For test materials
    if ($isPretest) {
        return $progress->pretest_score !== null;
    } elseif ($isPosttest) {
        return $progress->posttest_score !== null;
    } elseif ($isRecap) {
        return true;
    }
    
    // Untuk material reguler
    $hasFile = in_array('file', $contentTypes);
    $hasVideo = in_array('video', $contentTypes);
    $hasAttendance = in_array('attendance', $contentTypes) || ($material->attendance_required ?? true);
    
    $attendanceCompleted = !$hasAttendance || $progress->attendance_status === 'completed';
    
    // Cek file completion
    $fileCompleted = true;
    if ($hasFile && !empty($material->file_path)) {
        $filePaths = $this->parseFilePath($material->file_path);
        $totalFiles = count($filePaths);
        
        if ($totalFiles > 0) {
            if ($progress->all_files_downloaded) {
                $fileCompleted = true;
            } else {
                $downloadedFiles = $this->safeJsonDecode($progress->downloaded_files, []);
                $fileCompleted = (count($downloadedFiles) >= $totalFiles);
            }
        }
    }
    
    // Cek video completion - PERBAIKAN: HARUS 100%
    $videoCompleted = true;
    if ($hasVideo) {
        // VIDEO HARUS DITONTON 100% - TIDAK ADA KOMPROMI
        $videoWatchedCompleted = false;
        if ($material->require_video_completion) {
            $minWatchPercentage = 100; // UBAH DARI 90 KE 100
            $videoProgress = $progress->video_progress ?? 0;
            $videoWatchedCompleted = ($videoProgress >= $minWatchPercentage);
        } else {
            // Default juga harus 100%
            $videoWatchedCompleted = ($progress->video_progress ?? 0) >= 100;
        }
        
        // Cek pertanyaan video
        $videoQuestionsCompleted = true;
        if ($material->has_video_questions && $material->total_video_points > 0) {
            // Cek apakah semua pertanyaan video sudah terjawab
            $totalQuestions = $material->question_count ?? VideoQuestion::where('material_id', $material->id)->count();
            $answeredQuestions = UserVideoQuestionAnswer::where('user_id', $progress->user_id)
                ->where('material_id', $material->id)
                ->count();
            
            $videoQuestionsCompleted = ($answeredQuestions >= $totalQuestions);
            
            // Log untuk debugging
            Log::info('Video Questions Completion Check:', [
                'material_id' => $material->id,
                'user_id' => $progress->user_id,
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'is_completed' => $videoQuestionsCompleted
            ]);
        }
        
        // Video selesai hanya jika video ditonton 100% DAN semua pertanyaan terjawab
        $videoCompleted = $videoWatchedCompleted && $videoQuestionsCompleted;
        
        Log::info('Video Completion Check:', [
            'material_id' => $material->id,
            'user_id' => $progress->user_id,
            'video_progress' => $progress->video_progress ?? 0,
            'video_watched_completed' => $videoWatchedCompleted,
            'video_questions_completed' => $videoQuestionsCompleted,
            'final_video_completed' => $videoCompleted
        ]);
    }
    
    $finalResult = $attendanceCompleted && $fileCompleted && $videoCompleted;
    
    Log::info('Material Completion Final Check:', [
        'material_id' => $material->id,
        'user_id' => $progress->user_id,
        'attendance_completed' => $attendanceCompleted,
        'file_completed' => $fileCompleted,
        'video_completed' => $videoCompleted,
        'final_result' => $finalResult
    ]);
    
    return $finalResult;
}

/**
 * Cek apakah video dan pertanyaan sudah selesai
 */
private function isVideoAndQuestionsCompleted($userId, $materialId)
{
    $material = Materials::find($materialId);
    if (!$material) {
        Log::warning('Material not found', ['material_id' => $materialId]);
        return false;
    }
    
    $progress = MaterialProgress::where('user_id', $userId)
        ->where('material_id', $materialId)
        ->first();
    
    if (!$progress) {
        Log::warning('Progress not found', [
            'user_id' => $userId,
            'material_id' => $materialId
        ]);
        return false;
    }
    
    // VIDEO HARUS DITONTON 100%
    $minWatchPercentage = 100; // UBAH DARI 90 KE 100
    $videoProgress = $progress->video_progress ?? 0;
    $videoWatched = ($videoProgress >= $minWatchPercentage);
    
    // Cek semua pertanyaan video terjawab
    $totalQuestions = VideoQuestion::where('material_id', $materialId)->count();
    $answeredQuestions = UserVideoQuestionAnswer::where('user_id', $userId)
        ->where('material_id', $materialId)
        ->count();
    
    $questionsAnswered = ($totalQuestions === 0) || ($answeredQuestions >= $totalQuestions);
    
    $result = $videoWatched && $questionsAnswered;
    
    Log::info('Video and Questions Completion Check:', [
        'material_id' => $materialId,
        'user_id' => $userId,
        'video_progress' => $videoProgress,
        'video_watched' => $videoWatched,
        'total_questions' => $totalQuestions,
        'answered_questions' => $answeredQuestions,
        'questions_answered' => $questionsAnswered,
        'final_result' => $result
    ]);
    
    return $result;
}


    /**
     * Enroll to course
     */
 public function enroll(Request $request, $id)
{
    $kursus = Kursus::where('status', 'aktif')
                    ->where('id', $id)
                    ->firstOrFail();

    $user = Auth::user();

    // ❌ Sudah ikut kursus
    $alreadyEnrolled = Enrollment::where('user_id', $user->id)
                               ->where('kursus_id', $id)
                               ->exists();

    if ($alreadyEnrolled) {
        return redirect()->back()
            ->with('error', 'Anda sudah mengikuti kursus ini.')
            ->with('error_type', 'enrollment');
    }

    // ❌ Kuota penuh
    if ($kursus->kuota_peserta &&
        $kursus->peserta_terdaftar >= $kursus->kuota_peserta) {
        return redirect()->back()
            ->with('error', 'Maaf, kuota kursus sudah penuh.')
            ->with('error_type', 'quota');
    }

    // 🔐 ENROLL CODE (PASSWORD KURSUS)
    if (!empty($kursus->enroll_code)) {

        // jika kursus pakai kode tapi user tidak mengisi
        if (!$request->filled('enroll_code')) {
            return redirect()->back()
                ->with('error', 'Kode enroll wajib diisi untuk kursus ini.')
                ->with('error_type', 'enroll_code')
                ->with('enroll_course_id', $id) // Simpan ID kursus
                ->with('attempted_code', $request->enroll_code);
        }

        // jika kode salah
        if ($request->enroll_code !== $kursus->enroll_code) {
            return redirect()->back()
                ->with('error', 'Kode enroll yang Anda masukkan salah. Silakan masukkan kembali kode yang benar.')
                ->with('error_type', 'enroll_code')
                ->with('enroll_course_id', $id) // Simpan ID kursus
                ->with('attempted_code', $request->enroll_code); // Simpan kode yang dimasukkan
        }
    }

    try {
        // Hitung total materi aktif
        $totalMaterials = Materials::where('course_id', $id)
                                    ->where('is_active', true)
                                    ->count();

        // ✅ Create enrollment
        Enrollment::create([
            'user_id' => $user->id,
            'kursus_id' => $id,
            'total_activities' => $totalMaterials,
            'enrolled_at' => now()
        ]);

        // ✅ Tambah peserta terdaftar
        $kursus->increment('peserta_terdaftar');

        return redirect()->route('mitra.kursus.saya')
            ->with('success', 'Berhasil mengikuti kursus!');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
            ->with('error_type', 'system');
    }
}


    /**
     * My courses
     */
    public function myCourses()
    {
        $user = Auth::user();
        
        $enrolledCourses = Enrollment::with('kursus')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mitra.kursus-saya', compact('enrolledCourses'));
    }

    // MARK: - Progress Tracking Methods
    public function markAttendance(Request $request, $kursus, $material)
    {
        Log::info('Mark Attendance:', [
            'kursus' => $kursus,
            'material' => $material,
            'user_id' => Auth::id()
        ]);

        $user = Auth::user();
        
        // Validasi user terenroll di kursus ini
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('kursus_id', $kursus)
            ->firstOrFail();

        $materialRecord = Materials::where('is_active', true)
            ->where('id', $material)
            ->where('course_id', $kursus)
            ->firstOrFail();

        // Update atau buat progress
        $progress = MaterialProgress::where('user_id', $user->id)
            ->where('material_id', $material)
            ->first();
        
        if ($progress) {
            $progress->attendance_status = 'completed';
            $progress->save();
        } else {
            $progress = MaterialProgress::create([
                'user_id' => $user->id,
                'material_id' => $material,
                'attendance_status' => 'completed',
                'material_status' => 'pending',
                'video_status' => 'pending'
            ]);
        }

        // PERBAIKAN: Otomatis cek dan unlock material berikutnya
        $this->checkAndUnlockNextMaterial($user->id, $material, $kursus);

        // Update enrollment progress
        $this->updateEnrollmentProgress($user->id, $kursus);

        return response()->json([
            'success' => true,
            'message' => 'Kehadiran berhasil dicatat',
            'material_id' => $material,
            'attendance_status' => 'completed'
        ]);
    }

    // MARK: - File Download Methods
    /**
     * Download material file (ZIP atau single file)
     */
    public function downloadMaterialFile($kursus, $material)
    {
        Log::info('Download Material File:', [
            'kursus' => $kursus,
            'material' => $material,
            'user_id' => Auth::id()
        ]);
        
        $user = Auth::user();
        
        // Pastikan user terenroll di kursus ini
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('kursus_id', $kursus)
            ->firstOrFail();
        
        // Ambil materi yang aktif
        $materialRecord = Materials::where('is_active', true)
            ->where('id', $material)
            ->where('course_id', $kursus)
            ->firstOrFail();
        
        // Cek apakah materi memiliki file
        if (!$materialRecord->file_path || empty($materialRecord->file_path)) {
            abort(404, 'File materi tidak tersedia');
        }
        
        try {
            // PERBAIKAN: Parse file_path dengan helper method
            $filePaths = $this->parseFilePath($materialRecord->file_path);
            
            // Filter hanya file yang valid
            $validFilePaths = [];
            foreach ($filePaths as $filePath) {
                if (Storage::disk('public')->exists($filePath)) {
                    $validFilePaths[] = $filePath;
                }
            }
            
            if (empty($validFilePaths)) {
                abort(404, 'Tidak ada file yang tersedia');
            }
            
            // Catat SEMUA file sebagai downloaded
            $this->recordAllFilesDownloaded($user->id, $material, count($validFilePaths));
            
            // PERBAIKAN: Otomatis cek dan unlock material berikutnya
            $this->checkAndUnlockNextMaterial($user->id, $material, $kursus);
            
            // Update enrollment progress
            $this->updateEnrollmentProgress($user->id, $kursus);
            
            // Jika hanya ada 1 file, download langsung
            if (count($validFilePaths) === 1) {
                $filePath = $validFilePaths[0];
                $fileName = basename($filePath);
                
                return Storage::disk('public')->download($filePath, $fileName);
            }
            
            // Jika ada multiple files, buat zip archive
            return $this->createZipDownload($validFilePaths, $materialRecord->title);
            
        } catch (\Exception $e) {
            Log::error('Error downloading material file:', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'material' => $material
            ]);
            
            return back()->with('error', 'Gagal mengunduh file: ' . $e->getMessage());
        }
    }

    /**
     * Membuat zip archive dari multiple files
     */
    private function createZipDownload(array $filePaths, string $materialTitle)
    {
        $zipFileName = 'materi-' . Str::slug($materialTitle) . '-' . time() . '.zip';
        $zipPath = storage_path('app/public/temp/' . $zipFileName);
        
        if (!Storage::disk('public')->exists('temp')) {
            Storage::disk('public')->makeDirectory('temp');
        }

        $zip = new ZipArchive;
        
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($filePaths as $filePath) {
                $fileName = basename($filePath);
                $zip->addFile(Storage::disk('public')->path($filePath), $fileName);
            }
            
            $zip->close();
            
            return response()->download($zipPath)->deleteFileAfterSend(true);
        } else {
            throw new \Exception('Gagal membuat file zip');
        }
    }

    private function recordAllFilesDownloaded($userId, $materialId, $totalFiles)
    {
        try {
            $progress = MaterialProgress::where('user_id', $userId)
                ->where('material_id', $materialId)
                ->first();
            
            $downloadedFiles = range(0, $totalFiles - 1);
            
            if (!$progress) {
                MaterialProgress::create([
                    'user_id' => $userId,
                    'material_id' => $materialId,
                    'downloaded_files' => json_encode($downloadedFiles),
                    'total_files' => $totalFiles,
                    'all_files_downloaded' => true,
                    'material_status' => 'completed',
                    'attendance_status' => 'pending',
                    'video_status' => 'pending'
                ]);
            } else {
                $progress->update([
                    'downloaded_files' => json_encode($downloadedFiles),
                    'total_files' => $totalFiles,
                    'all_files_downloaded' => true,
                    'material_status' => 'completed'
                ]);
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error recording all files downloaded:', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'material_id' => $materialId
            ]);
            return false;
        }
    }

    /**
     * API untuk mencatat video telah ditonton
     */
    public function recordVideoWatched(Request $request, $kursus, $material)
    {
        try {
            $user = Auth::user();
            
            // Update progress video
            $progress = MaterialProgress::where('user_id', $user->id)
                ->where('material_id', $material)
                ->first();
            
            if ($progress) {
                $progress->video_status = 'completed';
                $progress->save();
            } else {
                $materialRecord = Materials::find($material);
                MaterialProgress::create([
                    'user_id' => $user->id,
                    'material_id' => $material,
                    'video_status' => 'completed',
                    'material_status' => 'pending',
                    'attendance_status' => 'pending'
                ]);
            }

            // PERBAIKAN: Otomatis cek dan unlock material berikutnya
            $this->checkAndUnlockNextMaterial($user->id, $material, $kursus);

            // Update enrollment progress
            $this->updateEnrollmentProgress($user->id, $kursus);

            return response()->json([
                'success' => true,
                'message' => 'Video telah ditandai sebagai telah ditonton'
            ]);

        } catch (\Exception $e) {
            Log::error('Error recording video watched:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'material' => $material
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat progress video'
            ], 500);
        }
    }

    /**
     * PERBAIKAN: Method untuk cek dan unlock material berikutnya
     */
    private function checkAndUnlockNextMaterial($userId, $currentMaterialId, $kursusId)
    {
        try {
            $currentMaterial = Materials::where('is_active', true)
                ->where('id', $currentMaterialId)
                ->where('course_id', $kursusId)
                ->first();
            
            if (!$currentMaterial) {
                return false;
            }
            
            // Cek apakah material saat ini sudah selesai
            $progress = MaterialProgress::where('user_id', $userId)
                ->where('material_id', $currentMaterialId)
                ->first();
            
            if (!$progress) {
                return false;
            }
            
            $isCurrentCompleted = $this->isMaterialCompleted($progress, $currentMaterial);
            
            if ($isCurrentCompleted) {
                // Cari material berikutnya
                $nextMaterial = Materials::where('course_id', $kursusId)
                    ->where('is_active', true)
                    ->where('order', '>', $currentMaterial->order)
                    ->orderBy('order')
                    ->first();
                
                if ($nextMaterial) {
                    Log::info('Material berikutnya siap dibuka:', [
                        'current_material_id' => $currentMaterialId,
                        'next_material_id' => $nextMaterial->id,
                        'next_material_order' => $nextMaterial->order
                    ]);
                    
                    return true;
                }
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Error checking and unlocking next material:', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'current_material_id' => $currentMaterialId,
                'kursus_id' => $kursusId
            ]);
            return false;
        }
    }

    /**
     * Method baru: Refresh status material untuk real-time update
     */
    public function refreshMaterialStatus($kursusId, $materialId)
    {
        try {
            $user = Auth::user();
            
            $kursus = Kursus::where('status', 'aktif')
                ->where('id', $kursusId)
                ->with(['materials' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('order');
                }])
                ->firstOrFail();
            
            // Cari material yang diminta
            $targetMaterial = null;
            foreach ($kursus->materials as $material) {
                if ($material->id == $materialId) {
                    $targetMaterial = $material;
                    break;
                }
            }
            
            if (!$targetMaterial) {
                return response()->json([
                    'success' => false,
                    'message' => 'Material tidak ditemukan'
                ], 404);
            }
            
            // Hitung ulang status untuk material ini dan berikutnya
            $userProgress = MaterialProgress::where('user_id', $user->id)
                ->whereIn('material_id', $kursus->materials->pluck('id'))
                ->get()
                ->keyBy('material_id');
            
            $materialsWithStatus = [];
            
            foreach ($kursus->materials->sortBy('order') as $material) {
                $progress = $userProgress[$material->id] ?? null;
                
                // Tentukan status untuk setiap material
                if ($material->order === 1) {
                    $isAccessible = true;
                } else {
                    $previousMaterial = $kursus->materials->where('order', $material->order - 1)->first();
                    if ($previousMaterial) {
                        $previousProgress = $userProgress[$previousMaterial->id] ?? null;
                        $isAccessible = $this->isMaterialCompleted($previousProgress, $previousMaterial);
                    } else {
                        $isAccessible = true;
                    }
                }
                
                $isCompleted = $this->isMaterialCompleted($progress, $material);
                
                if ($isCompleted) {
                    $status = 'completed';
                    $statusClass = 'completed';
                } elseif ($isAccessible) {
                    $status = 'current';
                    $statusClass = 'current';
                } else {
                    $status = 'locked';
                    $statusClass = 'locked';
                }
                
                $materialsWithStatus[] = [
                    'id' => $material->id,
                    'title' => $material->title,
                    'order' => $material->order,
                    'status' => $status,
                    'status_class' => $statusClass
                ];
            }
            
            return response()->json([
                'success' => true,
                'materials' => $materialsWithStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error refreshing material status:', [
                'error' => $e->getMessage(),
                'kursus_id' => $kursusId,
                'material_id' => $materialId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal refresh status material'
            ], 500);
        }
    }

    public function completeMaterial(Request $request, $kursusId, $materialId)
    {
        try {
            $user = Auth::user();
            
            // Validasi user terenroll
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('kursus_id', $kursusId)
                ->firstOrFail();
            
            // Update material progress
            $progress = MaterialProgress::where('user_id', $user->id)
                ->where('material_id', $materialId)
                ->first();
            
            if ($progress) {
                // Update material_status ke completed
                $progress->material_status = 'completed';
                $progress->save();
                
                // PERBAIKAN: Otomatis cek dan unlock material berikutnya
                $this->checkAndUnlockNextMaterial($user->id, $materialId, $kursusId);
                
                // Update enrollment progress
                $this->updateEnrollmentProgress($user->id, $kursusId);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Material berhasil diselesaikan'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Progress tidak ditemukan'
                ], 404);
            }
            
        } catch (\Exception $e) {
            Log::error('Error completing material:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'material_id' => $materialId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai material selesai'
            ], 500);
        }
    }

    /**
     * Untuk menampilkan daftar file yang tersedia (optional)
     */
    public function showMaterialFiles($kursus, $material)
    {
        $user = Auth::user();
        
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('kursus_id', $kursus)
            ->firstOrFail();

        $materialRecord = Materials::where('is_active', true)
            ->where('id', $material)
            ->where('course_id', $kursus)
            ->firstOrFail();

        // Parse file_path
        $filePaths = $this->parseFilePath($materialRecord->file_path);

        // Siapkan data file
        $files = [];
        foreach ($filePaths as $index => $filePath) {
            if (Storage::disk('public')->exists($filePath)) {
                $files[] = [
                    'index' => $index,
                    'name' => basename($filePath),
                    'size' => Storage::disk('public')->size($filePath),
                    'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
                    'download_url' => route('mitra.kursus.material.download', [
                        'kursus' => $kursus,
                        'material' => $material
                    ])
                ];
            }
        }

        return view('mitra.material-files', [
            'kursus' => $enrollment->kursus,
            'material' => $materialRecord,
            'files' => $files,
            'hasMultiple' => count($files) > 1,
            'downloadAllUrl' => route('mitra.kursus.material.download', [
                'kursus' => $kursus,
                'material' => $material
            ])
        ]);
    }

    public function viewMaterialVideo($kursus, $material)
{
    Log::info('View Material Video:', [
        'kursus' => $kursus,
        'material' => $material,
        'user_id' => Auth::id()
    ]);

    $user = Auth::user();
    
    // Pastikan user terenroll di kursus ini
    $enrollment = Enrollment::where('user_id', $user->id)
        ->where('kursus_id', $kursus)
        ->firstOrFail();

    // Ambil materi yang aktif
    $materialRecord = Materials::where('is_active', true)
        ->where('id', $material)
        ->where('course_id', $kursus)
        ->firstOrFail();

    // Cek content types
    $contentTypes = $this->getContentTypes($materialRecord->learning_objectives);
    $hasVideo = in_array('video', $contentTypes);
    
    if (!$hasVideo) {
        return redirect()->back()
            ->with('error', 'Material ini tidak memiliki video.');
    }

    // **PERBAIKAN: Siapkan data video dengan benar**
    $videoData = $this->prepareVideoData($materialRecord);
    
    // **PERBAIKAN UTAMA: Ambil pertanyaan video dengan format yang benar**
    $videoQuestions = VideoQuestion::where('material_id', $materialRecord->id)
        ->orderBy('time_in_seconds')
        ->get()
        ->map(function($question) {
            return [
                'question_id' => $question->id,
                'time_in_seconds' => $question->time_in_seconds,
                'question' => $question->question,
                'options' => is_array($question->options) 
                    ? $question->options 
                    : json_decode($question->options, true) ?? [],
                'correct_option' => $question->correct_option,
                'points' => $question->points,
                'explanation' => $question->explanation,
                'required_to_continue' => $question->required_to_continue ?? true
            ];
        })->toArray();
    
    // Debug
    Log::info('Video Questions Data:', [
        'material_id' => $materialRecord->id,
        'question_count' => count($videoQuestions),
        'question_times' => array_column($videoQuestions, 'time_in_seconds'),
        'has_video_questions' => count($videoQuestions) > 0
    ]);
    
    // Jika video tidak available, redirect dengan error
    if (!$videoData['is_available'] ?? false) {
        return redirect()->back()
            ->with('error', 'Video tidak tersedia. Silakan hubungi administrator.')
            ->with('debug_info', json_encode($videoData));
    }
    
    // Get progress
    $progress = MaterialProgress::where('user_id', $user->id)
        ->where('material_id', $material)
        ->first();
    
    if ($progress) {
        $progress->video_status = 'in_progress';
        $progress->save();
    } else {
        $progress = MaterialProgress::create([
            'user_id' => $user->id,
            'material_id' => $material,
            'video_status' => 'in_progress',
            'material_status' => 'pending',
            'attendance_status' => 'pending'
        ]);
    }

    return view('mitra.kursus.video-player-optimized', [
        'kursus' => $enrollment->kursus,
        'material' => $materialRecord,
        'videoData' => $videoData,
        'videoQuestions' => $videoQuestions,
        'progress' => $progress,
        'playerConfig' => $this->safeJsonDecode($materialRecord->player_config, [])
    ]);
}
    /**
     * PERBAIKAN BESAR: Method prepareVideoData sesuai dengan admin controller
     */
    /**

 */
private function prepareVideoData($material)
{
    $videoType = $material->video_type ?? 'unknown';
    
    $result = [
        'type' => $videoType,
        'is_available' => false,
        'url' => '',
        'embed_url' => '',
        'direct_link' => '',
        'player_type' => 'html5',
        'is_local' => false,
        'is_hosted' => false,
        'duration' => 0,
        'duration_formatted' => '0:00',
        'duration_minutes' => 0,
        'duration_from_database' => $material->duration ?? 0, // Simpan durasi dari DB untuk referensi
    ];
    
    if ($videoType === 'local') {
        $videoFile = $this->safeJsonDecode($material->video_file);
        
        if (is_array($videoFile) && isset($videoFile['path'])) {
            $path = $videoFile['path'];
            $fileExists = Storage::disk('public')->exists($path);
            
            if ($fileExists) {
                $result['is_available'] = true;
                $result['path'] = $path;
                $result['direct_link'] = Storage::disk('public')->url($path);
                $result['url'] = $result['direct_link'];
                $result['player_type'] = 'html5';
                $result['is_local'] = true;
                $result['mime_type'] = mime_content_type(Storage::disk('public')->path($path)) ?: 'video/mp4';
                
                // **PERBAIKAN UTAMA: Selalu baca durasi dari file video, bukan dari database**
                $actualDuration = $this->getLocalVideoDuration($path);
                
                // Prioritaskan durasi dari file
                if ($actualDuration > 0) {
                    $result['duration'] = (int)$actualDuration;
                    $result['duration_accurate'] = true;
                    $result['duration_source'] = 'video_file';
                } elseif (isset($videoFile['duration']) && $videoFile['duration'] > 0) {
                    // Fallback ke data di database
                    $result['duration'] = (int)$videoFile['duration'];
                    $result['duration_accurate'] = false;
                    $result['duration_source'] = 'database';
                } else {
                    // Fallback ke material duration
                    $result['duration'] = (int)($material->duration ?? 0);
                    $result['duration_accurate'] = false;
                    $result['duration_source'] = 'material_record';
                }
                
                // Format durasi
                $result['duration_formatted'] = $this->formatDuration($result['duration']);
                $result['duration_minutes'] = ceil($result['duration'] / 60);
            }
        }
        
    } elseif ($videoType === 'youtube') {
        if (!empty($material->video_url)) {
            $result['is_available'] = true;
            $result['url'] = $material->video_url;
            $result['embed_url'] = $this->getYouTubeEmbedUrl($material->video_url);
            $result['player_type'] = 'youtube';
            
            // Untuk YouTube, gunakan durasi dari database
            $result['duration'] = $material->duration ?? 0;
            $result['duration_formatted'] = $this->formatDuration($result['duration']);
            $result['duration_minutes'] = ceil($result['duration'] / 60);
            $result['duration_accurate'] = true; // YouTube API biasanya akurat
            $result['duration_source'] = 'database';
        }
        
    } elseif ($videoType === 'hosted') {
        $videoFile = $this->safeJsonDecode($material->video_file);
        if ($videoFile && isset($videoFile['embed_link'])) {
            $result['is_available'] = true;
            $result['embed_url'] = $videoFile['embed_link'];
            $result['direct_link'] = $videoFile['web_view_link'] ?? $videoFile['embed_link'];
            $result['url'] = $result['direct_link'];
            $result['player_type'] = 'drive';
            $result['is_hosted'] = true;
            
            // Untuk Google Drive, prioritaskan dari videoFile, lalu database
            if (isset($videoFile['duration']) && $videoFile['duration'] > 0) {
                $result['duration'] = (int)$videoFile['duration'];
                $result['duration_source'] = 'video_file_data';
            } else {
                $result['duration'] = (int)($material->duration ?? 0);
                $result['duration_source'] = 'database';
            }
            
            $result['duration_formatted'] = $this->formatDuration($result['duration']);
            $result['duration_minutes'] = ceil($result['duration'] / 60);
        }
    }
    
    Log::info('Video Data with Duration Accuracy:', [
        'material_id' => $material->id,
        'type' => $result['type'],
        'duration' => $result['duration'],
        'duration_source' => $result['duration_source'] ?? 'unknown',
        'duration_accurate' => $result['duration_accurate'] ?? false,
        'database_duration' => $material->duration ?? 0,
        'duration_formatted' => $result['duration_formatted']
    ]);
    
    return $result;
}

/**
 * Helper untuk membaca durasi video lokal
 */
private function getLocalVideoDuration($path)
{
    try {
        $fullPath = Storage::disk('public')->path($path);
        
        // OPTION 1: Coba dengan getID3
        if (class_exists('\getID3')) {
            $getID3 = new \getID3();
            $fileInfo = $getID3->analyze($fullPath);
            
            if (isset($fileInfo['playtime_seconds'])) {
                return (int)$fileInfo['playtime_seconds'];
            }
        }
        
        // OPTION 2: Coba dengan shell command (ffprobe)
        if (function_exists('shell_exec')) {
            // Coba ffprobe
            $ffprobePath = null;
            $possiblePaths = [
                'ffprobe',
                '/usr/bin/ffprobe',
                '/usr/local/bin/ffprobe',
                'C:\ffmpeg\bin\ffprobe.exe',
                'C:\laragon\bin\ffmpeg\bin\ffprobe.exe'
            ];
            
            foreach ($possiblePaths as $path) {
                if (@shell_exec("$path -version")) {
                    $ffprobePath = $path;
                    break;
                }
            }
            
            if ($ffprobePath) {
                $command = "$ffprobePath -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($fullPath);
                $output = shell_exec($command);
                
                if ($output && is_numeric(trim($output))) {
                    return (int)trim($output);
                }
            }
        }
        
    } catch (\Exception $e) {
        Log::warning('Error reading local video duration: ' . $e->getMessage());
    }
    
    return 0;
}

/**
 * Format durasi dalam format jam:menit:detik
 */
private function formatDuration($seconds)
{
    if ($seconds <= 0) return '0:00';
    
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
    } else {
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}

public function quickFixVideo($materialId)
{
    try {
        $material = Materials::findOrFail($materialId);
        
        if ($material->video_type === 'local' && !empty($material->video_file)) {
            $videoData = $this->safeJsonDecode($material->video_file);
            
            if (is_array($videoData) && isset($videoData['path'])) {
                $path = $videoData['path'];
                
                // Cek jika file ada
                if (Storage::disk('public')->exists($path)) {
                    // Update dengan URL langsung
                    $material->video_file = json_encode([
                        'type' => 'local',
                        'path' => $path,
                        'url' => Storage::disk('public')->url($path),
                        'size' => Storage::disk('public')->size($path),
                        'mime_type' => mime_content_type(Storage::disk('public')->path($path)) ?: 'video/mp4',
                        'direct_play' => true
                    ]);
                    $material->save();
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Video data fixed!',
                        'url' => Storage::disk('public')->url($path)
                    ]);
                }
            }
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Video data not fixed'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Helper untuk mendapatkan YouTube embed URL
 */
private function getYouTubeEmbedUrl($url)
{
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    return $url;
}

/**
 * Ganti method getMaterialsWithStatus untuk bagian video:
 */
private function getMaterialsWithStatus($materials, $user, $kursusId)
{
    $processedMaterials = [];
    
    // Ambil semua progress user untuk kursus ini
    $userProgress = MaterialProgress::where('user_id', $user->id)
        ->whereIn('material_id', $materials->pluck('id'))
        ->get()
        ->keyBy('material_id');
    
    foreach ($materials->sortBy('order') as $index => $material) {
        // Skip jika materi tidak aktif
        if (!$material->is_active) {
            continue;
        }
        
        $progress = $userProgress[$material->id] ?? null;
        
        // Dapatkan content types dengan benar
        $contentTypes = $this->getContentTypes($material->learning_objectives);

        if (empty($contentTypes)) {
            $contentTypes = $this->detectContentTypesFromMaterial($material);
        }
        
        // LOGIKA PERBAIKAN: Deteksi jenis konten yang tersedia
        $isPretest = in_array('pretest', $contentTypes);
        $isPosttest = in_array('posttest', $contentTypes);
        $isRecap = $material->type === 'recap';
        
        // Untuk materi biasa, cek content types
        if (!$isPretest && !$isPosttest && !$isRecap) {
            $hasFile = $this->hasFileContent($material, $contentTypes);
            $hasVideo = $this->hasVideoContent($material, $contentTypes);
            $hasAttendance = in_array('attendance', $contentTypes) || ($material->attendance_required ?? true);
            
            // **PERBAIKAN UTAMA: Gunakan method dari model untuk cek video availability**
            $videoAvailable = $material->isVideoAvailable();
            $videoType = $material->video_type ?? null;
            
            // Log untuk debugging
            Log::info('Video availability check - USING MODEL:', [
                'material_id' => $material->id,
                'video_type' => $videoType,
                'has_video_content' => $hasVideo,
                'video_available' => $videoAvailable,
                'model_method_result' => $material->isVideoAvailable(),
                'learning_objectives' => $contentTypes
            ]);
            
        } else {
            // Untuk test/recap, set semua ke false
            $hasFile = false;
            $hasVideo = false;
            $hasAttendance = false;
            $videoAvailable = false;
            $videoType = null;
        }
        
        // Tentukan apakah material ini bisa diakses
        $isAccessible = $this->canAccessMaterial($user->id, $kursusId, $material);
        
        // Tentukan apakah material ini sudah selesai
        $isCompleted = $this->isMaterialCompleted($progress, $material);
        
        // Tentukan status
        if ($isCompleted) {
            $status = 'completed';
            $statusClass = 'completed';
        } elseif ($isAccessible) {
            $status = 'current';
            $statusClass = 'current';
        } else {
            $status = 'locked';
            $statusClass = 'locked';
        }
        
        // Get specific status
        $attendanceStatus = $progress ? ($progress->attendance_status ?? 'pending') : 'pending';
        $materialStatus = $progress ? ($progress->material_status ?? 'pending') : 'pending';
        $videoStatus = $progress ? ($progress->video_status ?? 'pending') : 'pending';
        
        // Untuk test types
        $isTestCompleted = false;
        $testScore = null;
        
        if (($isPretest || $isPosttest) && $progress) {
            if ($isPretest && $progress->pretest_score !== null) {
                $isTestCompleted = true;
                $testScore = $progress->pretest_score;
            } elseif ($isPosttest && $progress->posttest_score !== null) {
                $isTestCompleted = true;
                $testScore = $progress->posttest_score;
            }
        }

        // File handling
        $downloadedFiles = [];
        $filePaths = [];
        $totalFiles = 0;
        
        if ($hasFile && !empty($material->file_path)) {
            $filePaths = $this->parseFilePath($material->file_path);
            $totalFiles = count($filePaths);
            
            if ($progress && $progress->downloaded_files) {
                $downloadedFiles = $this->safeJsonDecode($progress->downloaded_files, []);
            }
        }
        
        // Parse player_config
        $playerConfig = $material->player_config ?? [];
        
        // Parse soal test jika ada
        $soalTest = null;
        if ($isPretest && !empty($material->soal_pretest)) {
            $soalTest = $this->safeJsonDecode($material->soal_pretest);
        } elseif ($isPosttest && !empty($material->soal_posttest)) {
            $soalTest = $this->safeJsonDecode($material->soal_posttest);
        }
        
        // Determine material type
        $materialType = $material->type;
        if ($isPretest) {
            $materialType = 'pre_test';
        } elseif ($isPosttest) {
            $materialType = 'post_test';
        } elseif ($isRecap) {
            $materialType = 'recap';
        }
        
        // **PERBAIKAN: Gunakan prepareVideoData yang sudah diperbaiki**
        $preparedVideoData = null;
        if ($videoAvailable) {
            $preparedVideoData = $this->prepareVideoData($material);
            
            // Log hasil prepare video data dengan durasi
            Log::info('Prepared Video Data Result with Duration:', [
                'material_id' => $material->id,
                'duration' => $preparedVideoData['duration'] ?? 0,
                'duration_formatted' => $preparedVideoData['duration_formatted'] ?? '0:00',
                'duration_minutes' => $preparedVideoData['duration_minutes'] ?? 0
            ]);
        }
        
        $processedMaterials[] = [
            'id' => $material->id,
            'title' => $material->title,
            'description' => $material->description,
            'order' => $material->order,
            'type' => $materialType,
            'status' => $status,
            'status_class' => $statusClass,
            'attendance_status' => $attendanceStatus,
            'material_status' => $materialStatus,
            'video_status' => $videoStatus,
            'is_test_completed' => $isTestCompleted,
            'test_score' => $testScore,
            'soal_test' => $soalTest,
            'durasi_pretest' => $material->durasi_pretest,
            'durasi_posttest' => $material->durasi_posttest,
            'passing_grade' => $material->passing_grade ?? 70,
            'file_path' => $material->file_path,
            'file_paths' => $filePaths,
            'total_files' => $totalFiles,
            'video_url' => $material->video_url,
            'video_type' => $videoType,
            'video_duration' => $preparedVideoData['duration'] ?? 0,
            'video_duration_formatted' => $preparedVideoData['duration_formatted'] ?? '0:00',
            'video_duration_minutes' => $preparedVideoData['duration_minutes'] ?? 0,
            'video_data' => $preparedVideoData,
            'player_config' => $playerConfig,
            'allow_skip' => $material->allow_skip ?? false,
            'downloaded_files' => $downloadedFiles,
            // Konten yang tersedia
            'has_file' => $hasFile && $totalFiles > 0,
            'has_video' => $videoAvailable, // Gunakan $videoAvailable
            'has_attendance' => $hasAttendance,
            'is_pretest' => $isPretest,
            'is_posttest' => $isPosttest,
            'is_recap' => $isRecap,
            'attendance_required' => $hasAttendance,
            'duration' => $material->duration ?? 0,
            'has_video_questions' => $material->has_video_questions ?? false,
            'question_count' => $material->question_count ?? 0,
            'total_video_points' => $material->total_video_points ?? 0,
            'require_video_completion' => $material->require_video_completion ?? true,
        ];
    }
    
    return $processedMaterials;
}

/**
 * Tambahkan method untuk fix existing data
 */
public function fixMaterialVideos()
{
    try {
        $materials = Materials::whereIn('video_type', ['hosted', 'local'])
            ->get();
        
        $fixedCount = 0;
        $errorCount = 0;
        $details = [];
        
        foreach ($materials as $material) {
            try {
                $videoFile = $material->video_file;
                $videoFileOriginal = $videoFile;
                
                // CASE 1: String kosong atau "null"
                if ($videoFile === '' || $videoFile === 'null' || $videoFile === null) {
                    $material->video_file = null;
                    $material->save();
                    $fixedCount++;
                    $details[] = "Material {$material->id}: Set null for empty string";
                    continue;
                }
                
                // CASE 2: String tapi bukan JSON valid
                if (is_string($videoFile)) {
                    $decoded = json_decode($videoFile, true);
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // Try to fix common issues
                        $fixedJson = $this->fixJsonString($videoFile);
                        if ($fixedJson) {
                            $material->video_file = json_decode($fixedJson, true);
                            $material->save();
                            $fixedCount++;
                            $details[] = "Material {$material->id}: Fixed invalid JSON";
                        } else {
                            $material->video_file = null;
                            $material->save();
                            $fixedCount++;
                            $details[] = "Material {$material->id}: Set null for unfixable JSON";
                        }
                    } elseif (is_array($decoded)) {
                        // Valid JSON, save as array
                        $material->video_file = $decoded;
                        $material->save();
                        $fixedCount++;
                        $details[] = "Material {$material->id}: Saved valid JSON as array";
                    }
                }
                
                // CASE 3: Sudah array, simpan ulang untuk validasi
                elseif (is_array($videoFile)) {
                    $material->video_file = $videoFile;
                    $material->save();
                    $fixedCount++;
                    $details[] = "Material {$material->id}: Re-saved array data";
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $details[] = "Material {$material->id}: ERROR - " . $e->getMessage();
                Log::error('Error fixing video_file for material ' . $material->id . ': ' . $e->getMessage());
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "Fixed $fixedCount materials, $errorCount errors",
            'total_materials' => count($materials),
            'fixed_count' => $fixedCount,
            'error_count' => $errorCount,
            'details' => $details
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in fixMaterialVideos: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Helper untuk fix JSON string
 */
private function fixJsonString($jsonString)
{
    if (empty($jsonString)) {
        return null;
    }
    
    // Coba beberapa fix strategies
    $strategies = [
        // Strategy 1: Remove trailing commas
        function($str) {
            return preg_replace('/,\s*([\]}])/', '$1', $str);
        },
        
        // Strategy 2: Fix unquoted keys
        function($str) {
            return preg_replace('/(\w+):/', '"$1":', $str);
        },
        
        // Strategy 3: Fix single quotes to double quotes
        function($str) {
            $result = str_replace("'", '"', $str);
            // Unescape any escaped single quotes that became escaped double quotes
            $result = str_replace('\"', '"', $result);
            return $result;
        },
        
        // Strategy 4: Try to decode as array and re-encode
        function($str) {
            $data = @unserialize($str);
            if ($data !== false) {
                return json_encode($data);
            }
            return null;
        }
    ];
    
    $currentString = $jsonString;
    
    foreach ($strategies as $strategy) {
        $fixed = $strategy($currentString);
        if ($fixed && $fixed !== $currentString) {
            // Test if fixed version is valid JSON
            json_decode($fixed);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $fixed;
            }
        }
    }
    
    return null;
}

private function generateVideoToken($materialId, $userId)
{
    $secret = config('app.key');
    $timestamp = time();
    $data = $materialId . $userId . $secret . $timestamp;
    return hash('sha256', $data);
}

    /**
     * Get video stream untuk video lokal (protected)
     */
    public function streamVideo($kursus, $material, $token)
{
    try {
        $user = Auth::user();
        
        // Validasi token
        $expectedToken = $this->generateVideoToken($material, $user->id);
        if ($token !== $expectedToken) {
            abort(403, 'Unauthorized access to video');
        }
        
        // Cek enrollment
        Enrollment::where('user_id', $user->id)
            ->where('kursus_id', $kursus)
            ->firstOrFail();
        
        // Ambil materi
        $materialRecord = Materials::where('is_active', true)
            ->where('id', $material)
            ->where('course_id', $kursus)
            ->firstOrFail();
        
        // Pastikan video lokal
        if ($materialRecord->video_type !== 'local') {
            abort(404, 'Not a local video');
        }
        
        // Dapatkan path video dari video_file
        $videoInfo = $this->safeJsonDecode($materialRecord->video_file);
        if (!$videoInfo || !isset($videoInfo['path'])) {
            abort(404, 'Video path not found');
        }
        
        $videoPath = $videoInfo['path'];
        
        // Validasi file exists
        if (!Storage::disk('public')->exists($videoPath)) {
            Log::error('Video file not found in storage:', [
                'path' => $videoPath,
                'full_path' => Storage::disk('public')->path($videoPath)
            ]);
            abort(404, 'Video file not found');
        }
        
        $fullPath = Storage::disk('public')->path($videoPath);
        
        // Dapatkan mime type
        $mimeType = mime_content_type($fullPath);
        if (!$mimeType) {
            $mimeType = 'video/mp4'; // Default fallback
        }
        
        $fileSize = filesize($fullPath);
        
        // Handle range requests (untuk streaming)
        $range = request()->header('Range');
        
        if ($range) {
            // Parse range header
            list($param, $range) = explode('=', $range);
            if (strtolower(trim($param)) !== 'bytes') {
                abort(400, 'Invalid range parameter');
            }
            
            list($from, $to) = explode('-', $range);
            $from = intval($from);
            $to = ($to === '') ? $fileSize - 1 : intval($to);
            
            $length = $to - $from + 1;
            
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $from-$to/$fileSize");
            header("Content-Length: $length");
        } else {
            header("Content-Length: $fileSize");
        }
        
        header("Content-Type: $mimeType");
        header('Accept-Ranges: bytes');
        header('Cache-Control: private, max-age=31536000');
        header('X-Accel-Buffering: no'); // Important for streaming
        
        // Stream video file
        $stream = fopen($fullPath, 'rb');
        
        if ($range) {
            fseek($stream, $from);
            
            $remaining = $length;
            while (!feof($stream) && $remaining > 0) {
                $chunkSize = min(1024 * 8, $remaining);
                echo fread($stream, $chunkSize);
                $remaining -= $chunkSize;
                flush();
            }
        } else {
            while (!feof($stream)) {
                echo fread($stream, 1024 * 8);
                flush();
            }
        }
        
        fclose($stream);
        exit;
        
    } catch (\Exception $e) {
        Log::error('Error streaming video: ' . $e->getMessage(), [
            'kursus' => $kursus,
            'material' => $material,
            'error' => $e->getTraceAsString()
        ]);
        abort(404, 'Video stream error');
    }
}

    public function getVideoInfo($kursus, $material)
    {
        try {
            $user = Auth::user();
            
            // Cek enrollment
            Enrollment::where('user_id', $user->id)
                ->where('kursus_id', $kursus)
                ->firstOrFail();
            
            // Ambil materi
            $materialRecord = Materials::where('is_active', true)
                ->where('id', $material)
                ->where('course_id', $kursus)
                ->firstOrFail();
            
            $videoData = $this->prepareVideoData($materialRecord);
            
            return response()->json([
                'success' => true,
                'video_data' => $videoData,
                'token' => $this->generateVideoToken($material, $user->id)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting video info: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan info video'
            ], 500);
        }
    }

    public function completeVideo(Request $request, $kursus, $material)
    {
        try {
            $user = Auth::user();
            
            // Update progress
            $progress = MaterialProgress::where('user_id', $user->id)
                ->where('material_id', $material)
                ->first();
            
            if ($progress) {
                $progress->video_status = 'completed';
                $progress->video_progress = 100;
                $progress->save();
            } else {
                MaterialProgress::create([
                    'user_id' => $user->id,
                    'material_id' => $material,
                    'video_status' => 'completed',
                    'video_progress' => 100,
                    'material_status' => 'pending',
                    'attendance_status' => 'pending'
                ]);
            }

            // Cek dan unlock material berikutnya
            $this->checkAndUnlockNextMaterial($user->id, $material, $kursus);

            // Update enrollment progress
            $this->updateEnrollmentProgress($user->id, $kursus);

            return response()->json([
                'success' => true,
                'message' => 'Video berhasil ditandai sebagai selesai'
            ]);

        } catch (\Exception $e) {
            Log::error('Error completing video:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'material' => $material
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai video selesai'
            ], 500);
        }
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

    public function saveVideoQuestionAnswer(Request $request, $kursus, $material)
{
    try {
        $user = Auth::user();
        
        $request->validate([
            'question_id' => 'required',
            'answer' => 'required|integer',
            'is_correct' => 'required|boolean',
            'points' => 'required|integer|min:0'
        ]);

        // Cari question
        $question = VideoQuestion::find($request->question_id);
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Pertanyaan tidak ditemukan'
            ], 404);
        }

        // Simpan jawaban
        $answer = UserVideoQuestionAnswer::updateOrCreate(
            [
                'user_id' => $user->id,
                'material_id' => $material,
                'question_id' => $request->question_id
            ],
            [
                'answer' => $request->answer,
                'is_correct' => $request->is_correct,
                'points_earned' => $request->points,
                'answered_at' => now()
            ]
        );

        // Update MaterialProgress
        $materialProgress = MaterialProgress::where('user_id', $user->id)
            ->where('material_id', $material)
            ->first();
            
        if (!$materialProgress) {
            $materialProgress = MaterialProgress::create([
                'user_id' => $user->id,
                'material_id' => $material,
                'video_question_points' => $request->points,
                'material_status' => 'pending',
                'attendance_status' => 'pending',
                'video_status' => 'in_progress',
                'video_progress' => 0 // Default 0%
            ]);
        } else {
            // Update total points
            $totalPoints = UserVideoQuestionAnswer::where('user_id', $user->id)
                ->where('material_id', $material)
                ->sum('points_earned');
            
            $materialProgress->video_question_points = $totalPoints;
            $materialProgress->save();
        }

        // Cek apakah video sudah 100% DAN semua pertanyaan terjawab
        $isVideoAndQuestionsCompleted = $this->isVideoAndQuestionsCompleted($user->id, $material);
        
        if ($isVideoAndQuestionsCompleted) {
            // Update video_status ke completed
            if ($materialProgress) {
                $materialProgress->video_status = 'completed';
                $materialProgress->save();
            }
            
            // Cek dan unlock material berikutnya
            $this->checkAndUnlockNextMaterial($user->id, $material, $kursus);
            
            // Update enrollment progress
            $this->updateEnrollmentProgress($user->id, $kursus);
            
            Log::info('Video questions answered and video 100% - material completed', [
                'material_id' => $material,
                'user_id' => $user->id
            ]);
        } else {
            // Cek progress video saat ini
            $videoProgress = $materialProgress->video_progress ?? 0;
            Log::info('Video questions answered but video not 100% yet', [
                'material_id' => $material,
                'user_id' => $user->id,
                'video_progress' => $videoProgress,
                'questions_answered' => UserVideoQuestionAnswer::where('user_id', $user->id)
                    ->where('material_id', $material)
                    ->count(),
                'total_questions' => VideoQuestion::where('material_id', $material)->count()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Jawaban tersimpan',
            'answer_id' => $answer->id,
            'total_points' => $materialProgress->video_question_points ?? $request->points,
            'answered_count' => UserVideoQuestionAnswer::where('user_id', $user->id)
                ->where('material_id', $material)
                ->count(),
            'total_questions' => VideoQuestion::where('material_id', $material)->count(),
            'is_video_and_questions_completed' => $isVideoAndQuestionsCompleted,
            'video_progress' => $materialProgress->video_progress ?? 0
        ]);

    } catch (\Exception $e) {
        Log::error('Error saving video question answer:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'question_id' => $request->question_id ?? 'unknown'
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan jawaban: ' . $e->getMessage()
        ], 500);
    }
}

public function forceCompleteVideo(Request $request, $kursus, $material)
{
    try {
        $user = Auth::user();
        
        Log::info('FORCE COMPLETE VIDEO REQUEST:', [
            'user_id' => $user->id,
            'material_id' => $material,
            'data' => $request->all()
        ]);
        
        // Cek enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('kursus_id', $kursus)
            ->firstOrFail();
        
        // Cari atau buat progress
        $progress = MaterialProgress::firstOrNew([
            'user_id' => $user->id,
            'material_id' => $material
        ]);
        
        // FORCE SET KE 100%
        $progress->video_progress = 100;
        $progress->video_current_time = $request->video_duration ?? 0;
        $progress->video_duration = $request->video_duration ?? 0;
        $progress->video_status = 'completed';
        $progress->save();
        
        // Cek apakah semua pertanyaan video sudah dijawab
        $totalQuestions = VideoQuestion::where('material_id', $material)->count();
        $answeredQuestions = UserVideoQuestionAnswer::where('user_id', $user->id)
            ->where('material_id', $material)
            ->count();
        
        $allQuestionsAnswered = ($totalQuestions === 0) || ($answeredQuestions >= $totalQuestions);
        
        Log::info('Force complete video result:', [
            'material_id' => $material,
            'user_id' => $user->id,
            'video_progress' => 100,
            'all_questions_answered' => $allQuestionsAnswered,
            'answered' => $answeredQuestions,
            'total' => $totalQuestions
        ]);
        
        // Jika semua pertanyaan terjawab, unlock material berikutnya
        if ($allQuestionsAnswered) {
            $this->checkAndUnlockNextMaterial($user->id, $material, $kursus);
            $this->updateEnrollmentProgress($user->id, $kursus);
            
            Log::info('Video 100% complete + all questions answered - unlocking next', [
                'material_id' => $material,
                'kursus_id' => $kursus
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Video telah diselesaikan 100%',
            'video_progress' => 100,
            'all_questions_answered' => $allQuestionsAnswered
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in forceCompleteVideo:', [
            'error' => $e->getMessage(),
            'user_id' => Auth::id(),
            'material' => $material
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal menyelesaikan video: ' . $e->getMessage()
        ], 500);
    }
}

    public function updateVideoProgress(Request $request, $kursus, $material)
{
    try {
        $user = Auth::user();
        
        $request->validate([
            'progress_percentage' => 'required|numeric|min:0|max:100',
            'current_time' => 'required|numeric',
            'duration' => 'required|numeric',
        ]);
        
        // Cek apakah video sudah selesai berdasarkan persentase
        $materialRecord = Materials::where('is_active', true)
            ->where('id', $material)
            ->first();
        
        if (!$materialRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Material tidak ditemukan'
            ], 404);
        }
        
        // Update atau buat progress
        $progress = MaterialProgress::where('user_id', $user->id)
            ->where('material_id', $material)
            ->first();
        
        // VIDEO HARUS 100% - TIDAK ADA KOMPROMI
        $minWatchPercentage = 100; // UBAH DARI 90 KE 100
        
        $videoWatchedCompleted = ($request->progress_percentage >= $minWatchPercentage);
        
        if ($progress) {
            // Update progress video
            $progress->video_progress = $request->progress_percentage;
            $progress->video_current_time = $request->current_time;
            $progress->video_duration = $request->duration;
            
            // Cek apakah video sudah 100% DAN semua pertanyaan terjawab
            if ($videoWatchedCompleted) {
                $totalQuestions = VideoQuestion::where('material_id', $material)->count();
                $answeredQuestions = UserVideoQuestionAnswer::where('user_id', $user->id)
                    ->where('material_id', $material)
                    ->count();
                
                $allQuestionsAnswered = ($totalQuestions === 0) || ($answeredQuestions >= $totalQuestions);
                
                // Hanya tandai video_status sebagai completed jika BOTH conditions met
                if ($allQuestionsAnswered) {
                    $progress->video_status = 'completed';
                    Log::info('Video marked as completed - 100% watched and all questions answered', [
                        'material_id' => $material,
                        'user_id' => $user->id,
                        'video_progress' => $request->progress_percentage
                    ]);
                } else {
                    // Jika video 100% tapi pertanyaan belum semua terjawab
                    $progress->video_status = 'in_progress';
                    Log::info('Video 100% but questions not all answered', [
                        'material_id' => $material,
                        'user_id' => $user->id,
                        'answered' => $answeredQuestions,
                        'total' => $totalQuestions
                    ]);
                }
            } else {
                // Video belum 100%
                $progress->video_status = 'in_progress';
            }
            
            $progress->save();
        } else {
            // Buat progress baru
            $progress = MaterialProgress::create([
                'user_id' => $user->id,
                'material_id' => $material,
                'video_status' => 'in_progress',
                'video_progress' => $request->progress_percentage,
                'video_current_time' => $request->current_time,
                'video_duration' => $request->duration,
                'material_status' => 'pending',
                'attendance_status' => 'pending'
            ]);
        }
        
        // Cek apakah video sudah selesai 100% DAN semua pertanyaan terjawab
        $isVideoAndQuestionsCompleted = $this->isVideoAndQuestionsCompleted($user->id, $material);
        
        if ($isVideoAndQuestionsCompleted) {
            // Pastikan video_status ke completed
            if ($progress && $progress->video_status !== 'completed') {
                $progress->video_status = 'completed';
                $progress->save();
            }
            
            // Cek dan unlock material berikutnya
            $this->checkAndUnlockNextMaterial($user->id, $material, $kursus);
            
            // Update enrollment progress
            $this->updateEnrollmentProgress($user->id, $kursus);
            
            Log::info('Video and questions fully completed - unlocking next material', [
                'material_id' => $material,
                'user_id' => $user->id,
                'kursus_id' => $kursus
            ]);
        }
        
        return response()->json([
            'success' => true,
            'progress' => $progress,
            'video_watched_completed' => $videoWatchedCompleted,
            'is_video_and_questions_completed' => $isVideoAndQuestionsCompleted,
            'message' => $videoWatchedCompleted ? 
                'Video telah ditonton 100%' : 
                'Progress video: ' . $request->progress_percentage . '%'
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error updating video progress:', [
            'error' => $e->getMessage(),
            'user_id' => Auth::id(),
            'material' => $material,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal update progress video: ' . $e->getMessage()
        ], 500);
    }
}

    // MARK: - Test Methods sesuai dengan admin controller
    public function showTest($kursus, $material, $testType)
    {
        Log::info('Show Test:', [
            'kursus' => $kursus,
            'material' => $material,
            'test_type' => $testType,
            'user_id' => Auth::id()
        ]);

        $kursusRecord = Kursus::findOrFail($kursus);
        
        // HANYA ambil materi yang AKTIF
        $materialRecord = Materials::where('is_active', true)
                            ->where('id', $material)
                            ->firstOrFail();
        
        $user = Auth::user();

        // Cek content types untuk menentukan jenis test
        $contentTypes = $this->getContentTypes($materialRecord->learning_objectives);
        $isPretest = in_array('pretest', $contentTypes);
        $isPosttest = in_array('posttest', $contentTypes);
        
        // Validate test type
        if (($testType === 'pretest' && !$isPretest) || ($testType === 'posttest' && !$isPosttest)) {
            abort(404);
        }

        // Tentukan soal berdasarkan jenis test
        if ($testType === 'pretest') {
            $soalTest = $materialRecord->soal_pretest;
            $durasi = $materialRecord->durasi_pretest;
        } else {
            $soalTest = $materialRecord->soal_posttest;
            $durasi = $materialRecord->durasi_posttest;
        }

        // Validasi apakah soal tersedia
        if (empty($soalTest)) {
            return redirect()->route('mitra.kursus.show', $kursus)
                ->with('error', 'Soal ' . $testType . ' belum tersedia.');
        }

        // Parse soal dari JSON menggunakan helper method
        $soalTest = $this->safeJsonDecode($soalTest);
        if (empty($soalTest)) {
            return redirect()->route('mitra.kursus.show', $kursus)
                ->with('error', 'Format soal tidak valid.');
        }

        // Check if user can access this test
        if (!$this->canAccessMaterial($user->id, $kursus, $materialRecord)) {
            return redirect()->route('mitra.kursus.show', $kursus)
                ->with('error', 'Silakan selesaikan materi sebelumnya terlebih dahulu.');
        }

        // Cek apakah user sudah mengerjakan test ini
        $progress = MaterialProgress::where('user_id', $user->id)
            ->where('material_id', $material)
            ->first();

        if ($testType === 'pretest' && $progress && $progress->pretest_score !== null) {
            return redirect()->route('mitra.kursus.show', $kursus)
                ->with('info', 'Anda sudah mengerjakan pretest ini.');
        }

        if ($testType === 'posttest' && $progress && $progress->posttest_score !== null) {
            return redirect()->route('mitra.kursus.show', $kursus)
                ->with('info', 'Anda sudah mengerjakan posttest ini.');
        }

        return view('mitra.test', [
            'kursus' => $kursusRecord,
            'material' => $materialRecord,
            'testType' => $testType,
            'soalTest' => $soalTest,
            'durasi' => $durasi,
            'passing_grade' => $materialRecord->passing_grade ?? 70
        ]);
    }

    public function submitTest(Request $request, $kursus, $material, $testType)
    {
        Log::info('=== TEST SUBMISSION START ===', [
            'kursus' => $kursus,
            'material' => $material,
            'test_type' => $testType,
            'user_id' => Auth::id()
        ]);

        try {
            // HANYA ambil materi yang AKTIF
            $materialRecord = Materials::where('is_active', true)
                                ->findOrFail($material);
            $user = Auth::user();

            // Cek content types
            $contentTypes = $this->getContentTypes($materialRecord->learning_objectives);
            $isPretest = in_array('pretest', $contentTypes);
            $isPosttest = in_array('posttest', $contentTypes);
            
            if (($testType === 'pretest' && !$isPretest) || ($testType === 'posttest' && !$isPosttest)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis test tidak sesuai'
                ], 404);
            }

            // Validasi lebih fleksibel untuk menerima answers kosong
            $validated = $request->validate([
                'answers' => 'sometimes|array',
                'answers.*' => 'sometimes|nullable|integer'
            ]);

            $userAnswers = $validated['answers'] ?? [];
            $score = 0;

            // Tentukan soal yang akan digunakan berdasarkan tipe test
            if ($testType === 'pretest') {
                $soalTest = $this->safeJsonDecode($materialRecord->soal_pretest, []);
            } else {
                $soalTest = $this->safeJsonDecode($materialRecord->soal_posttest, []);
            }

            $totalQuestions = count($soalTest);

            // Validasi: pastikan ada soal yang tersedia
            if ($totalQuestions === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada soal yang tersedia untuk test ini'
                ], 400);
            }

            // Hitung score berdasarkan tipe test
            foreach ($soalTest as $index => $soal) {
                if (isset($userAnswers[$index]) && 
                    $userAnswers[$index] !== null && 
                    $userAnswers[$index] !== '' && 
                    $userAnswers[$index] == ($soal['jawaban_benar'] ?? null)) {
                    $score++;
                }
            }

            $finalScore = $totalQuestions > 0 ? ($score / $totalQuestions) * 100 : 0;
            $isPassed = $finalScore >= ($materialRecord->passing_grade ?? 70);

            // Save progress
            $progressData = [
                'attempts' => DB::raw('COALESCE(attempts, 0) + 1'),
                'is_completed' => $isPassed ? 1 : 0
            ];

            if ($testType === 'pretest') {
                $progressData['pretest_score'] = $finalScore;
                $progressData['pretest_completed_at'] = now();
            } else {
                $progressData['posttest_score'] = $finalScore;
                $progressData['posttest_completed_at'] = now();
            }

            // Gunakan transaction untuk memastikan data konsisten
            DB::beginTransaction();
            
            try {
                $progress = MaterialProgress::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'material_id' => $material
                    ],
                    $progressData
                );

                // PERBAIKAN: Otomatis cek dan unlock material berikutnya
                $this->checkAndUnlockNextMaterial($user->id, $material, $kursus);
                
                // Update enrollment progress
                $this->updateEnrollmentProgress($user->id, $kursus);
                
                DB::commit();

                return response()->json([
                    'success' => true,
                    'score' => round($finalScore, 2),
                    'is_passed' => $isPassed,
                    'passing_grade' => $materialRecord->passing_grade ?? 70,
                    'total_questions' => $totalQuestions,
                    'correct_answers' => $score,
                    'answered_count' => count($userAnswers),
                    'message' => 'Test berhasil disubmit'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            Log::error('Validation error:', ['errors' => $e->errors()]);
            
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errorMessages[] = "$field: $message";
                }
            }
            $errorMessage = implode(', ', $errorMessages);
            
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $errorMessage
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Test Submission Error:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    private function updateEnrollmentProgress($userId, $kursusId)
    {
        try {
            $enrollment = Enrollment::where('user_id', $userId)
                ->where('kursus_id', $kursusId)
                ->first();
            
            if ($enrollment) {
                // Hitung progress berdasarkan materi yang sudah diselesaikan
                $totalMaterials = Materials::where('course_id', $kursusId)
                                            ->where('is_active', true)
                                            ->count();
                
                // Hitung materi yang sudah diselesaikan
                $completedMaterials = 0;
                $allMaterials = Materials::where('course_id', $kursusId)
                    ->where('is_active', true)
                    ->orderBy('order')
                    ->get();
                
                foreach ($allMaterials as $material) {
                    $progress = MaterialProgress::where('user_id', $userId)
                        ->where('material_id', $material->id)
                        ->first();
                    
                    if ($this->isMaterialCompleted($progress, $material)) {
                        $completedMaterials++;
                    }
                }
                
                $progressPercentage = $totalMaterials > 0 ? round(($completedMaterials / $totalMaterials) * 100) : 0;
                
                // Update status enrollment
                $status = ($progressPercentage >= 100) ? 'completed' : 'in_progress';
                
                $enrollment->update([
                    'progress_percentage' => $progressPercentage,
                    'status' => $status,
                    'completed_at' => $status === 'completed' ? now() : null
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating enrollment progress:', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'kursus_id' => $kursusId
            ]);
        }
    }

    /**
     * API untuk mendapatkan progress terbaru
     */
    public function getProgress($kursusId)
    {
        try {
            $user = Auth::user();
            
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('kursus_id', $kursusId)
                ->firstOrFail();
            
            // Hitung progress berdasarkan materi yang sudah diselesaikan
            $totalMaterials = Materials::where('course_id', $kursusId)
                                        ->where('is_active', true)
                                        ->count();
            
            // Hitung berapa banyak material yang sudah selesai
            $completedMaterials = 0;
            $allMaterials = Materials::where('course_id', $kursusId)
                ->where('is_active', true)
                ->orderBy('order')
                ->get();
            
            foreach ($allMaterials as $material) {
                $progress = MaterialProgress::where('user_id', $user->id)
                    ->where('material_id', $material->id)
                    ->first();
                
                if ($this->isMaterialCompleted($progress, $material)) {
                    $completedMaterials++;
                }
            }
            
            $progressPercentage = $totalMaterials > 0 ? round(($completedMaterials / $totalMaterials) * 100) : 0;
            
            return response()->json([
                'success' => true,
                'progress_percentage' => $progressPercentage,
                'completed_materials' => $completedMaterials,
                'total_materials' => $totalMaterials
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting progress:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'kursus_id' => $kursusId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan progress'
            ], 500);
        }
    }

    /**
     * API untuk mendapatkan status material terbaru
     */
    public function getMaterialStatus($kursusId, $materialId)
    {
        try {
            $user = Auth::user();
            
            $materialRecord = Materials::where('is_active', true)
                ->where('id', $materialId)
                ->where('course_id', $kursusId)
                ->firstOrFail();
            
            $progress = MaterialProgress::where('user_id', $user->id)
                ->where('material_id', $materialId)
                ->first();
            
            // Tentukan status
            if ($materialRecord->order === 1) {
                $isAccessible = true;
            } else {
                $previousMaterial = Materials::where('course_id', $kursusId)
                    ->where('is_active', true)
                    ->where('order', $materialRecord->order - 1)
                    ->first();
                
                if (!$previousMaterial) {
                    $isAccessible = true;
                } else {
                    $previousProgress = MaterialProgress::where('user_id', $user->id)
                        ->where('material_id', $previousMaterial->id)
                        ->first();
                    
                    $isAccessible = $this->isMaterialCompleted($previousProgress, $previousMaterial);
                }
            }
            
            $isCompleted = $this->isMaterialCompleted($progress, $materialRecord);
            
            if ($isCompleted) {
                $status = 'completed';
            } elseif ($isAccessible) {
                $status = 'current';
            } else {
                $status = 'locked';
            }
            
            return response()->json([
                'success' => true,
                'material_id' => $materialId,
                'status' => $status,
                'attendance_status' => $progress ? $progress->attendance_status : 'pending',
                'material_status' => $progress ? $progress->material_status : 'pending',
                'video_status' => $progress ? $progress->video_status : 'pending',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting material status:', [
                'error' => $e->getMessage(),
                'kursus_id' => $kursusId,
                'material_id' => $materialId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan status material'
            ], 500);
        }
    }

    /**
     * Refresh status semua material dalam kursus
     */
    public function refreshAllMaterialsStatus($kursusId)
    {
        try {
            $user = Auth::user();
            
            $kursus = Kursus::where('status', 'aktif')
                ->where('id', $kursusId)
                ->with(['materials' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('order');
                }])
                ->firstOrFail();
            
            $materials = $this->getMaterialsWithStatus($kursus->materials, $user, $kursusId);
            
            return response()->json([
                'success' => true,
                'materials' => $materials
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error refreshing all materials status:', [
                'error' => $e->getMessage(),
                'kursus_id' => $kursusId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal refresh status material'
            ], 500);
        }
    }

    public function getMaterialSubtasks($kursusId, $materialId)
    {
        try {
            $kursus = Kursus::findOrFail($kursusId);
            $material = Materials::where('is_active', true)
                                ->where('id', $materialId)
                                ->where('course_id', $kursusId)
                                ->firstOrFail();
            
            $user = Auth::user();
            
            // Pastikan user terenroll
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('kursus_id', $kursusId)
                ->firstOrFail();
            
            // Dapatkan progress untuk material ini
            $progress = MaterialProgress::where('user_id', $user->id)
                ->where('material_id', $materialId)
                ->first();
            
            // Tentukan status menggunakan logika yang sama
            if ($material->order === 1) {
                $isAccessible = true;
            } else {
                $previousMaterial = Materials::where('course_id', $kursusId)
                    ->where('is_active', true)
                    ->where('order', $material->order - 1)
                    ->first();
                
                if (!$previousMaterial) {
                    $isAccessible = true;
                } else {
                    $previousProgress = MaterialProgress::where('user_id', $user->id)
                        ->where('material_id', $previousMaterial->id)
                        ->first();
                    
                    $isAccessible = $this->isMaterialCompleted($previousProgress, $previousMaterial);
                }
            }
            
            $isCompleted = $this->isMaterialCompleted($progress, $material);
            
            if ($isCompleted) {
                $status = 'completed';
                $statusClass = 'completed';
            } elseif ($isAccessible) {
                $status = 'current';
                $statusClass = 'current';
            } else {
                $status = 'locked';
                $statusClass = 'locked';
            }
            
            // Parse file_path dengan helper method
            $filePaths = $this->parseFilePath($material->file_path);
            $totalFiles = count($filePaths);
            
            // Prepare material data
            $materialData = [
                'id' => $material->id,
                'title' => $material->title,
                'description' => $material->description,
                'type' => 'material',
                'status' => $status,
                'status_class' => $statusClass,
                'attendance_required' => $material->attendance_required ?? true,
                'attendance_status' => $progress && $progress->attendance_status === 'completed' ? 'completed' : 'pending',
                'has_material' => !empty($material->file_path),
                'material_status' => $progress && $progress->material_status === 'completed' ? 'completed' : 'pending',
                'file_path' => $material->file_path,
                'file_paths' => $filePaths,
                'total_files' => $totalFiles,
                'has_video' => !empty($material->video_url),
                'video_status' => $progress && $progress->video_status === 'completed' ? 'completed' : 'pending',
                'video_url' => $material->video_url,
            ];
            
            return response()->json([
                'success' => true,
                'html' => view('mitra.partials.material-subtasks', [
                    'material' => $materialData,
                    'kursus' => $kursus
                ])->render()
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getMaterialSubtasks:', [
                'error' => $e->getMessage(),
                'kursusId' => $kursusId,
                'materialId' => $materialId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading material subtasks'
            ], 500);
        }
    }

    public function showRecap($kursus, $material)
    {
        Log::info('Show Recap:', [
            'kursus' => $kursus,
            'material' => $material,
            'user_id' => Auth::id()
        ]);

        $kursusRecord = Kursus::findOrFail($kursus);
        // HANYA ambil materi yang AKTIF
        $materialRecord = Materials::where('is_active', true)
                            ->findOrFail($material);
        
        $user = Auth::user();

        // Cek content types
        $contentTypes = $this->getContentTypes($materialRecord->learning_objectives);
        $isRecap = $materialRecord->type === 'recap';
        
        if (!$isRecap) {
            abort(404);
        }

        // Get all progress for this course
        $progress = MaterialProgress::where('user_id', $user->id)
            ->whereIn('material_id', $kursusRecord->materials()
                                            ->where('is_active', true)
                                            ->pluck('id'))
            ->with('material')
            ->get();

        return view('mitra.recap', compact('kursusRecord', 'materialRecord', 'progress'));
    }

    // Helper method to check if user can access material
    private function canAccessMaterial($userId, $kursusId, $material)
    {
        // First material is always accessible
        if ($material->order === 1) {
            return true;
        }

        // Get previous material (HANYA YANG AKTIF)
        $previousMaterial = Materials::where('course_id', $kursusId)
            ->where('is_active', true)
            ->where('order', $material->order - 1)
            ->first();

        if (!$previousMaterial) {
            return true;
        }

        // Check if previous material is completed
        $previousProgress = MaterialProgress::where('user_id', $userId)
            ->where('material_id', $previousMaterial->id)
            ->first();

        if (!$previousProgress) {
            return false;
        }

        return $this->isMaterialCompleted($previousProgress, $previousMaterial);
    }
    
    /**
     * DEBUGGING METHOD: Get video data for specific material
     */
    public function debugMaterialVideo($materialId)
    {
        try {
            $material = Materials::findOrFail($materialId);
            
            $rawData = [
                'id' => $material->id,
                'title' => $material->title,
                'video_type' => $material->video_type,
                'video_url' => $material->video_url,
                'video_file_raw' => $material->video_file,
                'video_file_decoded' => json_decode($material->video_file, true),
                'json_error' => json_last_error(),
                'json_error_msg' => json_last_error_msg(),
                'learning_objectives' => json_decode($material->learning_objectives, true),
                'is_active' => $material->is_active
            ];
            
            $processedData = $this->prepareVideoData($material);
            
            // Check availability
            $videoData = json_decode($material->video_file, true);
            $isHostedAvailable = $this->isHostedVideoAvailable($videoData, $materialId);
            
            return response()->json([
                'success' => true,
                'raw_database_data' => $rawData,
                'processed_video_data' => $processedData,
                'availability_check' => [
                    'is_hosted_available' => $isHostedAvailable,
                    'video_data_keys' => is_array($videoData) ? array_keys($videoData) : null,
                    'video_data_sample' => $videoData
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Debug material video error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }


// Controller
public function checkUnlock(Request $request, $kursus, $material)
{
    try {
        $user = Auth::user();
        
        // Cek apakah material saat ini sudah selesai
        $materialRecord = Materials::where('is_active', true)
            ->where('id', $material)
            ->where('course_id', $kursus)
            ->firstOrFail();
        
        $progress = MaterialProgress::where('user_id', $user->id)
            ->where('material_id', $material)
            ->first();
        
        if (!$progress) {
            return response()->json(['success' => false, 'message' => 'Progress tidak ditemukan']);
        }
        
        $isCompleted = $this->isMaterialCompleted($progress, $materialRecord);
        
        if ($isCompleted) {
            // Cari material berikutnya
            $nextMaterial = Materials::where('course_id', $kursus)
                ->where('is_active', true)
                ->where('order', '>', $materialRecord->order)
                ->orderBy('order')
                ->first();
            
            if ($nextMaterial) {
                return response()->json([
                    'success' => true,
                    'next_material_id' => $nextMaterial->id,
                    'next_material_title' => $nextMaterial->title,
                    'message' => 'Material berikutnya siap dibuka'
                ]);
            }
        }
        
        return response()->json(['success' => false, 'message' => 'Belum bisa unlock material berikutnya']);
        
    } catch (\Exception $e) {
        Log::error('Error checking unlock:', ['error' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => 'Terjadi kesalahan']);
    }
}


            public function daftar($id)
    {
        $kursus = Kursus::findOrFail($id);

        // ❌ CEK KURSUS PENUH
        if ($kursus->isPenuh()) {
            return back()->with('error', 'Kuota sudah penuh!');
        }

        // ❌ CEK SUDAH TERDAFTAR
        $sudahDaftar = Enrollment::where('kursus_id', $kursus->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($sudahDaftar) {
            return back()->with('error', 'Anda sudah terdaftar di kursus ini.');
        }

        // ✅ SIMPAN ENROLLMENT
        Enrollment::create([
            'kursus_id' => $kursus->id,
            'user_id' => Auth::id(),
            'status'    => 'aktif',
        ]);

        // ✅ TAMBAH PESERTA TERDAFTAR
        $kursus->increment('peserta_terdaftar');

        return back()->with('success', 'Berhasil mendaftar kursus!');
    }

}