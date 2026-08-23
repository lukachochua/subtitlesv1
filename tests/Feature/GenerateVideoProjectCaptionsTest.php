<?php

use App\Actions\ExtractVideoProjectAudio;
use App\Actions\GenerateVideoProjectCaptions;
use App\Actions\InspectVideoProject;
use App\Actions\LoadVideoProjectCaptionData;
use App\Actions\TranscribeVideoProjectAudioWithNemo;
use App\Enums\TranscriptionStatus;
use App\Models\VideoProject;
use App\ValueObjects\CaptionCue;

use function Pest\Laravel\mock;

test('runs the pipeline and persists editable cues', function () {
    $project = captionGenerationProject();
    mock(InspectVideoProject::class)->shouldReceive('handle')->once()->andReturnUsing(function (VideoProject $project): VideoProject {
        $project->update(['duration_ms' => 1_000]);

        return $project;
    });
    mock(ExtractVideoProjectAudio::class)->shouldReceive('handle')->once()->andReturn('audio.wav');
    mock(TranscribeVideoProjectAudioWithNemo::class)->shouldReceive('handle')->once()->andReturn('result.json');
    mock(LoadVideoProjectCaptionData::class)->shouldReceive('handle')->once()->andReturn(['words' => [], 'cues' => [new CaptionCue(1, 'გამარჯობა.', 100, 900)]]);

    $saved = app(GenerateVideoProjectCaptions::class)->handle($project);
    $project->refresh();
    expect($saved)->toHaveCount(1)
        ->and($project->captionCues()->value('text'))->toBe('გამარჯობა.')
        ->and($project->transcription_status)->toBe(TranscriptionStatus::Completed)
        ->and($project->transcribed_at)->not->toBeNull();
});

test('records safe failure state', function () {
    $project = captionGenerationProject(['duration_ms' => 1_000]);
    mock(InspectVideoProject::class)->shouldNotReceive('handle');
    mock(ExtractVideoProjectAudio::class)->shouldReceive('handle')->once()->andThrow(new RuntimeException('raw detail'));

    expect(fn () => app(GenerateVideoProjectCaptions::class)->handle($project))->toThrow(RuntimeException::class);
    $project->refresh();
    expect($project->transcription_status)->toBe(TranscriptionStatus::Failed)
        ->and($project->transcription_error)->toBe(GenerateVideoProjectCaptions::FAILURE_MESSAGE);
});

test('refuses to overwrite edited cues', function () {
    $project = captionGenerationProject(['duration_ms' => 1_000]);
    $project->captionCues()->create(['order' => 1, 'text' => 'შესწორებული', 'start_ms' => 0, 'end_ms' => 900]);
    mock(ExtractVideoProjectAudio::class)->shouldNotReceive('handle');

    expect(fn () => app(GenerateVideoProjectCaptions::class)->handle($project))
        ->toThrow(LogicException::class, 'Saved captions already exist and will not be overwritten.');
});

/** @param array<string, mixed> $attributes */
function captionGenerationProject(array $attributes = []): VideoProject
{
    return VideoProject::create(['original_filename' => 'source.mp4', 'disk' => 'local', 'path' => 'video-projects/source.mp4', 'mime_type' => 'video/mp4', 'size_bytes' => 12, ...$attributes]);
}
