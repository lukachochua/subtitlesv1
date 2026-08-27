<?php

namespace App\Actions;

use App\ValueObjects\CaptionCue;
use App\ValueObjects\TranscriptionWord;
use Illuminate\Support\Str;
use InvalidArgumentException;

class GenerateCaptionCues
{
    private const MAXIMUM_WORDS = 8;

    private const MAXIMUM_CHARACTERS = 42;

    private const MAXIMUM_DURATION_MS = 3500;

    private const SPEECH_GAP_MS = 800;

    /**
     * @param  list<TranscriptionWord>  $words
     * @return list<CaptionCue>
     */
    public function handle(array $words): array
    {
        $this->validateWords($words);

        $cues = [];
        $cueWords = [];

        foreach ($words as $word) {
            if ($cueWords !== [] && $this->shouldStartNewCue($cueWords, $word)) {
                $cues[] = $this->makeCue($cueWords, count($cues) + 1);
                $cueWords = [];
            }

            $cueWords[] = $word;

            if ($this->hasStrongEndingPunctuation($word)) {
                $cues[] = $this->makeCue($cueWords, count($cues) + 1);
                $cueWords = [];
            }
        }

        if ($cueWords !== []) {
            $cues[] = $this->makeCue($cueWords, count($cues) + 1);
        }

        return $cues;
    }

    /**
     * @param  list<TranscriptionWord>  $words
     */
    private function validateWords(array $words): void
    {
        $previousWord = null;

        foreach ($words as $word) {
            if ($previousWord !== null && $word->startMs < $previousWord->endMs) {
                throw new InvalidArgumentException('Overlapping transcription words cannot generate caption cues.');
            }

            $previousWord = $word;
        }
    }

    /**
     * @param  non-empty-list<TranscriptionWord>  $cueWords
     */
    private function shouldStartNewCue(array $cueWords, TranscriptionWord $nextWord): bool
    {
        $firstWord = $cueWords[0];
        $previousWord = $cueWords[array_key_last($cueWords)];

        if ($nextWord->startMs - $previousWord->endMs >= self::SPEECH_GAP_MS) {
            return true;
        }

        if (count($cueWords) >= self::MAXIMUM_WORDS) {
            return true;
        }

        $texts = array_map(
            fn (TranscriptionWord $word): string => $word->text,
            $cueWords,
        );
        $texts[] = $nextWord->text;

        if (Str::length(implode(' ', $texts)) > self::MAXIMUM_CHARACTERS) {
            return true;
        }

        return $nextWord->endMs - $firstWord->startMs > self::MAXIMUM_DURATION_MS;
    }

    private function hasStrongEndingPunctuation(TranscriptionWord $word): bool
    {
        return Str::endsWith($word->text, ['.', '?', '!', '…']);
    }

    /**
     * @param  non-empty-list<TranscriptionWord>  $words
     */
    private function makeCue(array $words, int $order): CaptionCue
    {
        $lastWord = $words[array_key_last($words)];

        return new CaptionCue(
            order: $order,
            text: implode(' ', array_map(
                fn (TranscriptionWord $word): string => $word->text,
                $words,
            )),
            startMs: $words[0]->startMs,
            endMs: $lastWord->endMs,
            words: $words,
        );
    }
}
