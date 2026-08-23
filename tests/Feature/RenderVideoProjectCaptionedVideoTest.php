<?php

use App\Actions\GenerateVideoProjectAssFile;
use App\Actions\RenderVideoProjectCaptionedVideo;
use App\Enums\VideoRenderQuality;
use App\Enums\VideoRenderStatus;
use App\Models\VideoProject;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\mock;

test('renders captioned video to a verified private project path', function () {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'source-video');
    Process::preventStrayProcesses();

    $videoProject = createVideoProjectForCaptionRendering();
    $assPath = "video-projects/{$videoProject->id}/captions.ass";
    $pendingOutputPath = "video-projects/{$videoProject->id}/captioned.rendering.mp4";
    $completedOutputPath = "video-projects/{$videoProject->id}/captioned.mp4";
    Storage::disk('local')->put($completedOutputPath, 'older-export');

    mock(GenerateVideoProjectAssFile::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (VideoProject $project): bool => $project->is($videoProject))
        ->andReturnUsing(function () use ($assPath): string {
            Storage::disk('local')->put($assPath, 'ass-content');

            return $assPath;
        });

    Process::fake(function () use ($pendingOutputPath) {
        Storage::disk('local')->put($pendingOutputPath, 'new-captioned-video');

        return Process::result();
    });

    $outputPath = app(RenderVideoProjectCaptionedVideo::class)->handle($videoProject);
    $videoProject->refresh();

    expect($outputPath)->toBe($completedOutputPath)
        ->and(Storage::disk('local')->get($completedOutputPath))->toBe('new-captioned-video')
        ->and($videoProject->render_status)->toBe(VideoRenderStatus::Completed)
        ->and($videoProject->render_quality)->toBe(VideoRenderQuality::High)
        ->and($videoProject->render_error)->toBeNull()
        ->and($videoProject->rendered_at)->not->toBeNull();
    Storage::disk('local')->assertMissing($pendingOutputPath);

    Process::assertRan(function (PendingProcess $process, ProcessResult $result) use ($videoProject, $assPath, $pendingOutputPath): bool {
        return $process->timeout === 3_600
            && $process->command === [
                '/usr/bin/ffmpeg',
                '-nostdin',
                '-hide_banner',
                '-loglevel',
                'error',
                '-y',
                '-i',
                Storage::disk('local')->path($videoProject->path),
                '-vf',
                'ass='.Storage::disk('local')->path($assPath).':shaping=complex',
                '-map',
                '0:v:0',
                '-map',
                '0:a?',
                '-c:v',
                'libx264',
                '-preset',
                'slow',
                '-crf',
                '14',
                '-c:a',
                'copy',
                '-movflags',
                '+faststart',
                Storage::disk('local')->path($pendingOutputPath),
            ];
    });
});

test('removes a partial render and preserves the previous export when FFmpeg fails', function () {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'source-video');
    Process::preventStrayProcesses();

    $videoProject = createVideoProjectForCaptionRendering();
    $assPath = "video-projects/{$videoProject->id}/captions.ass";
    $pendingOutputPath = "video-projects/{$videoProject->id}/captioned.rendering.mp4";
    $completedOutputPath = "video-projects/{$videoProject->id}/captioned.mp4";
    Storage::disk('local')->put($completedOutputPath, 'older-export');

    mock(GenerateVideoProjectAssFile::class)
        ->shouldReceive('handle')
        ->once()
        ->andReturnUsing(function () use ($assPath): string {
            Storage::disk('local')->put($assPath, 'ass-content');

            return $assPath;
        });

    Process::fake(function () use ($pendingOutputPath) {
        Storage::disk('local')->put($pendingOutputPath, 'partial-video');

        return Process::result(errorOutput: 'render failed', exitCode: 1);
    });

    expect(fn () => app(RenderVideoProjectCaptionedVideo::class)->handle($videoProject))
        ->toThrow(RuntimeException::class);

    $videoProject->refresh();
    Storage::disk('local')->assertMissing($pendingOutputPath);
    expect(Storage::disk('local')->get($completedOutputPath))->toBe('older-export')
        ->and($videoProject->render_status)->toBe(VideoRenderStatus::Failed)
        ->and($videoProject->render_error)->toBe(RenderVideoProjectCaptionedVideo::FAILURE_MESSAGE);
});

test('rejects a successful process that creates no usable video', function () {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'source-video');
    Process::preventStrayProcesses();

    $videoProject = createVideoProjectForCaptionRendering();
    $assPath = "video-projects/{$videoProject->id}/captions.ass";

    mock(GenerateVideoProjectAssFile::class)
        ->shouldReceive('handle')
        ->once()
        ->andReturnUsing(function () use ($assPath): string {
            Storage::disk('local')->put($assPath, 'ass-content');

            return $assPath;
        });

    Process::fake(['*' => Process::result()]);

    expect(fn () => app(RenderVideoProjectCaptionedVideo::class)->handle($videoProject))
        ->toThrow(RuntimeException::class, 'FFmpeg did not create a usable captioned video.');

    $videoProject->refresh();
    expect($videoProject->render_status)->toBe(VideoRenderStatus::Failed)
        ->and($videoProject->render_error)->toBe(RenderVideoProjectCaptionedVideo::FAILURE_MESSAGE);
});

test('records a failure when ASS generation fails before FFmpeg starts', function () {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'source-video');
    Process::preventStrayProcesses();

    $videoProject = createVideoProjectForCaptionRendering();

    mock(GenerateVideoProjectAssFile::class)
        ->shouldReceive('handle')
        ->once()
        ->andThrow(new RuntimeException('ASS generation failed.'));

    expect(fn () => app(RenderVideoProjectCaptionedVideo::class)->handle($videoProject))
        ->toThrow(RuntimeException::class, 'ASS generation failed.');

    $videoProject->refresh();
    expect($videoProject->render_status)->toBe(VideoRenderStatus::Failed)
        ->and($videoProject->render_error)->toBe(RenderVideoProjectCaptionedVideo::FAILURE_MESSAGE);

    Process::assertNothingRan();
});

function createVideoProjectForCaptionRendering(): VideoProject
{
    return VideoProject::create([
        'original_filename' => 'source.mp4',
        'disk' => 'local',
        'path' => 'video-projects/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 12,
    ]);
}
