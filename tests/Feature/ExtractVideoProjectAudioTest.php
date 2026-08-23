<?php

use App\Actions\ExtractVideoProjectAudio;
use App\Models\VideoProject;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

test('extracts ASR-ready audio to a project-controlled private path', function () {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'video-content');
    Process::preventStrayProcesses();

    $videoProject = createVideoProjectForAudioExtraction();
    $expectedAudioPath = "video-projects/{$videoProject->id}/audio.wav";

    Process::fake(function () use ($expectedAudioPath) {
        Storage::disk('local')->put($expectedAudioPath, 'wav-content');

        return Process::result();
    });

    $audioPath = (new ExtractVideoProjectAudio)->handle($videoProject);

    expect($audioPath)->toBe($expectedAudioPath)
        ->and(Storage::disk('local')->exists($audioPath))->toBeTrue()
        ->and(Storage::disk('local')->size($audioPath))->toBeGreaterThan(0);

    Process::assertRan(function (PendingProcess $process, ProcessResult $result) use ($videoProject, $expectedAudioPath): bool {
        return $process->timeout === 120
            && $process->command === [
                '/usr/bin/ffmpeg',
                '-nostdin',
                '-hide_banner',
                '-loglevel',
                'error',
                '-y',
                '-i',
                Storage::disk('local')->path($videoProject->path),
                '-vn',
                '-ac',
                '1',
                '-ar',
                '16000',
                '-c:a',
                'pcm_s16le',
                Storage::disk('local')->path($expectedAudioPath),
            ];
    });
});

test('does not run FFmpeg when the project file is missing', function () {
    Storage::fake('local');
    Process::preventStrayProcesses();
    Process::fake();

    $videoProject = createVideoProjectForAudioExtraction();

    expect(fn () => (new ExtractVideoProjectAudio)->handle($videoProject))
        ->toThrow(RuntimeException::class, 'The video project file does not exist.');

    Process::assertNothingRan();
});

test('removes a partial audio file when FFmpeg fails', function () {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'video-content');
    Process::preventStrayProcesses();

    $videoProject = createVideoProjectForAudioExtraction();
    $audioPath = "video-projects/{$videoProject->id}/audio.wav";

    Process::fake(function () use ($audioPath) {
        Storage::disk('local')->put($audioPath, 'partial-audio');

        return Process::result(errorOutput: 'Conversion failed', exitCode: 1);
    });

    expect(fn () => (new ExtractVideoProjectAudio)->handle($videoProject))
        ->toThrow(RuntimeException::class);

    expect(Storage::disk('local')->exists($audioPath))->toBeFalse();
});

test('rejects a successful process that creates no audio file', function () {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'video-content');
    Process::preventStrayProcesses();
    Process::fake([
        '*' => Process::result(),
    ]);

    $videoProject = createVideoProjectForAudioExtraction();

    expect(fn () => (new ExtractVideoProjectAudio)->handle($videoProject))
        ->toThrow(RuntimeException::class, 'FFmpeg did not create a usable audio file.');
});

function createVideoProjectForAudioExtraction(): VideoProject
{
    return VideoProject::create([
        'original_filename' => 'test.mp4',
        'disk' => 'local',
        'path' => 'video-projects/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 13,
    ]);
}
