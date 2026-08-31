<?php

namespace App\Actions;

use App\ValueObjects\AppliedTranscriptionCorrections;
use App\ValueObjects\TranscriptionWord;
use UnexpectedValueException;

class ApplyTranscriptionCorrections
{
    /**
     * @param  list<TranscriptionWord>  $rawWords
     * @param  array<string, mixed>  $response
     */
    public function handle(array $rawWords, array $response): AppliedTranscriptionCorrections
    {
        $corrections = $response['corrections'] ?? null;

        if (! is_array($corrections) || ! array_is_list($corrections)) {
            throw new UnexpectedValueException('LLM response requires a corrections list.');
        }

        $correctedWords = $rawWords;
        $validatedCorrections = [];
        $correctedIndexes = [];

        foreach ($corrections as $correctionIndex => $correction) {
            if (! is_array($correction)) {
                throw new UnexpectedValueException("Correction at index {$correctionIndex} must be an object.");
            }

            $wordIndex = $correction['word_index'] ?? null;
            $original = $correction['original'] ?? null;
            $replacement = $correction['replacement'] ?? null;
            $category = $correction['category'] ?? null;
            $confidence = $correction['confidence'] ?? null;

            if (! is_int($wordIndex) || ! array_key_exists($wordIndex, $rawWords)) {
                throw new UnexpectedValueException("Correction at index {$correctionIndex} has an invalid word index.");
            }

            if (isset($correctedIndexes[$wordIndex])) {
                throw new UnexpectedValueException("Word index {$wordIndex} is corrected more than once.");
            }

            if (! is_string($original) || $original !== $rawWords[$wordIndex]->text) {
                throw new UnexpectedValueException("Correction at index {$correctionIndex} does not match the original word.");
            }

            if (! is_string($replacement) || trim($replacement) === '') {
                throw new UnexpectedValueException("Correction at index {$correctionIndex} has an empty replacement.");
            }

            if (preg_match('/\s/u', $replacement) === 1) {
                throw new UnexpectedValueException("Correction at index {$correctionIndex} must replace exactly one token.");
            }

            if (! is_string($category) || trim($category) === '') {
                throw new UnexpectedValueException("Correction at index {$correctionIndex} requires a category.");
            }

            if ((! is_int($confidence) && ! is_float($confidence)) || $confidence < 0 || $confidence > 1) {
                throw new UnexpectedValueException("Correction at index {$correctionIndex} has invalid confidence.");
            }

            $validatedCorrection = [
                'word_index' => $wordIndex,
                'original' => $original,
                'replacement' => $replacement,
                'category' => $category,
                'confidence' => (float) $confidence,
            ];
            $correctedWords[$wordIndex] = new TranscriptionWord(
                text: $replacement,
                startMs: $rawWords[$wordIndex]->startMs,
                endMs: $rawWords[$wordIndex]->endMs,
            );
            $validatedCorrections[] = $validatedCorrection;
            $correctedIndexes[$wordIndex] = true;
        }

        return new AppliedTranscriptionCorrections($rawWords, array_values($correctedWords), $validatedCorrections);
    }
}
