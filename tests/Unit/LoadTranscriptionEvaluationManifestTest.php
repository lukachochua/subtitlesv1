<?php

use App\Actions\LoadTranscriptionEvaluationManifest;

test('parses a versioned manifest and resolves relative experiment paths', function () {
    $directory = sys_get_temp_dir().'/transcription-manifest-'.uniqid();
    mkdir($directory);
    $manifestPath = $directory.'/manifest.json';
    file_put_contents($manifestPath, json_encode([
        'dataset_version' => 'georgian-v1',
        'clips' => [[
            'id' => 'clean-mic-01', 'audio' => 'media/clean.wav', 'reference' => 'გამარჯობა საქართველო',
            'category' => 'clean-microphone', 'notes' => 'Verified by speaker.',
            'nemo_result' => 'results/clean.nemo.json', 'audio_duration_seconds' => 30,
            'manual_correction_seconds' => 12,
        ]],
    ], JSON_THROW_ON_ERROR));

    $manifest = (new LoadTranscriptionEvaluationManifest)->handle($manifestPath);

    expect($manifest['dataset_version'])->toBe('georgian-v1')
        ->and($manifest['clips'][0]['audio'])->toBe($directory.'/media/clean.wav')
        ->and($manifest['clips'][0]['nemo_result'])->toBe($directory.'/results/clean.nemo.json')
        ->and($manifest['clips'][0]['manual_correction_seconds'])->toBe(12.0);
});

test('rejects duplicate clip IDs', function () {
    $manifestPath = tempnam(sys_get_temp_dir(), 'manifest-');
    file_put_contents($manifestPath, json_encode([
        'dataset_version' => 'v1',
        'clips' => array_fill(0, 2, [
            'id' => 'same-id', 'audio' => '/tmp/audio.wav', 'reference' => 'ტექსტი',
            'category' => 'clean', 'nemo_result' => '/tmp/result.json',
        ]),
    ], JSON_THROW_ON_ERROR));

    expect(fn () => (new LoadTranscriptionEvaluationManifest)->handle($manifestPath))
        ->toThrow(UnexpectedValueException::class, 'Evaluation clip ID must be unique: same-id');
});
