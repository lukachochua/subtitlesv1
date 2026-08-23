<?php

namespace App\Http\Controllers;

use App\Actions\RenderVideoProjectCaptionedVideo;
use App\Models\VideoProject;
use Illuminate\Http\RedirectResponse;
use Throwable;

class RenderVideoProjectCaptionedVideoController extends Controller
{
    public function __invoke(
        VideoProject $videoProject,
        RenderVideoProjectCaptionedVideo $renderVideoProjectCaptionedVideo,
    ): RedirectResponse {
        try {
            $renderVideoProjectCaptionedVideo->handle($videoProject);
        } catch (Throwable $exception) {
            report($exception);

            return to_route('video-projects.show', $videoProject)
                ->withErrors([
                    'render' => 'The captioned video could not be exported. Check the media files and try again.',
                ]);
        }

        return to_route('video-projects.show', $videoProject);
    }
}
