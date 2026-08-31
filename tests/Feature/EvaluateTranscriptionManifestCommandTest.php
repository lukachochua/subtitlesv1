<?php

test('evaluates a manifest and serializes reproducible aggregate results', function () {
    $directory = sys_get_temp_dir().'/transcription-command-'.uniqid();
    mkdir($directory);
    file_put_contents($directory.'/audio.wav', 'test audio placeholder');
    file_put_contents($directory.'/nemo.json', json_encode([
        'text' => 'საქართველო იმარჯვებს დღეს',
        'timestamp' => ['word' => []],
    ], JSON_THROW_ON_ERROR));
    file_put_contents($directory.'/manifest.json', json_encode([
        'dataset_version' => 'georgian-v1',
        'clips' => [[
            'id' => 'clean-01', 'audio' => 'audio.wav', 'reference' => 'საქართველო დღეს იმარჯვებს',
            'category' => 'clean-microphone', 'notes' => '', 'nemo_result' => 'nemo.json',
            'audio_duration_seconds' => 30, 'manual_correction_seconds' => 15,
        ]],
    ], JSON_THROW_ON_ERROR));
    $outputPath = $directory.'/result.json';

    $this->artisan('transcription:evaluate', [
        'manifest' => $directory.'/manifest.json',
        '--output' => $outputPath,
    ])->assertSuccessful();

    $result = json_decode(file_get_contents($outputPath), true, flags: JSON_THROW_ON_ERROR);

    expect($result['dataset_version'])->toBe('georgian-v1')
        ->and($result['clips'][0]['metrics']['substitutions'])->toBe(2)
        ->and($result['clips'][0]['correction_seconds_per_audio_minute'])->toBe(30)
        ->and($result['summary']['clips'])->toBe(1)
        ->and($result['summary']['wer'])->toBe($result['clips'][0]['metrics']['wer']);
});
