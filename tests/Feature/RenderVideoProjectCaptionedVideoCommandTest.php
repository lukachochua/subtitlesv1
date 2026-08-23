<?php

use App\Actions\RenderVideoProjectCaptionedVideo;
use App\Models\VideoProject;

use function Pest\Laravel\mock;

test('renders a video project by ID', function () {
    $videoProject = VideoProject::create([
        'original_filename' => 'source.mp4',
        'disk' => 'local',
        'path' => 'video-projects/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 12,
    ]);

    mock(RenderVideoProjectCaptionedVideo::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (VideoProject $project): bool => $project->is($videoProject))
        ->andReturn("video-projects/{$videoProject->id}/captioned.mp4");

    $this->artisan('video-projects:render', ['videoProject' => $videoProject->id])
        ->expectsOutput("Video project {$videoProject->id} rendered to video-projects/{$videoProject->id}/captioned.mp4.")
        ->assertSuccessful();
});

test('rejects an invalid video project ID for rendering', function (string $videoProjectId) {
    mock(RenderVideoProjectCaptionedVideo::class)->shouldNotReceive('handle');

    $this->artisan('video-projects:render', ['videoProject' => $videoProjectId])
        ->expectsOutput('The video project ID must be a positive integer.')
        ->assertFailed();
})->with(['zero' => '0', 'negative' => '-1', 'text' => 'abc']);

test('reports a missing video project for rendering', function () {
    mock(RenderVideoProjectCaptionedVideo::class)->shouldNotReceive('handle');

    $this->artisan('video-projects:render', ['videoProject' => 999])
        ->expectsOutput('Video project 999 was not found.')
        ->assertFailed();
});
