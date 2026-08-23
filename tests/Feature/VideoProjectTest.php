<?php

use App\Models\VideoProject;

test('stores source video metadata', function () {
    $videoProject = VideoProject::create([
        'original_filename' => 'ქართული-ინტერვიუ.mp4',
        'disk' => 'local',
        'path' => 'video-projects/test/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 48_392_017,
        'duration_ms' => 7_967,
    ]);

    $this->assertModelExists($videoProject);

    expect($videoProject->getTable())->toBe('video_projects')
        ->and($videoProject->only([
            'original_filename',
            'disk',
            'path',
            'mime_type',
            'size_bytes',
            'duration_ms',
        ]))->toBe([
            'original_filename' => 'ქართული-ინტერვიუ.mp4',
            'disk' => 'local',
            'path' => 'video-projects/test/source.mp4',
            'mime_type' => 'video/mp4',
            'size_bytes' => 48_392_017,
            'duration_ms' => 7_967,
        ]);
});

test('allows duration to remain unknown before media inspection', function () {
    $videoProject = VideoProject::create([
        'original_filename' => 'uninspected.mp4',
        'disk' => 'local',
        'path' => 'video-projects/test/uninspected.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 1_024,
    ]);

    expect($videoProject->duration_ms)->toBeNull();
});
