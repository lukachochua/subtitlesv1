<?php

namespace App\Actions;

use App\Models\VideoProject;
use App\ValueObjects\CaptionCue;
use App\ValueObjects\TranscriptionWord;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;
use UnexpectedValueException;

class LoadVideoProjectCaptionData
{
    public function __construct(
        private ConvertNemoTranscriptionWords $convertNemoTranscriptionWords,
        private GenerateCaptionCues $generateCaptionCues,
    ) {}

    /**
     * @return array{words: list<TranscriptionWord>, cues: list<CaptionCue>}
     */
    public function handle(VideoProject $videoProject): array
    {
        if ($videoProject->duration_ms === null) {
            throw new RuntimeException(
                "Video project {$videoProject->id} must be inspected before its transcription.",
            );
        }

        $transcriptionPath = "video-projects/{$videoProject->id}/transcription.nemo-fastconformer.raw.json";
        $disk = Storage::disk($videoProject->disk);

        if (! $disk->exists($transcriptionPath)) {
            throw new RuntimeException(
                "Video project {$videoProject->id} does not have a NeMo transcription result.",
            );
        }

        try {
            $transcription = json_decode(
                $disk->get($transcriptionPath),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new UnexpectedValueException(
                'The NeMo transcription result contains invalid JSON.',
                previous: $exception,
            );
        }

        if (! is_array($transcription)) {
            throw new UnexpectedValueException('The NeMo transcription result is invalid.');
        }

        $words = $this->convertNemoTranscriptionWords->handle(
            $transcription,
            $videoProject->duration_ms,
        );

        return [
            'words' => $words,
            'cues' => $this->generateCaptionCues->handle($words),
        ];
    }
}
