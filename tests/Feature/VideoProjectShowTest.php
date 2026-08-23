<?php

use App\Models\VideoProject;
use Inertia\Testing\AssertableInertia as Assert;

test('displays safe uploaded video metadata', function () {
    $videoProject = VideoProject::create([
        'original_filename' => 'ქართული-ინტერვიუ.mp4',
        'disk' => 'local',
        'path' => 'video-projects/private-source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 48_392_017,
    ]);

    $this->get(route('video-projects.show', $videoProject))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('VideoProjects/Show')
            ->where('videoProject', [
                'id' => $videoProject->id,
                'original_filename' => 'ქართული-ინტერვიუ.mp4',
                'mime_type' => 'video/mp4',
                'size_bytes' => 48_392_017,
            ])
            ->missing('videoProject.disk')
            ->missing('videoProject.path'));
});

test('returns not found for a missing video project', function () {
    $this->get(route('video-projects.show', 999))->assertNotFound();
});
