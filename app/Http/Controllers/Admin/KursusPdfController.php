<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kursus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class KursusPdfController extends Controller
{
    public function detail(Kursus $kursus)
    {
        // ⛔ MATIKAN SEMUA OUTPUT BUFFER
        while (ob_get_level()) {
            ob_end_clean();
        }

        $kursus->load(['enrollments.user', 'materials']);

        $pesertaData = [];

        foreach ($kursus->enrollments as $enrollment) {
            $user = $enrollment->user;

            $pesertaData[] = [
                'user' => $user,
                'enrollment' => $enrollment,
                'progress_percentage' => 0,
                'completed_materials' => 0,
                'total_materials' => 0,
                'nilai' => null,
            ];
        }

        $pdf = Pdf::loadView(
            'laporan.admin.kursus.pdf-detail',
            compact('kursus', 'pesertaData')
        );

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('defaultFont', 'DejaVu Sans');
        $pdf->setOption('isRemoteEnabled', false);

        $fileName = 'detail-kursus-' . Str::slug($kursus->judul_kursus) . '.pdf';

        return response()->make(
            $pdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
            ]
        );
    }
}
