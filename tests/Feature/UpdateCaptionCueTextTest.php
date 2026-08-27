<?php

use App\Models\CaptionCue;
use App\Models\VideoProject;
use Illuminate\Support\Str;

test('updates only the text of a saved caption cue', function () {
    [$videoProject, $captionCue] = createCaptionCueForTextUpdate();
    $captionCue->words()->createMany([
        ['order' => 1, 'text' => 'არასწორი', 'start_ms' => 480, 'end_ms' => 1_200],
        ['order' => 2, 'text' => 'ტექსტი', 'start_ms' => 1_160, 'end_ms' => 2_160],
    ]);

    $this->patch(route('video-projects.caption-cues.update', [
        $videoProject,
        $captionCue,
    ]), [
        'text' => 'სწორი ტექსტი',
        'order' => 99,
        'start_ms' => 0,
        'end_ms' => 8_000,
    ])->assertRedirectToRoute('video-projects.show', $videoProject);

    $captionCue->refresh();

    expect($captionCue->text)->toBe('სწორი ტექსტი')
        ->and($captionCue->order)->toBe(1)
        ->and($captionCue->start_ms)->toBe(480)
        ->and($captionCue->end_ms)->toBe(2_160)
        ->and($captionCue->words()->get()->map->only(['text', 'start_ms', 'end_ms'])->all())->toBe([
            ['text' => 'სწორი', 'start_ms' => 480, 'end_ms' => 1_160],
            ['text' => 'ტექსტი', 'start_ms' => 1_160, 'end_ms' => 2_160],
        ]);
});

test('rejects changing the word count when exact word timings exist', function () {
    [$videoProject, $captionCue] = createCaptionCueForTextUpdate();
    $captionCue->words()->createMany([
        ['order' => 1, 'text' => 'არასწორი', 'start_ms' => 480, 'end_ms' => 1_000],
        ['order' => 2, 'text' => 'ტექსტი', 'start_ms' => 1_100, 'end_ms' => 2_160],
    ]);

    $this->from(route('video-projects.show', $videoProject))
        ->patch(route('video-projects.caption-cues.update', [$videoProject, $captionCue]), [
            'text' => 'ახლა სამი სიტყვაა',
        ])
        ->assertRedirect(route('video-projects.show', $videoProject))
        ->assertSessionHasErrors('text');

    expect($captionCue->fresh()->text)->toBe('არასწორი ტექსტი')
        ->and($captionCue->words()->pluck('text')->all())->toBe(['არასწორი', 'ტექსტი']);
});

test('rejects invalid caption cue text', function (mixed $text) {
    [$videoProject, $captionCue] = createCaptionCueForTextUpdate();

    $this->from(route('video-projects.show', $videoProject))
        ->patch(route('video-projects.caption-cues.update', [
            $videoProject,
            $captionCue,
        ]), ['text' => $text])
        ->assertRedirect(route('video-projects.show', $videoProject))
        ->assertSessionHasErrors('text');

    expect($captionCue->fresh()->text)->toBe('არასწორი ტექსტი');
})->with([
    'missing' => null,
    'empty' => '',
    'too long' => fn (): string => Str::repeat('ა', 501),
    'not a string' => [['ქართული']],
]);

test('does not update a cue belonging to another project', function () {
    [$videoProject] = createCaptionCueForTextUpdate('video-projects/first/source.mp4');
    [, $otherCaptionCue] = createCaptionCueForTextUpdate('video-projects/second/source.mp4');

    $this->patch(route('video-projects.caption-cues.update', [
        $videoProject,
        $otherCaptionCue,
    ]), [
        'text' => 'არ უნდა შეიცვალოს',
    ])->assertNotFound();

    expect($otherCaptionCue->fresh()->text)->toBe('არასწორი ტექსტი');
});

/**
 * @return array{VideoProject, CaptionCue}
 */
function createCaptionCueForTextUpdate(
    string $path = 'video-projects/cue-text-update/source.mp4',
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
        'text' => 'არასწორი ტექსტი',
        'start_ms' => 480,
        'end_ms' => 2_160,
    ]);

    return [$videoProject, $captionCue];
}
