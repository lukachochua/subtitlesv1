<?php

use App\Models\VideoProject;
use Illuminate\Support\Facades\Storage;

test('downloads a completed private captioned MP4', function () {
    Storage::fake('local');
    $videoProject = createVideoProjectForCaptionedDownload();
    Storage::disk('local')->put(
        "video-projects/{$videoProject->id}/captioned.mp4",
        'captioned-video',
    );

    $this->get(route('video-projects.export.show', $videoProject))
        ->assertOk()
        ->assertDownload("captioned-video-project-{$videoProject->id}.mp4")
        ->assertHeader('content-type', 'video/mp4')
        ->assertHeader('cache-control', 'no-store, private');
});

test('returns not found when the completed export is missing', function () {
    Storage::fake('local');
    $videoProject = createVideoProjectForCaptionedDownload();

    $this->get(route('video-projects.export.show', $videoProject))
        ->assertNotFound();
});

test('returns not found when downloading from a missing project', function () {
    Storage::fake('local');

    $this->get(route('video-projects.export.show', 999))->assertNotFound();
});

function createVideoProjectForCaptionedDownload(): VideoProject
{
    return VideoProject::create([
        'original_filename' => 'source.mp4',
        'disk' => 'local',
        'path' => 'video-projects/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 12,
    ]);
}
