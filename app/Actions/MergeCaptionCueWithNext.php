<?php

namespace App\Actions;

use App\Models\CaptionCue;
use App\Models\VideoProject;
use Illuminate\Support\Facades\DB;
use LogicException;

class MergeCaptionCueWithNext
{
    public function handle(CaptionCue $captionCue): CaptionCue
    {
        return DB::transaction(function () use ($captionCue): CaptionCue {
            $videoProject = VideoProject::query()
                ->lockForUpdate()
                ->findOrFail($captionCue->video_project_id);

            $lockedCaptionCue = $videoProject->captionCues()
                ->reorder()
                ->lockForUpdate()
                ->findOrFail($captionCue->id);

            $nextCaptionCue = $videoProject->captionCues()
                ->reorder('order')
                ->where('order', '>', $lockedCaptionCue->order)
                ->lockForUpdate()
                ->first();

            if ($nextCaptionCue === null) {
                throw new LogicException('The last caption cue cannot be merged with a next cue.');
            }

            $wordOrderOffset = $lockedCaptionCue->words()->count();
            $nextCaptionCue->words()->reorder('order')->lockForUpdate()->get()
                ->each(function ($word) use ($lockedCaptionCue, $wordOrderOffset): void {
                    $word->forceFill([
                        'caption_cue_id' => $lockedCaptionCue->id,
                        'order' => $wordOrderOffset + $word->order,
                    ])->save();
                });

            $lockedCaptionCue->update([
                'text' => trim($lockedCaptionCue->text).' '.trim($nextCaptionCue->text),
                'end_ms' => $nextCaptionCue->end_ms,
            ]);

            $nextOrder = $nextCaptionCue->order;
            $nextCaptionCue->delete();

            $laterCues = $videoProject->captionCues()
                ->reorder('order')
                ->where('order', '>', $nextOrder)
                ->lockForUpdate()
                ->get();

            foreach ($laterCues as $laterCue) {
                $laterCue->update(['order' => $laterCue->order - 1]);
            }

            return $lockedCaptionCue;
        });
    }
}
