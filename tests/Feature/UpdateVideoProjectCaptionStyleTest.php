<?php

use App\Models\VideoProject;

test('replaces the complete project caption style', function () {
    $videoProject = createVideoProjectForCaptionStyleUpdate();
    $captionStyle = validCaptionStyle([
        'font' => 'georgian_serif',
        'font_size_px' => 42,
        'bold' => false,
        'italic' => true,
        'vertical_position_percent' => 35,
        'outline_width_px' => 1.5,
        'shadow' => false,
    ]);

    $this->patch(
        route('video-projects.caption-style.update', $videoProject),
        [...$captionStyle, 'unexpected' => 'ignored'],
    )->assertRedirectToRoute('video-projects.show', $videoProject);

    expect($videoProject->fresh()->caption_style)->toBe($captionStyle);
});

test('rejects invalid caption style values', function (string $field, mixed $value) {
    $videoProject = createVideoProjectForCaptionStyleUpdate();

    $this->from(route('video-projects.show', $videoProject))
        ->patch(
            route('video-projects.caption-style.update', $videoProject),
            validCaptionStyle([$field => $value]),
        )
        ->assertRedirect(route('video-projects.show', $videoProject))
        ->assertSessionHasErrors($field);

    expect($videoProject->fresh()->caption_style)->toBeNull();
})->with([
    'font is required' => ['font', null],
    'font must be supported' => ['font', 'comic_sans'],
    'font size minimum' => ['font_size_px', 11],
    'font size maximum' => ['font_size_px', 73],
    'bold must be boolean' => ['bold', 'yes'],
    'italic must be boolean' => ['italic', 'no'],
    'text color format' => ['text_color', 'white'],
    'background color format' => ['background_color', '#fff'],
    'background opacity minimum' => ['background_opacity_percent', -1],
    'background opacity maximum' => ['background_opacity_percent', 101],
    'alignment must be supported' => ['text_alignment', 'justify'],
    'vertical position minimum' => ['vertical_position_percent', -1],
    'vertical position maximum' => ['vertical_position_percent', 101],
    'outline color format' => ['outline_color', 'black'],
    'outline width minimum' => ['outline_width_px', -0.5],
    'outline width maximum' => ['outline_width_px', 4.5],
    'outline width step' => ['outline_width_px', 1.25],
    'shadow must be boolean' => ['shadow', 'yes'],
]);

test('returns not found for a missing video project', function () {
    $this->patch(
        route('video-projects.caption-style.update', 999),
        validCaptionStyle(),
    )->assertNotFound();
});

function createVideoProjectForCaptionStyleUpdate(): VideoProject
{
    return VideoProject::create([
        'original_filename' => 'styled.mp4',
        'disk' => 'local',
        'path' => 'video-projects/caption-style-update/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 2_048,
        'duration_ms' => 8_000,
    ]);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function validCaptionStyle(array $overrides = []): array
{
    return [...VideoProject::DEFAULT_CAPTION_STYLE, ...$overrides];
}
