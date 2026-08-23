<?php

use App\ValueObjects\TranscriptionWord;

test('it represents one immutable timestamped word', function () {
    $word = new TranscriptionWord(
        text: 'გამარჯობა,',
        startMs: 8240,
        endMs: 8880,
    );

    expect($word)
        ->text->toBe('გამარჯობა,')
        ->startMs->toBe(8240)
        ->endMs->toBe(8880);
});

test('it rejects empty text', function (string $text) {
    expect(fn () => new TranscriptionWord($text, 0, 80))
        ->toThrow(InvalidArgumentException::class, 'Transcription word text must not be empty.');
})->with([
    'empty' => '',
    'spaces' => '   ',
    'whitespace' => "\t\n",
]);

test('it rejects a negative start', function () {
    expect(fn () => new TranscriptionWord('ერთი', -1, 80))
        ->toThrow(InvalidArgumentException::class, 'Transcription word start must not be negative.');
});

test('it rejects an end that is not after the start', function (int $endMs) {
    expect(fn () => new TranscriptionWord('ერთი', 80, $endMs))
        ->toThrow(InvalidArgumentException::class, 'Transcription word end must be after its start.');
})->with([
    'equal to start' => 80,
    'before start' => 79,
]);

test('its properties cannot be changed after construction', function () {
    $word = new TranscriptionWord('ერთი', 1680, 1760);

    expect(function () use ($word): void {
        $word->text = 'ორი';
    })->toThrow(Error::class);
});
