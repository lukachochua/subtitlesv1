<?php

use App\Actions\LoadVideoProjectCaptionData;
use App\Models\VideoProject;
use App\ValueObjects\CaptionCue;
use App\ValueObjects\TranscriptionWord;
use Illuminate\Support\Facades\Storage;

test('loads normalized words and generated cues for one video project', function () {
    Storage::fake('local');
    $videoProject = createVideoProjectForCaptionData();
    $fixture = file_get_contents(base_path('tests/Fixtures/nemo-transcription.json'));

    if ($fixture === false) {
        throw new RuntimeException('The NeMo transcription fixture could not be read.');
    }

    Storage::disk('local')->put(
        "video-projects/{$videoProject->id}/transcription.nemo-fastconformer.raw.json",
        $fixture,
    );

    $captionData = app(LoadVideoProjectCaptionData::class)->handle($videoProject);

    expect($captionData['words'])->toEqual([
        new TranscriptionWord('ერთი', 160, 240),
        new TranscriptionWord('ორი,', 640, 960),
        new TranscriptionWord('გამარჯობა.', 1600, 2886),
    ])->and($captionData['cues'])->toEqual([
        new CaptionCue(1, 'ერთი ორი, გამარჯობა.', 160, 2886),
    ]);
});

test('requires a known video duration before loading caption data', function () {
    Storage::fake('local');
    $videoProject = createVideoProjectForCaptionData(durationMs: null);

    expect(fn () => app(LoadVideoProjectCaptionData::class)->handle($videoProject))
        ->toThrow(
            RuntimeException::class,
            "Video project {$videoProject->id} must be inspected before its transcription.",
        );
});

test('requires a private NeMo result before loading caption data', function () {
    Storage::fake('local');
    $videoProject = createVideoProjectForCaptionData();

    expect(fn () => app(LoadVideoProjectCaptionData::class)->handle($videoProject))
        ->toThrow(
            RuntimeException::class,
            "Video project {$videoProject->id} does not have a NeMo transcription result.",
        );
});

test('rejects invalid NeMo result JSON', function () {
    Storage::fake('local');
    $videoProject = createVideoProjectForCaptionData();
    Storage::disk('local')->put(
        "video-projects/{$videoProject->id}/transcription.nemo-fastconformer.raw.json",
        '{invalid-json',
    );

    expect(fn () => app(LoadVideoProjectCaptionData::class)->handle($videoProject))
        ->toThrow(
            UnexpectedValueException::class,
            'The NeMo transcription result contains invalid JSON.',
        );
});

function createVideoProjectForCaptionData(?int $durationMs = 2886): VideoProject
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
