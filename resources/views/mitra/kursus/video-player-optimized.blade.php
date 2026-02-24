@extends('mitra.layouts.app')

@section('title', 'Video Materi - ' . $material->title)

@push('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
@php
// Helper function untuk memotong teks
function shortenText($text, $length = 50) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Data dari controller sudah lengkap
$videoData = $videoData ?? [];
$videoType = $videoData['type'] ?? 'unknown';
$isAvailable = $videoData['is_available'] ?? false;
$embedUrl = $videoData['embed_url'] ?? '';
$directLink = $videoData['direct_link'] ?? '';
$videoUrl = $videoData['url'] ?? $directLink ?? $embedUrl ?? '';
$playerType = $videoData['player_type'] ?? 'iframe';
$isLocalVideo = $videoType === 'local';
$isYouTube = $videoType === 'youtube';
$isHosted = $videoType === 'hosted';

// Player config dari controller (sudah diproses)
$playerConfig = $playerConfig ?? $videoData['player_config'] ?? [];
$disableForwardSeek = $playerConfig['disable_forward_seek'] ?? false;
$disableBackwardSeek = $playerConfig['disable_backward_seek'] ?? false;
$disableRightClick = $playerConfig['disable_right_click'] ?? false;
$allowSkip = $playerConfig['allow_skip'] ?? ($material->allow_skip ?? false);
$requireCompletion = $playerConfig['require_completion'] ?? ($material->require_video_completion ?? true);
$autoPauseOnQuestion = $playerConfig['auto_pause_on_question'] ?? true;
$hasSeekRestriction = $disableForwardSeek || $disableBackwardSeek;

// ==============================================
// DATA SOAL VIDEO DARI CONTROLLER
// ==============================================
$videoQuestionsData = $videoQuestions ?? [];
$hasVideoQuestions = !empty($videoQuestionsData) && count($videoQuestionsData) > 0;

// Konversi ke JSON untuk JavaScript
$videoQuestionTimings = $hasVideoQuestions ? json_encode(
    array_map(function($q) {
        return [
            'question_id' => $q['question_id'] ?? ($q['id'] ?? uniqid()),
            'time_in_seconds' => $q['time_in_seconds'] ?? ($q['time_in_seconds'] ?? 0),
            'question' => $q['question'] ?? ($q['question'] ?? ''),
            'options' => is_array($q['options'] ?? []) 
                ? ($q['options'] ?? []) 
                : [],
            'correct_option' => $q['correct_option'] ?? ($q['correct_option'] ?? 0),
            'points' => $q['points'] ?? ($q['points'] ?? 1),
            'explanation' => $q['explanation'] ?? ($q['explanation'] ?? ''),
            'required_to_continue' => $q['required_to_continue'] ?? ($q['required_to_continue'] ?? true)
        ];
    }, $videoQuestionsData)
) : '[]';

// ==============================================
// FIX UTAMA: PERBAIKI URL VIDEO LOKAL
// ==============================================

if ($isLocalVideo) {
    // AMBIL FILENAME DARI DIRECT LINK APAPUN
    $filename = '';
    if (!empty($directLink)) {
        // Extract filename
        $filename = basename($directLink);
        $filename = explode('?', $filename)[0];
    }
    
    // JIKA FILENAME ADA, BUAT URL YANG BENAR
    if (!empty($filename) && preg_match('/\.(mp4|mov|avi|mkv|webm|ogg)$/i', $filename)) {
        // LANGSUNG GUNAKAN DOMAIN HOSTING TANPA CEK STORAGE
        $hostingDomain = 'https://lms-mitra.batuah.tech';
        $directLink = $hostingDomain . '/storage/videos/' . $filename;
        $videoUrl = $directLink;
        $isAvailable = true;
        
        // DEBUG
        // echo "<!-- Video URL: $directLink -->";
    } else {
        $isAvailable = false;
        $directLink = '';
        $videoUrl = '';
    }
}
// Ambil materi berikutnya (jika ada)
$nextMaterial = null;
$allMaterials = $kursus->materials->sortBy('order');
$currentIndex = $allMaterials->search(function($item) use ($material) {
    return $item->id == $material->id;
});

if ($currentIndex !== false && $currentIndex + 1 < count($allMaterials)) {
    $nextMaterial = $allMaterials[$currentIndex + 1];
}
@endphp

<style>
    :root {
        --video-height: 70vh;
        --controls-height: 60px;
        --question-overlay-bg: rgba(0, 0, 0, 0.85);
    }
    
    .video-loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, #1a237e, #0d47a1);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        transition: opacity 0.5s ease;
        border-radius: 12px;
    }

    #video-completed-overlay,
#video-completed-overlay * {
    cursor: default !important;
}

#video-completed-overlay button,
#video-completed-overlay .action-button,
#video-completed-overlay a {
    cursor: pointer !important;
}

/* Override kelas .inactive yang mungkin menghilangkan cursor */
.video-container-wrapper.inactive #video-completed-overlay,
.video-container-wrapper.inactive #video-completed-overlay * {
    cursor: default !important;
}
    
    .video-container-wrapper {
        position: relative;
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        background: #000;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        cursor: pointer;
        user-select: none;
        min-height: 400px;
    }
    
    .video-container {
        position: relative;
        width: 100%;
        height: var(--video-height);
        min-height: 400px;
        background: #000;
    }
    
    .local-video-wrapper {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        background: #000;
    }
    
    #main-video-player {
        width: 100%;
        height: 100%;
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        background: #000;
        display: block;
    }
    
    .aspect-ratio-16-9 {
        position: relative;
        width: 100%;
        padding-top: 56.25%;
        background: #000;
    }
    
    .aspect-ratio-16-9 .video-content {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #000;
    }
    
    /* FIXED CENTER PLAY BUTTON */
    .center-play-button {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.7);
        border: 4px solid rgba(255, 255, 255, 0.9);
        display: none; /* AWALNYA DISEMBUNYIKAN */
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 30;
        opacity: 0;
        transition: opacity 0.3s, transform 0.2s;
        backdrop-filter: blur(5px);
    }
    
    .center-play-button.show {
        display: flex;
        opacity: 1;
    }
    
    .center-play-button:hover {
        background: rgba(0, 0, 0, 0.9);
        border-color: #ffffff;
        transform: translate(-50%, -50%) scale(1.05);
    }
    
    .center-play-button i {
        font-size: 40px;
        color: white;
        margin-left: 5px;
    }
    
    .center-play-button.playing i {
        margin-left: 0;
    }
    
    .video-container-wrapper:hover .center-play-button.show {
        opacity: 1;
    }
    
    .video-controls-container {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.9));
        padding: 15px 20px 10px;
        z-index: 20;
        opacity: 0;
        transition: opacity 0.3s ease;
        transform: translateY(100%);
    }
    
    .video-container-wrapper:hover .video-controls-container,
    .video-container-wrapper.controls-visible .video-controls-container {
        opacity: 1;
        transform: translateY(0);
    }
    
    .controls-top {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 10px;
    }
    
    .progress-container {
        flex: 1;
        height: 6px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
        cursor: pointer;
        position: relative;
        overflow: visible;
    }
    
    .progress-container.disabled {
        cursor: not-allowed;
        opacity: 0.5;
        pointer-events: none;
    }
    
    .progress-bar {
        height: 100%;
        background: #2196F3;
        border-radius: 3px;
        width: 0%;
        position: relative;
        transition: width 0.1s linear;
    }
    
    .progress-bar::after {
        content: '';
        position: absolute;
        right: -6px;
        top: 50%;
        transform: translateY(-50%);
        width: 12px;
        height: 12px;
        background: #2196F3;
        border-radius: 50%;
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .progress-container:hover .progress-bar::after {
        opacity: 1;
    }
    
    .controls-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 5px;
    }
    
    .left-controls, .right-controls {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .control-button {
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
        padding: 5px;
        border-radius: 4px;
        transition: background 0.2s, transform 0.2s;
    }
    
    .control-button.disabled {
        opacity: 0.3;
        cursor: not-allowed;
        pointer-events: none;
    }
    
    .control-button:hover:not(.disabled) {
        background: rgba(255, 255, 255, 0.1);
        transform: scale(1.1);
    }
    
    .control-button:active:not(.disabled) {
        transform: scale(0.95);
    }
    
    .time-display {
        color: white;
        font-size: 14px;
        font-family: monospace;
        min-width: 100px;
        text-align: center;
    }
    
    .volume-control {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .volume-slider {
        width: 80px;
        height: 4px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 2px;
        position: relative;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .volume-control:hover .volume-slider {
        opacity: 1;
    }
    
    .volume-slider-fill {
        position: absolute;
        height: 100%;
        background: #2196F3;
        border-radius: 2px;
        width: 100%;
    }
    
    .playback-rate-menu {
        position: absolute;
        bottom: 50px;
        background: rgba(0, 0, 0, 0.9);
        border-radius: 6px;
        padding: 10px 0;
        min-width: 120px;
        display: none;
        z-index: 100;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .playback-rate-menu.show {
        display: block;
    }
    
    .playback-rate-item {
        padding: 8px 20px;
        color: white;
        cursor: pointer;
        transition: background 0.2s;
        font-size: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .playback-rate-item:hover {
        background: rgba(255, 255, 255, 0.1);
    }
    
    .playback-rate-item.active {
        background: #2196F3;
        color: white;
    }
    
    .settings-menu {
        position: absolute;
        bottom: 50px;
        right: 0;
        background: rgba(0, 0, 0, 0.9);
        border-radius: 6px;
        padding: 10px 0;
        min-width: 150px;
        display: none;
        z-index: 100;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .settings-menu.show {
        display: block;
    }
    
    .settings-item {
        padding: 10px 15px;
        color: white;
        cursor: pointer;
        transition: background 0.2s;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .settings-item:hover {
        background: rgba(255, 255, 255, 0.1);
    }
    
    .settings-item i {
        width: 20px;
        text-align: center;
    }
    
    /* CSS untuk Video Questions - STYLE BARU TERINTEGRASI */
    .video-question-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.92);
        backdrop-filter: blur(10px);
        z-index: 1000;
        display: flex;
        justify-content: center;
        align-items: center;
        animation: questionSlideIn 0.4s ease-out;
        border-radius: 12px;
        overflow: hidden;
    }

    @keyframes questionSlideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .video-question-container {
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        border-radius: 12px;
        width: 90%;
        max-width: 700px;
        max-height: 85vh;
        overflow: hidden;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        position: relative;
        animation: containerScale 0.5s ease-out;
    }

    @keyframes containerScale {
        from {
            transform: scale(0.95);
        }
        to {
            transform: scale(1);
        }
    }

    .question-header {
        background: linear-gradient(135deg, #2196F3, #0D47A1);
        padding: 20px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .question-header h4 {
        margin: 0;
        color: white;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .question-time-indicator {
        background: rgba(255, 255, 255, 0.15);
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        color: white;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .question-content {
        padding: 30px;
        background: rgba(255, 255, 255, 0.02);
    }

    .question-text {
        font-size: 1.2rem;
        line-height: 1.6;
        margin-bottom: 30px;
        color: #e0e0e0;
        text-align: center;
        padding: 0 20px;
    }

    .options-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 30px;
    }

    @media (max-width: 768px) {
        .options-grid {
            grid-template-columns: 1fr;
        }
    }

    .option-card {
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 15px;
        position: relative;
        overflow: hidden;
    }

    .option-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transition: left 0.5s ease;
    }

    .option-card:hover::before {
        left: 100%;
    }

    .option-card:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(33, 150, 243, 0.5);
        transform: translateY(-3px);
    }

    .option-card.selected {
        background: rgba(33, 150, 243, 0.15);
        border-color: #2196F3;
        box-shadow: 0 5px 15px rgba(33, 150, 243, 0.3);
    }

    .option-card.disabled {
        opacity: 0.6;
        cursor: not-allowed;
        pointer-events: none;
    }

    .option-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.1rem;
        color: #e0e0e0;
        transition: all 0.3s ease;
    }

    .option-card.selected .option-icon {
        background: #2196F3;
        color: white;
    }

    .option-text {
        flex: 1;
        color: #e0e0e0;
        font-size: 1rem;
        line-height: 1.5;
    }

    .question-footer {
        padding: 20px 30px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(0, 0, 0, 0.3);
    }

    .question-stats {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .points-badge {
        background: linear-gradient(135deg, #FF9800, #F57C00);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .question-counter {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .question-actions {
        display: flex;
        gap: 10px;
    }

    .video-completed-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.95);
        display: none;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 2000;
        animation: fadeIn 0.5s ease;
        border-radius: 12px;
    }

    .completion-content {
        background: linear-gradient(135deg, #1a237e, #0d47a1);
        padding: 40px;
        border-radius: 15px;
        text-align: center;
        max-width: 500px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .completion-icon {
        font-size: 4rem;
        color: #4CAF50;
        margin-bottom: 20px;
        animation: bounce 1s ease infinite alternate;
    }

    @keyframes bounce {
        from { transform: translateY(0); }
        to { transform: translateY(-10px); }
    }

    .completion-content h3 {
        color: white;
        margin-bottom: 15px;
    }

    .completion-content p {
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 25px;
    }

    .completion-stats {
        background: rgba(255, 255, 255, 0.1);
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 25px;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .stat-item {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: white;
    }

    .stat-label {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .completion-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 20px;
    }

    .video-question-indicator {
        position: absolute;
        top: 50px;
        right: 15px;
        background: rgba(33, 150, 243, 0.9);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: bold;
        z-index: 40;
        display: flex;
        align-items: center;
        gap: 8px;
        backdrop-filter: blur(5px);
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
    }
    
    .seek-warning {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.8);
        color: #ff9800;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        z-index: 100;
        animation: fadeInOut 2s ease;
        display: none;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 152, 0, 0.3);
    }
    
    @keyframes fadeInOut {
        0%, 100% { opacity: 0; }
        20%, 80% { opacity: 1; }
    }
    
    .video-fallback {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: #000;
        color: white;
        padding: 20px;
        text-align: center;
        z-index: 5;
    }
    
    .spinner-border {
        width: 3rem;
        height: 3rem;
        border-width: 0.25em;
    }
    
    /* Question Marker Styles */
    .question-marker {
        position: absolute;
        transform: translateX(-50%);
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 5;
        top: -2px;
        width: 8px;
        height: 14px;
        border-radius: 4px;
        background: #FF9800;
    }
    
    .question-marker:hover {
        transform: translateX(-50%) scale(1.5);
        box-shadow: 0 0 8px rgba(255, 152, 0, 0.8);
    }
    
    .question-marker.answered {
        background: #4CAF50 !important;
    }
    
    .question-marker.unanswered {
        background: #FF9800 !important;
    }
    
    /* Notification Styles */
    .video-notification {
        position: fixed;
        bottom: 100px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10000;
        padding: 12px 24px;
        border-radius: 8px;
        color: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        max-width: 80%;
        text-align: center;
        font-weight: 500;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        animation: notificationSlide 2s ease;
    }
    
    @keyframes notificationSlide {
        0% {
            transform: translateX(-50%) translateY(20px);
            opacity: 0;
        }
        10% {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        90% {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        100% {
            transform: translateX(-50%) translateY(-20px);
            opacity: 0;
        }
    }
    
    @media (max-width: 768px) {
        .video-container {
            height: 50vh;
            min-height: 300px;
        }
        
        .center-play-button {
            width: 60px;
            height: 60px;
        }
        
        .center-play-button i {
            font-size: 30px;
        }
        
        .controls-bottom {
            flex-wrap: wrap;
        }
        
        .time-display {
            min-width: 80px;
            font-size: 12px;
        }
        
        .volume-slider {
            display: none;
        }
        
        .video-question-container {
            width: 95%;
            margin: 10px;
        }
        
        .question-content {
            padding: 15px;
        }
        
        .option-card {
            padding: 15px;
        }
        
        .video-question-indicator {
            top: 10px;
            right: 10px;
            font-size: 0.7rem;
            padding: 5px 10px;
        }
        
        .completion-content {
            padding: 20px;
            margin: 10px;
        }
        
        .completion-actions {
            flex-direction: column;
            align-items: center;
        }
    }
    
    .video-container-wrapper {
        transition: transform 0.3s;
    }
    
    .video-container-wrapper.scaled {
        transform: scale(0.98);
    }
    
    .video-container-wrapper.inactive {
        cursor: none;
    }
    
    .video-container-wrapper.inactive .video-controls-container,
    .video-container-wrapper.inactive .center-play-button {
        opacity: 0;
        pointer-events: none;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .pulse {
        animation: pulse 1.5s infinite;
    }
    
    .completion-status {
        position: absolute;
        top: 15px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(76, 175, 80, 0.9);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        z-index: 25;
        backdrop-filter: blur(5px);
        display: none;
    }
    
    .action-button {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 1rem;
    }
    
    .btn-primary-gradient {
        background: linear-gradient(135deg, #2196F3, #0D47A1);
        color: white;
        border: none;
    }
    
    .btn-primary-gradient:hover {
        background: linear-gradient(135deg, #1976D2, #0D47A1);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(33, 150, 243, 0.3);
    }
    
    .btn-secondary-gradient {
        background: linear-gradient(135deg, #757575, #424242);
        color: white;
        border: none;
    }
    
    .btn-secondary-gradient:hover {
        background: linear-gradient(135deg, #616161, #424242);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(117, 117, 117, 0.3);
    }
</style>

<div class="container-fluid py-3">
    <div class="row">
        <div class="col-12">
            <!-- Navigasi Kembali -->
            <div class="mb-3">
                <a href="{{ route('mitra.kursus.show', $kursus) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Materi
                </a>
            </div>
            
            <!-- Video Player Section -->
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-0">
                                <i class="fas fa-play-circle me-2"></i>
                                {{ $material->title }}
                            </h4>
                            <small class="opacity-75">{{ $material->description }}</small>
                        </div>
                        <div>
                            <span class="badge bg-light text-primary">
                                <i class="fas fa-clock me-1"></i>
                                {{ $material->duration ? ceil($material->duration / 60) . ' menit' : 'Video' }}
                            </span>
                            @if($hasVideoQuestions)
                            <span class="badge bg-warning text-dark ms-2">
                                <i class="fas fa-question-circle me-1"></i>
                                {{ count($videoQuestionsData) }} Pertanyaan
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <!-- Video Container -->
                    <div class="video-container-wrapper" id="video-wrapper">
                        <!-- Loading Overlay -->
                        <div class="video-loading-overlay" id="video-loading">
                            <div class="spinner-border text-light mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5 class="text-white mb-2">Memuat Video...</h5>
                            <p class="text-white-50 mb-0">Mohon tunggu sebentar</p>
                        </div>
                        
                        <!-- Completion Status -->
                        <div class="completion-status" id="completion-status">
                            <i class="fas fa-check-circle me-1"></i>
                            <span>Video Selesai</span>
                        </div>
                        
                        @if($hasVideoQuestions)
                        <!-- Video Question Indicator -->
                        <div class="video-question-indicator" id="video-question-indicator">
                            <i class="fas fa-question-circle"></i>
                            <span id="question-count">0/{{ count($videoQuestionsData) }}</span>
                        </div>
                        @endif
                        
                        <!-- Center Play/Pause Button - AWALNYA TIDAK DITAMPILKAN -->
                        <div class="center-play-button" id="center-play-button">
                            <i class="fas fa-play"></i>
                        </div>
                        
                        <!-- Seek Warning -->
                        <div class="seek-warning" id="seek-warning"></div>

                        <!-- Video Completed Overlay -->
                        <div class="video-completed-overlay" id="video-completed-overlay">
                            <div class="completion-content">
                                <div class="completion-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3>Video Selesai!</h3>
                                <p>Selamat! Anda telah menyelesaikan video materi ini.</p>
                                
                                @if($hasVideoQuestions)
                                <div class="completion-stats">
                                    <div class="stat-item">
                                        <span class="stat-value" id="completed-questions">0</span>
                                        <span class="stat-label">Pertanyaan Dijawab</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value" id="earned-points">0</span>
                                        <span class="stat-label">Poin Diperoleh</span>
                                    </div>
                                </div>
                                @endif
                                
                                <div class="completion-actions">
                                    <button class="action-button btn-secondary-gradient" id="replay-video-btn">
                                        <i class="fas fa-redo me-1"></i> Tonton Ulang
                                    </button>
                                    @if($nextMaterial)
                                    <a href="{{ route('mitra.kursus.show', ['kursus' => $kursus, 'material' => $nextMaterial]) }}" 
                                       class="action-button btn-primary-gradient">
                                        <i class="fas fa-forward me-1"></i> Materi Berikutnya
                                    </a>
                                    @else
                                    <a href="{{ route('mitra.kursus.show', $kursus) }}" 
                                       class="action-button btn-primary-gradient">
                                        <i class="fas fa-check-circle me-1"></i> Selesai Belajar
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Video Container -->
                        <div class="video-container" id="video-container">
                            @if($isAvailable && !empty($embedUrl) && !$isLocalVideo)
                                <!-- YouTube atau Google Drive Player -->
                                <div class="aspect-ratio-16-9">
                                    <div class="video-content">
                                        <iframe 
                                            src="{{ $embedUrl }}" 
                                            allow="autoplay; fullscreen; picture-in-picture; encrypted-media"
                                            allowfullscreen
                                            id="video-iframe"
                                            title="{{ $material->title }}"
                                            loading="lazy"
                                            style="width: 100%; height: 100%; border: none;">
                                        </iframe>
                                    </div>
                                </div>
                            
                            @elseif($isLocalVideo && !empty($directLink))
                                <!-- Video Player untuk Video Lokal -->
                                <?php $videoSrc = $directLink; ?>
                                
                                <div class="local-video-wrapper" id="local-video-wrapper">
                                    <video 
    id="main-video-player"
    preload="metadata"
    playsinline
    webkit-playsinline
    poster="{{ $posterUrl ?? '' }}"
    style="width: 100%; height: 100%; object-fit: contain;"
    crossorigin="anonymous">
    
    <source src="{{ $videoSrc }}" type="video/mp4">
    
    <p class="text-white p-3">
        Browser Anda tidak mendukung pemutaran video HTML5.<br>
        <a href="{{ $videoSrc }}" class="text-primary" download>Download video</a>
    </p>
</video>
                                </div>
                                
                            @else
                                <!-- Fallback Message -->
                                <div class="video-fallback">
                                    <i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                                    <h5>Video tidak tersedia</h5>
                                    <p class="mb-3">
                                        @if($isLocalVideo && empty($directLink))
                                            URL video tidak ditemukan.
                                        @elseif(!$isAvailable)
                                            Video belum diunggah atau tidak dapat diakses.
                                        @else
                                            Terjadi kesalahan dalam memuat video.
                                        @endif
                                    </p>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-secondary" onclick="window.location.reload()">
                                            <i class="fas fa-redo me-1"></i> Refresh
                                        </button>
                                        <a href="{{ route('mitra.kursus.show', $kursus) }}" class="btn btn-outline-warning">
                                            <i class="fas fa-arrow-left me-1"></i> Kembali
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Video Controls -->
                        <div class="video-controls-container" id="video-controls">
                            <!-- Progress Bar -->
                            <div class="controls-top">
                                <div class="progress-container" id="progress-container">
                                    <div class="progress-bar" id="progress-bar"></div>
                                </div>
                            </div>
                            
                            <!-- Bottom Controls -->
                            <div class="controls-bottom">
                                <!-- Left Controls -->
                                <div class="left-controls">
                                    <button class="control-button" id="play-pause-btn">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    
                                    <button class="control-button" id="skip-backward-btn">
                                        <i class="fas fa-backward"></i>
                                    </button>
                                    
                                    <button class="control-button" id="skip-forward-btn">
                                        <i class="fas fa-forward"></i>
                                    </button>
                                    
                                    <!-- Volume Control -->
                                    <div class="volume-control">
                                        <button class="control-button" id="volume-btn">
                                            <i class="fas fa-volume-up"></i>
                                        </button>
                                        <div class="volume-slider" id="volume-slider">
                                            <div class="volume-slider-fill" id="volume-slider-fill"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Time Display -->
                                    <div class="time-display" id="time-display">
                                        <span id="current-time">0:00</span> / 
                                        <span id="duration-time">0:00</span>
                                    </div>
                                </div>
                                
                                <!-- Right Controls -->
                                <div class="right-controls">
                                    <!-- Playback Rate -->
                                    <div class="playback-rate-container" style="position: relative;">
                                        <button class="control-button" id="playback-rate-btn">
                                            <i class="fas fa-tachometer-alt"></i>
                                        </button>
                                        <div class="playback-rate-menu" id="playback-rate-menu">
                                            <div class="playback-rate-item" data-rate="0.5">0.5x</div>
                                            <div class="playback-rate-item" data-rate="0.75">0.75x</div>
                                            <div class="playback-rate-item active" data-rate="1">Normal</div>
                                            <div class="playback-rate-item" data-rate="1.25">1.25x</div>
                                            <div class="playback-rate-item" data-rate="1.5">1.5x</div>
                                            <div class="playback-rate-item" data-rate="2">2x</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Settings Menu -->
                                    <div class="settings-container" style="position: relative;">
                                        <button class="control-button" id="settings-btn">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <div class="settings-menu" id="settings-menu">
                                            <div class="settings-item" id="quality-option">
                                                <i class="fas fa-hd"></i>
                                                <span>Kualitas</span>
                                            </div>
                                            <div class="settings-item" id="subtitles-option">
                                                <i class="fas fa-closed-captioning"></i>
                                                <span>Subtitle</span>
                                            </div>
                                            <div class="settings-item" id="pip-option">
                                                <i class="fas fa-picture-in-picture"></i>
                                                <span>Picture-in-Picture</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Fullscreen -->
                                    <button class="control-button" id="fullscreen-btn">
                                        <i class="fas fa-expand"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question Overlay Container -->
                    <div id="question-overlay-container"></div>
                </div>
                
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('mitra.kursus.show', $kursus) }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Materi
                        </a>
                        
                        @if($hasVideoQuestions)
                        <div class="text-end">
                            <small class="text-primary">
                                <i class="fas fa-question-circle me-1"></i>
                                Video berisi {{ count($videoQuestionsData) }} pertanyaan interaktif
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables untuk akses dari inline event handlers
window.videoPlayer = null;
window.isQuestionActive = false;
window.isVideoCompleted = false;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Video Player Initialized - SIMPLE VERSION');
    
    // ============================
    // ELEMENTS
    // ============================
    const videoWrapper = document.getElementById('video-wrapper');
    const videoContainer = document.getElementById('video-container');
    const videoPlayer = document.getElementById('main-video-player') || document.getElementById('video-iframe');
    const videoLoading = document.getElementById('video-loading');
    const centerPlayButton = document.getElementById('center-play-button');
    const videoControls = document.getElementById('video-controls');
    const videoCompletedOverlay = document.getElementById('video-completed-overlay');
    const replayVideoBtn = document.getElementById('replay-video-btn');
    
    // Control Buttons
    const playPauseBtn = document.getElementById('play-pause-btn');
    const skipBackwardBtn = document.getElementById('skip-backward-btn');
    const skipForwardBtn = document.getElementById('skip-forward-btn');
    const volumeBtn = document.getElementById('volume-btn');
    const volumeSlider = document.getElementById('volume-slider');
    const volumeSliderFill = document.getElementById('volume-slider-fill');
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    const playbackRateBtn = document.getElementById('playback-rate-btn');
    const playbackRateMenu = document.getElementById('playback-rate-menu');
    const settingsBtn = document.getElementById('settings-btn');
    const settingsMenu = document.getElementById('settings-menu');
    
    // Progress and Time
    const progressContainer = document.getElementById('progress-container');
    const progressBar = document.getElementById('progress-bar');
    const currentTimeEl = document.getElementById('current-time');
    const durationTimeEl = document.getElementById('duration-time');
    const timeDisplay = document.getElementById('time-display');
    
    // Seek Warning
    const seekWarning = document.getElementById('seek-warning');
    
    // Question Elements
    const videoQuestions = {!! $videoQuestionTimings !!};
    const hasVideoQuestions = {!! $hasVideoQuestions ? 'true' : 'false' !!};
    const autoPauseOnQuestion = {!! $autoPauseOnQuestion ? 'true' : 'false' !!};
    const questionIndicator = document.getElementById('video-question-indicator');
    const questionCountEl = document.getElementById('question-count');
    
    // Completion Elements
    const completedQuestionsEl = document.getElementById('completed-questions');
    const earnedPointsEl = document.getElementById('earned-points');
    
    // ============================
    // CONFIG FROM CONTROLLER (via Blade)
    // ============================
    const disableForwardSeek = {{ $disableForwardSeek ? 'true' : 'false' }};
    const disableBackwardSeek = {{ $disableBackwardSeek ? 'true' : 'false' }};
    const disableRightClick = {{ $disableRightClick ? 'true' : 'false' }};
    const allowSkip = {{ $allowSkip ? 'true' : 'false' }};
    const requireCompletion = {{ $requireCompletion ? 'true' : 'false' }};
    
    // ============================
    // STATE VARIABLES
    // ============================
    let isPlaying = false;
    let isFullscreen = false;
    let videoDurationSet = false;
    let isMuted = false;
    let currentVolume = 1;
    let playbackRate = 1;
    let isLocalVideo = {{ $isLocalVideo ? 'true' : 'false' }};
    let inactivityTimer;
    let controlsVisible = false;
    let isDraggingProgress = false;
    let isDraggingVolume = false;
    let previousTime = 0;
    
    // Video Question State Variables
    let answeredQuestions = new Set();
    let currentQuestion = null;
    window.isQuestionActive = false;
    window.isVideoCompleted = false;
    let totalPointsEarned = 0;
    let questionLastTriggerTime = 0;
    let questionCheckCooldown = false;
    
    // ============================
    // INITIALIZATION
    // ============================
    function initVideoPlayer() {
    console.log('🎬 Initializing video player...');
    
    // Hide center play button initially
    centerPlayButton.style.display = 'none';
    
    if (videoPlayer && videoPlayer.tagName === 'VIDEO') {
        setupLocalVideoPlayer();
    } else if (videoPlayer && videoPlayer.tagName === 'IFRAME') {
        setupIframePlayer();
    } else {
        console.error('❌ No video player element found!');
        hideLoading();
    }
    
    // Initialize question tracking
    if (hasVideoQuestions) {
        initializeQuestionTracking();
    }
    
    // Initialize volume slider
    updateVolumeSlider();
    
    // Setup replay button
    if (replayVideoBtn) {
        replayVideoBtn.addEventListener('click', replayVideo);
    }
    
    // Setup next material button
    const nextMaterialBtn = document.querySelector('.action-button[href*="material"]');
    if (nextMaterialBtn) {
        nextMaterialBtn.addEventListener('click', function(e) {
            if (!window.isVideoCompleted) {
                e.preventDefault();
                markVideoAsCompleted();
                setTimeout(() => {
                    window.location.href = this.href;
                }, 500);
            }
        });
    }
}
    
    function setupLocalVideoPlayer() {
        console.log('🔧 Setting up local video player...');
        
        // Set initial volume
        videoPlayer.volume = currentVolume;
        videoPlayer.playbackRate = playbackRate;
        
        // Apply player config
        if (disableRightClick) {
            videoWrapper.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            });
        }
        
        // Hide skip buttons if restricted
        if (disableForwardSeek) {
            skipForwardBtn.classList.add('disabled');
        }
        
        if (disableBackwardSeek) {
            skipBackwardBtn.classList.add('disabled');
        }
        
        // Disable progress bar if both restrictions
        if (disableForwardSeek && disableBackwardSeek) {
            progressContainer.classList.add('disabled');
        }
        
        // ============================
        // VIDEO EVENT LISTENERS
        // ============================
        videoPlayer.addEventListener('loadedmetadata', handleVideoMetadata);
        videoPlayer.addEventListener('loadeddata', handleVideoLoaded);
        videoPlayer.addEventListener('canplay', handleCanPlay);
        videoPlayer.addEventListener('play', handlePlay);
        videoPlayer.addEventListener('pause', handlePause);
        videoPlayer.addEventListener('timeupdate', handleTimeUpdate);
        videoPlayer.addEventListener('ended', handleVideoEnded);
        videoPlayer.addEventListener('volumechange', handleVolumeChange);
        videoPlayer.addEventListener('ratechange', handleRateChange);
        videoPlayer.addEventListener('error', handleVideoError);
        videoPlayer.addEventListener('seeking', handleSeeking);
        videoPlayer.addEventListener('seeked', handleSeeked);
        
        // ============================
        // CONTROL BUTTON EVENTS
        // ============================
        centerPlayButton.addEventListener('click', togglePlayPause);
        playPauseBtn.addEventListener('click', togglePlayPause);
        
        skipBackwardBtn.addEventListener('click', function() {
            if (disableBackwardSeek) return;
            skipBackward();
        });
        
        skipForwardBtn.addEventListener('click', function() {
            if (disableForwardSeek) return;
            skipForward();
        });
        
        // Progress Bar Events
        if (!disableForwardSeek && !disableBackwardSeek) {
            progressContainer.addEventListener('click', handleProgressClick);
            progressContainer.addEventListener('mousedown', startDraggingProgress);
            document.addEventListener('mousemove', handleProgressDrag);
            document.addEventListener('mouseup', stopDraggingProgress);
            
            // Touch events for mobile
            progressContainer.addEventListener('touchstart', startDraggingProgressTouch);
            document.addEventListener('touchmove', handleProgressDragTouch);
            document.addEventListener('touchend', stopDraggingProgressTouch);
        } else {
            progressContainer.classList.add('disabled');
        }
        
        // Volume Control Events
        volumeBtn.addEventListener('click', toggleMute);
        volumeSlider.addEventListener('click', handleVolumeClick);
        volumeSlider.addEventListener('mousedown', startDraggingVolume);
        document.addEventListener('mousemove', handleVolumeDrag);
        document.addEventListener('mouseup', stopDraggingVolume);
        
        // Playback Rate Menu
        playbackRateBtn.addEventListener('click', togglePlaybackRateMenu);
        playbackRateMenu.querySelectorAll('.playback-rate-item').forEach(item => {
            item.addEventListener('click', function() {
                const rate = parseFloat(this.dataset.rate);
                setPlaybackRate(rate);
                playbackRateMenu.classList.remove('show');
            });
        });
        
        // Settings Menu
        settingsBtn.addEventListener('click', toggleSettingsMenu);
        
        // Fullscreen
        fullscreenBtn.addEventListener('click', toggleFullscreen);
        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
        document.addEventListener('mozfullscreenchange', handleFullscreenChange);
        
        // Mouse movement detection for controls visibility
        videoWrapper.addEventListener('mousemove', showControls);
        videoWrapper.addEventListener('mouseleave', startInactivityTimer);
        videoWrapper.addEventListener('click', showControls);
        
        // Keyboard shortcuts
        document.addEventListener('keydown', handleKeyboardShortcuts);
        
        // Click video to play/pause
        videoWrapper.addEventListener('click', function(e) {
            if (e.target !== centerPlayButton && 
                e.target !== playPauseBtn && 
                !e.target.closest('.video-controls-container') &&
                !e.target.closest('.video-question-overlay') &&
                !e.target.closest('.video-completed-overlay')) {
                togglePlayPause();
            }
        });
        
        // Double click for fullscreen
        videoWrapper.addEventListener('dblclick', toggleFullscreen);
        
        // Start inactivity timer
        startInactivityTimer();
    }
    
    function setupIframePlayer() {
        console.log('🔧 Setting up iframe player...');
        
        // For iframe, hide custom controls
        videoControls.style.display = 'none';
        centerPlayButton.style.display = 'none';
        
        // Just handle loading state
        hideLoading();
    }
    
    // ============================
    // VIDEO EVENT HANDLERS
    // ============================
    function handleVideoMetadata() {
    if (videoPlayer.duration && !videoDurationSet) {
        const totalDuration = formatTime(videoPlayer.duration);
        durationTimeEl.textContent = totalDuration;
        videoDurationSet = true;
    }
    
    currentTimeEl.textContent = formatTime(videoPlayer.currentTime);
    
    // Add question markers jika ada
    if (hasVideoQuestions && videoPlayer.duration) {
        addQuestionMarkersToTimeline();
    }
}
    
    function handleVideoLoaded() {
        console.log('✅ Video data loaded');
    }
    
    function handleCanPlay() {
        console.log('▶️ Video can play');
        hideLoading();
        
        // Show center play button
        centerPlayButton.style.display = 'flex';
        setTimeout(() => {
            centerPlayButton.classList.add('show');
        }, 100);
    }
    
    function handleTimeUpdate() {
    if (!videoPlayer.duration) return;
    
    const currentTime = videoPlayer.currentTime;
    previousTime = currentTime;
    
    if (!isDraggingProgress) {
        updateProgressBar();
        currentTimeEl.textContent = formatTime(currentTime);
    }
    
    // Jika video hampir selesai, tandai sebagai 100%
    if (videoPlayer.duration) {
        const progressPercentage = (currentTime / videoPlayer.duration) * 100;
        
        if (progressPercentage >= 99.9 && !window.isVideoCompleted) {
            progressBar.style.width = '100%';
            currentTimeEl.textContent = formatTime(videoPlayer.duration);
        }
    }
    
    // Cek pertanyaan
    if (hasVideoQuestions && !window.isQuestionActive && !isDraggingProgress && !videoPlayer.seeking) {
        checkForVideoQuestionsPrecise();
    }
}
    
    function handleSeeking() {
        console.log('⏩ Seeking to:', videoPlayer.currentTime);
    }
    
    function handleSeeked() {
        console.log('✅ Seeked to:', videoPlayer.currentTime);
        
        // Cek restrictions setelah seek
        if (disableForwardSeek || disableBackwardSeek) {
            const seekDirection = videoPlayer.currentTime > previousTime ? 'forward' : 'backward';
            
            if (seekDirection === 'forward' && disableForwardSeek) {
                videoPlayer.currentTime = previousTime;
                return;
            }
            
            if (seekDirection === 'backward' && disableBackwardSeek) {
                videoPlayer.currentTime = previousTime;
                return;
            }
        }
    }
    
    function handlePlay() {
        isPlaying = true;
        updatePlayPauseButton();
        centerPlayButton.classList.add('playing');
        centerPlayButton.querySelector('i').className = 'fas fa-pause';
        startInactivityTimer();
    }
    
    function handlePause() {
        isPlaying = false;
        updatePlayPauseButton();
        centerPlayButton.classList.remove('playing');
        centerPlayButton.querySelector('i').className = 'fas fa-play';
    }
    
    function handleVideoEnded() {
    console.log('🎬 Video ended');
    isPlaying = false;
    updatePlayPauseButton();
    centerPlayButton.classList.remove('playing');
    centerPlayButton.querySelector('i').className = 'fas fa-replay';
    
    // Mark video progress as 100%
    if (videoPlayer) {
        videoPlayer.currentTime = videoPlayer.duration || 0;
    }
    
    // Show completion overlay
    showVideoCompletionOverlay();
    
    // Mark as completed
    markVideoAsCompleted();
}
    
    function handleVideoError(e) {
        console.error('❌ Video error:', e);
        hideLoading();
    }
    
    function handleVolumeChange() {
        currentVolume = videoPlayer.volume;
        isMuted = videoPlayer.muted;
        updateVolumeButton();
        updateVolumeSlider();
    }
    
    function handleRateChange() {
        playbackRate = videoPlayer.playbackRate;
        updatePlaybackRateButton();
    }
    
    // ============================
    // VIDEO QUESTION FUNCTIONS - SIMPLIFIED
    // ============================
    function initializeQuestionTracking() {
        console.log('📝 Initializing video questions:', videoQuestions.length);
        
        // Sort questions by time
        videoQuestions.sort((a, b) => a.time_in_seconds - b.time_in_seconds);
        
        // Update question indicator
        updateQuestionIndicator();
        
        // Add question markers to timeline
        if (videoPlayer && videoPlayer.duration) {
            setTimeout(addQuestionMarkersToTimeline, 1000);
        }
    }
    
    function checkForVideoQuestionsPrecise() {
        if (!videoPlayer || window.isQuestionActive || answeredQuestions.size >= videoQuestions.length) return;
        
        const currentTime = Math.floor(videoPlayer.currentTime);
        
        // Skip jika cooldown aktif
        if (questionCheckCooldown) return;
        
        // Set cooldown
        questionCheckCooldown = true;
        setTimeout(() => { questionCheckCooldown = false; }, 500);
        
        // Cek semua pertanyaan
        for (const question of videoQuestions) {
            if (answeredQuestions.has(question.question_id)) continue;
            
            const questionTime = Math.floor(question.time_in_seconds);
            
            if (currentTime === questionTime || 
                (currentTime > questionTime && currentTime < questionTime + 2)) {
                
                // Cek apakah pertanyaan sudah pernah muncul
                if (questionLastTriggerTime === currentTime) {
                    continue;
                }
                
                // Tandai waktu trigger
                questionLastTriggerTime = currentTime;
                
                // Tampilkan pertanyaan
                showVideoQuestion(question);
                break;
            }
        }
    }
    
    function showVideoQuestion(question) {
        console.log('🎯 Question triggered at:', question.time_in_seconds, 'seconds');
        
        currentQuestion = question;
        window.isQuestionActive = true;
        
        // Auto pause video
        if (autoPauseOnQuestion && videoPlayer && !videoPlayer.paused) {
            videoPlayer.pause();
        }
        
        // Create question overlay
        const questionHtml = `
            <div class="video-question-overlay" id="question-${question.question_id}">
                <div class="video-question-container">
                    <div class="question-header">
                        <h4>
                            <i class="fas fa-question-circle"></i>
                            Pertanyaan
                        </h4>
                        <div class="question-time-indicator">
                            <i class="fas fa-clock"></i>
                            Detik ${formatTime(question.time_in_seconds)}
                        </div>
                    </div>
                    
                    <div class="question-content">
                        <div class="question-text">
                            ${question.question}
                        </div>
                        
                        <div class="options-grid" id="options-${question.question_id}">
                            ${question.options.map((option, index) => `
                                <div class="option-card" 
                                     data-index="${index}" 
                                     onclick="window.handleQuestionAnswer(${question.question_id}, ${index})">
                                    <div class="option-icon">
                                        ${String.fromCharCode(65 + index)}
                                    </div>
                                    <div class="option-text">
                                        ${option}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    
                    <div class="question-footer">
                        <div class="question-stats">
                            <div class="question-counter">
                                <i class="fas fa-list-ol"></i>
                                ${answeredQuestions.size + 1}/${videoQuestions.length}
                            </div>
                        </div>
                        <div class="question-actions">
                            ${!question.required_to_continue ? 
                                `<button class="btn btn-outline-secondary btn-sm" onclick="window.skipQuestion(${question.question_id})">
                                    <i class="fas fa-forward me-1"></i> Lewati
                                </button>` : ''
                            }
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.getElementById('question-overlay-container').innerHTML = questionHtml;
    }
    
    // FUNGSI JAWABAN YANG DISEDERHANAKAN
    window.handleQuestionAnswer = async function(questionId, optionIndex) {
        const question = videoQuestions.find(q => q.question_id === questionId);
        if (!question) return;
        
        const isCorrect = optionIndex === question.correct_option;
        
        // Tampilkan visual feedback
        const optionsContainer = document.getElementById(`options-${questionId}`);
        const cards = optionsContainer.querySelectorAll('.option-card');
        
        // Nonaktifkan semua kartu
        cards.forEach(card => {
            card.classList.add('disabled');
            card.style.pointerEvents = 'none';
        });
        
        // Tandai jawaban yang dipilih
        cards[optionIndex].classList.add('selected');
        
        // Mark as answered
        answeredQuestions.add(questionId);
        
        // Tambah poin jika benar
        if (isCorrect) {
            totalPointsEarned += question.points;
        }
        
        // Update indicator
        updateQuestionIndicator();
        
        // Kirim jawaban ke server
        await saveQuestionAnswer(questionId, optionIndex, isCorrect, isCorrect ? question.points : 0);
        
        // Auto close setelah 2 detik
        setTimeout(() => {
            closeQuestionModalAndContinue(questionId);
        }, 2000);
    };
    
    function closeQuestionModalAndContinue(questionId) {
        const overlay = document.getElementById(`question-${questionId}`);
        if (overlay) {
            overlay.style.animation = 'questionSlideIn 0.4s ease-out reverse';
            setTimeout(() => {
                if (overlay.parentNode) {
                    overlay.remove();
                }
            }, 400);
        }
        
        window.isQuestionActive = false;
        currentQuestion = null;
        
        // Lanjutkan video
        if (videoPlayer && videoPlayer.paused && !window.isVideoCompleted) {
            videoPlayer.play();
        }
    }
    
    window.skipQuestion = function(questionId) {
        answeredQuestions.add(questionId);
        closeQuestionModalAndContinue(questionId);
        updateQuestionIndicator();
    };
    
    // ============================
    // PLAYBACK CONTROLS
    // ============================
    function togglePlayPause() {
        if (window.isQuestionActive || window.isVideoCompleted) return;
        
        if (videoPlayer.paused) {
            videoPlayer.play().catch(error => {
                console.error('Error playing video:', error);
            });
        } else {
            videoPlayer.pause();
        }
        showControls();
    }
    
    function skipBackward() {
        if (disableBackwardSeek) return;
        
        const newTime = Math.max(0, videoPlayer.currentTime - 10);
        videoPlayer.currentTime = newTime;
        showControls();
    }
    
    function skipForward() {
        if (disableForwardSeek) return;
        
        const newTime = Math.min(videoPlayer.duration, videoPlayer.currentTime + 10);
        videoPlayer.currentTime = newTime;
        showControls();
    }
    
    // ============================
    // PROGRESS BAR
    // ============================
    function handleProgressClick(e) {
        if (!videoPlayer.duration || disableForwardSeek || disableBackwardSeek) return;
        if (window.isQuestionActive || window.isVideoCompleted) return;
        
        const rect = progressContainer.getBoundingClientRect();
        const pos = (e.clientX - rect.left) / rect.width;
        const newTime = pos * videoPlayer.duration;
        
        // Check restrictions
        if (newTime > videoPlayer.currentTime && disableForwardSeek) return;
        if (newTime < videoPlayer.currentTime && disableBackwardSeek) return;
        
        videoPlayer.currentTime = newTime;
        showControls();
    }
    
    function startDraggingProgress(e) {
        if (disableForwardSeek && disableBackwardSeek) return;
        if (window.isQuestionActive || window.isVideoCompleted) return;
        
        e.preventDefault();
        isDraggingProgress = true;
        videoWrapper.classList.add('scaled');
        showControls();
    }
    
    function startDraggingProgressTouch(e) {
        if (disableForwardSeek && disableBackwardSeek) return;
        if (window.isQuestionActive || window.isVideoCompleted) return;
        
        e.preventDefault();
        isDraggingProgress = true;
        videoWrapper.classList.add('scaled');
        showControls();
    }
    
    function handleProgressDrag(e) {
        if (!isDraggingProgress) return;
        
        e.preventDefault();
        const rect = progressContainer.getBoundingClientRect();
        const pos = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
        const newTime = pos * videoPlayer.duration;
        
        // Update progress bar visual
        progressBar.style.width = (pos * 100) + '%';
        
        // Update time display while dragging
        updateTimeDisplay(newTime, videoPlayer.duration);
    }
    
    function handleProgressDragTouch(e) {
        if (!isDraggingProgress) return;
        
        e.preventDefault();
        const rect = progressContainer.getBoundingClientRect();
        const touch = e.touches[0];
        const pos = Math.max(0, Math.min(1, (touch.clientX - rect.left) / rect.width));
        const newTime = pos * videoPlayer.duration;
        
        // Update progress bar visual
        progressBar.style.width = (pos * 100) + '%';
        
        // Update time display while dragging
        updateTimeDisplay(newTime, videoPlayer.duration);
    }
    
    function stopDraggingProgress(e) {
        if (!isDraggingProgress) return;
        
        isDraggingProgress = false;
        videoWrapper.classList.remove('scaled');
        
        if (videoPlayer.duration && !disableForwardSeek && !disableBackwardSeek) {
            const rect = progressContainer.getBoundingClientRect();
            const pos = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            const newTime = pos * videoPlayer.duration;
            
            // Check restrictions
            if (newTime > videoPlayer.currentTime && disableForwardSeek) {
                updateProgressBar();
                return;
            }
            
            if (newTime < videoPlayer.currentTime && disableBackwardSeek) {
                updateProgressBar();
                return;
            }
            
            videoPlayer.currentTime = newTime;
        }
    }
    
    function stopDraggingProgressTouch(e) {
        if (!isDraggingProgress) return;
        
        isDraggingProgress = false;
        videoWrapper.classList.remove('scaled');
        
        if (videoPlayer.duration && !disableForwardSeek && !disableBackwardSeek) {
            const rect = progressContainer.getBoundingClientRect();
            const touch = e.changedTouches[0];
            const pos = Math.max(0, Math.min(1, (touch.clientX - rect.left) / rect.width));
            const newTime = pos * videoPlayer.duration;
            
            // Check restrictions
            if (newTime > videoPlayer.currentTime && disableForwardSeek) {
                updateProgressBar();
                return;
            }
            
            if (newTime < videoPlayer.currentTime && disableBackwardSeek) {
                updateProgressBar();
                return;
            }
            
            videoPlayer.currentTime = newTime;
        }
    }
    
    function updateProgressBar() {
    if (!videoPlayer.duration) return;
    const percent = (videoPlayer.currentTime / videoPlayer.duration) * 100;
    progressBar.style.width = percent + '%';
}
    
    // ============================
    // VOLUME CONTROLS
    // ============================
    function toggleMute() {
        videoPlayer.muted = !videoPlayer.muted;
        showControls();
    }
    
    function handleVolumeClick(e) {
        const rect = volumeSlider.getBoundingClientRect();
        const pos = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
        currentVolume = pos;
        videoPlayer.volume = currentVolume;
        videoPlayer.muted = currentVolume === 0;
        updateVolumeSlider();
        showControls();
    }
    
    function startDraggingVolume(e) {
        isDraggingVolume = true;
        showControls();
    }
    
    function handleVolumeDrag(e) {
        if (!isDraggingVolume) return;
        
        e.preventDefault();
        const rect = volumeSlider.getBoundingClientRect();
        const pos = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
        currentVolume = pos;
        videoPlayer.volume = currentVolume;
        videoPlayer.muted = currentVolume === 0;
        updateVolumeSlider();
    }
    
    function stopDraggingVolume(e) {
        isDraggingVolume = false;
    }
    
    function updateVolumeSlider() {
        const volume = videoPlayer.muted ? 0 : videoPlayer.volume;
        volumeSliderFill.style.width = (volume * 100) + '%';
    }
    
    // ============================
    // TIME DISPLAY FUNCTIONS
    // ============================
    function updateCurrentTimeDisplay() {
        if (!videoPlayer.duration) return;
        updateTimeDisplay(videoPlayer.currentTime, videoPlayer.duration);
    }
    
    function updateDurationDisplay() {
        if (!videoPlayer.duration) return;
        updateTimeDisplay(videoPlayer.currentTime, videoPlayer.duration);
    }
    
    function updateTimeDisplay(current, duration) {
        currentTimeEl.textContent = formatTime(current);
        durationTimeEl.textContent = formatTime(duration);
    }
    
    function formatTime(seconds) {
    if (isNaN(seconds)) return '0:00';
    
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = Math.floor(seconds % 60);
    
    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    } else {
        return `${minutes}:${secs.toString().padStart(2, '0')}`;
    }
}
    
    // ============================
    // UI UPDATES
    // ============================
    function updatePlayPauseButton() {
        if (videoPlayer.paused) {
            playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
            centerPlayButton.querySelector('i').className = 'fas fa-play';
        } else {
            playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
            centerPlayButton.querySelector('i').className = 'fas fa-pause';
        }
    }
    
    function updateVolumeButton() {
        if (videoPlayer.muted || videoPlayer.volume === 0) {
            volumeBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
        } else if (videoPlayer.volume < 0.5) {
            volumeBtn.innerHTML = '<i class="fas fa-volume-down"></i>';
        } else {
            volumeBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
        }
    }
    
    function updatePlaybackRateButton() {
        // Tidak perlu update text
    }
    
    // ============================
    // VIDEO COMPLETION FUNCTIONS
    // ============================
    function showVideoCompletionOverlay() {
        window.isVideoCompleted = true;
        videoCompletedOverlay.style.display = 'flex';
        
        // Update stats
        if (completedQuestionsEl && earnedPointsEl) {
            completedQuestionsEl.textContent = answeredQuestions.size;
            earnedPointsEl.textContent = totalPointsEarned;
        }
        
        // Hide controls
        hideControls();
        centerPlayButton.style.display = 'none';
    }
    
    function hideVideoCompletionOverlay() {
        window.isVideoCompleted = false;
        videoCompletedOverlay.style.display = 'none';
        centerPlayButton.style.display = 'flex';
    }
    
    function replayVideo() {
        hideVideoCompletionOverlay();
        videoPlayer.currentTime = 0;
        videoPlayer.play();
        showControls();
    }
    
    // ============================
    // HELPER FUNCTIONS
    // ============================
    function hideLoading() {
        if (videoLoading) {
            videoLoading.style.display = 'none';
        }
    }
    
    function updateQuestionIndicator() {
        if (!questionIndicator) return;
        
        const answeredCount = answeredQuestions.size;
        const totalQuestions = videoQuestions.length;
        
        questionCountEl.textContent = `${answeredCount}/${totalQuestions}`;
        
        // Update completion stats
        if (completedQuestionsEl && earnedPointsEl) {
            completedQuestionsEl.textContent = answeredCount;
            earnedPointsEl.textContent = totalPointsEarned;
        }
        
        // Update markers
        updateQuestionMarkers();
    }
    
    function addQuestionMarkersToTimeline() {
        if (!videoPlayer || !videoPlayer.duration || !hasVideoQuestions) return;
        
        // Remove existing markers
        const existingMarkers = document.querySelectorAll('.question-marker');
        existingMarkers.forEach(marker => marker.remove());
        
        // Add new markers
        videoQuestions.forEach(question => {
            const markerTime = question.time_in_seconds;
            const markerPosition = (markerTime / videoPlayer.duration) * 100;
            
            const marker = document.createElement('div');
            marker.className = `question-marker ${answeredQuestions.has(question.question_id) ? 'answered' : 'unanswered'}`;
            marker.style.cssText = `
                position: absolute;
                left: ${markerPosition}%;
                top: -2px;
                width: 8px;
                height: 14px;
                background: ${answeredQuestions.has(question.question_id) ? '#4CAF50' : '#FF9800'};
                border-radius: 4px;
                z-index: 5;
                cursor: pointer;
                transition: all 0.3s ease;
                transform: translateX(-50%);
            `;
            marker.addEventListener('click', (e) => {
                e.stopPropagation();
                if (!answeredQuestions.has(question.question_id)) {
                    videoPlayer.currentTime = markerTime;
                    showControls();
                }
            });
            
            progressContainer.appendChild(marker);
        });
    }
    
    function updateQuestionMarkers() {
        const markers = document.querySelectorAll('.question-marker');
        markers.forEach((marker, index) => {
            if (index < videoQuestions.length) {
                const question = videoQuestions[index];
                marker.style.background = answeredQuestions.has(question.question_id) ? '#4CAF50' : '#FF9800';
                marker.className = `question-marker ${answeredQuestions.has(question.question_id) ? 'answered' : 'unanswered'}`;
            }
        });
    }
    
    // ============================
    // CONTROLS VISIBILITY
    // ============================
    function showControls() {
        if (window.isQuestionActive || window.isVideoCompleted) return;
        
        controlsVisible = true;
        videoWrapper.classList.add('controls-visible');
        videoWrapper.classList.remove('inactive');
        
        // Show center play button only when paused
        if (videoPlayer.paused) {
            centerPlayButton.style.opacity = '1';
        }
        
        clearTimeout(inactivityTimer);
        startInactivityTimer();
    }
    
    function startInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => {
            if (!videoPlayer.paused && !window.isQuestionActive && !window.isVideoCompleted) {
                hideControls();
            }
        }, 3000);
    }
    
    function hideControls() {
        controlsVisible = false;
        videoWrapper.classList.remove('controls-visible');
        videoWrapper.classList.add('inactive');
        
        // Always hide center button when inactive
        centerPlayButton.style.opacity = '0';
        
        // Hide menus
        playbackRateMenu.classList.remove('show');
        settingsMenu.classList.remove('show');
    }
    
    // ============================
    // MENUS
    // ============================
    function setPlaybackRate(rate) {
        videoPlayer.playbackRate = rate;
        playbackRate = rate;
        
        // Update menu selection
        playbackRateMenu.querySelectorAll('.playback-rate-item').forEach(item => {
            if (parseFloat(item.dataset.rate) === rate) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
    
    function togglePlaybackRateMenu() {
        playbackRateMenu.classList.toggle('show');
        settingsMenu.classList.remove('show');
        showControls();
    }
    
    function toggleSettingsMenu() {
        settingsMenu.classList.toggle('show');
        playbackRateMenu.classList.remove('show');
        showControls();
    }
    
    // ============================
    // FULLSCREEN
    // ============================
    function toggleFullscreen() {
        if (!isFullscreen) {
            enterFullscreen();
        } else {
            exitFullscreen();
        }
        showControls();
    }
    
    function enterFullscreen() {
        const elem = videoWrapper;
        
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen();
        } else if (elem.mozRequestFullScreen) {
            elem.mozRequestFullScreen();
        } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen();
        }
    }
    
    function exitFullscreen() {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
    
    function handleFullscreenChange() {
        isFullscreen = !!(document.fullscreenElement || 
                         document.webkitFullscreenElement ||
                         document.mozFullScreenElement ||
                         document.msFullscreenElement);
        
        if (isFullscreen) {
            fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
            videoWrapper.classList.add('fullscreen');
        } else {
            fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
            videoWrapper.classList.remove('fullscreen');
        }
    }
    
    // ============================
    // KEYBOARD SHORTCUTS
    // ============================
    function handleKeyboardShortcuts(e) {
        // Ignore if user is typing in input
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        
        // Handle question shortcuts first
        if (window.isQuestionActive && currentQuestion) {
            handleQuestionShortcuts(e);
            return;
        }
        
        // Handle video completion overlay
        if (window.isVideoCompleted) {
            if (e.key === 'Escape') {
                hideVideoCompletionOverlay();
            }
            return;
        }
        
        switch(e.key.toLowerCase()) {
            case ' ':
            case 'k':
                e.preventDefault();
                togglePlayPause();
                break;
                
            case 'f':
                toggleFullscreen();
                break;
                
            case 'm':
                e.preventDefault();
                toggleMute();
                break;
                
            case 'arrowleft':
                e.preventDefault();
                skipBackward();
                break;
                
            case 'arrowright':
                e.preventDefault();
                skipForward();
                break;
        }
    }
    
    function handleQuestionShortcuts(e) {
        if (!currentQuestion) return;
        
        const key = e.key.toUpperCase();
        
        // A, B, C, D untuk memilih jawaban
        if (['A', 'B', 'C', 'D'].includes(key)) {
            const optionIndex = key.charCodeAt(0) - 65;
            if (optionIndex < currentQuestion.options.length) {
                window.handleQuestionAnswer(currentQuestion.question_id, optionIndex);
            }
        }
        
        // Escape untuk skip (jika diizinkan)
        if (e.key === 'Escape' && !currentQuestion.required_to_continue) {
            window.skipQuestion(currentQuestion.question_id);
        }
    }
    
    // Fungsi sederhana untuk menyimpan jawaban
    async function saveQuestionAnswer(questionId, answer, isCorrect, points) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            
            if (!csrfToken) {
                console.error('❌ CSRF token not found');
                return;
            }
            
            const response = await fetch(`/mitra/kursus/{{ $kursus->id }}/material/{{ $material->id }}/save-video-question`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    question_id: questionId,
                    answer: answer,
                    is_correct: isCorrect,
                    points: points,
                    time_answered: new Date().toISOString()
                })
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('❌ Error saving answer:', error);
            return null;
        }
    }
    
    async function markVideoAsCompleted() {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        
        if (!csrfToken) {
            console.warn('⚠️ CSRF token not found');
            return;
        }
        
        const response = await fetch(`/mitra/kursus/{{ $kursus->id }}/materials/{{ $material->id }}/force-complete-video`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                questions_answered: Array.from(answeredQuestions),
                total_points: totalPointsEarned,
                completed_at: new Date().toISOString(),
                video_progress: 100,
                video_duration: videoPlayer?.duration || 0,
                force_complete: true
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Beri tahu halaman induk
            if (window.opener || window.parent !== window) {
                try {
                    window.opener?.postMessage?.({ 
                        type: 'video_completed', 
                        materialId: {{ $material->id }},
                        kursusId: {{ $kursus->id }},
                        force_completed: true
                    }, '*');
                    
                    window.parent?.postMessage?.({ 
                        type: 'video_completed', 
                        materialId: {{ $material->id }},
                        kursusId: {{ $kursus->id }},
                        force_completed: true
                    }, '*');
                } catch (e) {
                    console.log('ℹ️ Could not notify parent window');
                }
            }
        }
    } catch (error) {
        console.error('❌ Error completing video:', error);
    }
}
    
    // Initialize the player
    initVideoPlayer();
});
</script>

<!-- Tambahkan Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Tambahkan Bootstrap 5 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection