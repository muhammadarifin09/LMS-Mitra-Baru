@extends('mitra.layouts.app')

@section('title', ucfirst($testType) . ' - ' . $material->title) <!-- Ubah di sini -->
4

@section('content')
<style>
    .test-container {
        width: 95%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
        background: #f8f9fa;
        min-height: 100vh;
    }

    .test-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 8px 25px rgba(30, 60, 114, 0.3);
    }

    .test-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 10px;
        text-align: center;
    }

    .test-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .info-item {
        text-align: center;
        padding: 15px;
        background: rgba(255,255,255,0.1);
        border-radius: 10px;
        backdrop-filter: blur(10px);
    }

    .info-icon {
        font-size: 1.5rem;
        margin-bottom: 8px;
    }

    .info-value {
        font-size: 1.2rem;
        font-weight: 700;
        margin: 5px 0;
    }

    .info-label {
        font-size: 0.85rem;
        opacity: 0.9;
    }

    .test-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        gap: 20px;
    }

    .progress-section {
        background: white;
        padding: 20px;
        border-radius: 12px;
        flex: 1;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .progress-text {
        font-weight: 600;
        color: #2c3e50;
    }

    .question-counter {
        background: #1e3c72;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .progress-bar {
        width: 100%;
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .timer-container {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        padding: 20px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1.4rem;
        box-shadow: 0 4px 15px rgba(30, 60, 114, 0.3);
        text-align: center;
        min-width: 140px;
        flex-shrink: 0;
    }

    .timer-label {
        font-size: 0.8rem;
        opacity: 0.9;
        margin-bottom: 5px;
    }

    .question-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        min-height: 400px;
        display: flex;
        flex-direction: column;
    }

    .question-card.active {
        border-color: #1e3c72;
        box-shadow: 0 8px 25px rgba(30, 60, 114, 0.2);
    }

    .question-number {
        background: #1e3c72;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        margin-bottom: 15px;
        flex-shrink: 0;
    }

    .question-text {
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 25px;
        color: #2c3e50;
        line-height: 1.5;
        flex-shrink: 0;
    }

    .options-container {
        margin-left: 10px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-around;
    }

    .option-item {
        margin-bottom: 12px;
        padding: 18px 20px;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        background: #f8f9fa;
        flex: 1;
        max-height: 80px;
    }

    .option-item:hover {
        border-color: #1e3c72;
        background-color: #f0f4ff;
        transform: translateY(-2px);
    }

    .option-item.selected {
        border-color: #1e3c72;
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        box-shadow: 0 4px 15px rgba(30, 60, 114, 0.2);
    }

    .option-label {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        margin-right: 15px;
        flex-shrink: 0;
        background: #1e3c72;
        color: white;
    }

    .option-item.selected .option-label {
        background: #1565c0;
    }

    .option-text {
        flex: 1;
        font-size: 1.1rem;
        font-weight: 500;
        color: #2c3e50;
    }

    .navigation-buttons {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 2px solid #e9ecef;
    }

    .nav-btn {
        padding: 12px 25px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nav-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
    }

    .nav-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .btn-prev {
        background: #6c757d;
        color: white;
    }

    .btn-next {
        background: #1e3c72;
        color: white;
    }

    .btn-submit {
        background: linear-gradient(135deg, #27ae60, #229954);
        color: white;
        padding: 15px 30px;
        font-size: 1.1rem;
        margin-left: auto;
    }

    .completion-message {
        background: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        border-left: 4px solid #28a745;
        display: none;
    }

    @media (max-width: 1200px) {
        .test-container {
            width: 98%;
            max-width: 100%;
        }
    }

    @media (max-width: 992px) {
        .test-controls {
            flex-direction: column;
        }
        
        .timer-container {
            width: 100%;
        }
        
        .options-container {
            margin-left: 0;
        }
    }

    @media (max-width: 768px) {
        .test-container {
            padding: 15px;
        }

        .test-header {
            padding: 20px;
        }

        .test-info {
            grid-template-columns: 1fr;
        }

        .question-card {
            padding: 20px;
            min-height: auto;
        }

        .option-item {
            padding: 15px;
            max-height: none;
        }

        .navigation-buttons {
            flex-direction: column;
            gap: 10px;
        }

        .nav-btn {
            width: 100%;
            justify-content: center;
        }
        
        .option-label {
            width: 35px;
            height: 35px;
            margin-right: 10px;
        }
        
        .option-text {
            font-size: 1rem;
        }
        
        .btn-submit {
            margin-left: 0;
            order: -1;
        }
    }
</style>

<div class="container-fluid py-4">
    <div class="test-container">
        <!-- Test Header -->
        <div class="test-header">
            <h1 class="test-title">{{ $material->title }}</h1>
            <div class="test-info">
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-clock"></i></div>
                    <div class="info-value">{{ $durasi }} menit</div>
                    <div class="info-label">Durasi Test</div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-trophy"></i></div>
                    <div class="info-value">{{ $material->passing_grade }}%</div>
                    <div class="info-label">Passing Grade</div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-list-ol"></i></div>
                    <div class="info-value">{{ count($soalTest) }}</div>
                    <div class="info-label">Jumlah Soal</div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div class="info-value">{{ ucfirst($testType) }}</div>
                    <div class="info-label">Jenis Test</div>
                </div>
            </div>
        </div>

        <!-- Progress & Timer Section -->
        <div class="test-controls">
            <div class="progress-section">
                <div class="progress-header">
                    <div class="progress-text">Progress Pengerjaan</div>
                    <div class="question-counter">
                        <span id="current-question">1</span> / <span id="total-questions">{{ count($soalTest) }}</span>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progress-fill" style="width: 0%"></div>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 0.9rem; color: #6c757d;">
                    <span>Terjawab: <span id="answered-count">0</span>/<span id="total-count">{{ count($soalTest) }}</span></span>
                    <span id="progress-percent">0%</span>
                </div>
            </div>

            <!-- Timer -->
            <div class="timer-container">
                <div class="timer-label">SISA WAKTU</div>
                <div id="timer">00:00</div>
            </div>
        </div>

        <!-- Completion Message -->
        <div class="completion-message" id="completion-message">
            <i class="fas fa-check-circle me-2"></i>
            Semua soal telah dijawab! Silakan submit jawaban Anda.
        </div>

        <!-- Questions -->
        <form id="test-form">
            @csrf
            <div id="questions-container">
                @foreach($soalTest as $index => $soal)
                <div class="question-card {{ $index === 0 ? 'active' : '' }}" 
                     data-question-id="{{ $soal['id'] ?? $index }}" 
                     id="question-{{ $index }}"
                     style="display: {{ $index === 0 ? 'flex' : 'none' }};">
                    
                    <div class="question-number">{{ $index + 1 }}</div>
                    
                    <div class="question-text">
                        {{ $soal['pertanyaan'] }}
                    </div>
                    
                    <div class="options-container">
                        @foreach($soal['pilihan'] as $optionIndex => $pilihan)
                        <div class="option-item" onclick="selectOption(this, {{ $index }}, {{ $optionIndex }})">
                            <div class="option-label">{{ chr(65 + $optionIndex) }}</div>
                            <div class="option-text">{{ $pilihan }}</div>
                            <input type="radio" name="answers[{{ $index }}]" value="{{ $optionIndex }}" style="display: none;">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Navigation & Submit -->
            <div class="navigation-buttons">
                <button type="button" class="nav-btn btn-prev" onclick="previousQuestion()" id="prev-btn" disabled>
                    <i class="fas fa-arrow-left"></i> Soal Sebelumnya
                </button>
                
                <button type="button" class="nav-btn btn-next" onclick="nextQuestion()" id="next-btn">
                    Soal Selanjutnya <i class="fas fa-arrow-right"></i>
                </button>
                
                <button type="button" class="nav-btn btn-submit" onclick="validateAndSubmit()" id="submit-btn">
                    <i class="fas fa-paper-plane"></i> Submit Jawaban
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentQuestion = 0;
const totalQuestions = {{ count($soalTest) }};
let timeLeft = {{ $durasi * 60 }};
let timerInterval;
let userAnswers = {};
let answeredCount = 0;

function startTimer() {
    timerInterval = setInterval(() => {
        timeLeft--;
        updateTimerDisplay();
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            autoSubmitTest();
        }
    }, 1000);
}

function updateTimerDisplay() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    document.getElementById('timer').textContent = 
        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    if (timeLeft <= 300) {
        document.querySelector('.timer-container').style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
    }
}

function selectOption(element, questionIndex, optionIndex) {
    const questionCard = document.getElementById(`question-${questionIndex}`);
    const options = questionCard.querySelectorAll('.option-item');
    
    options.forEach(opt => {
        opt.classList.remove('selected');
    });
    
    element.classList.add('selected');
    
    const previousAnswer = userAnswers[questionIndex];
    userAnswers[questionIndex] = optionIndex;
    
    if (previousAnswer === undefined) {
        answeredCount++;
        updateProgress();
    }
    
    checkCompletion();
    updateNavigationButtons();
}

function updateProgress() {
    const progress = (answeredCount / totalQuestions) * 100;
    document.getElementById('progress-fill').style.width = `${progress}%`;
    document.getElementById('answered-count').textContent = answeredCount;
    document.getElementById('progress-percent').textContent = `${Math.round(progress)}%`;
    document.getElementById('current-question').textContent = currentQuestion + 1;
}

function checkCompletion() {
    if (answeredCount === totalQuestions) {
        document.getElementById('completion-message').style.display = 'block';
    }
}

function updateNavigationButtons() {
    document.getElementById('prev-btn').disabled = currentQuestion === 0;
    
    if (currentQuestion === totalQuestions - 1) {
        document.getElementById('next-btn').style.display = 'none';
    } else {
        document.getElementById('next-btn').style.display = 'flex';
    }
}

function showQuestion(index) {
    document.querySelectorAll('.question-card').forEach(card => {
        card.style.display = 'none';
        card.classList.remove('active');
    });
    
    const currentCard = document.getElementById(`question-${index}`);
    currentCard.style.display = 'flex';
    currentCard.classList.add('active');
    
    updateProgress();
    updateNavigationButtons();
}

function nextQuestion() {
    if (currentQuestion < totalQuestions - 1) {
        currentQuestion++;
        showQuestion(currentQuestion);
    }
}

function previousQuestion() {
    if (currentQuestion > 0) {
        currentQuestion--;
        showQuestion(currentQuestion);
    }
}

function autoSubmitTest() {
    clearInterval(timerInterval);
    
    // Tampilkan pesan waktu habis
    Swal.fire({
        title: 'Waktu Habis!',
        html: 'Waktu pengerjaan telah habis.<br>' + 
              (answeredCount > 0 ? 
               `Anda telah menjawab <strong>${answeredCount} soal</strong> dari ${totalQuestions} soal.<br>` : 
               'Anda belum menjawab satupun soal.<br>') +
              'Jawaban akan disubmit secara otomatis.',
        icon: answeredCount > 0 ? 'info' : 'warning',
        confirmButtonColor: '#1e3c72',
        confirmButtonText: 'OK',
        allowOutsideClick: false
    }).then(() => {
        // Submit dengan data yang ada (meskipun kosong)
        submitTest();
    });
}

function submitTest() {
    // Tampilkan loading
    Swal.fire({
        title: 'Menyimpan Jawaban...',
        text: 'Sedang menyimpan jawaban Anda',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Siapkan data untuk dikirim
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    
    // Tambahkan semua jawaban (bisa kosong jika tidak ada yang dijawab)
    Object.keys(userAnswers).forEach(key => {
        formData.append(`answers[${key}]`, userAnswers[key]);
    });

    // Log data yang akan dikirim
    console.log('Submitting test data:', {
        answers: userAnswers,
        answeredCount: answeredCount
    });

    // Kirim data
// GANTI line 407-411 dengan:
fetch('{{ route("mitra.kursus.test.submit", [$kursus->id, $material->id, $testType]) }}', {
    method: 'POST',
    body: formData,
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    }
})
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        Swal.close();
        
        if (data.success) {
            // Langsung redirect ke halaman kursus tanpa menampilkan notifikasi hasil
            window.location.href = '{{ url("/mitra/kursus/" . $kursus->id) }}';
        } else {
            // Jika ada error, tampilkan pesan error
            Swal.fire({
                title: 'Gagal Submit',
                text: data.message || 'Terjadi kesalahan saat menyimpan jawaban',
                icon: 'error',
                confirmButtonColor: '#e74c3c',
                confirmButtonText: 'Kembali'
            });
        }
    })
    .catch(error => {
        console.error('Error submitting test:', error);
        Swal.close();
        
        // Tampilkan pesan error
        const errorMessage = error.message || 'Koneksi terputus atau server bermasalah';
        Swal.fire({
            title: 'Gagal Submit',
            html: `Terjadi kesalahan: ${errorMessage}<br><br>
                   <small>Silakan coba lagi atau hubungi admin jika masalah berlanjut</small>`,
            icon: 'error',
            confirmButtonColor: '#e74c3c',
            confirmButtonText: 'Coba Lagi',
            showCancelButton: true,
            cancelButtonText: 'Kembali'
        }).then((result) => {
            if (result.isConfirmed) {
                // Coba submit lagi
                submitTest();
            } else {
                // Kembali ke halaman kursus
                window.location.href = '{{ url("/mitra/kursus/" . $kursus->id) }}';
            }
        });
    });
}

// Update fungsi validateAndSubmit untuk menggunakan fungsi submitTest yang baru
function validateAndSubmit() {
    const unanswered = totalQuestions - answeredCount;
    
    let confirmMessage = '';
    if (answeredCount === 0) {
        confirmMessage = 'Anda belum menjawab satupun soal. Yakin ingin submit?';
    } else if (unanswered > 0) {
        confirmMessage = `Anda belum menjawab <strong>${unanswered} soal</strong>. Yakin ingin submit?`;
    } else {
        confirmMessage = 'Anda telah menjawab semua soal. Yakin ingin submit?';
    }

    Swal.fire({
        title: 'Submit Jawaban?',
        html: confirmMessage,
        icon: answeredCount === 0 ? 'warning' : (unanswered > 0 ? 'question' : 'success'),
        showCancelButton: true,
        confirmButtonColor: answeredCount > 0 ? '#27ae60' : '#6c757d',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Submit!',
        cancelButtonText: 'Batal',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            clearInterval(timerInterval);
            submitTest();
        }
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    showQuestion(0);
    startTimer();
    updateNavigationButtons();
    updateProgress();
    
    console.log('Test initialized:', {
        totalQuestions,
        timeLeft,
        testType: '{{ $testType }}',
        materialId: '{{ $material->id }}',
        kursusId: '{{ $kursus->id }}',
        submitUrl: '{{ route("mitra.kursus.materials.take-test.submit", [$kursus->id, $material->id, $testType]) }}'
    });
});

window.addEventListener('beforeunload', function (e) {
    if (answeredCount > 0 && answeredCount < totalQuestions) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>
@endsection