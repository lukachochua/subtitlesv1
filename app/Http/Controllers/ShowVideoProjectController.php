<?php

namespace App\Http\Controllers;

use App\Actions\LoadVideoProjectCaptionData;
use App\Models\CaptionCue as PersistedCaptionCue;
use App\Models\VideoProject;
use App\ValueObjects\CaptionCue as GeneratedCaptionCue;
use Inertia\Inertia;
use Inertia\Response;

class ShowVideoProjectController extends Controller
{
    public function __invoke(
        VideoProject $videoProject,
        LoadVideoProjectCaptionData $loadVideoProjectCaptionData,
    ): Response {
        return Inertia::render('VideoProjects/Show', [
            'videoProject' => $videoProject->only([
                'id',
                'original_filename',
                'mime_type',
                'size_bytes',
                'duration_ms',
            ]),
            'cues' => $this->captionCues(
                $videoProject,
                $loadVideoProjectCaptionData,
            ),
        ]);
    }

    /**
     * @return list<array{id: int|null, order: int, text: string, start_ms: int, end_ms: int}>|null
     */
    private function captionCues(
        VideoProject $videoProject,
        LoadVideoProjectCaptionData $loadVideoProjectCaptionData,
    ): ?array {
        $savedCues = $videoProject->captionCues()->get();

        if ($savedCues->isNotEmpty()) {
            return $savedCues
                ->map(fn (PersistedCaptionCue $cue): array => [
                    'id' => $cue->id,
                    'order' => $cue->order,
                    'text' => $cue->text,
                    'start_ms' => $cue->start_ms,
                    'end_ms' => $cue->end_ms,
                ])
                ->all();
        }

        if (! $loadVideoProjectCaptionData->hasTranscriptionResult($videoProject)) {
            return null;
        }

        $captionData = $loadVideoProjectCaptionData->handle($videoProject);

        return array_map(
            fn (GeneratedCaptionCue $cue): array => [
                'id' => null,
                'order' => $cue->order,
                'text' => $cue->text,
                'start_ms' => $cue->startMs,
                'end_ms' => $cue->endMs,
            ],
            $captionData['cues'],
        );
    }
}
