<?php

use App\Models\VideoProject;
use Illuminate\Support\Facades\Storage;

test('loads and persists generated cues for one video project', function () {
    Storage::fake('local');
    $videoProject = createVideoProjectForCaptionCueCommand();
    storeNemoTranscriptionFixtureForCaptionCueCommand($videoProject);

    $this->artisan('video-projects:persist-caption-cues', [
        'videoProject' => $videoProject->id,
    ])
        ->expectsOutput("Video project {$videoProject->id} saved 1 caption cues.")
        ->assertSuccessful();

    expect($videoProject->captionCues()->get()->map->only([
        'order',
        'text',
        'start_ms',
        'end_ms',
    ])->all())->toBe([
        [
            'order' => 1,
            'text' => 'ერთი ორი, გამარჯობა.',
            'start_ms' => 160,
            'end_ms' => 2_886,
        ],
    ]);
});

test('refuses to replace existing saved cues', function () {
    Storage::fake('local');
    $videoProject = createVideoProjectForCaptionCueCommand();
    storeNemoTranscriptionFixtureForCaptionCueCommand($videoProject);
    $videoProject->captionCues()->create([
        'order' => 1,
        'text' => 'ხელით შესწორებული ტექსტი',
        'start_ms' => 160,
        'end_ms' => 2_886,
    ]);

    $this->artisan('video-projects:persist-caption-cues', [
        'videoProject' => $videoProject->id,
    ])
        ->expectsOutput(
            "Could not persist video project {$videoProject->id} caption cues: Video project {$videoProject->id} already has saved caption cues.",
        )
        ->assertFailed();

    expect($videoProject->captionCues()->pluck('text')->all())->toBe([
        'ხელით შესწორებული ტექსტი',
    ]);
});

test('requires a NeMo result before persisting caption cues', function () {
    Storage::fake('local');
    $videoProject = createVideoProjectForCaptionCueCommand();

    $this->artisan('video-projects:persist-caption-cues', [
        'videoProject' => $videoProject->id,
    ])
        ->expectsOutput("Video project {$videoProject->id} does not have a NeMo transcription result.")
        ->assertFailed();
});

test('fails clearly when the video project does not exist', function () {
    Storage::fake('local');

    $this->artisan('video-projects:persist-caption-cues', [
        'videoProject' => 999,
    ])
        ->expectsOutput('Video project 999 was not found.')
        ->assertFailed();
});

test('rejects a non-positive video project ID', function () {
    Storage::fake('local');

    $this->artisan('video-projects:persist-caption-cues', [
        'videoProject' => 0,
    ])
        ->expectsOutput('The video project ID must be a positive integer.')
        ->assertFailed();
});

function createVideoProjectForCaptionCueCommand(): VideoProject
{
    return VideoProject::create([
        'original_filename' => 'test.mp4',
        'disk' => 'local',
        'path' => 'video-projects/persist-caption-cues/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 13,
        'duration_ms' => 2_886,
    ]);
}

function storeNemoTranscriptionFixtureForCaptionCueCommand(VideoProject $videoProject): void
{
    $fixture = file_get_contents(base_path('tests/Fixtures/nemo-transcription.json'));

    if ($fixture === false) {
        throw new RuntimeException('The NeMo transcription fixture could not be read.');
    }

    Storage::disk('local')->put(
        "video-projects/{$videoProject->id}/transcription.nemo-fastconformer.raw.json",
        $fixture,
    );
}
