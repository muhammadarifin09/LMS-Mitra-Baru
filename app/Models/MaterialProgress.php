<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialProgress extends Model
{
    use HasFactory;

    protected $table = 'material_progress';

    protected $fillable = [
        'user_id',
        'material_id',
        'attendance_status',
        'material_status', 
        'video_status',
        'quiz_answers',
        'pretest_score',
        'posttest_score',
        'pretest_completed_at',
        'posttest_completed_at',
        'attempts',
        'is_completed',
        'completed_at',
        'video_question_points',
        'video_progress',
        'video_current_time',
        'video_duration',
        'total_files',
        'downloaded_files',
        'all_files_downloaded'
    ];

    protected $casts = [
        'quiz_answers' => 'array',
        'completed_at' => 'datetime',
        'pretest_completed_at' => 'datetime',
        'posttest_completed_at' => 'datetime',
        'is_completed' => 'boolean',
        'downloaded_files' => 'array',
        'all_files_downloaded' => 'boolean'
    ];

    // RELASI
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function material()
    {
        return $this->belongsTo(Materials::class, 'material_id');
    }

    // Pastikan UserVideoQuestionAnswer model sudah dibuat
    public function videoQuestionAnswers()
    {
        return $this->hasMany(UserVideoQuestionAnswer::class, 'material_id', 'material_id')
                    ->where('user_id', $this->user_id);
    }

    // HELPER METHODS
    public function markAttendanceCompleted()
    {
        $this->update(['attendance_status' => 'completed']);
        return $this;
    }

    public function markMaterialCompleted()
    {
        $this->update(['material_status' => 'completed']);
        return $this;
    }

    public function markVideoCompleted()
    {
        $this->update(['video_status' => 'completed']);
        return $this;
    }

    public function markPretestCompleted($score)
    {
        $this->update([
            'pretest_score' => $score,
            'pretest_completed_at' => now(),
            'attempts' => ($this->attempts ?? 0) + 1
        ]);
        return $this;
    }

    public function markPosttestCompleted($score)
    {
        $this->update([
            'posttest_score' => $score,
            'posttest_completed_at' => now(),
            'attempts' => ($this->attempts ?? 0) + 1
        ]);
        return $this;
    }

    // Method untuk menandai video question selesai
    public function markVideoQuestionsCompleted($points)
    {
        $this->update([
            'video_question_points' => $points,
            'video_status' => 'completed'
        ]);
        return $this;
    }

    public function isCompleted()
    {
        return $this->is_completed || (
            $this->attendance_status === 'completed' &&
            $this->material_status === 'completed' &&
            $this->video_status === 'completed'
        );
    }
}