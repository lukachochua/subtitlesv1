<?php

use App\Actions\GenerateAssSubtitleContent;
use App\Models\CaptionCue;
use Illuminate\Support\Collection;

test('generates dimension-aware ASS with complete style and timestamp mappings', function () {
    $content = (new GenerateAssSubtitleContent)->handle(
        new Collection([new CaptionCue([
            'order' => 1,
            'text' => 'ქართული ტექსტი',
            'start_ms' => 2_141,
            'end_ms' => 4_326,
        ])]),
        captionStyleForAss([
            'font' => 'georgian_serif',
            'font_size_px' => 32,
            'italic' => true,
            'text_color' => '#12abef',
            'background_color' => '#991b1b',
            'background_opacity_percent' => 90,
            'text_alignment' => 'left',
            'vertical_position_percent' => 92,
            'outline_color' => '#123456',
            'outline_width_px' => 1.5,
            'shadow' => false,
        ]),
        1_080,
        1_920,
    );

    expect($content)
        ->toContain("PlayResX: 360\nPlayResY: 640")
        ->toContain('Style: CaptionText,Noto Serif Georgian,32,&H00EFAB12,&H00EFAB12,&H00563412,&H00000000,-1,-1,0,0,100,100,0,0,1,1.5,0,2,18,18,0,1')
        ->toContain('Style: CaptionBox,Noto Serif Georgian,32,&HFF000000,&HFF000000,&H1A1B1B99,&H1A1B1B99,-1,-1,0,0,100,100,0,0,3,4,0,2,18,18,0,1')
        ->toContain('Dialogue: 0,0:00:02.14,0:00:04.33,CaptionBox,,0,0,0,,{\\an1\\pos(18,539)}ქართული ტექსტი')
        ->toContain('Dialogue: 1,0:00:02.14,0:00:04.33,CaptionText,,0,0,0,,{\\an1\\pos(18,539)}ქართული ტექსტი');
});

test('uses one text layer when the background is transparent', function () {
    $content = (new GenerateAssSubtitleContent)->handle(
        new Collection([new CaptionCue([
            'order' => 1,
            'text' => "ხაზი {ერთი}\\ტესტი\nხაზი ორი",
            'start_ms' => 80,
            'end_ms' => 2_141,
        ])]),
        captionStyleForAss([
            'font' => 'system_sans',
            'bold' => false,
            'background_opacity_percent' => 0,
            'text_alignment' => 'right',
            'vertical_position_percent' => 10,
        ]),
        1_920,
        1_080,
    );

    expect($content)
        ->toContain("PlayResX: 1138\nPlayResY: 640")
        ->toContain('Style: CaptionText,Noto Sans Georgian,28,&H00FFFFFF,&H00FFFFFF,&H00000000,&H00000000,0,0,0,0,100,100,0,0,1,0,1,2,18,18,0,1')
        ->not->toContain('Style: CaptionBox')
        ->not->toContain('Dialogue: 0')
        ->toContain('Dialogue: 1,0:00:00.08,0:00:02.14,CaptionText,,0,0,0,,{\\an9\\pos(1120,73)}ხაზი \\{ერთი\\}\\\\ტესტი\\Nხაზი ორი');
});

test('rejects unsupported dimensions, fonts, alignments, and colors', function (array $style, int $width, int $height, string $message) {
    expect(fn () => (new GenerateAssSubtitleContent)->handle(
        new Collection([new CaptionCue([
            'order' => 1,
            'text' => 'ქართული',
            'start_ms' => 0,
            'end_ms' => 1_000,
        ])]),
        captionStyleForAss($style),
        $width,
        $height,
    ))->toThrow(InvalidArgumentException::class, $message);
})->with([
    'width' => [[], 0, 640, 'ASS source dimensions must be positive integers.'],
    'height' => [[], 368, 0, 'ASS source dimensions must be positive integers.'],
    'font' => [['font' => 'unknown'], 368, 640, 'Unsupported ASS caption font: unknown'],
    'alignment' => [['text_alignment' => 'justify'], 368, 640, 'Unsupported ASS caption alignment: justify'],
    'color' => [['text_color' => 'white'], 368, 640, 'Invalid ASS caption color: white'],
]);

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function captionStyleForAss(array $overrides = []): array
{
    return array_replace([
        'font' => 'georgian_sans',
        'font_size_px' => 28,
        'bold' => true,
        'italic' => false,
        'text_color' => '#ffffff',
        'background_color' => '#000000',
        'background_opacity_percent' => 75,
        'text_alignment' => 'center',
        'vertical_position_percent' => 100,
        'outline_color' => '#000000',
        'outline_width_px' => 0,
        'shadow' => true,
    ], $overrides);
}
