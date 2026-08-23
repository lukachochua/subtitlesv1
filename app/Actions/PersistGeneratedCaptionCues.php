<?php

namespace App\Actions;

use App\Models\CaptionCue as PersistedCaptionCue;
use App\Models\VideoProject;
use App\ValueObjects\CaptionCue as GeneratedCaptionCue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class PersistGeneratedCaptionCues
{
    /**
     * @param  non-empty-list<GeneratedCaptionCue>  $generatedCues
     * @return Collection<int, PersistedCaptionCue>
     */
    public function handle(VideoProject $videoProject, array $generatedCues): Collection
    {
        if ($generatedCues === []) {
            throw new InvalidArgumentException('At least one generated caption cue is required.');
        }

        return DB::transaction(function () use ($videoProject, $generatedCues): Collection {
            $lockedVideoProject = VideoProject::query()
                ->lockForUpdate()
                ->findOrFail($videoProject->getKey());

            if ($lockedVideoProject->captionCues()->exists()) {
                throw new LogicException(
                    "Video project {$lockedVideoProject->id} already has saved caption cues.",
                );
            }

            return $lockedVideoProject->captionCues()->createMany(
                array_map(
                    fn (GeneratedCaptionCue $cue): array => [
                        'order' => $cue->order,
                        'text' => $cue->text,
                        'start_ms' => $cue->startMs,
                        'end_ms' => $cue->endMs,
                    ],
                    $generatedCues,
                ),
            );
        });
    }
}
