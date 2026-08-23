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
        Schema::table('video_projects', function (Blueprint $table) {
            $table->string('transcription_status')->nullable();
            $table->text('transcription_error')->nullable();
            $table->timestamp('transcribed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_projects', function (Blueprint $table) {
            $table->dropColumn([
                'transcription_status',
                'transcription_error',
                'transcribed_at',
            ]);
        });
    }
};
