<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVideoQuestionAnswer extends Model
{
    use HasFactory;

    protected $table = 'user_video_question_answers';

    protected $fillable = [
        'user_id',           // Pastikan ini ADA
        'material_id',       // Pastikan ini ADA  
        'question_id',       // Pastikan ini ADA
        'answer',
        'is_correct',
        'points_earned',
        'answered_at'
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'answered_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(M_User::class, 'user_id');
    }

    public function material()
    {
        return $this->belongsTo(Materials::class, 'material_id');
    }

    public function question()
    {
        return $this->belongsTo(VideoQuestion::class, 'question_id');
    }
}