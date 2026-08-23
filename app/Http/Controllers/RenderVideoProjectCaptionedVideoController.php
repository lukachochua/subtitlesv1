<?php

namespace App\Http\Controllers;

use App\Actions\RenderVideoProjectCaptionedVideo;
use App\Enums\VideoRenderQuality;
use App\Http\Requests\RenderVideoProjectCaptionedVideoRequest;
use App\Models\VideoProject;
use Illuminate\Http\RedirectResponse;
use Throwable;

class RenderVideoProjectCaptionedVideoController extends Controller
{
    public function __invoke(
        RenderVideoProjectCaptionedVideoRequest $request,
        VideoProject $videoProject,
        RenderVideoProjectCaptionedVideo $renderVideoProjectCaptionedVideo,
    ): RedirectResponse {
        try {
            $renderVideoProjectCaptionedVideo->handle(
                $videoProject,
                VideoRenderQuality::from($request->string('quality')->toString()),
            );
        } catch (Throwable $exception) {
            report($exception);

            return to_route('video-projects.show', $videoProject)
                ->withErrors([
                    'render' => RenderVideoProjectCaptionedVideo::FAILURE_MESSAGE,
                ]);
        }

        return to_route('video-projects.show', $videoProject);
    }
}
