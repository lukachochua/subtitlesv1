<?php

use App\Enums\TranscriptionStatus;
use App\Enums\VideoRenderQuality;
use App\Enums\VideoRenderStatus;
use App\Models\VideoProject;
use Carbon\CarbonInterface;

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

test('casts persisted render lifecycle fields', function () {
    $videoProject = VideoProject::create([
        'original_filename' => 'rendered.mp4',
        'disk' => 'local',
        'path' => 'video-projects/test/rendered.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 1_024,
        'render_status' => VideoRenderStatus::Completed,
        'render_error' => null,
        'rendered_at' => '2026-08-23 20:00:00',
    ])->fresh();

    expect($videoProject->render_status)->toBe(VideoRenderStatus::Completed)
        ->and($videoProject->render_error)->toBeNull()
        ->and($videoProject->rendered_at)->toBeInstanceOf(CarbonInterface::class);
});

test('casts quality and transcription lifecycle fields', function () {
    $videoProject = VideoProject::create([
        'original_filename' => 'transcribed.mp4',
        'disk' => 'local',
        'path' => 'video-projects/test/transcribed.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 1_024,
        'render_quality' => VideoRenderQuality::High,
        'transcription_status' => TranscriptionStatus::Completed,
        'transcribed_at' => '2026-08-23 21:00:00',
    ])->fresh();

    expect($videoProject->render_quality)->toBe(VideoRenderQuality::High)
        ->and($videoProject->transcription_status)->toBe(TranscriptionStatus::Completed)
        ->and($videoProject->transcribed_at)->toBeInstanceOf(CarbonInterface::class);
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
        'active_word_enabled' => false,
        'active_word_color' => '#22c55e',
        'active_word_style' => 'background',
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
