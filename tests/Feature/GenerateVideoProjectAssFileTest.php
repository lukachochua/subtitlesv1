<?php

use App\Actions\GenerateVideoProjectAssFile;
use App\Models\VideoProject;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

test('writes ASS subtitles from saved project cues and source dimensions', function () {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'video-content');
    Process::preventStrayProcesses();
    Process::fake([
        '*' => Process::result(output: json_encode([
            'streams' => [['width' => 368, 'height' => 640]],
        ], JSON_THROW_ON_ERROR)),
    ]);

    $videoProject = createVideoProjectForAssGeneration();
    $videoProject->captionCues()->createMany([
        ['order' => 2, 'text' => 'მეორე', 'start_ms' => 2_141, 'end_ms' => 3_000],
        ['order' => 1, 'text' => 'პირველი', 'start_ms' => 80, 'end_ms' => 2_000],
    ]);

    $assPath = app(GenerateVideoProjectAssFile::class)->handle($videoProject);

    expect($assPath)->toBe("video-projects/{$videoProject->id}/captions.ass")
        ->and(Storage::disk('local')->get($assPath))
        ->toContain('PlayResX: 368')
        ->toContain('Dialogue: 1,0:00:00.08,0:00:02.00,CaptionText')
        ->toContain('Dialogue: 1,0:00:02.14,0:00:03.00,CaptionText');

    Process::assertRan(function (PendingProcess $process, ProcessResult $result) use ($videoProject): bool {
        return $process->timeout === 30
            && $process->command === [
                '/usr/bin/ffprobe',
                '-v',
                'error',
                '-select_streams',
                'v:0',
                '-show_entries',
                'stream=width,height',
                '-of',
                'json',
                Storage::disk('local')->path($videoProject->path),
            ];
    });
});

test('refuses generation when the source video or saved cues are missing', function (bool $putSource, bool $putCue, string $message) {
    Storage::fake('local');
    Process::preventStrayProcesses();
    Process::fake();

    if ($putSource) {
        Storage::disk('local')->put('video-projects/source.mp4', 'video-content');
    }

    $videoProject = createVideoProjectForAssGeneration();

    if ($putCue) {
        $videoProject->captionCues()->create([
            'order' => 1,
            'text' => 'ქართული',
            'start_ms' => 0,
            'end_ms' => 1_000,
        ]);
    }

    expect(fn () => app(GenerateVideoProjectAssFile::class)->handle($videoProject))
        ->toThrow(RuntimeException::class, $message);

    Process::assertNothingRan();
})->with([
    'missing source' => [false, true, 'The video project file does not exist.'],
    'missing cues' => [true, false, 'The video project has no saved caption cues.'],
]);

test('rejects invalid ffprobe output without writing subtitles', function (string $output) {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'video-content');
    Process::preventStrayProcesses();
    Process::fake(['*' => Process::result(output: $output)]);

    $videoProject = createVideoProjectForAssGeneration();
    $videoProject->captionCues()->create([
        'order' => 1,
        'text' => 'ქართული',
        'start_ms' => 0,
        'end_ms' => 1_000,
    ]);

    expect(fn () => app(GenerateVideoProjectAssFile::class)->handle($videoProject))
        ->toThrow(UnexpectedValueException::class);

    Storage::disk('local')->assertMissing("video-projects/{$videoProject->id}/captions.ass");
})->with([
    'invalid JSON' => ['not-json'],
    'no stream' => [json_encode(['streams' => []], JSON_THROW_ON_ERROR)],
    'zero dimensions' => [json_encode(['streams' => [['width' => 0, 'height' => 640]]], JSON_THROW_ON_ERROR)],
]);

function createVideoProjectForAssGeneration(): VideoProject
{
    return VideoProject::create([
        'original_filename' => 'source.mp4',
        'disk' => 'local',
        'path' => 'video-projects/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 13,
    ]);
}
