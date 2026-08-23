<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVideoProjectCaptionStyleRequest;
use App\Models\VideoProject;
use Illuminate\Http\RedirectResponse;

class UpdateVideoProjectCaptionStyleController extends Controller
{
    public function __invoke(
        UpdateVideoProjectCaptionStyleRequest $request,
        VideoProject $videoProject,
    ): RedirectResponse {
        $videoProject->update([
            'caption_style' => $request->validated(),
        ]);

        return to_route('video-projects.show', $videoProject);
    }
}
