<?php

namespace App\Http\Controllers;

use App\Models\VideoProject;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadVideoProjectCaptionedVideoController extends Controller
{
    public function __invoke(VideoProject $videoProject): StreamedResponse
    {
        $disk = Storage::disk($videoProject->disk);
        $outputPath = "video-projects/{$videoProject->id}/captioned.mp4";

        abort_unless($disk->exists($outputPath), 404);

        $response = $disk->download(
            $outputPath,
            "captioned-video-project-{$videoProject->id}.mp4",
            ['Content-Type' => 'video/mp4'],
        );
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');

        return $response;
    }
}
