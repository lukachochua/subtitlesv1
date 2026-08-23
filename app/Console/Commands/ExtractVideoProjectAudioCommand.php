<?php

namespace App\Console\Commands;

use App\Actions\ExtractVideoProjectAudio;
use App\Models\VideoProject;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('video-projects:extract-audio {videoProject : The video project ID}')]
#[Description('Extract ASR-ready audio from one video project')]
class ExtractVideoProjectAudioCommand extends Command
{
    public function handle(ExtractVideoProjectAudio $extractVideoProjectAudio): int
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

        $audioPath = $extractVideoProjectAudio->handle($videoProject);

        $this->info("Video project {$videoProject->id} audio extracted to {$audioPath}.");

        return self::SUCCESS;
    }
}
