<?php

namespace App\Actions;

use App\Models\CaptionCue;
use App\Models\CaptionWord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCaptionCueText
{
    public function handle(CaptionCue $captionCue, string $text): void
    {
        DB::transaction(function () use ($captionCue, $text): void {
            $lockedCaptionCue = CaptionCue::query()
                ->lockForUpdate()
                ->findOrFail($captionCue->id);
            $words = $lockedCaptionCue->words()->reorder('order')->lockForUpdate()->get();
            $correctedWords = preg_split('/\s+/u', trim($text)) ?: [];

            if ($words->isNotEmpty() && count($correctedWords) !== $words->count()) {
                throw ValidationException::withMessages([
                    'text' => 'Spelling corrections must keep the same number of words so their exact timing can be preserved.',
                ]);
            }

            $lockedCaptionCue->update(['text' => implode(' ', $correctedWords)]);

            $words->each(function (CaptionWord $word, int $index) use ($correctedWords, $words, $lockedCaptionCue): void {
                $maximumEndMs = isset($words[$index + 1])
                    ? $words[$index + 1]->start_ms
                    : $lockedCaptionCue->end_ms;

                $word->update([
                    'text' => $correctedWords[$index],
                    'end_ms' => min($word->end_ms, $maximumEndMs),
                ]);
            });
        });
    }
}
