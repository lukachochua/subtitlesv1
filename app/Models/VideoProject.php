<?php

namespace App\Models;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['original_filename', 'disk', 'path', 'mime_type', 'size_bytes', 'duration_ms', 'caption_style'])]
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
     *     shadow: bool
     * }
     */
    public const DEFAULT_CAPTION_STYLE = [
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
    ];

    public function captionCues(): HasMany
    {
        return $this->hasMany(CaptionCue::class)->orderBy('order');
    }

    /**
     * @return array<string, mixed>
     */
    public function resolvedCaptionStyle(): array
    {
        return $this->caption_style ?? self::DEFAULT_CAPTION_STYLE;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_ms' => 'integer',
            'caption_style' => 'array',
        ];
    }
}
