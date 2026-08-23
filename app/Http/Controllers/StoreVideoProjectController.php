<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoProjectRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;

class StoreVideoProjectController extends Controller
{
    public function __invoke(StoreVideoProjectRequest $request): RedirectResponse
    {
        /** @var UploadedFile $video */
        $video = $request->file('video');

        $path = $video->store('video-projects', 'local');

        abort_if($path === false, 500, 'Unable to store the uploaded video.');

        return to_route('home');
    }
}
