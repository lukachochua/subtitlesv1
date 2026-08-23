<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCaptionCueEndTimeRequest;
use App\Models\CaptionCue;
use App\Models\VideoProject;
use Illuminate\Http\RedirectResponse;

class UpdateCaptionCueEndTimeController extends Controller
{
    public function __invoke(
        UpdateCaptionCueEndTimeRequest $request,
        VideoProject $videoProject,
        CaptionCue $captionCue,
    ): RedirectResponse {
        $captionCue->update($request->validated());

        return to_route('video-projects.show', $videoProject);
    }
}
