<?php

use App\Models\CaptionCue;
use App\Models\VideoProject;

test('updates only the start time of a saved caption cue', function () {
    [$videoProject, $captionCue] = createCaptionCueForStartTimeUpdate();

    $this->patch(route('video-projects.caption-cues.start-time.update', [
        $videoProject,
        $captionCue,
    ]), [
        'start_ms' => 640,
        'order' => 99,
        'text' => 'არ უნდა შეიცვალოს',
        'end_ms' => 8_000,
    ])->assertRedirectToRoute('video-projects.show', $videoProject);

    $captionCue->refresh();

    expect($captionCue->start_ms)->toBe(640)
        ->and($captionCue->order)->toBe(1)
        ->and($captionCue->text)->toBe('საწყისი ტექსტი')
        ->and($captionCue->end_ms)->toBe(2_160);
});

test('rejects an invalid basic start time', function (mixed $startMs) {
    [$videoProject, $captionCue] = createCaptionCueForStartTimeUpdate();

    $this->from(route('video-projects.show', $videoProject))
        ->patch(route('video-projects.caption-cues.start-time.update', [
            $videoProject,
            $captionCue,
        ]), ['start_ms' => $startMs])
        ->assertRedirect(route('video-projects.show', $videoProject))
        ->assertSessionHasErrors('start_ms');

    expect($captionCue->fresh()->start_ms)->toBe(480);
})->with([
    'missing' => null,
    'negative' => -1,
    'fractional' => 480.5,
    'not an integer' => 'ოთხას ოთხმოცი',
]);

test('requires the start time to be before the cue end', function (int $startMs) {
    [$videoProject, $captionCue] = createCaptionCueForStartTimeUpdate();

    $this->from(route('video-projects.show', $videoProject))
        ->patch(route('video-projects.caption-cues.start-time.update', [
            $videoProject,
            $captionCue,
        ]), ['start_ms' => $startMs])
        ->assertRedirect(route('video-projects.show', $videoProject))
        ->assertSessionHasErrors('start_ms');

    expect($captionCue->fresh()->start_ms)->toBe(480);
})->with([
    'equal to end' => 2_160,
    'after end' => 2_161,
]);

test('requires a known video duration', function () {
    [$videoProject, $captionCue] = createCaptionCueForStartTimeUpdate(durationMs: null);

    $this->from(route('video-projects.show', $videoProject))
        ->patch(route('video-projects.caption-cues.start-time.update', [
            $videoProject,
            $captionCue,
        ]), ['start_ms' => 640])
        ->assertRedirect(route('video-projects.show', $videoProject))
        ->assertSessionHasErrors('start_ms');

    expect($captionCue->fresh()->start_ms)->toBe(480);
});

test('requires the start time not to exceed video duration', function () {
    [$videoProject, $captionCue] = createCaptionCueForStartTimeUpdate(
        durationMs: 2_000,
        endMs: 3_000,
    );

    $this->from(route('video-projects.show', $videoProject))
        ->patch(route('video-projects.caption-cues.start-time.update', [
            $videoProject,
            $captionCue,
        ]), ['start_ms' => 2_500])
        ->assertRedirect(route('video-projects.show', $videoProject))
        ->assertSessionHasErrors('start_ms');

    expect($captionCue->fresh()->start_ms)->toBe(480);
});

test('does not update a cue belonging to another project', function () {
    [$videoProject] = createCaptionCueForStartTimeUpdate(
        path: 'video-projects/start-time-first/source.mp4',
    );
    [, $otherCaptionCue] = createCaptionCueForStartTimeUpdate(
        path: 'video-projects/start-time-second/source.mp4',
    );

    $this->patch(route('video-projects.caption-cues.start-time.update', [
        $videoProject,
        $otherCaptionCue,
    ]), ['start_ms' => 640])->assertNotFound();

    expect($otherCaptionCue->fresh()->start_ms)->toBe(480);
});

/**
 * @return array{VideoProject, CaptionCue}
 */
function createCaptionCueForStartTimeUpdate(
    string $path = 'video-projects/cue-start-time-update/source.mp4',
    ?int $durationMs = 8_000,
    int $endMs = 2_160,
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
        'end_ms' => $endMs,
    ]);

    return [$videoProject, $captionCue];
}
