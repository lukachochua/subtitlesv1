<?php

namespace App\Http\Controllers;

use App\Models\VideoProject;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ShowVideoProjectMediaController extends Controller
{
    public function __invoke(VideoProject $videoProject): BinaryFileResponse
    {
        $disk = Storage::disk($videoProject->disk);

        abort_unless($disk->exists($videoProject->path), 404);

        $response = response()->file($disk->path($videoProject->path), [
            'Content-Type' => $videoProject->mime_type,
        ]);

        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');

        return $response;
    }
}
