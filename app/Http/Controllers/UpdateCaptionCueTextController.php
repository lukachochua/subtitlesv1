<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCaptionCueTextRequest;
use App\Models\CaptionCue;
use App\Models\VideoProject;
use Illuminate\Http\RedirectResponse;

class UpdateCaptionCueTextController extends Controller
{
    public function __invoke(
        UpdateCaptionCueTextRequest $request,
        VideoProject $videoProject,
        CaptionCue $captionCue,
    ): RedirectResponse {
        $captionCue->update($request->validated());

        return to_route('video-projects.show', $videoProject);
    }
}
