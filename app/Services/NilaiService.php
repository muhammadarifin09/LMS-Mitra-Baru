<?php

namespace App\Services;

use App\Models\MaterialProgress;
use App\Models\UserVideoQuestionAnswer;
use App\Models\Kursus;
use App\Models\Setting;

class NilaiService
{
    public function hitungNilai($userId, Kursus $kursus)
{
    $materialIds = $kursus->materials->pluck('id');

    /* =====================
       PRETEST
       ===================== */
    $nilaiPretest = MaterialProgress::where('user_id', $userId)
        ->whereIn('material_id', $materialIds)
        ->whereNotNull('pretest_score')
        ->avg('pretest_score');

    /* =====================
       POSTTEST
       ===================== */
    $nilaiPosttest = MaterialProgress::where('user_id', $userId)
        ->whereIn('material_id', $materialIds)
        ->whereNotNull('posttest_score')
        ->avg('posttest_score');

    /* =====================
       VIDEO (CEK USER SUDAH JAWAB SOAL)
       ===================== */
   /* =====================
   VIDEO (HITUNG BERDASARKAN JUMLAH SOAL)
   ===================== */
    $videoAnswers = UserVideoQuestionAnswer::where('user_id', $userId)
        ->whereHas('question', function ($q) use ($materialIds) {
            $q->whereIn('material_id', $materialIds);
        })
        ->get();

    if ($videoAnswers->isEmpty()) {
        $nilaiVideo = null;
    } else {
        $totalPointUser = $videoAnswers->sum('points_earned');

        // asumsi setiap soal bernilai maksimal 100
        $totalPointMax = $videoAnswers->count() * 100;

        $nilaiVideo = ($totalPointUser / $totalPointMax) * 100;
    }


    /* =====================
       KUMPULKAN NILAI YANG ADA
       ===================== */
    $nilaiList = collect([
        $nilaiPretest,
        $nilaiPosttest,
        $nilaiVideo,
    ])->filter(fn ($v) => $v !== null);

    /* =====================
       BELUM ADA APA-APA
       ===================== */
    if ($nilaiList->isEmpty()) {
        return null; // ➜ "Belum ada nilai"
    }

    /* =====================
       RATA-RATA SAMA RATA
       ===================== */
    return round($nilaiList->avg(), 2);
}


   public function statusNilai(?float $nilai): string
{
    if ($nilai === null) {
        return 'belum';
    }

    $kkm = Setting::getValue('kkm_global', 60);

    return $nilai >= (int)$kkm ? 'lulus' : 'tidak_lulus';
}
}
