<?php

namespace App\Http\Controllers;

use App\Actions\LoadVideoProjectCaptionData;
use App\Models\VideoProject;
use App\ValueObjects\CaptionCue;
use Inertia\Inertia;
use Inertia\Response;

class ShowVideoProjectController extends Controller
{
    public function __invoke(
        VideoProject $videoProject,
        LoadVideoProjectCaptionData $loadVideoProjectCaptionData,
    ): Response {
        $cues = null;

        if ($loadVideoProjectCaptionData->hasTranscriptionResult($videoProject)) {
            $captionData = $loadVideoProjectCaptionData->handle($videoProject);
            $cues = array_map(
                fn (CaptionCue $cue): array => [
                    'order' => $cue->order,
                    'text' => $cue->text,
                    'start_ms' => $cue->startMs,
                    'end_ms' => $cue->endMs,
                ],
                $captionData['cues'],
            );
        }

        return Inertia::render('VideoProjects/Show', [
            'videoProject' => $videoProject->only([
                'id',
                'original_filename',
                'mime_type',
                'size_bytes',
                'duration_ms',
            ]),
            'cues' => $cues,
        ]);
    }
}
