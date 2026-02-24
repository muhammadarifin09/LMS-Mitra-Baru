<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVideoProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'material_id',
        'progress_percentage',
        'current_time',           // Ubah dari last_watched_second
        'duration',
        'is_completed',
        'completed_at',
        'total_watch_time',       // Tambahkan ini
        'completion_count',       // Tambahkan ini
        'last_watched_at',        // Tambahkan ini
        'total_points_earned',
        'watch_history',
        'answered_questions'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'last_watched_at' => 'datetime',
        'watch_history' => 'array',
        'answered_questions' => 'array',
        'total_watch_time' => 'integer',
        'completion_count' => 'integer',
        'current_time' => 'integer',
        'duration' => 'integer',
        'progress_percentage' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(M_User::class);
    }

    public function material()
    {
        return $this->belongsTo(Materials::class, 'material_id');
    }

    public function getAnsweredQuestionsCountAttribute()
    {
        return is_array($this->answered_questions) ? count($this->answered_questions) : 0;
    }

    public function hasAnsweredQuestion($questionId)
    {
        return is_array($this->answered_questions) && in_array($questionId, $this->answered_questions);
    }
    
    // Scope untuk video yang sudah selesai berdasarkan persentase minimum
    public function scopeCompleted($query, $minPercentage = 90)
    {
        return $query->where('progress_percentage', '>=', $minPercentage)
                    ->where('is_completed', true);
    }
    
    // Scope untuk video yang sudah ditonton (progress > 0)
    public function scopeWatched($query, $minProgress = 10)
    {
        return $query->where('progress_percentage', '>=', $minProgress);
    }
}