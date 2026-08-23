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
            $table->string('render_status')->nullable();
            $table->text('render_error')->nullable();
            $table->timestamp('rendered_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_projects', function (Blueprint $table) {
            $table->dropColumn([
                'render_status',
                'render_error',
                'rendered_at',
            ]);
        });
    }
};
