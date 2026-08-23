<?php

use App\Models\CaptionCue;
use App\Models\VideoProject;

test('splits a cue at the requested time and shifts later cue order', function () {
    [$videoProject, $captionCue, $laterCaptionCue] = createCaptionCueForSplit();

    $this->post(route('video-projects.caption-cues.split.store', [
        $videoProject,
        $captionCue,
    ]), ['split_ms' => 2_000])
        ->assertRedirectToRoute('video-projects.show', $videoProject);

    $cues = $videoProject->captionCues()->get();

    expect($cues)->toHaveCount(3)
        ->and($cues[0]->id)->toBe($captionCue->id)
        ->and($cues[0]->order)->toBe(1)
        ->and($cues[0]->text)->toBe('ერთი ორი სამი')
        ->and($cues[0]->start_ms)->toBe(1_000)
        ->and($cues[0]->end_ms)->toBe(2_000)
        ->and($cues[1]->order)->toBe(2)
        ->and($cues[1]->text)->toBe('ოთხი ხუთი')
        ->and($cues[1]->start_ms)->toBe(2_000)
        ->and($cues[1]->end_ms)->toBe(3_000)
        ->and($cues[2]->id)->toBe($laterCaptionCue->id)
        ->and($cues[2]->order)->toBe(3)
        ->and($cues[2]->text)->toBe('შემდეგი ტექსტი');
});

test('rejects a split time outside the cue interior', function (mixed $splitMs) {
    [$videoProject, $captionCue] = createCaptionCueForSplit();

    $this->from(route('video-projects.show', $videoProject))
        ->post(route('video-projects.caption-cues.split.store', [
            $videoProject,
            $captionCue,
        ]), ['split_ms' => $splitMs])
        ->assertRedirect(route('video-projects.show', $videoProject))
        ->assertSessionHasErrors('split_ms');

    expect($videoProject->captionCues()->count())->toBe(2)
        ->and($captionCue->fresh()->text)->toBe('ერთი ორი სამი ოთხი ხუთი')
        ->and($captionCue->fresh()->end_ms)->toBe(3_000);
})->with([
    'missing' => null,
    'negative' => -1,
    'fractional' => 2_000.5,
    'not an integer' => 'ორი ათასი',
    'at start' => 1_000,
    'before start' => 999,
    'at end' => 3_000,
    'after end' => 3_001,
]);

test('rejects splitting a cue with fewer than two words', function () {
    [$videoProject, $captionCue] = createCaptionCueForSplit();
    $captionCue->update(['text' => 'გამარჯობა']);

    $this->from(route('video-projects.show', $videoProject))
        ->post(route('video-projects.caption-cues.split.store', [
            $videoProject,
            $captionCue,
        ]), ['split_ms' => 2_000])
        ->assertRedirect(route('video-projects.show', $videoProject))
        ->assertSessionHasErrors('split_ms');

    expect($videoProject->captionCues()->count())->toBe(2)
        ->and($captionCue->fresh()->text)->toBe('გამარჯობა')
        ->and($captionCue->fresh()->end_ms)->toBe(3_000);
});

test('does not split a cue belonging to another project', function () {
    [$videoProject] = createCaptionCueForSplit(path: 'video-projects/split-first/source.mp4');
    [$otherVideoProject, $otherCaptionCue] = createCaptionCueForSplit(
        path: 'video-projects/split-second/source.mp4',
    );

    $this->post(route('video-projects.caption-cues.split.store', [
        $videoProject,
        $otherCaptionCue,
    ]), ['split_ms' => 2_000])->assertNotFound();

    expect($otherVideoProject->captionCues()->count())->toBe(2)
        ->and($otherCaptionCue->fresh()->text)->toBe('ერთი ორი სამი ოთხი ხუთი');
});

/**
 * @return array{VideoProject, CaptionCue, CaptionCue}
 */
function createCaptionCueForSplit(
    string $path = 'video-projects/cue-split/source.mp4',
): array {
    $videoProject = VideoProject::create([
        'original_filename' => 'ქართული-ინტერვიუ.mp4',
        'disk' => 'local',
        'path' => $path,
        'mime_type' => 'video/mp4',
        'size_bytes' => 2_048,
        'duration_ms' => 8_000,
    ]);

    $captionCue = $videoProject->captionCues()->create([
        'order' => 1,
        'text' => 'ერთი ორი სამი ოთხი ხუთი',
        'start_ms' => 1_000,
        'end_ms' => 3_000,
    ]);

    $laterCaptionCue = $videoProject->captionCues()->create([
        'order' => 2,
        'text' => 'შემდეგი ტექსტი',
        'start_ms' => 3_500,
        'end_ms' => 4_500,
    ]);

    return [$videoProject, $captionCue, $laterCaptionCue];
}
