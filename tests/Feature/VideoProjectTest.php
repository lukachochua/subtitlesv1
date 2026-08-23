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

test('resolves the complete default caption style when none is stored', function () {
    $videoProject = VideoProject::create([
        'original_filename' => 'unstyled.mp4',
        'disk' => 'local',
        'path' => 'video-projects/test/unstyled.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 1_024,
    ]);

    expect($videoProject->caption_style)->toBeNull()
        ->and($videoProject->resolvedCaptionStyle())->toBe(VideoProject::DEFAULT_CAPTION_STYLE);
});

test('casts and resolves a stored caption style as an array', function () {
    $captionStyle = [
        'font' => 'georgian_serif',
        'font_size_px' => 36,
        'bold' => false,
        'italic' => true,
        'text_color' => '#facc15',
        'background_color' => '#1d4ed8',
        'background_opacity_percent' => 60,
        'text_alignment' => 'left',
        'vertical_position_percent' => 40,
        'outline_color' => '#000000',
        'outline_width_px' => 1.5,
        'shadow' => false,
    ];

    $videoProject = VideoProject::create([
        'original_filename' => 'styled.mp4',
        'disk' => 'local',
        'path' => 'video-projects/test/styled.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 2_048,
        'caption_style' => $captionStyle,
    ])->fresh();

    expect($videoProject->caption_style)->toBe($captionStyle)
        ->and($videoProject->resolvedCaptionStyle())->toBe($captionStyle);
});

test('resolves missing or incorrectly typed stored style fields to defaults', function () {
    $videoProject = VideoProject::create([
        'original_filename' => 'styled.mp4',
        'disk' => 'local',
        'path' => 'video-projects/test/styled.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 2_048,
        'caption_style' => [
            ...VideoProject::DEFAULT_CAPTION_STYLE,
            'font_size_px' => 'large',
            'text_color' => null,
            'bold' => 'yes',
        ],
    ])->fresh();

    expect($videoProject->resolvedCaptionStyle())
        ->toBe(VideoProject::DEFAULT_CAPTION_STYLE);
});
