<?php

use App\Actions\ConvertNemoTranscriptionWords;
use App\ValueObjects\TranscriptionWord;

test('it converts NeMo words to rounded internal timestamps', function () {
    $words = (new ConvertNemoTranscriptionWords)->handle(nemoTranscription([
        ['word' => 'ერთი', 'start' => 1.6804, 'end' => 1.7596],
        ['word' => 'ორი,', 'start' => 2.32, 'end' => 2.72],
    ]), durationMs: 3000);

    expect($words)->toEqual([
        new TranscriptionWord('ერთი', 1680, 1760),
        new TranscriptionWord('ორი,', 2320, 2720),
    ]);
});

test('it clamps an overrun within one timestamp frame tolerance', function () {
    $words = (new ConvertNemoTranscriptionWords)->handle(nemoTranscription([
        ['word' => 'კარგა.', 'start' => 12.88, 'end' => 13.92],
    ]), durationMs: 13_886);

    expect($words[0])->toEqual(new TranscriptionWord('კარგა.', 12_880, 13_886));
});

test('it permits overlapping word alignments when boundaries remain ordered', function () {
    $words = (new ConvertNemoTranscriptionWords)->handle(nemoTranscription([
        ['word' => 'როგორ', 'start' => 1.0, 'end' => 1.5],
        ['word' => 'ხარ', 'start' => 1.4, 'end' => 1.8],
    ]), durationMs: 2000);

    expect($words)->toHaveCount(2)
        ->and($words[1])->toEqual(new TranscriptionWord('ხარ', 1400, 1800));
});

test('it requires a positive media duration', function (int $durationMs) {
    expect(fn () => (new ConvertNemoTranscriptionWords)->handle(
        nemoTranscription([['word' => 'ერთი', 'start' => 0.0, 'end' => 0.08]]),
        $durationMs,
    ))->toThrow(InvalidArgumentException::class, 'Transcription duration must be positive.');
})->with([
    'zero' => 0,
    'negative' => -1,
]);

test('it rejects a missing or malformed word list', function (array $transcription) {
    expect(fn () => (new ConvertNemoTranscriptionWords)->handle($transcription, 1000))
        ->toThrow(UnexpectedValueException::class, 'NeMo did not return a valid word timestamp list.');
})->with([
    'missing' => [[]],
    'empty' => [['timestamp' => ['word' => []]]],
    'not a list' => [['timestamp' => ['word' => ['first' => []]]]],
]);

test('it rejects malformed word entries', function (array $entry) {
    expect(fn () => (new ConvertNemoTranscriptionWords)->handle(
        nemoTranscription([$entry]),
        1000,
    ))->toThrow(UnexpectedValueException::class);
})->with([
    'missing text' => [['start' => 0.0, 'end' => 0.08]],
    'empty text' => [['word' => ' ', 'start' => 0.0, 'end' => 0.08]],
    'missing start' => [['word' => 'ერთი', 'end' => 0.08]],
    'numeric string start' => [['word' => 'ერთი', 'start' => '0.0', 'end' => 0.08]],
    'infinite start' => [['word' => 'ერთი', 'start' => INF, 'end' => 0.08]],
    'negative start' => [['word' => 'ერთი', 'start' => -0.08, 'end' => 0.08]],
    'reversed interval' => [['word' => 'ერთი', 'start' => 0.08, 'end' => 0.04]],
    'duration overrun above tolerance' => [['word' => 'ერთი', 'start' => 0.8, 'end' => 1.101]],
]);

test('it rejects decreasing timestamp sequences', function (array $entries, string $message) {
    expect(fn () => (new ConvertNemoTranscriptionWords)->handle(
        nemoTranscription($entries),
        2000,
    ))->toThrow(UnexpectedValueException::class, $message);
})->with([
    'decreasing starts' => [
        [
            ['word' => 'ერთი', 'start' => 1.0, 'end' => 1.2],
            ['word' => 'ორი', 'start' => 0.9, 'end' => 1.3],
        ],
        'NeMo word start times are not ordered.',
    ],
    'decreasing ends' => [
        [
            ['word' => 'ერთი', 'start' => 1.0, 'end' => 1.4],
            ['word' => 'ორი', 'start' => 1.1, 'end' => 1.3],
        ],
        'NeMo word end times are not ordered.',
    ],
]);

/**
 * @param  list<array<string, mixed>>  $words
 * @return array{timestamp: array{word: list<array<string, mixed>>}}
 */
function nemoTranscription(array $words): array
{
    return ['timestamp' => ['word' => $words]];
}
