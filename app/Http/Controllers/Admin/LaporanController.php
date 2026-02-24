<?php

namespace App\Http\Controllers\Admin;
use App\Services\NilaiService;
use App\Http\Controllers\Controller;
use App\Models\Kursus;
use App\Models\MaterialProgress;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str; // TAMBAHKAN INI
// use App\Exports\KursusExport;
// use App\Exports\KursusPesertaExport;
// use Maatwebsite\Excel\Facades\Excel;
use App\Models\LaporanKursus;
use Carbon\Carbon;
use App\Models\Biodata;
use App\Models\Enrollment;
use App\Models\M_User;
use App\Models\LaporanMitra;
use Illuminate\Http\Request;
use App\Models\VideoQuestion;
use App\Models\UserVideoQuestionAnswer;



class LaporanController extends Controller
{
    // ======================
    // LIST KURSUS
    // ======================
    protected $nilaiService;

    public function __construct(NilaiService $nilaiService)
{
    $this->nilaiService = $nilaiService;
}


    public function exportKursusCsv()
{
    $filename = 'laporan-kursus-' . date('Y-m-d') . '.csv';

    $headers = [
        "Content-Type" => "text/csv; charset=UTF-8",
        "Content-Disposition" => "attachment; filename=\"$filename\"",
    ];

    $callback = function () {
        $file = fopen('php://output', 'w');

        // BOM supaya Excel Indonesia rapi
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header kolom
        fputcsv($file, [
            'No',
            'Judul Kursus',
            // 'Deskripsi Singkat',
            'Jumlah Peserta',
            'Tanggal Dibuat',
        ], ';');

        $kursus = Kursus::withCount('enrollments')
            ->orderBy('judul_kursus')
            ->get();

        $no = 1;
        foreach ($kursus as $item) {
            fputcsv($file, [
                $no++,
                $item->judul_kursus,
                // $item->deskripsi_singkat ?? '-',
                $item->enrollments_count,
                $item->created_at->format('d-m-Y H:i'),
            ], ';');
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportKursusDetailCsv(Kursus $kursus)
{
    $filename = 'laporan-peserta-' . Str::slug($kursus->judul_kursus) . '.csv';

    $headers = [
        "Content-Type" => "text/csv; charset=UTF-8",
        "Content-Disposition" => "attachment; filename=\"$filename\"",
    ];

    $callback = function () use ($kursus) {
        $file = fopen('php://output', 'w');

        // BOM supaya Excel Indonesia aman
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header CSV
        fputcsv($file, [
            'No',
            'Nama Peserta',
            'Email / Username',
            'Tanggal Daftar',
            'Progress (%)',
            'Nilai Rata-rata',
        ], ';');

        $no = 1;
       foreach ($kursus->enrollments as $enrollment) {
    $user = $enrollment->user;

    $totalMaterials = $kursus->materials->where('is_active', true)->count();
    $completedMaterials = $this->hitungMateriSelesai($user->id, $kursus);

    $progress = $totalMaterials > 0
        ? round(($completedMaterials / $totalMaterials) * 100)
        : 0;

    $nilai = $this->nilaiService->hitungNilai($user->id, $kursus);

    fputcsv($file, [
        $no++,
        $user->nama ?? $user->nama ?? '-',
        $user->username ?? $user->email ?? '-',
        $enrollment->created_at->format('d-m-Y'),
        $progress,
        $nilai,
    ], ';');
}


        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}



    public function kursusIndex()
    {
        $kursus = Kursus::withCount('enrollments')
            ->orderBy('judul_kursus')
            ->paginate(10); // 10 data per halaman

        return view('laporan.admin.kursus.index', compact('kursus'));
    }

    // ======================
    // DETAIL KURSUS
    // ======================
    public function kursusDetail(Kursus $kursus)
{
    $kursus->load(['enrollments.user', 'materials']);
    
    // Hitung statistik untuk setiap peserta
    $pesertaData = [];
    foreach ($kursus->enrollments as $enrollment) {
        $user = $enrollment->user;
        $nilai = $this->nilaiService->hitungNilai($user->id, $kursus);
        
        $totalMaterials = $kursus->materials->where('is_active', true)->count();
        $completedMaterials = $this->hitungMateriSelesai($user->id, $kursus);
        $progressPercentage = $totalMaterials > 0 
            ? round(($completedMaterials / $totalMaterials) * 100) 
            : 0;
        
        $pesertaData[] = [
            'user' => $user,
            'enrollment' => $enrollment,
            'progress_percentage' => $progressPercentage,
            'completed_materials' => $completedMaterials,
            'total_materials' => $totalMaterials,
            'nilai' => $nilai
        ];
    }
    
    // Hitung statistik keseluruhan
    $totalProgress = collect($pesertaData)->avg('progress_percentage');
    $totalNilai = collect($pesertaData)->avg('nilai');
    $pesertaSelesai = collect($pesertaData)->where('progress_percentage', 100)->count();

    // 🔵 AMBIL DATA LAPORAN YANG SUDAH DISIMPAN
    $laporan = LaporanKursus::where('kursus_id', $kursus->id)
        ->latest('periode')
        ->first();

    return view('laporan.admin.kursus.detail', compact(
        'kursus', 
        'pesertaData', 
        'totalProgress', 
        'totalNilai', 
        'pesertaSelesai',
        'laporan' // 🔵 WAJIB DIKIRIM
    ));
}


    // ======================
    // HITUNG MATERI YANG SUDAH SELESAI
    // ======================
    private function hitungMateriSelesai($userId, Kursus $kursus)
    {
        $completedCount = 0;
        
        foreach ($kursus->materials->where('is_active', true) as $material) {
            $progress = MaterialProgress::where('user_id', $userId)
                ->where('material_id', $material->id)
                ->first();
            
            if ($this->isMaterialCompleted($progress, $material)) {
                $completedCount++;
            }
        }
        
        return $completedCount;
    }

    // ======================
    // CEK MATERI SELESAI
    // ======================
    private function isMaterialCompleted($progress, $material)
    {
        if (!$progress) {
            return false;
        }

        $contentTypes = $this->getContentTypes($material->learning_objectives);
        $isPretest = in_array('pretest', $contentTypes);
        $isPosttest = in_array('posttest', $contentTypes);
        $isRecap = $material->type === 'recap';

        // === TEST MATERIAL ===
        if ($isPretest) {
            return $progress->pretest_score !== null;
        }

        if ($isPosttest) {
            return $progress->posttest_score !== null;
        }

        if ($isRecap) {
            return true;
        }

        // === MATERIAL REGULER ===
        $hasFile = in_array('file', $contentTypes);
        $hasVideo = in_array('video', $contentTypes);
        $hasAttendance = in_array('attendance', $contentTypes) || ($material->attendance_required ?? true);

        // Attendance
        $attendanceCompleted = !$hasAttendance || $progress->attendance_status === 'completed';

        // File completion
        $fileCompleted = true;
        if ($hasFile && !empty($material->file_path)) {
            $filePaths = $this->parseFilePath($material->file_path);
            $totalFiles = count($filePaths);

            if ($totalFiles > 0) {
                if ($progress->all_files_downloaded) {
                    $fileCompleted = true;
                } else {
                    $downloadedFiles = $this->safeJsonDecode($progress->downloaded_files, []);
                    $fileCompleted = count($downloadedFiles) >= $totalFiles;
                }
            }
        }

        // === VIDEO WAJIB 100% ===
        $videoCompleted = true;
        if ($hasVideo) {
            $videoWatchedCompleted = ($progress->video_progress ?? 0) >= 100;

            // Video questions
            $videoQuestionsCompleted = true;
            if ($material->has_video_questions && $material->total_video_points > 0) {
                $totalQuestions = $material->question_count
                    ?? VideoQuestion::where('material_id', $material->id)->count();

                $answeredQuestions = UserVideoQuestionAnswer::where('user_id', $progress->user_id)
                    ->where('material_id', $material->id)
                    ->count();

                $videoQuestionsCompleted = $answeredQuestions >= $totalQuestions;
            }

            $videoCompleted = $videoWatchedCompleted && $videoQuestionsCompleted;
        }

        return $attendanceCompleted && $fileCompleted && $videoCompleted;
    }

    private function getContentTypes($learningObjectives)
{
    if (empty($learningObjectives)) {
        return [];
    }

    $contentTypes = [];

    try {
        $objectives = is_array($learningObjectives)
            ? $learningObjectives
            : json_decode($learningObjectives, true);

        if (is_array($objectives)) {
            foreach ($objectives as $objective) {
                if (isset($objective['type'])) {
                    $contentTypes[] = strtolower($objective['type']);
                }
            }
        }
    } catch (\Exception $e) {
        return [];
    }

    return array_unique($contentTypes);
}


private function parseFilePath($filePath)
{
    if (empty($filePath)) {
        return [];
    }

    $paths = json_decode($filePath, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($paths)) {
        return $paths;
    }

    return [$filePath];
}

private function safeJsonDecode($value, $default = [])
{
    if (empty($value)) {
        return $default;
    }

    try {
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
    } catch (\Exception $e) {
        return $default;
    }
}

    // ======================
    // HITUNG NILAI
    // ======================
    public function hitungNilai($userId, Kursus $kursus)
    {
        $materialIds = $kursus->materials->pluck('id');

        $progress = MaterialProgress::where('user_id', $userId)
            ->whereIn('material_id', $materialIds)
            ->get();

        $totalScore = 0;
        $totalTest = 0;

        foreach ($progress as $p) {
            if ($p->pretest_score !== null) {
                $totalScore += $p->pretest_score;
                $totalTest++;
            }
            if ($p->posttest_score !== null) {
                $totalScore += $p->posttest_score;
                $totalTest++;
            }
        }

        return $totalTest > 0
        ? round($totalScore / $totalTest, 2)
        : null; // ✅ GANTI DI SINI
    }

    // ======================
    // HITUNG PROGRESS (dari enrollment)
    // ======================
    public function hitungProgress($enroll)
    {
        if ($enroll->total_activities == 0) return 0;

        return round(
            ($enroll->completed_activities / $enroll->total_activities) * 100
        );
    }

    // ======================
    // EXPORT PDF
    // ======================

   // Di method exportKursusPdfRingkas() di controller
public function exportKursusPdfRingkas(Kursus $kursus)
{
    $kursus->load(['enrollments.user', 'materials']);
    
    // Data minimal untuk ringkasan
    $totalPeserta = $kursus->enrollments->count();
    $totalMateri = $kursus->materials->count();
    
    // Hitung progress sederhana
    $pesertaData = [];
    $totalProgress = 0;
    $pesertaSelesai = 0;
    
    foreach ($kursus->enrollments as $enrollment) {
        $user = $enrollment->user;
        $progress = $this->hitungProgress($enrollment);
        $nilai = $this->nilaiService->hitungNilai($user->id, $kursus);
        
        $totalMaterials = $kursus->materials->where('is_active', true)->count();
        $completedMaterials = $this->hitungMateriSelesai($user->id, $kursus);
        $progressPercentage = $totalMaterials > 0 
            ? round(($completedMaterials / $totalMaterials) * 100) 
            : 0;
        
        $pesertaData[] = [
            'user' => $user,
            'progress_percentage' => $progressPercentage,
            'nilai' => $nilai
        ];
        
        $totalProgress += $progressPercentage;
        if ($progressPercentage == 100) {
            $pesertaSelesai++;
        }
    }
    
    $avgProgress = $totalPeserta > 0 ? round($totalProgress / $totalPeserta, 1) : 0;
    $totalNilai = collect($pesertaData)->avg('nilai') ?? 0;
    
    $pdf = Pdf::loadView(
        'laporan.admin.kursus.pdf-ringkas',
        compact(
            'kursus', 
            'totalPeserta', 
            'totalMateri', 
            'avgProgress', 
            'totalNilai', 
            'pesertaSelesai',
            'pesertaData'
        )
    );
    
    $pdf->setPaper('A4', 'portrait');
    $pdf->setOption('margin-top', 10);
    $pdf->setOption('margin-right', 10);
    $pdf->setOption('margin-bottom', 10);
    $pdf->setOption('margin-left', 10);
    $pdf->setOption('default-font', 'arial');
    
    $fileName = 'ringkasan-kursus-' . Str::slug($kursus->judul_kursus) . '-' . date('Y-m-d') . '.pdf';
    
    return $pdf->download($fileName);
}

    public function exportKursusPdfDetail(Kursus $kursus)
    {
        $kursus->load(['enrollments.user', 'materials']);

        $pesertaData = [];
        foreach ($kursus->enrollments as $enrollment) {
            $user = $enrollment->user;

            $totalMaterials = $kursus->materials->where('is_active', true)->count();
            $completedMaterials = $this->hitungMateriSelesai($user->id, $kursus);

            $progressPercentage = $totalMaterials > 0
                ? round(($completedMaterials / $totalMaterials) * 100)
                : 0;

            $pesertaData[] = [
                'user' => $user,
                'enrollment' => $enrollment,
                'progress_percentage' => $progressPercentage,
                'completed_materials' => $completedMaterials,
                'total_materials' => $totalMaterials,
                'nilai' => $this->nilaiService->hitungNilai($user->id, $kursus),
            ];
        }

        $totalProgress = collect($pesertaData)->avg('progress_percentage');
        $totalNilai = collect($pesertaData)->avg('nilai');
        $pesertaSelesai = collect($pesertaData)
            ->where('progress_percentage', 100)
            ->count();

        $fileName = 'detail-kursus-' . Str::slug($kursus->judul_kursus) . '-' . date('Y-m-d') . '.pdf';

        $pdf = Pdf::loadView(
            'laporan.admin.kursus.pdf-detail',
            compact(
                'kursus',
                'pesertaData',
                'totalProgress',
                'totalNilai',
                'pesertaSelesai'
            )
        )->setPaper('A4', 'portrait')
        ->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        return $pdf->download($fileName);
    }
    
    public function generateLaporanKursus(Kursus $kursus)
{
    $kursus->load(['enrollments.user', 'materials']);

    $totalPeserta = $kursus->enrollments->count();
    $pesertaSelesai = 0;
    $totalProgress = 0;
    $totalNilai = 0;
    $jumlahNilai = 0;

    foreach ($kursus->enrollments as $enrollment) {
        $progress = $this->hitungProgress($enrollment);
        $nilai = $this->nilaiService->hitungNilai($enrollment->user_id, $kursus);


        $totalProgress += $progress;

        if ($progress === 100) {
            $pesertaSelesai++;
        }

        // NULL = belum ada nilai, 0 = nilai valid
        if ($nilai !== null) {
            $totalNilai += $nilai;
            $jumlahNilai++;
        }
    }

    $rataProgress = $totalPeserta > 0
        ? round($totalProgress / $totalPeserta, 2)
        : 0;

    $rataNilai = $jumlahNilai > 0
        ? round($totalNilai / $jumlahNilai, 2)
        : null;

    // ✅ SIMPAN DATA (TANPA RETURN)
    LaporanKursus::create([
        'kursus_id' => $kursus->id,
        'periode' => now()->format('Y-m'),
        'total_peserta' => $totalPeserta,
        'peserta_selesai' => $pesertaSelesai,
        'rata_rata_progress' => $rataProgress,
        'rata_rata_nilai' => $rataNilai ?? 0,
    ]);

    // ✅ BARU REDIRECT
    return redirect()
        ->route('admin.laporan.kursus.detail', $kursus->id)
        ->with('success', 'Laporan kursus berhasil disimpan ke arsip.');
}

    // ======================
    // LAPORAN MITRA
    // ======================

    public function mitraIndex(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search  = $request->get('search');

        $mitra = M_User::whereHas('biodata') // pastikan memang mitra
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('nama', 'like', "%$search%")
                        ->orWhereHas('biodata', function ($q) use ($search) {
                            $q->where('id_sobat', 'like', "%$search%")
                                ->orWhere('kecamatan', 'like', "%$search%");
                        });
                }
            })
            ->with('biodata')
            ->withCount('enrollments')
            ->orderBy('nama')
            ->paginate($perPage)
            ->appends($request->query());

        return view('laporan.admin.mitra.index', compact('mitra'));
    }

    public function mitraDetail($id)
    {
        $mitra = M_User::with(['biodata', 'enrollments.kursus.materials'])
            ->findOrFail($id);

        $kursusData = [];
        $totalNilai = 0;
        $jumlahNilai = 0;
        
        foreach ($mitra->enrollments as $enrollment) {
            $kursus = $enrollment->kursus;
            
            // Hitung progress
            $totalMaterials = $kursus->materials->where('is_active', true)->count();
            $completedMaterials = $this->hitungMateriSelesai($mitra->id, $kursus);
            $progress = $totalMaterials > 0 
                ? round(($completedMaterials / $totalMaterials) * 100) 
                : 0;
            
            // Nilai hanya jika progress 100%
            $nilai = $this->nilaiService->hitungNilai($mitra->id, $kursus);

            if ($nilai !== null) {
                $totalNilai += $nilai;
                $jumlahNilai++;
            }

            $kursusData[] = [
                'kursus' => $kursus,
                'progress' => $progress,
                'nilai' => $nilai,
                'completed_materials' => $completedMaterials,
                'total_materials' => $totalMaterials,
                'tanggal_daftar' => $enrollment->created_at->format('d/m/Y'),
                'tanggal_selesai' => $enrollment->completed_at
                    ? $enrollment->completed_at->format('d/m/Y')
                    : '-'
            ];
        }

        // ✅ RATA-RATA NILAI (SUMBER RESMI)
        $rataNilai = $jumlahNilai > 0
            ? round($totalNilai / $jumlahNilai, 2)
            : null;

        return view('laporan.admin.mitra.detail', compact(
            'mitra',
            'kursusData',
            'rataNilai'
        ));
    }

    // ======================
    // EXPORT LAPORAN MITRA
    // ======================

    public function exportMitraCsv()
    {
        $filename = 'laporan-mitra-' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            
            // BOM untuk Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, [
                'No',
                'ID Sobat',
                'Nama Mitra',
                'Kecamatan',
                'Desa',
                'Total Kursus Diikuti'
            ], ';');
            
            // Ambil data mitra
            $mitra = M_User::whereHas('biodata')
                ->with(['biodata'])
                ->withCount('enrollments')
                ->orderBy('nama')
                ->get();
            
            $no = 1;
            foreach ($mitra as $user) {
                fputcsv($file, [
                    $no++,
                    $user->biodata->id_sobat ?? '-',
                    $user->biodata->nama_lengkap ?? '-',
                    $user->biodata->kecamatan ?? '-',
                    $user->biodata->desa ?? '-',
                    $user->enrollments_count
                ], ';');
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function exportMitraDetailCsv(M_User $mitra)
    {
        $filename = 'laporan-kursus-mitra-' . Str::slug($mitra->biodata->nama_lengkap) . '.csv';

        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($mitra) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header untuk detail kursus mitra
            fputcsv($file, [
                'No',
                'Judul Kursus',
                'Tanggal Daftar',
                'Progress (%)',
                'Materi Selesai',
                'Total Materi',
                'Tanggal Selesai',
                'Nilai Akhir'
            ], ';');
            
            $mitra->load(['enrollments.kursus.materials']);
            
            $no = 1;
            foreach ($mitra->enrollments as $enrollment) {
                $kursus = $enrollment->kursus;
                
                $totalMaterials = $kursus->materials->where('is_active', true)->count();
                $completedMaterials = $this->hitungMateriSelesai($mitra->id, $kursus);
                $progressPercentage = $totalMaterials > 0 
                    ? round(($completedMaterials / $totalMaterials) * 100) 
                    : 0;
                
                $nilai = $progressPercentage == 100
                ? $this->nilaiService->hitungNilai($mitra->id, $kursus)
                : '-';
                
                fputcsv($file, [
                    $no++,
                    $kursus->judul_kursus,
                    $enrollment->created_at->format('d-m-Y'),
                    $progressPercentage,
                    $completedMaterials,
                    $totalMaterials,
                    $enrollment->completed_at
                        ? $enrollment->completed_at->format('d-m-Y')
                        : '-',
                    $nilai
                ], ';');
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function exportMitraPdfDetail(M_User $mitra)
    {
        $mitra->load(['biodata', 'enrollments.kursus.materials']);
        
        // Data detail per kursus
        $kursusData = [];
        foreach ($mitra->enrollments as $enrollment) {
            $kursus = $enrollment->kursus;
            
            $totalMaterials = $kursus->materials->where('is_active', true)->count();
            $completedMaterials = $this->hitungMateriSelesai($mitra->id, $kursus);
            $progressPercentage = $totalMaterials > 0 
                ? round(($completedMaterials / $totalMaterials) * 100) 
                : 0;
            
            $nilai = $progressPercentage == 100
            ? $this->nilaiService->hitungNilai($mitra->id, $kursus)
            : null;

            $kursusData[] = [
                'kursus' => $kursus,
                'progress_percentage' => $progressPercentage,
                'completed_materials' => $completedMaterials,
                'total_materials' => $totalMaterials,
                'nilai' => $nilai,
                'tanggal_daftar' => $enrollment->created_at->format('d-m-Y'),
                'tanggal_selesai' => $enrollment->completed_at
                    ? $enrollment->completed_at->format('d-m-Y')
                    : '-'
            ];
        }

        // Statistik keseluruhan
        $totalKursus = count($kursusData);
        $kursusSelesai = collect($kursusData)->where('progress_percentage', 100)->count();
        $rataProgress = collect($kursusData)->avg('progress_percentage');
        $nilaiData = collect($kursusData)->where('nilai', '!=', null)->pluck('nilai');
        $rataNilai = $nilaiData->count() > 0 ? $nilaiData->avg() : null;

        $fileName = 'laporan-mitra-' . Str::slug($mitra->nama) . '-' . date('Y-m-d') . '.pdf';

        $pdf = Pdf::loadView(
            'laporan.admin.mitra.pdf-detail',
            compact(
                'mitra',
                'kursusData',
                'totalKursus',
                'kursusSelesai',
                'rataProgress',
                'rataNilai'
            )
        )->setPaper('A4', 'portrait')
        ->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        return $pdf->download($fileName);
    }

    public function generateLaporanMitra(M_User $mitra)
    {
        $mitra->load(['biodata', 'enrollments.kursus.materials']);
        
        // Hitung statistik dari kursusData (sama seperti di method mitraDetail)
        $totalKursus = 0;
        $kursusSelesai = 0;
        $totalProgress = 0;
        $totalNilai = 0;
        $jumlahNilai = 0;
        
        foreach ($mitra->enrollments as $enrollment) {
            $totalKursus++;
            
            $kursus = $enrollment->kursus;
            
            $totalMaterials = $kursus->materials->where('is_active', true)->count();
            $completedMaterials = $this->hitungMateriSelesai($mitra->id, $kursus);
            $progress = $totalMaterials > 0 
                ? round(($completedMaterials / $totalMaterials) * 100) 
                : 0;
            
            $totalProgress += $progress;
            
            if ($progress == 100) {
                $kursusSelesai++;
                
                $nilai = $this->nilaiService->hitungNilai($mitra->id, $kursus);
                if ($nilai !== null) {
                    $totalNilai += $nilai;
                    $jumlahNilai++;
                }
            }
        }
        
        // Hitung rata-rata
        $rataProgress = $totalKursus > 0 ? round($totalProgress / $totalKursus, 2) : 0;
        $rataNilai = $jumlahNilai > 0 ? round($totalNilai / $jumlahNilai, 2) : null;
        
        // Cek apakah sudah ada laporan untuk periode ini
        $periode = now()->format('Y-m');
        $existingLaporan = LaporanMitra::where('user_id', $mitra->id)
            ->where('periode', $periode)
            ->first();
        
        if ($existingLaporan) {
            // Update laporan yang sudah ada
            $existingLaporan->update([
                'id_sobat' => $mitra->biodata->id_sobat ?? null,
                'total_kursus_diikuti' => $totalKursus,
                'kursus_selesai' => $kursusSelesai,
                'rata_rata_progress' => $rataProgress,
                'rata_rata_nilai' => $rataNilai,
            ]);
            
            $message = 'Laporan mitra berhasil diperbarui.';
        } else {
            // Buat laporan baru
            LaporanMitra::create([
                'user_id' => $mitra->id,
                'id_sobat' => $mitra->biodata->id_sobat ?? null,
                'periode' => $periode,
                'total_kursus_diikuti' => $totalKursus,
                'kursus_selesai' => $kursusSelesai,
                'rata_rata_progress' => $rataProgress,
                'rata_rata_nilai' => $rataNilai,
            ]);
            
            $message = 'Laporan mitra berhasil disimpan ke arsip.';
        }
        
        return redirect()
            ->route('admin.laporan.mitra.detail', $mitra->id)
            ->with('success', $message);
    }

}