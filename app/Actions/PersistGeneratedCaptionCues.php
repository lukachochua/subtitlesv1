<?php

namespace App\Actions;

use App\Models\CaptionCue as PersistedCaptionCue;
use App\Models\VideoProject;
use App\ValueObjects\CaptionCue as GeneratedCaptionCue;
use App\ValueObjects\TranscriptionWord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class PersistGeneratedCaptionCues
{
    /**
     * @param  list<GeneratedCaptionCue>  $generatedCues
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
                ->findOrFail($videoProject->id);

            if ($lockedVideoProject->captionCues()->exists()) {
                throw new LogicException(
                    "Video project {$lockedVideoProject->id} already has saved caption cues.",
                );
            }

            $savedCues = new Collection;

            foreach ($generatedCues as $cue) {
                $savedCue = $lockedVideoProject->captionCues()->create([
                    'order' => $cue->order,
                    'text' => $cue->text,
                    'start_ms' => $cue->startMs,
                    'end_ms' => $cue->endMs,
                ]);

                $savedCue->words()->createMany(array_map(
                    fn (TranscriptionWord $word, int $index): array => [
                        'order' => $index + 1,
                        'text' => $word->text,
                        'start_ms' => $word->startMs,
                        'end_ms' => $word->endMs,
                    ],
                    $cue->words,
                    array_keys($cue->words),
                ));
                $savedCues->push($savedCue);
            }

            return $savedCues;
        });
    }
}
