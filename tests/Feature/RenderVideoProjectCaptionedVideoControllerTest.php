<?php

use App\Actions\RenderVideoProjectCaptionedVideo;
use App\Models\VideoProject;

use function Pest\Laravel\mock;

test('renders a project and redirects back to its editor', function () {
    $videoProject = createVideoProjectForBrowserRender();

    mock(RenderVideoProjectCaptionedVideo::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (VideoProject $project): bool => $project->is($videoProject))
        ->andReturn("video-projects/{$videoProject->id}/captioned.mp4");

    $this->post(route('video-projects.render.store', $videoProject))
        ->assertRedirectToRoute('video-projects.show', $videoProject)
        ->assertSessionHasNoErrors();
});

test('returns a useful form error when browser rendering fails', function () {
    $videoProject = createVideoProjectForBrowserRender();

    mock(RenderVideoProjectCaptionedVideo::class)
        ->shouldReceive('handle')
        ->once()
        ->andThrow(new RuntimeException('FFmpeg failed.'));

    $this->post(route('video-projects.render.store', $videoProject))
        ->assertRedirectToRoute('video-projects.show', $videoProject)
        ->assertSessionHasErrors([
            'render' => 'The captioned video could not be exported. Check the media files and try again.',
        ]);
});

test('returns not found when rendering a missing project', function () {
    mock(RenderVideoProjectCaptionedVideo::class)->shouldNotReceive('handle');

    $this->post(route('video-projects.render.store', 999))->assertNotFound();
});

function createVideoProjectForBrowserRender(): VideoProject
{
    return VideoProject::create([
        'original_filename' => 'source.mp4',
        'disk' => 'local',
        'path' => 'video-projects/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 12,
    ]);
}
