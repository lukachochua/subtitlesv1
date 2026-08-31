<?php

use App\Actions\NormalizeGeorgianTranscriptionText;

test('normalizes Unicode punctuation and whitespace without changing Georgian word forms', function () {
    $decomposed = "A\u{0301}";
    $normalized = (new NormalizeGeorgianTranscriptionText)->handle("  საქართველოს,\n\tსაქართველო! {$decomposed}  ");

    expect($normalized)->toBe('საქართველოს საქართველო Á');
});
