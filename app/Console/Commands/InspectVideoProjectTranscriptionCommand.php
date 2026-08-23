<?php

namespace App\Console\Commands;

use App\Actions\LoadVideoProjectCaptionData;
use App\Models\VideoProject;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

#[Signature('video-projects:inspect-transcription {videoProject : The video project ID}')]
#[Description('Display normalized NeMo words and generated cues for one video project')]
class InspectVideoProjectTranscriptionCommand extends Command
{
    public function handle(
        LoadVideoProjectCaptionData $loadVideoProjectCaptionData,
    ): int {
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

        try {
            $captionData = $loadVideoProjectCaptionData->handle($videoProject);
        } catch (InvalidArgumentException|UnexpectedValueException $exception) {
            $this->error("Could not inspect video project {$videoProjectId} transcription: {$exception->getMessage()}");

            return self::FAILURE;
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $words = $captionData['words'];
        $cues = $captionData['cues'];

        $rows = [];

        foreach ($words as $index => $word) {
            $rows[] = [$index + 1, $word->text, $word->startMs, $word->endMs];
        }

        $this->table(['Order', 'Text', 'Start (ms)', 'End (ms)'], $rows);

        $cueRows = [];

        foreach ($cues as $cue) {
            $cueRows[] = [$cue->order, $cue->text, $cue->startMs, $cue->endMs];
        }

        $this->newLine();
        $this->info('Generated caption cues:');
        $this->table(['Order', 'Text', 'Start (ms)', 'End (ms)'], $cueRows);

        return self::SUCCESS;
    }
}
