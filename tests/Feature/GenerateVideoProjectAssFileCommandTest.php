<?php

use App\Actions\GenerateVideoProjectAssFile;
use App\Models\VideoProject;

use function Pest\Laravel\mock;

test('generates ASS subtitles for a video project by ID', function () {
    $videoProject = VideoProject::create([
        'original_filename' => 'source.mp4',
        'disk' => 'local',
        'path' => 'video-projects/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 13,
    ]);

    mock(GenerateVideoProjectAssFile::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (VideoProject $project): bool => $project->is($videoProject))
        ->andReturn("video-projects/{$videoProject->id}/captions.ass");

    $this->artisan('video-projects:generate-ass', ['videoProject' => $videoProject->id])
        ->expectsOutput("Video project {$videoProject->id} ASS subtitles generated at video-projects/{$videoProject->id}/captions.ass.")
        ->assertSuccessful();
});

test('rejects an invalid video project ID', function (string $videoProjectId) {
    mock(GenerateVideoProjectAssFile::class)->shouldNotReceive('handle');

    $this->artisan('video-projects:generate-ass', ['videoProject' => $videoProjectId])
        ->expectsOutput('The video project ID must be a positive integer.')
        ->assertFailed();
})->with(['zero' => '0', 'negative' => '-1', 'text' => 'abc']);

test('reports a missing video project', function () {
    mock(GenerateVideoProjectAssFile::class)->shouldNotReceive('handle');

    $this->artisan('video-projects:generate-ass', ['videoProject' => 999])
        ->expectsOutput('Video project 999 was not found.')
        ->assertFailed();
});
