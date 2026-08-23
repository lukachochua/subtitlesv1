<?php

namespace App\Actions;

use App\Enums\VideoRenderStatus;
use App\Models\VideoProject;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class RenderVideoProjectCaptionedVideo
{
    public const FAILURE_MESSAGE = 'The captioned video could not be exported. Check the media files and try again.';

    public function __construct(private GenerateVideoProjectAssFile $generateVideoProjectAssFile) {}

    public function handle(VideoProject $videoProject): string
    {
        $videoProject->update([
            'render_status' => VideoRenderStatus::Pending,
            'render_error' => null,
        ]);

        $videoProject->update([
            'render_status' => VideoRenderStatus::Processing,
        ]);

        $disk = Storage::disk($videoProject->disk);
        $outputDirectory = "video-projects/{$videoProject->id}";
        $pendingOutputPath = "{$outputDirectory}/captioned.rendering.mp4";
        $completedOutputPath = "{$outputDirectory}/captioned.mp4";

        $disk->makeDirectory($outputDirectory);
        $disk->delete($pendingOutputPath);

        try {
            $assPath = $this->generateVideoProjectAssFile->handle($videoProject);

            if (! $disk->exists($assPath)) {
                throw new RuntimeException('The generated ASS subtitle file does not exist.');
            }

            $result = Process::timeout(3_600)->run([
                '/usr/bin/ffmpeg',
                '-nostdin',
                '-hide_banner',
                '-loglevel',
                'error',
                '-y',
                '-i',
                $disk->path($videoProject->path),
                '-vf',
                "ass={$disk->path($assPath)}:shaping=complex",
                '-map',
                '0:v:0',
                '-map',
                '0:a?',
                '-c:v',
                'libx264',
                '-preset',
                'medium',
                '-crf',
                '18',
                '-c:a',
                'copy',
                '-movflags',
                '+faststart',
                $disk->path($pendingOutputPath),
            ]);

            $result->throw();

            if (! $disk->exists($pendingOutputPath) || $disk->size($pendingOutputPath) === 0) {
                throw new RuntimeException('FFmpeg did not create a usable captioned video.');
            }

            $disk->delete($completedOutputPath);

            if (! $disk->move($pendingOutputPath, $completedOutputPath)) {
                throw new RuntimeException('The captioned video could not be finalized.');
            }

            $videoProject->update([
                'render_status' => VideoRenderStatus::Completed,
                'render_error' => null,
                'rendered_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $disk->delete($pendingOutputPath);
            $videoProject->update([
                'render_status' => VideoRenderStatus::Failed,
                'render_error' => self::FAILURE_MESSAGE,
            ]);

            throw $exception;
        }

        return $completedOutputPath;
    }
}
