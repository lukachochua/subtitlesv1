<?php

namespace App\ValueObjects;

final readonly class AppliedTranscriptionCorrections
{
    /**
     * @param  list<TranscriptionWord>  $rawWords
     * @param  list<TranscriptionWord>  $correctedWords
     * @param  list<array{word_index: int, original: string, replacement: string, category: string, confidence: float}>  $corrections
     */
    public function __construct(
        public array $rawWords,
        public array $correctedWords,
        public array $corrections,
    ) {}
}
