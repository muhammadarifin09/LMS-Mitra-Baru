@extends('mitra.layouts.app')

@section('title', 'Nilai')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="h3 fw-bold text-primary mb-1">
                📊 Nilai Kursus
            </h1>
            <p class="text-muted">Lihat perkembangan dan hasil evaluasi kursus Anda</p>
        </div>
        
        <!-- Tombol Action Group -->
        <div>
            <!-- Tombol Ekspor PDF -->
            <button id="pdfButton" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> PDF
            </button>

            <!-- Tombol Simpan ke Arsip -->
            <form action="{{ route('mitra.nilai.simpan') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Arsip
                </button>
            </form>
        </div>
    </div>

    <!-- Data mitra untuk PDF (hidden) -->
    @php
        $user = auth()->user();
        $namaMitra = $user->name ?? ($user->nama ?? 'Mitra');
        
        $allEnrollments = \App\Models\Enrollment::with('kursus.materials')
            ->where('user_id', $user->id)
            ->get();
        
        $totalKursus = $allEnrollments->count();
        $lulusCount = 0;
        $tidakLulusCount = 0;
        $belumDinilaiCount = 0;
        
        foreach ($allEnrollments as $enroll) {
            $kursus = $enroll->kursus;
            $nilaiAkhir = app(\App\Services\NilaiService::class)->hitungNilai($user->id, $kursus);
            $status = app(\App\Services\NilaiService::class)->statusNilai($nilaiAkhir);
            
            if ($status === 'lulus') {
                $lulusCount++;
            } elseif ($status === 'tidak_lulus') {
                $tidakLulusCount++;
            } else {
                $belumDinilaiCount++;
            }
        }
    @endphp
    
    <!-- Hidden inputs untuk data PDF -->
    <input type="hidden" id="dataNama" value="{{ $namaMitra }}">
    <input type="hidden" id="dataTotal" value="{{ $totalKursus }}">
    <input type="hidden" id="dataLulus" value="{{ $lulusCount }}">
    <input type="hidden" id="dataTidakLulus" value="{{ $tidakLulusCount }}">
    <input type="hidden" id="dataBelum" value="{{ $belumDinilaiCount }}">

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="small fw-bold text-primary text-uppercase mb-2">Total Kursus</div>
                            <div class="h3 fw-bold">{{ $totalKursus }}</div>
                        </div>
                        <div class="flex-shrink-0 ms-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-book fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-success border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="small fw-bold text-success text-uppercase mb-2">Lulus</div>
                            <div class="h3 fw-bold">{{ $lulusCount }}</div>
                        </div>
                        <div class="flex-shrink-0 ms-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-danger border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="small fw-bold text-danger text-uppercase mb-2">Tidak Lulus</div>
                            <div class="h3 fw-bold">{{ $tidakLulusCount }}</div>
                        </div>
                        <div class="flex-shrink-0 ms-3">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-secondary border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="small fw-bold text-secondary text-uppercase mb-2">Belum Dinilai</div>
                            <div class="h3 fw-bold">{{ $belumDinilaiCount }}</div>
                        </div>
                        <div class="flex-shrink-0 ms-3">
                            <div class="bg-secondary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-clock fa-2x text-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card for Table -->
    <div class="card shadow-lg border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-list-check me-2 text-primary"></i> Daftar Nilai Kursus
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" width="5%">No</th>
                            <th>Kursus</th>
                            <th class="text-center" width="15%">Nilai</th>
                            <th class="text-center" width="20%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nilai as $index => $n)
                        <tr>
                            <td class="ps-4">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="fas fa-graduation-cap text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $n['kursus']->judul_kursus }}</h6>
                                        <small class="text-muted">{{ $n['kursus']->kategori ?? 'Umum' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if(isset($n['nilai']) && $n['nilai'] !== null)
                                    <span class="fw-bold fs-5">{{ $n['nilai'] }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(isset($n['status']) && $n['status'] === 'lulus')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill">
                                        <i class="fas fa-check-circle me-1"></i> Lulus
                                    </span>
                                @elseif(isset($n['status']) && $n['status'] === 'tidak_lulus')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 rounded-pill">
                                        <i class="fas fa-times-circle me-1"></i> Tidak Lulus
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-3 py-2 rounded-pill">
                                        <i class="fas fa-clock me-1"></i> Belum Dinilai
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum ada data nilai</h5>
                                <p class="text-muted">Silakan ikuti kursus terlebih dahulu</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($nilai->count())
        <div class="card-footer bg-white py-3">
            {{ $nilai->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
</div>

<!-- Load jspdf -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<script>
// Tunggu sampai halaman selesai loading
window.onload = function() {
    console.log('Window loaded');
    
    // Cari tombol PDF
    var btn = document.getElementById('pdfButton');
    console.log('Tombol PDF:', btn);
    
    if (btn) {
        // Hapus semua event listener lama dengan clone
        var newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        
        // Tambah event listener baru
        newBtn.onclick = function(e) {
            e.preventDefault();
            console.log('Tombol diklik!');
            exportToPDF();
            return false;
        };
    }
};

// Fungsi export PDF dengan tampilan seperti gambar
function exportToPDF() {
    try {
        // Ambil data
        var namaMitra = document.getElementById('dataNama').value;
        var totalKursus = parseInt(document.getElementById('dataTotal').value) || 0;
        var lulusCount = parseInt(document.getElementById('dataLulus').value) || 0;
        var tidakLulusCount = parseInt(document.getElementById('dataTidakLulus').value) || 0;
        var belumDinilaiCount = parseInt(document.getElementById('dataBelum').value) || 0;
        
        // Inisialisasi PDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        
        const pageWidth = doc.internal.pageSize.width;
        const pageHeight = doc.internal.pageSize.height;
        const margin = 20;
        let yPos = 25;
        
        // ==================== HEADER ====================
        // Judul utama (tanpa background biru)
        doc.setFontSize(24);
        doc.setTextColor(0, 0, 0);
        doc.setFont('helvetica', 'bold');
        doc.text('LAPORAN NILAI KURSUS', pageWidth / 2, yPos, { align: 'center' });
        
        yPos += 8;
        
        // Subtitle
        doc.setFontSize(12);
        doc.setTextColor(100, 100, 100);
        doc.setFont('helvetica', 'normal');
        doc.text('MOOC BPS Kabupaten Tanah Laut', pageWidth / 2, yPos, { align: 'center' });
        
        yPos += 15;
        
        // Garis pembatas
        doc.setDrawColor(200, 200, 200);
        doc.setLineWidth(0.5);
        doc.line(margin, yPos, pageWidth - margin, yPos);
        
        yPos += 10;
        
        // ==================== INFORMASI MITRA ====================
        doc.setFontSize(14);
        doc.setTextColor(33, 82, 155);
        doc.setFont('helvetica', 'bold');
        doc.text('INFORMASI MITRA', margin, yPos);
        
        yPos += 8;
        
        doc.setFontSize(11);
        doc.setTextColor(60, 60, 60);
        doc.setFont('helvetica', 'normal');
        doc.text('Nama Mitra: ' + namaMitra, margin, yPos);
        
        yPos += 6;
        
        var tanggal = new Date().toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        doc.text('Tanggal Cetak: ' + tanggal, margin, yPos);
        
        yPos += 6;
        doc.text('Total Kursus: ' + totalKursus + ' Kursus', margin, yPos);
        
        yPos += 15;
        
        // ==================== TABEL STATISTIK ====================
        doc.setFontSize(14);
        doc.setTextColor(33, 82, 155);
        doc.setFont('helvetica', 'bold');
        doc.text('STATISTIK KELULUSAN', margin, yPos);
        
        yPos += 8;
        
        // Tabel statistik
        doc.autoTable({
            startY: yPos,
            head: [['Status', 'Jumlah', 'Persentase']],
            body: [
                ['Lulus', lulusCount.toString(), totalKursus > 0 ? ((lulusCount / totalKursus) * 100).toFixed(1) + '%' : '0%'],
                ['Tidak Lulus', tidakLulusCount.toString(), totalKursus > 0 ? ((tidakLulusCount / totalKursus) * 100).toFixed(1) + '%' : '0%'],
                ['Belum Dinilai', belumDinilaiCount.toString(), totalKursus > 0 ? ((belumDinilaiCount / totalKursus) * 100).toFixed(1) + '%' : '0%']
            ],
            theme: 'grid',
            headStyles: { 
                fillColor: [33, 82, 155],
                textColor: 255,
                fontStyle: 'bold',
                halign: 'center'
            },
            bodyStyles: {
                textColor: [60, 60, 60]
            },
            columnStyles: {
                0: { cellWidth: 80 },
                1: { cellWidth: 50, halign: 'center' },
                2: { cellWidth: 50, halign: 'center' }
            },
            margin: { left: margin, right: margin }
        });
        
        yPos = doc.lastAutoTable.finalY + 15;
        
        // ==================== TABEL NILAI ====================
        doc.setFontSize(14);
        doc.setTextColor(33, 82, 155);
        doc.setFont('helvetica', 'bold');
        doc.text('DAFTAR NILAI KURSUS', margin, yPos);
        
        yPos += 8;
        
        // Siapkan data tabel nilai
        var headers = [['No', 'Nama Kursus', 'Nilai', 'Status']];
        var rows = [];
        
        @foreach($nilai as $index => $n)
            var statusText = '';
            @if($n['status'] === 'lulus')
                statusText = 'LULUS';
            @elseif($n['status'] === 'tidak_lulus')
                statusText = 'TIDAK LULUS';
            @else
                statusText = 'BELUM DINILAI';
            @endif
            
            rows.push([
                '{{ $index + 1 }}',
                '{{ $n['kursus']->judul_kursus }}',
                @if(isset($n['nilai']) && $n['nilai'] !== null)
                    '{{ $n['nilai'] }}'
                @else
                    '-'
                @endif,
                statusText
            ]);
        @endforeach
        
        // Buat tabel nilai
        doc.autoTable({
            head: headers,
            body: rows,
            startY: yPos,
            margin: { left: margin, right: margin },
            theme: 'grid',
            headStyles: { 
                fillColor: [33, 82, 155],
                textColor: 255,
                fontStyle: 'bold',
                halign: 'center'
            },
            bodyStyles: {
                fontSize: 10,
                textColor: [60, 60, 60]
            },
            columnStyles: {
                0: { cellWidth: 15, halign: 'center' },
                1: { cellWidth: 'auto' },
                2: { cellWidth: 20, halign: 'center' },
                3: { cellWidth: 35, halign: 'center' }
            }
        });
        
        yPos = doc.lastAutoTable.finalY + 15;
        
        // ==================== RINGKASAN ====================
        doc.setFontSize(11);
        doc.setTextColor(33, 82, 155);
        doc.setFont('helvetica', 'bold');
        doc.text('RINGKASAN', margin, yPos);
        
        yPos += 6;
        
        doc.setFontSize(10);
        doc.setTextColor(60, 60, 60);
        doc.setFont('helvetica', 'normal');
        doc.text(
            'Total Kursus: ' + totalKursus + ' | Lulus: ' + lulusCount + ' | Tidak Lulus: ' + tidakLulusCount + ' | Belum Dinilai: ' + belumDinilaiCount,
            margin,
            yPos
        );
        
        // ==================== FOOTER ====================
        var pageCount = doc.internal.getNumberOfPages();
        for (var i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            
            var footerY = pageHeight - 15;
            
            doc.setDrawColor(200, 200, 200);
            doc.setLineWidth(0.3);
            doc.line(margin, footerY - 5, pageWidth - margin, footerY - 5);
            
            doc.setFontSize(8);
            doc.setTextColor(120, 120, 120);
            doc.text(
                'MOOC BPS Kabupaten Tanah Laut - Dokumen dicetak otomatis dari sistem',
                margin,
                footerY
            );
            
            doc.text(
                'Halaman ' + i + ' dari ' + pageCount,
                pageWidth - margin,
                footerY,
                { align: 'right' }
            );
        }
        
        // ==================== SIMPAN PDF ====================
        var cleanName = namaMitra.replace(/[^a-z0-9]/gi, '_').toLowerCase();
        var today = new Date();
        var timestamp = today.getFullYear() + '' + (today.getMonth() + 1) + '' + today.getDate();
        var fileName = 'laporan_nilai_' + cleanName + '_' + timestamp + '.pdf';
        
        doc.save(fileName);
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan: ' + error.message);
    }
}
</script>

<style>
button {
    cursor: pointer !important;
    pointer-events: auto !important;
}
</style>
@endsection