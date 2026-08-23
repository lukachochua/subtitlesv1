<?php

namespace App\Console\Commands;

use App\Actions\ConvertNemoTranscriptionWords;
use App\Models\VideoProject;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use JsonException;
use UnexpectedValueException;

#[Signature('video-projects:inspect-transcription {videoProject : The video project ID}')]
#[Description('Display normalized NeMo words for one video project')]
class InspectVideoProjectTranscriptionCommand extends Command
{
    public function handle(ConvertNemoTranscriptionWords $convertNemoTranscriptionWords): int
    {
        $videoProjectId = filter_var(
            $this->argument('videoProject'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );

        if ($videoProjectId === false) {
            $this->error('The video project ID must be a positive integer.');

            return self::FAILURE;
        }

        $videoProject = VideoProject::find($videoProjectId);

        if ($videoProject === null) {
            $this->error("Video project {$videoProjectId} was not found.");

            return self::FAILURE;
        }

        if ($videoProject->duration_ms === null) {
            $this->error("Video project {$videoProjectId} must be inspected before its transcription.");

            return self::FAILURE;
        }

        $transcriptionPath = "video-projects/{$videoProject->id}/transcription.nemo-fastconformer.raw.json";
        $disk = Storage::disk($videoProject->disk);

        if (! $disk->exists($transcriptionPath)) {
            $this->error("Video project {$videoProjectId} does not have a NeMo transcription result.");

            return self::FAILURE;
        }

        try {
            $transcription = json_decode(
                $disk->get($transcriptionPath),
                true,
                flags: JSON_THROW_ON_ERROR,
            );

            if (! is_array($transcription)) {
                throw new UnexpectedValueException('The NeMo transcription result is invalid.');
            }

            $words = $convertNemoTranscriptionWords->handle(
                $transcription,
                $videoProject->duration_ms,
            );
        } catch (JsonException|UnexpectedValueException $exception) {
            $this->error("Could not inspect video project {$videoProjectId} transcription: {$exception->getMessage()}");

            return self::FAILURE;
        }

        $rows = [];

        foreach ($words as $index => $word) {
            $rows[] = [$index + 1, $word->text, $word->startMs, $word->endMs];
        }

        $this->table(['Order', 'Text', 'Start (ms)', 'End (ms)'], $rows);

        return self::SUCCESS;
    }
}
