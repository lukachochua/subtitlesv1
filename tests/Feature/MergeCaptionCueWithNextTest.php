<?php

use App\Models\CaptionCue;
use App\Models\VideoProject;

test('merges a cue with its immediate next cue and shifts later order', function () {
    [$videoProject, $captionCue, $nextCaptionCue, $laterCaptionCue] = createCaptionCuesForMerge();

    $this->post(route('video-projects.caption-cues.merge-next.store', [
        $videoProject,
        $captionCue,
    ]))->assertRedirectToRoute('video-projects.show', $videoProject);

    $cues = $videoProject->captionCues()->get();

    expect($cues)->toHaveCount(2)
        ->and($cues[0]->id)->toBe($captionCue->id)
        ->and($cues[0]->order)->toBe(1)
        ->and($cues[0]->text)->toBe('პირველი ტექსტი მეორე ტექსტი')
        ->and($cues[0]->start_ms)->toBe(1_000)
        ->and($cues[0]->end_ms)->toBe(4_000)
        ->and($cues[1]->id)->toBe($laterCaptionCue->id)
        ->and($cues[1]->order)->toBe(2)
        ->and($cues[1]->text)->toBe('მესამე ტექსტი')
        ->and($nextCaptionCue->fresh())->toBeNull();
});

test('rejects merging the last cue because it has no next cue', function () {
    [$videoProject, , , $lastCaptionCue] = createCaptionCuesForMerge();

    $this->from(route('video-projects.show', $videoProject))
        ->post(route('video-projects.caption-cues.merge-next.store', [
            $videoProject,
            $lastCaptionCue,
        ]))
        ->assertRedirect(route('video-projects.show', $videoProject))
        ->assertSessionHasErrors('caption_cue');

    expect($videoProject->captionCues()->count())->toBe(3)
        ->and($lastCaptionCue->fresh()->text)->toBe('მესამე ტექსტი');
});

test('does not merge a cue belonging to another project', function () {
    [$videoProject] = createCaptionCuesForMerge(path: 'video-projects/merge-first/source.mp4');
    [$otherVideoProject, $otherCaptionCue] = createCaptionCuesForMerge(
        path: 'video-projects/merge-second/source.mp4',
    );

    $this->post(route('video-projects.caption-cues.merge-next.store', [
        $videoProject,
        $otherCaptionCue,
    ]))->assertNotFound();

    expect($otherVideoProject->captionCues()->count())->toBe(3)
        ->and($otherCaptionCue->fresh()->text)->toBe('პირველი ტექსტი');
});

/**
 * @return array{VideoProject, CaptionCue, CaptionCue, CaptionCue}
 */
function createCaptionCuesForMerge(
    string $path = 'video-projects/cue-merge/source.mp4',
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
        'text' => 'პირველი ტექსტი',
        'start_ms' => 1_000,
        'end_ms' => 2_000,
    ]);

    $nextCaptionCue = $videoProject->captionCues()->create([
        'order' => 2,
        'text' => 'მეორე ტექსტი',
        'start_ms' => 2_500,
        'end_ms' => 4_000,
    ]);

    $laterCaptionCue = $videoProject->captionCues()->create([
        'order' => 3,
        'text' => 'მესამე ტექსტი',
        'start_ms' => 4_500,
        'end_ms' => 5_500,
    ]);

    return [$videoProject, $captionCue, $nextCaptionCue, $laterCaptionCue];
}
