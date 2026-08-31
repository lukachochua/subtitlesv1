<?php

namespace App\ValueObjects;

final readonly class TranscriptionEvaluation
{
    public function __construct(
        public string $normalizedReference,
        public string $normalizedTranscript,
        public int $referenceWords,
        public int $referenceCharacters,
        public int $substitutions,
        public int $insertions,
        public int $deletions,
        public int $characterEdits,
    ) {}

    public function wordErrors(): int
    {
        return $this->substitutions + $this->insertions + $this->deletions;
    }

    public function wer(): float
    {
        return $this->rate($this->wordErrors(), $this->referenceWords);
    }

    public function cer(): float
    {
        return $this->rate($this->characterEdits, $this->referenceCharacters);
    }

    /**
     * @return array{
     *     normalized_reference: string,
     *     normalized_transcript: string,
     *     reference_words: int,
     *     reference_characters: int,
     *     substitutions: int,
     *     insertions: int,
     *     deletions: int,
     *     word_errors: int,
     *     character_edits: int,
     *     wer: float,
     *     cer: float
     * }
     */
    public function toArray(): array
    {
        return [
            'normalized_reference' => $this->normalizedReference,
            'normalized_transcript' => $this->normalizedTranscript,
            'reference_words' => $this->referenceWords,
            'reference_characters' => $this->referenceCharacters,
            'substitutions' => $this->substitutions,
            'insertions' => $this->insertions,
            'deletions' => $this->deletions,
            'word_errors' => $this->wordErrors(),
            'character_edits' => $this->characterEdits,
            'wer' => $this->wer(),
            'cer' => $this->cer(),
        ];
    }

    private function rate(int $errors, int $referenceUnits): float
    {
        if ($referenceUnits === 0) {
            return $errors === 0 ? 0.0 : 1.0;
        }

        return $errors / $referenceUnits;
    }
}
