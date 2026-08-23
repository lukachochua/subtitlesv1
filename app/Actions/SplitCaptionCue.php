<?php

namespace App\Actions;

use App\Models\CaptionCue;
use App\Models\VideoProject;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SplitCaptionCue
{
    public function handle(CaptionCue $captionCue, int $splitMs): CaptionCue
    {
        return DB::transaction(function () use ($captionCue, $splitMs): CaptionCue {
            $videoProject = VideoProject::query()
                ->lockForUpdate()
                ->findOrFail($captionCue->video_project_id);

            $lockedCaptionCue = $videoProject->captionCues()
                ->reorder()
                ->lockForUpdate()
                ->findOrFail($captionCue->getKey());

            if ($splitMs <= $lockedCaptionCue->start_ms || $splitMs >= $lockedCaptionCue->end_ms) {
                throw new InvalidArgumentException('Split time must be inside the cue interval.');
            }

            $words = preg_split('/\s+/u', trim($lockedCaptionCue->text)) ?: [];

            if (count($words) < 2) {
                throw new InvalidArgumentException('A cue needs at least two words to be split.');
            }

            $splitWordIndex = (int) ceil(count($words) / 2);
            $firstText = implode(' ', array_slice($words, 0, $splitWordIndex));
            $secondText = implode(' ', array_slice($words, $splitWordIndex));
            $originalEndMs = $lockedCaptionCue->end_ms;

            $laterCues = $videoProject->captionCues()
                ->reorder('order', 'desc')
                ->where('order', '>', $lockedCaptionCue->order)
                ->lockForUpdate()
                ->get();

            foreach ($laterCues as $laterCue) {
                $laterCue->update(['order' => $laterCue->order + 1]);
            }

            $lockedCaptionCue->update([
                'text' => $firstText,
                'end_ms' => $splitMs,
            ]);

            return $videoProject->captionCues()->create([
                'order' => $lockedCaptionCue->order + 1,
                'text' => $secondText,
                'start_ms' => $splitMs,
                'end_ms' => $originalEndMs,
            ]);
        });
    }
}
