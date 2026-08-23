<?php

use App\Models\VideoProject;
use Illuminate\Support\Facades\Storage;

test('displays normalized words for one video project', function () {
    Storage::fake('local');
    $videoProject = createVideoProjectForTranscriptionInspection();
    $fixture = file_get_contents(base_path('tests/Fixtures/nemo-transcription.json'));

    if ($fixture === false) {
        throw new RuntimeException('The NeMo transcription fixture could not be read.');
    }

    Storage::disk('local')->put(
        "video-projects/{$videoProject->id}/transcription.nemo-fastconformer.raw.json",
        $fixture,
    );

    $this->artisan('video-projects:inspect-transcription', [
        'videoProject' => $videoProject->id,
    ])
        ->expectsTable(
            ['Order', 'Text', 'Start (ms)', 'End (ms)'],
            [
                [1, 'ერთი', 160, 240],
                [2, 'ორი,', 640, 960],
                [3, 'გამარჯობა.', 1600, 2886],
            ],
        )
        ->assertSuccessful();
});

test('requires the video project to have a known duration', function () {
    Storage::fake('local');
    $videoProject = createVideoProjectForTranscriptionInspection(durationMs: null);

    $this->artisan('video-projects:inspect-transcription', [
        'videoProject' => $videoProject->id,
    ])
        ->expectsOutput("Video project {$videoProject->id} must be inspected before its transcription.")
        ->assertFailed();
});

test('fails clearly when the NeMo result is missing', function () {
    Storage::fake('local');
    $videoProject = createVideoProjectForTranscriptionInspection();

    $this->artisan('video-projects:inspect-transcription', [
        'videoProject' => $videoProject->id,
    ])
        ->expectsOutput("Video project {$videoProject->id} does not have a NeMo transcription result.")
        ->assertFailed();
});

test('fails clearly when the NeMo result contains invalid JSON', function () {
    Storage::fake('local');
    $videoProject = createVideoProjectForTranscriptionInspection();
    Storage::disk('local')->put(
        "video-projects/{$videoProject->id}/transcription.nemo-fastconformer.raw.json",
        '{invalid-json',
    );

    $this->artisan('video-projects:inspect-transcription', [
        'videoProject' => $videoProject->id,
    ])
        ->expectsOutputToContain("Could not inspect video project {$videoProject->id} transcription:")
        ->assertFailed();
});

test('fails clearly when NeMo word conversion fails', function () {
    Storage::fake('local');
    $videoProject = createVideoProjectForTranscriptionInspection();
    Storage::disk('local')->put(
        "video-projects/{$videoProject->id}/transcription.nemo-fastconformer.raw.json",
        json_encode(['timestamp' => ['word' => []]], JSON_THROW_ON_ERROR),
    );

    $this->artisan('video-projects:inspect-transcription', [
        'videoProject' => $videoProject->id,
    ])
        ->expectsOutput("Could not inspect video project {$videoProject->id} transcription: NeMo did not return a valid word timestamp list.")
        ->assertFailed();
});

test('fails clearly when the video project does not exist', function () {
    Storage::fake('local');

    $this->artisan('video-projects:inspect-transcription', [
        'videoProject' => 999,
    ])
        ->expectsOutput('Video project 999 was not found.')
        ->assertFailed();
});

test('rejects a non-positive video project ID', function () {
    Storage::fake('local');

    $this->artisan('video-projects:inspect-transcription', [
        'videoProject' => 0,
    ])
        ->expectsOutput('The video project ID must be a positive integer.')
        ->assertFailed();
});

function createVideoProjectForTranscriptionInspection(?int $durationMs = 2886): VideoProject
{
    return VideoProject::create([
        'original_filename' => 'test.mp4',
        'disk' => 'local',
        'path' => 'video-projects/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 13,
        'duration_ms' => $durationMs,
    ]);
}
