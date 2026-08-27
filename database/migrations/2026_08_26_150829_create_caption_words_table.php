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
        Schema::create('caption_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caption_cue_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedInteger('order');
            $table->string('text');
            $table->unsignedBigInteger('start_ms');
            $table->unsignedBigInteger('end_ms');
            $table->timestamps();

            $table->unique(['caption_cue_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caption_words');
    }
};
