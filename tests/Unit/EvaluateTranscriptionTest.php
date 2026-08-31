<?php

use App\Actions\EvaluateTranscription;

test('calculates Georgian WER CER and word edit operations', function () {
    $evaluation = app(EvaluateTranscription::class)->handle(
        'საქართველომ იტალიას დღეს მოუგო.',
        'საქართველო ესპანეთს უცებ მოუგო დამატებით',
    );

    expect($evaluation->referenceWords)->toBe(4)
        ->and($evaluation->substitutions)->toBe(3)
        ->and($evaluation->insertions)->toBe(1)
        ->and($evaluation->deletions)->toBe(0)
        ->and($evaluation->wordErrors())->toBe(4)
        ->and($evaluation->wer())->toBe(1.0)
        ->and($evaluation->characterEdits)->toBeGreaterThan(0)
        ->and($evaluation->cer())->toBeGreaterThan(0.0)->toBeLessThan(1.0);
});

test('counts insertions and deletions independently', function (string $reference, string $transcript, int $insertions, int $deletions) {
    $evaluation = app(EvaluateTranscription::class)->handle($reference, $transcript);

    expect($evaluation->insertions)->toBe($insertions)
        ->and($evaluation->deletions)->toBe($deletions)
        ->and($evaluation->substitutions)->toBe(0);
})->with([
    'insertion' => ['ერთი ორი', 'ერთი ახალი ორი', 1, 0],
    'deletion' => ['ერთი ახალი ორი', 'ერთი ორი', 0, 1],
]);

test('ignores punctuation but preserves suffix differences', function () {
    $punctuationOnly = app(EvaluateTranscription::class)->handle('გამარჯობა, საქართველო!', 'გამარჯობა საქართველო');
    $suffixDifference = app(EvaluateTranscription::class)->handle('საქართველოს', 'საქართველო');

    expect($punctuationOnly->wer())->toBe(0.0)
        ->and($punctuationOnly->cer())->toBe(0.0)
        ->and($suffixDifference->substitutions)->toBe(1)
        ->and($suffixDifference->wer())->toBe(1.0)
        ->and($suffixDifference->cer())->toBeGreaterThan(0.0)->toBeLessThan(1.0);
});
