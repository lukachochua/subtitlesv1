<?php

namespace App\Console\Commands;

use App\Actions\RenderVideoProjectCaptionedVideo;
use App\Models\VideoProject;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('video-projects:render {videoProject : The video project ID}')]
#[Description('Render one video project with its saved captions')]
class RenderVideoProjectCaptionedVideoCommand extends Command
{
    public function handle(RenderVideoProjectCaptionedVideo $renderVideoProjectCaptionedVideo): int
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

        $outputPath = $renderVideoProjectCaptionedVideo->handle($videoProject);

        $this->info("Video project {$videoProject->id} rendered to {$outputPath}.");

        return self::SUCCESS;
    }
}
