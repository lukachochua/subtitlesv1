<?php

use App\Models\VideoProject;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

test('extracts audio from one video project by ID', function () {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'video-content');
    Process::preventStrayProcesses();

    $videoProject = createVideoProjectForAudioExtractionCommand();
    $audioPath = "video-projects/{$videoProject->id}/audio.wav";

    Process::fake(function () use ($audioPath) {
        Storage::disk('local')->put($audioPath, 'wav-content');

        return Process::result();
    });

    $this->artisan('video-projects:extract-audio', [
        'videoProject' => $videoProject->id,
    ])
        ->expectsOutput("Video project {$videoProject->id} audio extracted to {$audioPath}.")
        ->assertSuccessful();

    expect(Storage::disk('local')->exists($audioPath))->toBeTrue();
});

test('fails clearly when the video project does not exist for audio extraction', function () {
    Process::preventStrayProcesses();
    Process::fake();

    $this->artisan('video-projects:extract-audio', [
        'videoProject' => 999,
    ])
        ->expectsOutput('Video project 999 was not found.')
        ->assertFailed();

    Process::assertNothingRan();
});

test('rejects a non-positive video project ID for audio extraction', function () {
    Process::preventStrayProcesses();
    Process::fake();

    $this->artisan('video-projects:extract-audio', [
        'videoProject' => 0,
    ])
        ->expectsOutput('The video project ID must be a positive integer.')
        ->assertFailed();

    Process::assertNothingRan();
});

function createVideoProjectForAudioExtractionCommand(): VideoProject
{
    return VideoProject::create([
        'original_filename' => 'test.mp4',
        'disk' => 'local',
        'path' => 'video-projects/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 13,
    ]);
}
