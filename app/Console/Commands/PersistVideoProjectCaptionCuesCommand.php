<?php

namespace App\Console\Commands;

use App\Actions\LoadVideoProjectCaptionData;
use App\Actions\PersistGeneratedCaptionCues;
use App\Models\VideoProject;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use UnexpectedValueException;

#[Signature('video-projects:persist-caption-cues {videoProject : The video project ID}')]
#[Description('Generate and persist caption cues for one video project')]
class PersistVideoProjectCaptionCuesCommand extends Command
{
    public function handle(
        LoadVideoProjectCaptionData $loadVideoProjectCaptionData,
        PersistGeneratedCaptionCues $persistGeneratedCaptionCues,
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
            $savedCues = $persistGeneratedCaptionCues->handle(
                $videoProject,
                $captionData['cues'],
            );
        } catch (InvalidArgumentException|LogicException|UnexpectedValueException $exception) {
            $this->error("Could not persist video project {$videoProjectId} caption cues: {$exception->getMessage()}");

            return self::FAILURE;
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Video project {$videoProjectId} saved {$savedCues->count()} caption cues.");

        return self::SUCCESS;
    }
}
