<?php

use App\Actions\GenerateVideoProjectCaptions;
use App\Models\VideoProject;
use Illuminate\Database\Eloquent\Collection;

use function Pest\Laravel\mock;

test('generates captions and redirects to the editor', function () {
    $project = browserGenerationProject();
    mock(GenerateVideoProjectCaptions::class)->shouldReceive('handle')->once()->withArgs(fn (VideoProject $value): bool => $value->is($project))->andReturn(new Collection);

    $this->post(route('video-projects.captions.generate.store', $project))
        ->assertRedirectToRoute('video-projects.show', $project)
        ->assertSessionHasNoErrors();
});

test('returns a safe browser error when generation fails', function () {
    $project = browserGenerationProject();
    mock(GenerateVideoProjectCaptions::class)->shouldReceive('handle')->once()->andThrow(new RuntimeException('private detail'));

    $this->post(route('video-projects.captions.generate.store', $project))
        ->assertSessionHasErrors(['transcription' => GenerateVideoProjectCaptions::FAILURE_MESSAGE]);
});

function browserGenerationProject(): VideoProject
{
    return VideoProject::create(['original_filename' => 'source.mp4', 'disk' => 'local', 'path' => 'video-projects/source.mp4', 'mime_type' => 'video/mp4', 'size_bytes' => 12]);
}
