<?php

use App\ValueObjects\CaptionCue;

test('it represents one immutable ordered caption cue', function () {
    $cue = new CaptionCue(
        order: 1,
        text: 'გამარჯობა, როგორ ხარ?',
        startMs: 8240,
        endMs: 10_240,
    );

    expect($cue)
        ->order->toBe(1)
        ->text->toBe('გამარჯობა, როგორ ხარ?')
        ->startMs->toBe(8240)
        ->endMs->toBe(10_240);
});

test('it rejects a non-positive order', function (int $order) {
    expect(fn () => new CaptionCue($order, 'ერთი', 0, 80))
        ->toThrow(InvalidArgumentException::class, 'Caption cue order must be a positive integer.');
})->with([
    'zero' => 0,
    'negative' => -1,
]);

test('it rejects empty text', function (string $text) {
    expect(fn () => new CaptionCue(1, $text, 0, 80))
        ->toThrow(InvalidArgumentException::class, 'Caption cue text must not be empty.');
})->with([
    'empty' => '',
    'spaces' => '   ',
    'whitespace' => "\t\n",
]);

test('it rejects a negative start', function () {
    expect(fn () => new CaptionCue(1, 'ერთი', -1, 80))
        ->toThrow(InvalidArgumentException::class, 'Caption cue start must not be negative.');
});

test('it rejects an end that is not after the start', function (int $endMs) {
    expect(fn () => new CaptionCue(1, 'ერთი', 80, $endMs))
        ->toThrow(InvalidArgumentException::class, 'Caption cue end must be after its start.');
})->with([
    'equal to start' => 80,
    'before start' => 79,
]);

test('its properties cannot be changed after construction', function () {
    $cue = new CaptionCue(1, 'ერთი ორი', 1680, 2720);

    expect(function () use ($cue): void {
        $cue->text = 'სამი';
    })->toThrow(Error::class);
});
