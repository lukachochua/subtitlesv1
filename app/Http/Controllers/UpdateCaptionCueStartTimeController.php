<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCaptionCueStartTimeRequest;
use App\Models\CaptionCue;
use App\Models\VideoProject;
use Illuminate\Http\RedirectResponse;

class UpdateCaptionCueStartTimeController extends Controller
{
    public function __invoke(
        UpdateCaptionCueStartTimeRequest $request,
        VideoProject $videoProject,
        CaptionCue $captionCue,
    ): RedirectResponse {
        $captionCue->update($request->validated());

        return to_route('video-projects.show', $videoProject);
    }
}
