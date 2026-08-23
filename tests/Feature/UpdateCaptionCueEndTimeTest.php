<?php

use App\Models\CaptionCue;
use App\Models\VideoProject;

test('updates only the end time of a saved caption cue', function () {
    [$videoProject, $captionCue] = createCaptionCueForEndTimeUpdate();

    $this->patch(route('video-projects.caption-cues.end-time.update', [$videoProject, $captionCue]), [
        'end_ms' => 2_400,
        'order' => 99,
        'text' => 'არ უნდა შეიცვალოს',
        'start_ms' => 0,
    ])->assertRedirectToRoute('video-projects.show', $videoProject);

    $captionCue->refresh();

    expect($captionCue->end_ms)->toBe(2_400)
        ->and($captionCue->order)->toBe(1)
        ->and($captionCue->text)->toBe('საწყისი ტექსტი')
        ->and($captionCue->start_ms)->toBe(480);
});

test('rejects an invalid basic end time', function (mixed $endMs) {
    [$videoProject, $captionCue] = createCaptionCueForEndTimeUpdate();

    $this->from(route('video-projects.show', $videoProject))
        ->patch(route('video-projects.caption-cues.end-time.update', [$videoProject, $captionCue]), ['end_ms' => $endMs])
        ->assertRedirect(route('video-projects.show', $videoProject))
        ->assertSessionHasErrors('end_ms');

    expect($captionCue->fresh()->end_ms)->toBe(2_160);
})->with([
    'missing' => null,
    'zero' => 0,
    'negative' => -1,
    'fractional' => 2_160.5,
    'not an integer' => 'ორი ათას ას სამოცი',
]);

test('requires the end time to be after the cue start', function (int $endMs) {
    [$videoProject, $captionCue] = createCaptionCueForEndTimeUpdate();

    $this->from(route('video-projects.show', $videoProject))
        ->patch(route('video-projects.caption-cues.end-time.update', [$videoProject, $captionCue]), ['end_ms' => $endMs])
        ->assertRedirect(route('video-projects.show', $videoProject))
        ->assertSessionHasErrors('end_ms');

    expect($captionCue->fresh()->end_ms)->toBe(2_160);
})->with([
    'equal to start' => 480,
    'before start' => 479,
]);

test('requires a known video duration', function () {
    [$videoProject, $captionCue] = createCaptionCueForEndTimeUpdate(durationMs: null);

    $this->from(route('video-projects.show', $videoProject))
        ->patch(route('video-projects.caption-cues.end-time.update', [$videoProject, $captionCue]), ['end_ms' => 2_400])
        ->assertRedirect(route('video-projects.show', $videoProject))
        ->assertSessionHasErrors('end_ms');

    expect($captionCue->fresh()->end_ms)->toBe(2_160);
});

test('requires the end time not to exceed video duration', function () {
    [$videoProject, $captionCue] = createCaptionCueForEndTimeUpdate();

    $this->from(route('video-projects.show', $videoProject))
        ->patch(route('video-projects.caption-cues.end-time.update', [$videoProject, $captionCue]), ['end_ms' => 8_001])
        ->assertRedirect(route('video-projects.show', $videoProject))
        ->assertSessionHasErrors('end_ms');

    expect($captionCue->fresh()->end_ms)->toBe(2_160);
});

test('does not update a cue belonging to another project', function () {
    [$videoProject] = createCaptionCueForEndTimeUpdate(path: 'video-projects/end-time-first/source.mp4');
    [, $otherCaptionCue] = createCaptionCueForEndTimeUpdate(path: 'video-projects/end-time-second/source.mp4');

    $this->patch(route('video-projects.caption-cues.end-time.update', [
        $videoProject,
        $otherCaptionCue,
    ]), ['end_ms' => 2_400])->assertNotFound();

    expect($otherCaptionCue->fresh()->end_ms)->toBe(2_160);
});

/**
 * @return array{VideoProject, CaptionCue}
 */
function createCaptionCueForEndTimeUpdate(
    string $path = 'video-projects/cue-end-time-update/source.mp4',
    ?int $durationMs = 8_000,
): array {
    $videoProject = VideoProject::create([
        'original_filename' => 'ქართული-ინტერვიუ.mp4',
        'disk' => 'local',
        'path' => $path,
        'mime_type' => 'video/mp4',
        'size_bytes' => 2_048,
        'duration_ms' => $durationMs,
    ]);

    $captionCue = $videoProject->captionCues()->create([
        'order' => 1,
        'text' => 'საწყისი ტექსტი',
        'start_ms' => 480,
        'end_ms' => 2_160,
    ]);

    return [$videoProject, $captionCue];
}
