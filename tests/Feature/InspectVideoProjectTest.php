<?php

use App\Actions\InspectVideoProject;
use App\Models\VideoProject;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

test('persists rounded container duration for a video with audio', function () {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'video-content');
    Process::preventStrayProcesses();
    Process::fake([
        '*' => Process::result(output: json_encode([
            'streams' => [
                ['codec_type' => 'video'],
                ['codec_type' => 'audio'],
            ],
            'format' => ['duration' => '7.966667'],
        ], JSON_THROW_ON_ERROR)),
    ]);

    $videoProject = createVideoProjectForInspection();

    $inspectedVideoProject = (new InspectVideoProject)->handle($videoProject);

    expect($inspectedVideoProject->duration_ms)->toBe(7_967)
        ->and($videoProject->fresh()->duration_ms)->toBe(7_967);

    Process::assertRan(function (PendingProcess $process, ProcessResult $result): bool {
        return $process->timeout === 30
            && $process->command === [
                '/usr/bin/ffprobe',
                '-v',
                'error',
                '-show_entries',
                'format=duration:stream=codec_type',
                '-of',
                'json',
                Storage::disk('local')->path('video-projects/source.mp4'),
            ];
    });
});

test('does not run ffprobe when the project file is missing', function () {
    Storage::fake('local');
    Process::preventStrayProcesses();
    Process::fake();

    $videoProject = createVideoProjectForInspection();

    expect(fn () => (new InspectVideoProject)->handle($videoProject))
        ->toThrow(RuntimeException::class, 'The video project file does not exist.');

    Process::assertNothingRan();
    expect($videoProject->fresh()->duration_ms)->toBeNull();
});

test('does not persist duration when ffprobe fails', function () {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'video-content');
    Process::preventStrayProcesses();
    Process::fake([
        '*' => Process::result(errorOutput: 'Invalid data', exitCode: 1),
    ]);

    $videoProject = createVideoProjectForInspection();

    expect(fn () => (new InspectVideoProject)->handle($videoProject))
        ->toThrow(RuntimeException::class);

    expect($videoProject->fresh()->duration_ms)->toBeNull();
});

test('rejects invalid ffprobe JSON without persisting duration', function () {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'video-content');
    Process::preventStrayProcesses();
    Process::fake([
        '*' => Process::result(output: '{invalid-json'),
    ]);

    $videoProject = createVideoProjectForInspection();

    expect(fn () => (new InspectVideoProject)->handle($videoProject))
        ->toThrow(UnexpectedValueException::class, 'ffprobe returned invalid JSON.');

    expect($videoProject->fresh()->duration_ms)->toBeNull();
});

test('rejects incomplete ffprobe metadata without persisting duration', function (array $inspection) {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'video-content');
    Process::preventStrayProcesses();
    Process::fake([
        '*' => Process::result(output: json_encode($inspection, JSON_THROW_ON_ERROR)),
    ]);

    $videoProject = createVideoProjectForInspection();

    expect(fn () => (new InspectVideoProject)->handle($videoProject))
        ->toThrow(UnexpectedValueException::class);

    expect($videoProject->fresh()->duration_ms)->toBeNull();
})->with([
    'missing duration' => [[
        'streams' => [['codec_type' => 'video'], ['codec_type' => 'audio']],
        'format' => [],
    ]],
    'non-positive duration' => [[
        'streams' => [['codec_type' => 'video'], ['codec_type' => 'audio']],
        'format' => ['duration' => '0'],
    ]],
    'missing video stream' => [[
        'streams' => [['codec_type' => 'audio']],
        'format' => ['duration' => '7.966667'],
    ]],
    'missing audio stream' => [[
        'streams' => [['codec_type' => 'video']],
        'format' => ['duration' => '7.966667'],
    ]],
]);

function createVideoProjectForInspection(): VideoProject
{
    return VideoProject::create([
        'original_filename' => 'test.mp4',
        'disk' => 'local',
        'path' => 'video-projects/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 13,
    ]);
}
