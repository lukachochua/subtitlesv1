<?php

use App\Models\VideoProject;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

test('inspects one video project by ID', function () {
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

    $videoProject = VideoProject::create([
        'original_filename' => 'test.mp4',
        'disk' => 'local',
        'path' => 'video-projects/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 13,
    ]);

    $this->artisan('video-projects:inspect', [
        'videoProject' => $videoProject->id,
    ])
        ->expectsOutput("Video project {$videoProject->id} inspected: 7967 ms.")
        ->assertSuccessful();

    expect($videoProject->fresh()->duration_ms)->toBe(7_967);
});

test('fails clearly when the video project does not exist', function () {
    Process::preventStrayProcesses();
    Process::fake();

    $this->artisan('video-projects:inspect', [
        'videoProject' => 999,
    ])
        ->expectsOutput('Video project 999 was not found.')
        ->assertFailed();

    Process::assertNothingRan();
});

test('rejects a non-positive video project ID', function () {
    Process::preventStrayProcesses();
    Process::fake();

    $this->artisan('video-projects:inspect', [
        'videoProject' => 0,
    ])
        ->expectsOutput('The video project ID must be a positive integer.')
        ->assertFailed();

    Process::assertNothingRan();
});
