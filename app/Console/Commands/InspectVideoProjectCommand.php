<?php

namespace App\Console\Commands;

use App\Actions\InspectVideoProject;
use App\Models\VideoProject;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('video-projects:inspect {videoProject : The video project ID}')]
#[Description('Inspect one video project and persist its duration')]
class InspectVideoProjectCommand extends Command
{
    public function handle(InspectVideoProject $inspectVideoProject): int
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

        $inspectedVideoProject = $inspectVideoProject->handle($videoProject);

        $this->info(
            "Video project {$inspectedVideoProject->id} inspected: {$inspectedVideoProject->duration_ms} ms.",
        );

        return self::SUCCESS;
    }
}
