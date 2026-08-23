<?php

namespace App\Http\Controllers;

use App\Actions\MergeCaptionCueWithNext;
use App\Http\Requests\MergeCaptionCueWithNextRequest;
use App\Models\CaptionCue;
use App\Models\VideoProject;
use Illuminate\Http\RedirectResponse;

class MergeCaptionCueWithNextController extends Controller
{
    public function __invoke(
        MergeCaptionCueWithNextRequest $request,
        VideoProject $videoProject,
        CaptionCue $captionCue,
        MergeCaptionCueWithNext $mergeCaptionCueWithNext,
    ): RedirectResponse {
        $mergeCaptionCueWithNext->handle($captionCue);

        return to_route('video-projects.show', $videoProject);
    }
}
