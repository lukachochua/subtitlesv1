<?php

use App\Actions\GenerateCaptionCues;
use App\ValueObjects\CaptionCue;
use App\ValueObjects\TranscriptionWord;

test('it generates the expected cues from the project 4 word sequence', function () {
    $words = [
        captionWord('ერთი', 1680, 1760),
        captionWord('ორი,', 2320, 2720),
        captionWord('სამი,', 2880, 3520),
        captionWord('ოთხი,', 3600, 4080),
        captionWord('ხუთი', 4160, 4480),
        captionWord('ექვსი', 4720, 5280),
        captionWord('შვიდი', 5360, 5760),
        captionWord('რვა', 6080, 6240),
        captionWord('ცხრა', 6560, 6880),
        captionWord('ათი', 7680, 8080),
        captionWord('გამარჯობა', 8240, 8880),
        captionWord('გაგიმარჯოს,', 9120, 10_160),
        captionWord('როგორ', 10_160, 10_240),
        captionWord('ხარმე.', 10_560, 11_040),
        captionWord('შელო', 11_600, 11_920),
        captionWord('მოხარმეც', 12_000, 12_800),
        captionWord('კარგა.', 12_880, 13_920),
    ];

    $cues = (new GenerateCaptionCues)->handle($words);

    expect($cues)->toEqual([
        new CaptionCue(1, 'ერთი ორი, სამი, ოთხი, ხუთი', 1680, 4480),
        new CaptionCue(2, 'ექვსი შვიდი რვა ცხრა', 4720, 6880),
        new CaptionCue(3, 'ათი გამარჯობა გაგიმარჯოს, როგორ ხარმე.', 7680, 11_040),
        new CaptionCue(4, 'შელო მოხარმეც კარგა.', 11_600, 13_920),
    ]);
});

test('it splits after strong punctuation', function (string $punctuation) {
    $cues = (new GenerateCaptionCues)->handle([
        captionWord("ერთი{$punctuation}", 0, 100),
        captionWord('ორი', 100, 200),
    ]);

    expect($cues)->toHaveCount(2)
        ->and($cues[0]->text)->toBe("ერთი{$punctuation}")
        ->and($cues[1]->text)->toBe('ორი');
})->with(['period' => '.', 'question' => '?', 'exclamation' => '!', 'ellipsis' => '…']);

test('it splits before an 800 millisecond speech gap', function () {
    $cues = (new GenerateCaptionCues)->handle([
        captionWord('ერთი', 0, 100),
        captionWord('ორი', 900, 1000),
    ]);

    expect($cues)->toHaveCount(2);
});

test('it limits a cue to eight words', function () {
    $words = [];

    for ($index = 0; $index < 9; $index++) {
        $words[] = captionWord('ა', $index * 100, ($index + 1) * 100);
    }

    $cues = (new GenerateCaptionCues)->handle($words);

    expect($cues)->toHaveCount(2)
        ->and($cues[0]->text)->toBe('ა ა ა ა ა ა ა ა')
        ->and($cues[1]->text)->toBe('ა');
});

test('it counts Georgian text as Unicode characters', function () {
    $cues = (new GenerateCaptionCues)->handle([
        captionWord(str_repeat('ა', 21), 0, 100),
        captionWord(str_repeat('ბ', 21), 100, 200),
    ]);

    expect($cues)->toHaveCount(2);
});

test('it limits normal cue duration to 3500 milliseconds', function () {
    $cues = (new GenerateCaptionCues)->handle([
        captionWord('ერთი', 0, 3000),
        captionWord('ორი', 3000, 3501),
    ]);

    expect($cues)->toHaveCount(2);
});

test('it emits a single word even when it exceeds normal limits', function () {
    $word = captionWord(str_repeat('ა', 43), 0, 4000);

    $cues = (new GenerateCaptionCues)->handle([$word]);

    expect($cues)->toEqual([
        new CaptionCue(1, $word->text, 0, 4000),
    ]);
});

test('it rejects overlapping transcription words', function () {
    expect(fn () => (new GenerateCaptionCues)->handle([
        captionWord('ერთი', 0, 200),
        captionWord('ორი', 100, 300),
    ]))->toThrow(
        InvalidArgumentException::class,
        'Overlapping transcription words cannot generate caption cues.',
    );
});

test('it returns no cues for no words', function () {
    expect((new GenerateCaptionCues)->handle([]))->toBe([]);
});

function captionWord(string $text, int $startMs, int $endMs): TranscriptionWord
{
    return new TranscriptionWord($text, $startMs, $endMs);
}
