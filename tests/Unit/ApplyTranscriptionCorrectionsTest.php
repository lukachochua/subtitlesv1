<?php

use App\Actions\ApplyTranscriptionCorrections;
use App\ValueObjects\TranscriptionWord;

test('applies valid one-token corrections while preserving raw words and timestamps', function () {
    $rawWords = [
        new TranscriptionWord('ჩვენი', 100, 300),
        new TranscriptionWord('ნაკრები', 320, 620),
    ];
    $rawSnapshot = $rawWords;
    $result = (new ApplyTranscriptionCorrections)->handle($rawWords, [
        'corrections' => [[
            'word_index' => 1, 'original' => 'ნაკრები', 'replacement' => 'ნაკრების',
            'category' => 'morphology', 'confidence' => 0.97,
        ]],
    ]);

    expect($result->rawWords)->toEqual($rawSnapshot)
        ->and($result->rawWords[1]->text)->toBe('ნაკრები')
        ->and($result->correctedWords)->toHaveCount(2)
        ->and($result->correctedWords[1]->text)->toBe('ნაკრების')
        ->and($result->correctedWords[1]->startMs)->toBe(320)
        ->and($result->correctedWords[1]->endMs)->toBe(620);
});

test('rejects invalid correction operations', function (array $correction, string $message) {
    $words = [new TranscriptionWord('ნაკრები', 100, 300)];

    expect(fn () => (new ApplyTranscriptionCorrections)->handle($words, ['corrections' => [$correction]]))
        ->toThrow(UnexpectedValueException::class, $message);
})->with([
    'invalid index' => [[
        'word_index' => 4, 'original' => 'ნაკრები', 'replacement' => 'ნაკრების', 'category' => 'morphology', 'confidence' => 0.9,
    ], 'invalid word index'],
    'original mismatch' => [[
        'word_index' => 0, 'original' => 'სხვა', 'replacement' => 'ნაკრების', 'category' => 'morphology', 'confidence' => 0.9,
    ], 'does not match the original word'],
    'empty replacement' => [[
        'word_index' => 0, 'original' => 'ნაკრები', 'replacement' => '', 'category' => 'morphology', 'confidence' => 0.9,
    ], 'empty replacement'],
    'multi-word replacement' => [[
        'word_index' => 0, 'original' => 'ნაკრები', 'replacement' => 'ჩვენი ნაკრების', 'category' => 'morphology', 'confidence' => 0.9,
    ], 'replace exactly one token'],
    'missing required category' => [[
        'word_index' => 0, 'original' => 'ნაკრები', 'replacement' => 'ნაკრების', 'confidence' => 0.9,
    ], 'requires a category'],
]);

test('rejects a malformed structured response', function () {
    $words = [new TranscriptionWord('ნაკრები', 100, 300)];

    expect(fn () => (new ApplyTranscriptionCorrections)->handle($words, ['message' => 'free-form answer']))
        ->toThrow(UnexpectedValueException::class, 'LLM response requires a corrections list.');
});
