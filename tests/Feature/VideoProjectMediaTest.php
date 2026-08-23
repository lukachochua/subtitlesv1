<?php

use App\Models\VideoProject;
use Illuminate\Support\Facades\Storage;

test('serves private project video inline', function () {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', 'video-content');

    $videoProject = VideoProject::create([
        'original_filename' => 'ქართული-ინტერვიუ.mp4',
        'disk' => 'local',
        'path' => 'video-projects/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 13,
    ]);

    $this->get(route('video-projects.media', $videoProject))
        ->assertOk()
        ->assertHeader('content-type', 'video/mp4')
        ->assertHeader('cache-control', 'no-store, private');
});

test('supports byte range requests for video seeking', function () {
    Storage::fake('local');
    Storage::disk('local')->put('video-projects/source.mp4', '0123456789');

    $videoProject = VideoProject::create([
        'original_filename' => 'interview.mp4',
        'disk' => 'local',
        'path' => 'video-projects/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 10,
    ]);

    $this->withHeader('Range', 'bytes=0-3')
        ->get(route('video-projects.media', $videoProject))
        ->assertStatus(206)
        ->assertHeader('content-range', 'bytes 0-3/10');
});

test('returns not found when the stored video is missing', function () {
    Storage::fake('local');

    $videoProject = VideoProject::create([
        'original_filename' => 'missing.mp4',
        'disk' => 'local',
        'path' => 'video-projects/missing.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 100,
    ]);

    $this->get(route('video-projects.media', $videoProject))->assertNotFound();
});
