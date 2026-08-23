<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoProjectRequest;
use App\Models\VideoProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StoreVideoProjectController extends Controller
{
    public function __invoke(StoreVideoProjectRequest $request): RedirectResponse
    {
        /** @var UploadedFile $video */
        $video = $request->file('video');

        $path = $video->store('video-projects', 'local');

        abort_if($path === false, 500, 'Unable to store the uploaded video.');

        try {
            $videoProject = VideoProject::create([
                'original_filename' => $video->getClientOriginalName(),
                'disk' => 'local',
                'path' => $path,
                'mime_type' => $video->getMimeType(),
                'size_bytes' => $video->getSize(),
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        return to_route('video-projects.show', $videoProject);
    }
}
