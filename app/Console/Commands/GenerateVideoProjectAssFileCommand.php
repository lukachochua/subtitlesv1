<?php

namespace App\Console\Commands;

use App\Actions\GenerateVideoProjectAssFile;
use App\Models\VideoProject;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('video-projects:generate-ass {videoProject : The video project ID}')]
#[Description('Generate an ASS subtitle file from one video project')]
class GenerateVideoProjectAssFileCommand extends Command
{
    public function handle(GenerateVideoProjectAssFile $generateVideoProjectAssFile): int
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

        $assPath = $generateVideoProjectAssFile->handle($videoProject);

        $this->info("Video project {$videoProject->id} ASS subtitles generated at {$assPath}.");

        return self::SUCCESS;
    }
}
