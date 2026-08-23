<?php

namespace App\Http\Controllers;

use App\Actions\GenerateVideoProjectCaptions;
use App\Models\VideoProject;
use Illuminate\Http\RedirectResponse;
use Throwable;

class GenerateVideoProjectCaptionsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        VideoProject $videoProject,
        GenerateVideoProjectCaptions $generateVideoProjectCaptions,
    ): RedirectResponse {
        try {
            $generateVideoProjectCaptions->handle($videoProject);
        } catch (Throwable $exception) {
            report($exception);

            return to_route('video-projects.show', $videoProject)->withErrors([
                'transcription' => GenerateVideoProjectCaptions::FAILURE_MESSAGE,
            ]);
        }

        return to_route('video-projects.show', $videoProject);
    }
}
