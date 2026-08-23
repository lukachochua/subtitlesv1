<?php

namespace App\Http\Controllers;

use App\Actions\LoadVideoProjectCaptionData;
use App\Enums\VideoRenderQuality;
use App\Models\CaptionCue as PersistedCaptionCue;
use App\Models\VideoProject;
use App\ValueObjects\CaptionCue as GeneratedCaptionCue;
use Illuminate\Support\Facades\Storage;
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
            'captionStyle' => $videoProject->resolvedCaptionStyle(),
            'renderQuality' => ($videoProject->render_quality ?? VideoRenderQuality::High)->value,
            'renderState' => [
                'status' => $videoProject->render_status?->value,
                'error' => $videoProject->render_error,
                'rendered_at' => $videoProject->rendered_at?->toIso8601String(),
            ],
            'transcriptionState' => [
                'status' => $videoProject->transcription_status?->value,
                'error' => $videoProject->transcription_error,
                'transcribed_at' => $videoProject->transcribed_at?->toIso8601String(),
            ],
            'hasCaptionedVideo' => Storage::disk($videoProject->disk)
                ->exists("video-projects/{$videoProject->id}/captioned.mp4"),
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
            return array_values($savedCues
                ->map(fn (PersistedCaptionCue $cue): array => [
                    'id' => $cue->id,
                    'order' => $cue->order,
                    'text' => $cue->text,
                    'start_ms' => $cue->start_ms,
                    'end_ms' => $cue->end_ms,
                ])
                ->all());
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
