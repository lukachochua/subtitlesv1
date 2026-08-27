<?php

namespace App\Http\Controllers;

use App\Actions\LoadVideoProjectCaptionData;
use App\Enums\VideoRenderQuality;
use App\Models\CaptionCue as PersistedCaptionCue;
use App\Models\CaptionWord;
use App\Models\VideoProject;
use App\ValueObjects\CaptionCue as GeneratedCaptionCue;
use App\ValueObjects\TranscriptionWord;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

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
     * @return list<array{id: int|null, order: int, text: string, start_ms: int, end_ms: int, words: list<array{order: int, text: string, start_ms: int, end_ms: int}>}>|null
     */
    private function captionCues(
        VideoProject $videoProject,
        LoadVideoProjectCaptionData $loadVideoProjectCaptionData,
    ): ?array {
        $savedCues = $videoProject->captionCues()->with('words')->get();

        if ($savedCues->isNotEmpty()) {
            $generatedCuesByOrder = collect();

            if (
                $savedCues->contains(fn (PersistedCaptionCue $cue): bool => $cue->words->isEmpty())
                && $loadVideoProjectCaptionData->hasTranscriptionResult($videoProject)
            ) {
                try {
                    $generatedCuesByOrder = collect(
                        $loadVideoProjectCaptionData->handle($videoProject)['cues'],
                    )->keyBy(fn (GeneratedCaptionCue $cue): int => $cue->order);
                } catch (Throwable) {
                    $generatedCuesByOrder = collect();
                }
            }

            return array_values($savedCues
                ->map(fn (PersistedCaptionCue $cue): array => [
                    'id' => $cue->id,
                    'order' => $cue->order,
                    'text' => $cue->text,
                    'start_ms' => $cue->start_ms,
                    'end_ms' => $cue->end_ms,
                    'words' => $cue->words->isNotEmpty()
                        ? $this->persistedWords($cue)
                        : $this->legacyWords($cue, $generatedCuesByOrder->get($cue->order)),
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
                'words' => array_map(
                    fn (TranscriptionWord $word, int $index): array => [
                        'order' => $index + 1,
                        'text' => $word->text,
                        'start_ms' => $word->startMs,
                        'end_ms' => $word->endMs,
                    ],
                    $cue->words,
                    array_keys($cue->words),
                ),
            ],
            $captionData['cues'],
        );
    }

    /** @return list<array{order: int, text: string, start_ms: int, end_ms: int}> */
    private function persistedWords(PersistedCaptionCue $cue): array
    {
        return array_values($cue->words->map(fn (CaptionWord $word): array => [
            'order' => $word->order,
            'text' => $word->text,
            'start_ms' => $word->start_ms,
            'end_ms' => $word->end_ms,
        ])->all());
    }

    /** @return list<array{order: int, text: string, start_ms: int, end_ms: int}> */
    private function legacyWords(PersistedCaptionCue $cue, mixed $generatedCue): array
    {
        if (! $generatedCue instanceof GeneratedCaptionCue) {
            return [];
        }

        $correctedWords = preg_split('/\s+/u', trim($cue->text)) ?: [];

        if (count($correctedWords) !== count($generatedCue->words)) {
            return [];
        }

        return array_map(
            fn (TranscriptionWord $word, int $index): array => [
                'order' => $index + 1,
                'text' => $correctedWords[$index],
                'start_ms' => $word->startMs,
                'end_ms' => min(
                    $word->endMs,
                    $generatedCue->words[$index + 1]->startMs ?? $cue->end_ms,
                ),
            ],
            $generatedCue->words,
            array_keys($generatedCue->words),
        );
    }
}
