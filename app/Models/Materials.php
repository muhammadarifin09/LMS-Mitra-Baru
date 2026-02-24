<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log;

class Materials extends Model
{
    protected $table = 'materials';
    
    protected $fillable = [
        'course_id',
        'title',
        'type',
        'order',
        'material_type',
        'description',
        'duration',
        'file_path',
        'video_url',
        'video_type',
        'is_active',
        'attendance_required',
        'learning_objectives',
        'soal_pretest',
        'durasi_pretest',
        'soal_posttest',
        'durasi_posttest',
        'video_file',
        'player_config',
        'allow_skip',
        'has_video_questions',
        'require_video_completion',
        'question_count',
        'total_video_points',
        'auto_duration',
        'is_pretest',
        'is_posttest',
        'total_views',
        'total_completions',
        'avg_completion_time',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'attendance_required' => 'boolean',
        'durasi_pretest' => 'integer',
        'durasi_posttest' => 'integer',
        'order' => 'integer',
        'duration' => 'integer',
        'soal_pretest' => 'array',  // <<< TAMBAHKAN INI
        'soal_posttest' => 'array',
        'allow_skip' => 'boolean',
        'has_video_questions' => 'boolean',
        'require_video_completion' => 'boolean',
        'question_count' => 'integer',
        'total_video_points' => 'integer',
        'auto_duration' => 'boolean',
        'is_pretest' => 'boolean',
        'is_posttest' => 'boolean',
        'total_views' => 'integer',
        'total_completions' => 'integer',
        'avg_completion_time' => 'decimal:2',
        // JSON casting untuk semua field JSON
        'file_path' => 'array',
        'learning_objectives' => 'array',
        'video_file' => 'array',
        'player_config' => 'array',
    ]; 

    /**
     * Mutator untuk video_file dengan validasi JSON yang kuat
     */
    protected function videoFile(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Jika null atau string "null", return null
                if (is_null($value) || $value === 'null' || $value === '') {
                    return null;
                }
                
                // Jika sudah array, langsung return
                if (is_array($value)) {
                    return $value;
                }
                
                // Jika string, coba decode JSON
                if (is_string($value)) {
                    // Coba decode
                    $decoded = json_decode($value, true);
                    
                    // Jika decode berhasil, return array
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }
                    
                    // Jika decode gagal, log error dan return null
                    Log::warning('JSON decode error in video_file mutator', [
                        'material_id' => $this->id,
                        'error' => json_last_error_msg(),
                        'json_value_sample' => substr($value, 0, 200)
                    ]);
                    return null;
                }
                
                // Untuk tipe data lain, return null
                return null;
            },
            set: function ($value) {
                // Jika value null atau string "null", simpan null
                if (is_null($value) || $value === 'null') {
                    return null;
                }
                
                // Jika value array, encode ke JSON
                if (is_array($value)) {
                    $json = json_encode($value);
                    
                    // Validasi JSON sebelum disimpan
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('JSON encode error in video_file mutator', [
                            'material_id' => $this->id,
                            'error' => json_last_error_msg(),
                            'value_sample' => json_encode(array_slice($value, 0, 3))
                        ]);
                        return null;
                    }
                    
                    return $json;
                }
                
                // Jika value sudah string JSON, validasi dulu
                if (is_string($value)) {
                    // Coba decode untuk validasi
                    json_decode($value);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $value;
                    }
                    
                    Log::warning('Invalid JSON string in video_file mutator', [
                        'material_id' => $this->id,
                        'error' => json_last_error_msg(),
                        'string_sample' => substr($value, 0, 200)
                    ]);
                    return null;
                }
                
                // Untuk tipe data lain, simpan null
                return null;
            }
        );
    }

    // Accessor untuk video data
public function getVideoDataAttribute()
{
    if (!empty($this->video_file)) {
        if (is_array($this->video_file)) {
            return $this->video_file;
        }
        
        if (is_string($this->video_file)) {
            $decoded = json_decode($this->video_file, true);
            return $decoded ?: [];
        }
    }
    
    return [];
}

// Method untuk cek apakah memiliki file
public function getHasFileAttribute()
{
    return !empty($this->file_path) && is_array($this->file_path) && count($this->file_path) > 0;
}

// Method untuk cek apakah memiliki video
public function getHasVideoAttribute()
{
    return !empty($this->video_url) || !empty($this->video_file);
}

    /**
     * Check if string is valid JSON
     */
    private function isValidJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * The "booted" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set order when creating
        static::creating(function ($material) {
            if (empty($material->order)) {
                $lastOrder = Materials::where('course_id', $material->course_id)->max('order');
                $material->order = $lastOrder ? $lastOrder + 1 : 1;
            }
        });

        // Reorder after deleting a material
        static::deleted(function ($material) {
            Materials::where('course_id', $material->course_id)
                ->where('order', '>', $material->order)
                ->decrement('order');
        });

        // Reorder after updating order (if changed manually)
        static::updating(function ($material) {
            if ($material->isDirty('order') && $material->order != $material->getOriginal('order')) {
                $oldOrder = $material->getOriginal('order');
                $newOrder = $material->order;
                $courseId = $material->course_id;

                if ($newOrder > $oldOrder) {
                    // Move down (order increased)
                    Materials::where('course_id', $courseId)
                        ->where('id', '!=', $material->id)
                        ->where('order', '>', $oldOrder)
                        ->where('order', '<=', $newOrder)
                        ->decrement('order');
                } else {
                    // Move up (order decreased)
                    Materials::where('course_id', $courseId)
                        ->where('id', '!=', $material->id)
                        ->where('order', '>=', $newOrder)
                        ->where('order', '<', $oldOrder)
                        ->increment('order');
                }
            }
        });
    }

    // Relationship sederhana tanpa eager loading yang berat
    public function kursus(): BelongsTo
    {
        return $this->belongsTo(Kursus::class, 'course_id')
            ->select(['id', 'judul_kursus', 'kode_kursus']);
    }

    public function videoQuestions(): HasMany
    {
        return $this->hasMany(VideoQuestion::class, 'material_id')
            ->select(['id', 'material_id', 'question', 'order', 'time_in_seconds'])
            ->orderBy('order');
    }

    public function hasVideoQuestions()
{
    return $this->videoQuestions()->exists();
}

    // Helper methods sederhana
    public function getFileCount(): int
    {
        $files = $this->file_path;
        return is_array($files) ? count($files) : 0;
    }
    
    public function getPretestCount(): int
    {
        $soal = $this->soal_pretest;
        return is_array($soal) ? count($soal) : 0;
    }
    
    public function getPosttestCount(): int
    {
        $soal = $this->soal_posttest;
        return is_array($soal) ? count($soal) : 0;
    }
    
    // Simple query scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }

    /**
     * Check if video is available
     */
    public function isVideoAvailable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $videoType = $this->video_type;
        
        if (empty($videoType)) {
            return false;
        }
        
        // YouTube videos
        if ($videoType === 'youtube') {
            return !empty($this->video_url) && $this->isValidYouTubeUrl($this->video_url);
        }
        
        // Hosted (Google Drive) or Local videos
        if (in_array($videoType, ['hosted', 'local'])) {
            $videoFile = $this->video_file;
            
            // Jika video_file null atau tidak array
            if (empty($videoFile) || !is_array($videoFile)) {
                return false;
            }
            
            // Untuk Google Drive videos
            if ($videoType === 'hosted') {
                $hasEmbedLink = !empty($videoFile['embed_link']);
                $hasFileId = !empty($videoFile['file_id']);
                $hasWebViewLink = !empty($videoFile['web_view_link']);
                
                return $hasEmbedLink || $hasFileId || $hasWebViewLink;
            }
            
            // Untuk Local videos
            if ($videoType === 'local') {
                $hasPath = !empty($videoFile['path']);
                $hasUrl = !empty($videoFile['url']);
                
                if ($hasPath) {
                    return \Illuminate\Support\Facades\Storage::disk('public')->exists($videoFile['path']);
                }
                
                return $hasUrl;
            }
        }
        
        return false;
    }
    
    /**
     * Check if YouTube URL is valid
     */
    private function isValidYouTubeUrl($url): bool
    {
        if (empty($url)) {
            return false;
        }
        
        $patterns = [
            '/^(https?\:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/',
            '/^(https?\:\/\/)?(www\.)?youtube\.com\/watch\?v=[\w-]+/',
            '/^(https?\:\/\/)?(www\.)?youtu\.be\/[\w-]+/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }
        
        return false;
    }

    public function getFilesWithUrls()
    {
        $files = [];
        
        if ($this->file_path) {
            $fileData = is_string($this->file_path) ? json_decode($this->file_path, true) : $this->file_path;
            
            if (is_array($fileData)) {
                foreach ($fileData as $path) {
                    $files[] = [
                        'path' => $path,
                        'name' => basename($path),
                        'url' => Storage::url($path)
                    ];
                }
            } elseif (is_string($this->file_path)) {
                $files[] = [
                    'path' => $this->file_path,
                    'name' => basename($this->file_path),
                    'url' => Storage::url($this->file_path)
                ];
            }
        }
        
        return $files;
    }
    
    /**
     * Get video data with URL
     */
    public function getVideoDataWithUrl()
    {
        if (!$this->video_file) {
            return null;
        }
        
        $videoData = is_string($this->video_file) ? json_decode($this->video_file, true) : $this->video_file;
        
        if ($videoData && isset($videoData['path'])) {
            $videoData['url'] = Storage::url($videoData['path']);
        }
        
        return $videoData;
    }
    
    /**
     * Get video embed URL
     */
    public function getVideoEmbedUrl()
    {
        if (!$this->isVideoAvailable()) {
            return null;
        }
        
        $videoType = $this->video_type;
        
        if ($videoType === 'youtube') {
            $videoId = $this->extractYouTubeId($this->video_url);
            if ($videoId) {
                return 'https://www.youtube.com/embed/' . $videoId . '?rel=0&modestbranding=1&showinfo=0';
            }
        }
        
        if ($videoType === 'hosted') {
            $videoFile = $this->video_file;
            if (isset($videoFile['embed_link'])) {
                return $videoFile['embed_link'];
            }
            
            if (isset($videoFile['file_id'])) {
                return 'https://drive.google.com/file/d/' . $videoFile['file_id'] . '/preview';
            }
        }
        
        if ($videoType === 'local') {
            $videoFile = $this->video_file;
            if (isset($videoFile['url'])) {
                return $videoFile['url'];
            }
        }
        
        return null;
    }
    
    /**
     * Extract YouTube ID from URL
     */
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
    
    /**
     * Get clean video data for frontend
     */
    public function getCleanVideoData(): array
    {
        if (!$this->isVideoAvailable()) {
            return [
                'is_available' => false,
                'error_message' => 'Video tidak tersedia'
            ];
        }
        
        $videoType = $this->video_type;
        $videoFile = $this->video_file;
        
        $data = [
            'type' => $videoType,
            'is_available' => true,
            'player_config' => $this->player_config,
            'allow_skip' => $this->allow_skip ?? false,
            'require_completion' => $this->require_video_completion ?? true,
            'has_video_questions' => $this->has_video_questions ?? false,
        ];
        
        if ($videoType === 'youtube') {
            $data['url'] = $this->video_url;
            $data['embed_url'] = $this->getVideoEmbedUrl();
            $data['thumbnail'] = 'https://img.youtube.com/vi/' . $this->extractYouTubeId($this->video_url) . '/maxresdefault.jpg';
        }
        
        if ($videoType === 'hosted' && is_array($videoFile)) {
            $data = array_merge($data, [
                'embed_url' => $videoFile['embed_link'] ?? $this->getVideoEmbedUrl(),
                'file_id' => $videoFile['file_id'] ?? null,
                'web_view_link' => $videoFile['web_view_link'] ?? null,
                'thumbnail' => $videoFile['thumbnail_link'] ?? null,
                'file_name' => $videoFile['file_name'] ?? null,
                'size' => $videoFile['size'] ?? null,
            ]);
        }
        
        if ($videoType === 'local' && is_array($videoFile)) {
            $data = array_merge($data, [
                'url' => $videoFile['url'] ?? Storage::url($videoFile['path'] ?? ''),
                'path' => $videoFile['path'] ?? null,
                'size' => $videoFile['size'] ?? null,
                'duration' => $videoFile['duration'] ?? $this->duration,
            ]);
        }
        
        return $data;
    }
}