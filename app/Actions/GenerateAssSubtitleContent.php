<?php

namespace App\Actions;

use App\Models\CaptionCue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class GenerateAssSubtitleContent
{
    private const PLAY_RESOLUTION_HEIGHT = 640;

    private const HORIZONTAL_MARGIN = 18;

    /**
     * @param  Collection<int, CaptionCue>  $captionCues
     * @param  array{
     *     font: string,
     *     font_size_px: int,
     *     bold: bool,
     *     italic: bool,
     *     text_color: string,
     *     background_color: string,
     *     background_opacity_percent: int,
     *     text_alignment: string,
     *     vertical_position_percent: int,
     *     outline_color: string,
     *     outline_width_px: int|float,
     *     shadow: bool
     * }  $style
     */
    public function handle(Collection $captionCues, array $style, int $sourceWidth, int $sourceHeight): string
    {
        if ($sourceWidth < 1 || $sourceHeight < 1) {
            throw new InvalidArgumentException('ASS source dimensions must be positive integers.');
        }

        $playResolutionWidth = max(1, (int) round(self::PLAY_RESOLUTION_HEIGHT * $sourceWidth / $sourceHeight));
        $fontName = $this->fontName($style['font']);
        $primaryColor = $this->assColor($style['text_color']);
        $outlineColor = $this->assColor($style['outline_color']);
        $backgroundColor = $this->assColor($style['background_color'], $style['background_opacity_percent']);
        $bold = $style['bold'] ? -1 : 0;
        $italic = $style['italic'] ? -1 : 0;
        $shadow = $style['shadow'] ? 1 : 0;
        $outlineWidth = $this->formatNumber($style['outline_width_px']);

        $lines = [
            '[Script Info]',
            'ScriptType: v4.00+',
            "PlayResX: {$playResolutionWidth}",
            'PlayResY: '.self::PLAY_RESOLUTION_HEIGHT,
            'WrapStyle: 0',
            'ScaledBorderAndShadow: yes',
            '',
            '[V4+ Styles]',
            'Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, OutlineColour, BackColour, Bold, Italic, Underline, StrikeOut, ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, Alignment, MarginL, MarginR, MarginV, Encoding',
            "Style: CaptionText,{$fontName},{$style['font_size_px']},{$primaryColor},{$primaryColor},{$outlineColor},&H00000000,{$bold},{$italic},0,0,100,100,0,0,1,{$outlineWidth},{$shadow},2,".self::HORIZONTAL_MARGIN.','.self::HORIZONTAL_MARGIN.',0,1',
        ];

        if ($style['background_opacity_percent'] > 0) {
            $lines[] = "Style: CaptionBox,{$fontName},{$style['font_size_px']},&HFF000000,&HFF000000,{$backgroundColor},{$backgroundColor},{$bold},{$italic},0,0,100,100,0,0,3,4,0,2,".self::HORIZONTAL_MARGIN.','.self::HORIZONTAL_MARGIN.',0,1';
        }

        $lines = [...$lines, '', '[Events]', 'Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text'];

        foreach ($captionCues as $captionCue) {
            $start = $this->timestamp($captionCue->start_ms);
            $end = $this->timestamp(max($captionCue->end_ms, $captionCue->start_ms + 10));
            $position = $this->positionOverride(
                $style['text_alignment'],
                $style['vertical_position_percent'],
                $playResolutionWidth,
            );
            $text = $position.$this->escapeText($captionCue->text);

            if ($style['background_opacity_percent'] > 0) {
                $lines[] = "Dialogue: 0,{$start},{$end},CaptionBox,,0,0,0,,{$text}";
            }

            $lines[] = "Dialogue: 1,{$start},{$end},CaptionText,,0,0,0,,{$text}";
        }

        return implode("\n", $lines)."\n";
    }

    private function fontName(string $font): string
    {
        return match ($font) {
            'georgian_sans', 'system_sans' => 'Noto Sans Georgian',
            'georgian_serif' => 'Noto Serif Georgian',
            default => throw new InvalidArgumentException("Unsupported ASS caption font: {$font}"),
        };
    }

    private function assColor(string $hexColor, int $opacityPercent = 100): string
    {
        if (preg_match('/^#(?<red>[0-9a-f]{2})(?<green>[0-9a-f]{2})(?<blue>[0-9a-f]{2})$/i', $hexColor, $matches) !== 1) {
            throw new InvalidArgumentException("Invalid ASS caption color: {$hexColor}");
        }

        $alpha = (int) round((100 - $opacityPercent) * 255 / 100);

        return sprintf(
            '&H%02X%s%s%s',
            $alpha,
            Str::upper($matches['blue']),
            Str::upper($matches['green']),
            Str::upper($matches['red']),
        );
    }

    private function timestamp(int $milliseconds): string
    {
        $centiseconds = (int) round($milliseconds / 10);
        $hours = intdiv($centiseconds, 360_000);
        $minutes = intdiv($centiseconds % 360_000, 6_000);
        $seconds = intdiv($centiseconds % 6_000, 100);
        $remainingCentiseconds = $centiseconds % 100;

        return sprintf('%d:%02d:%02d.%02d', $hours, $minutes, $seconds, $remainingCentiseconds);
    }

    private function positionOverride(string $alignment, int $positionPercent, int $playResolutionWidth): string
    {
        $horizontalAlignment = match ($alignment) {
            'left' => 1,
            'center' => 2,
            'right' => 3,
            default => throw new InvalidArgumentException("Unsupported ASS caption alignment: {$alignment}"),
        };
        $verticalAlignment = match (true) {
            $positionPercent <= 33 => 6,
            $positionPercent <= 66 => 3,
            default => 0,
        };
        $alignmentNumber = $horizontalAlignment + $verticalAlignment;
        $x = match ($alignment) {
            'left' => self::HORIZONTAL_MARGIN,
            'center' => (int) round($playResolutionWidth / 2),
            'right' => $playResolutionWidth - self::HORIZONTAL_MARGIN,
        };
        $topInset = 16;
        $bottomInset = 56;
        $usableHeight = self::PLAY_RESOLUTION_HEIGHT - $topInset - $bottomInset;
        $y = (int) round($topInset + ($usableHeight * $positionPercent / 100));

        return "{\\an{$alignmentNumber}\\pos({$x},{$y})}";
    }

    private function escapeText(string $text): string
    {
        return (string) Str::of($text)
            ->replace('\\', '\\\\')
            ->replace('{', '\\{')
            ->replace('}', '\\}')
            ->replace(["\r\n", "\r", "\n"], '\\N');
    }

    private function formatNumber(int|float $number): string
    {
        return rtrim(rtrim(number_format($number, 1, '.', ''), '0'), '.');
    }
}
