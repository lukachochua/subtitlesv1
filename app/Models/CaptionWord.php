<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $caption_cue_id
 * @property int $order
 * @property string $text
 * @property int $start_ms
 * @property int $end_ms
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read CaptionCue $captionCue
 */
#[Fillable(['order', 'text', 'start_ms', 'end_ms'])]
class CaptionWord extends Model
{
    /** @return BelongsTo<CaptionCue, $this> */
    public function captionCue(): BelongsTo
    {
        return $this->belongsTo(CaptionCue::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'start_ms' => 'integer',
            'end_ms' => 'integer',
        ];
    }
}
