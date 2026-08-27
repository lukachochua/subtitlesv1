<?php

namespace App\Http\Controllers;

use App\Actions\UpdateCaptionCueText;
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
        UpdateCaptionCueText $updateCaptionCueText,
    ): RedirectResponse {
        $updateCaptionCueText->handle($captionCue, $request->string('text')->toString());

        return to_route('video-projects.show', $videoProject);
    }
}
