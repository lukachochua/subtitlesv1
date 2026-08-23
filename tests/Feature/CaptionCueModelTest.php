<?php

use App\Models\CaptionCue;
use App\Models\VideoProject;

test('stores ordered caption cues through their video project', function () {
    $videoProject = VideoProject::create([
        'original_filename' => 'ქართული-ინტერვიუ.mp4',
        'disk' => 'local',
        'path' => 'video-projects/caption-cue-test/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 2_048,
        'duration_ms' => 8_000,
    ]);

    $secondCue = $videoProject->captionCues()->create([
        'order' => 2,
        'text' => 'კარგად ვარ.',
        'start_ms' => 2_240,
        'end_ms' => 3_840,
    ]);

    $firstCue = $videoProject->captionCues()->create([
        'order' => 1,
        'text' => 'როგორ ხარ?',
        'start_ms' => 480,
        'end_ms' => 2_160,
    ]);

    $this->assertModelExists($firstCue);
    $this->assertModelExists($secondCue);

    expect($firstCue->video_project_id)->toBe($videoProject->id)
        ->and($firstCue->order)->toBeInt()->toBe(1)
        ->and($firstCue->start_ms)->toBeInt()->toBe(480)
        ->and($firstCue->end_ms)->toBeInt()->toBe(2_160)
        ->and($firstCue->videoProject->is($videoProject))->toBeTrue()
        ->and($videoProject->captionCues->pluck('order')->all())->toBe([1, 2]);
});

test('deletes caption cues with their video project', function () {
    $videoProject = VideoProject::create([
        'original_filename' => 'წასაშლელი.mp4',
        'disk' => 'local',
        'path' => 'video-projects/caption-cue-delete-test/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 1_024,
    ]);

    $captionCue = $videoProject->captionCues()->create([
        'order' => 1,
        'text' => 'საცდელი ტექსტი',
        'start_ms' => 0,
        'end_ms' => 1_000,
    ]);

    $videoProject->delete();

    $this->assertModelMissing($captionCue);
    expect(CaptionCue::query()->count())->toBe(0);
});
