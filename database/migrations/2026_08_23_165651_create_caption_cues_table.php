<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('caption_cues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_project_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedInteger('order');
            $table->text('text');
            $table->unsignedBigInteger('start_ms');
            $table->unsignedBigInteger('end_ms');
            $table->timestamps();

            $table->unique(['video_project_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caption_cues');
    }
};
