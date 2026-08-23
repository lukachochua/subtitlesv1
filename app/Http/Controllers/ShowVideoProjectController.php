<?php

namespace App\Http\Controllers;

use App\Models\VideoProject;
use Inertia\Inertia;
use Inertia\Response;

class ShowVideoProjectController extends Controller
{
    public function __invoke(VideoProject $videoProject): Response
    {
        return Inertia::render('VideoProjects/Show', [
            'videoProject' => $videoProject->only([
                'id',
                'original_filename',
                'mime_type',
                'size_bytes',
                'duration_ms',
            ]),
        ]);
    }
}
