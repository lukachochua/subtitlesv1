<?php

namespace App\Models;

use App\Enums\TranscriptionStatus;
use App\Enums\VideoRenderQuality;
use App\Enums\VideoRenderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $original_filename
 * @property string $disk
 * @property string $path
 * @property string $mime_type
 * @property int $size_bytes
 * @property int|null $duration_ms
 * @property array<string, mixed>|null $caption_style
 * @property VideoRenderStatus|null $render_status
 * @property string|null $render_error
 * @property Carbon|null $rendered_at
 * @property VideoRenderQuality|null $render_quality
 * @property TranscriptionStatus|null $transcription_status
 * @property string|null $transcription_error
 * @property Carbon|null $transcribed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['original_filename', 'disk', 'path', 'mime_type', 'size_bytes', 'duration_ms', 'caption_style', 'render_status', 'render_error', 'rendered_at', 'render_quality', 'transcription_status', 'transcription_error', 'transcribed_at'])]
class VideoProject extends Model
{
    /**
     * @var array{
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
     *     shadow: bool,
     *     active_word_enabled: bool,
     *     active_word_color: string,
     *     active_word_style: string
     * }
     */
    public const DEFAULT_CAPTION_STYLE = [
        'font' => 'georgian_sans',
        'font_size_px' => 20,
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
        'active_word_enabled' => true,
        'active_word_color' => '#fde047',
        'active_word_style' => 'text',
    ];

    /**
     * @return HasMany<CaptionCue, $this>
     */
    public function captionCues(): HasMany
    {
        return $this->hasMany(CaptionCue::class)->orderBy('order');
    }

    /**
     * @return array{
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
     *     shadow: bool,
     *     active_word_enabled: bool,
     *     active_word_color: string,
     *     active_word_style: string
     * }
     */
    public function resolvedCaptionStyle(): array
    {
        $style = $this->caption_style;

        if ($style === null) {
            return self::DEFAULT_CAPTION_STYLE;
        }

        return [
            'font' => is_string($style['font'] ?? null) ? $style['font'] : self::DEFAULT_CAPTION_STYLE['font'],
            'font_size_px' => is_int($style['font_size_px'] ?? null) ? $style['font_size_px'] : self::DEFAULT_CAPTION_STYLE['font_size_px'],
            'bold' => is_bool($style['bold'] ?? null) ? $style['bold'] : self::DEFAULT_CAPTION_STYLE['bold'],
            'italic' => is_bool($style['italic'] ?? null) ? $style['italic'] : self::DEFAULT_CAPTION_STYLE['italic'],
            'text_color' => is_string($style['text_color'] ?? null) ? $style['text_color'] : self::DEFAULT_CAPTION_STYLE['text_color'],
            'background_color' => is_string($style['background_color'] ?? null) ? $style['background_color'] : self::DEFAULT_CAPTION_STYLE['background_color'],
            'background_opacity_percent' => is_int($style['background_opacity_percent'] ?? null) ? $style['background_opacity_percent'] : self::DEFAULT_CAPTION_STYLE['background_opacity_percent'],
            'text_alignment' => is_string($style['text_alignment'] ?? null) ? $style['text_alignment'] : self::DEFAULT_CAPTION_STYLE['text_alignment'],
            'vertical_position_percent' => is_int($style['vertical_position_percent'] ?? null) ? $style['vertical_position_percent'] : self::DEFAULT_CAPTION_STYLE['vertical_position_percent'],
            'outline_color' => is_string($style['outline_color'] ?? null) ? $style['outline_color'] : self::DEFAULT_CAPTION_STYLE['outline_color'],
            'outline_width_px' => is_int($style['outline_width_px'] ?? null) || is_float($style['outline_width_px'] ?? null) ? $style['outline_width_px'] : self::DEFAULT_CAPTION_STYLE['outline_width_px'],
            'shadow' => is_bool($style['shadow'] ?? null) ? $style['shadow'] : self::DEFAULT_CAPTION_STYLE['shadow'],
            'active_word_enabled' => is_bool($style['active_word_enabled'] ?? null) ? $style['active_word_enabled'] : self::DEFAULT_CAPTION_STYLE['active_word_enabled'],
            'active_word_color' => is_string($style['active_word_color'] ?? null) ? $style['active_word_color'] : self::DEFAULT_CAPTION_STYLE['active_word_color'],
            'active_word_style' => is_string($style['active_word_style'] ?? null) ? $style['active_word_style'] : self::DEFAULT_CAPTION_STYLE['active_word_style'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_ms' => 'integer',
            'caption_style' => 'array',
            'render_status' => VideoRenderStatus::class,
            'rendered_at' => 'datetime',
            'render_quality' => VideoRenderQuality::class,
            'transcription_status' => TranscriptionStatus::class,
            'transcribed_at' => 'datetime',
        ];
    }
}
