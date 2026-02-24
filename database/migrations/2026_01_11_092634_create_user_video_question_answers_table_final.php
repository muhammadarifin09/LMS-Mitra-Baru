<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // HAPUS TABEL JIKA SUDAH ADA (dari migration gagal sebelumnya)
        Schema::dropIfExists('user_video_question_answers');
        
        // BARU BUAT TABEL
        Schema::create('user_video_question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('video_questions')->onDelete('cascade');
            $table->integer('answer');
            $table->boolean('is_correct');
            $table->integer('points_earned')->default(0);
            $table->timestamp('answered_at')->useCurrent();
            $table->timestamps();
            
            // Nama index pendek
            $table->unique(['user_id', 'material_id', 'question_id'], 'uvqa_uniq');
            
            // Index untuk query
            $table->index(['user_id', 'material_id'], 'uvqa_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_video_question_answers');
    }
};