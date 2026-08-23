<?php

use App\Actions\TranscribeVideoProjectAudioWithNemo;
use App\Models\VideoProject;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

test('runs configured NeMo Python and finalizes valid timestamp output', function () {
    Storage::fake('local');
    Process::preventStrayProcesses();
    config(['services.nemo_asr.python' => '/usr/bin/php']);
    $project = nemoProject();
    $audio = "video-projects/{$project->id}/audio.wav";
    $pending = "video-projects/{$project->id}/transcription.nemo-fastconformer.processing.json";
    $completed = "video-projects/{$project->id}/transcription.nemo-fastconformer.raw.json";
    Storage::disk('local')->put($audio, 'wav');
    Process::fake(function () use ($pending) {
        Storage::disk('local')->put($pending, json_encode(['timestamp' => ['word' => [['word' => 'გამარჯობა', 'start' => 0.1, 'end' => 0.8]]]], JSON_THROW_ON_ERROR));

        return Process::result();
    });

    expect(app(TranscribeVideoProjectAudioWithNemo::class)->handle($project, $audio))->toBe($completed);
    Storage::disk('local')->assertExists($completed);
    Storage::disk('local')->assertMissing($pending);
    Process::assertRan(fn (PendingProcess $process, ProcessResult $result): bool => $process->timeout === 3_600 && $process->command === [
        '/usr/bin/php', base_path('transcribe_nemo.py'), Storage::disk('local')->path($audio), Storage::disk('local')->path($pending),
    ]);
});

test('fails before processing when configured Python is unavailable', function () {
    Storage::fake('local');
    Process::preventStrayProcesses();
    Process::fake();
    config(['services.nemo_asr.python' => '/missing/python']);

    expect(fn () => app(TranscribeVideoProjectAudioWithNemo::class)->handle(nemoProject(), 'audio.wav'))
        ->toThrow(RuntimeException::class, 'The configured NeMo Python executable is unavailable.');
    Process::assertNothingRan();
});

test('rejects unusable NeMo timestamps and removes partial output', function () {
    Storage::fake('local');
    Process::preventStrayProcesses();
    config(['services.nemo_asr.python' => '/usr/bin/php']);
    $project = nemoProject();
    $audio = "video-projects/{$project->id}/audio.wav";
    $pending = "video-projects/{$project->id}/transcription.nemo-fastconformer.processing.json";
    Storage::disk('local')->put($audio, 'wav');
    Process::fake(function () use ($pending) {
        Storage::disk('local')->put($pending, '{"timestamp":{"word":[]}}');

        return Process::result();
    });

    expect(fn () => app(TranscribeVideoProjectAudioWithNemo::class)->handle($project, $audio))
        ->toThrow(UnexpectedValueException::class, 'NeMo did not return usable word timestamps.');
    Storage::disk('local')->assertMissing($pending);
});

function nemoProject(): VideoProject
{
    return VideoProject::create(['original_filename' => 'source.mp4', 'disk' => 'local', 'path' => 'video-projects/source.mp4', 'mime_type' => 'video/mp4', 'size_bytes' => 12, 'duration_ms' => 2_000]);
}
