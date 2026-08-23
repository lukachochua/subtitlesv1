<?php

namespace App\Http\Controllers;

use App\Actions\SplitCaptionCue;
use App\Http\Requests\SplitCaptionCueRequest;
use App\Models\CaptionCue;
use App\Models\VideoProject;
use Illuminate\Http\RedirectResponse;

class SplitCaptionCueController extends Controller
{
    public function __invoke(
        SplitCaptionCueRequest $request,
        VideoProject $videoProject,
        CaptionCue $captionCue,
        SplitCaptionCue $splitCaptionCue,
    ): RedirectResponse {
        $splitCaptionCue->handle($captionCue, $request->integer('split_ms'));

        return to_route('video-projects.show', $videoProject);
    }
}
