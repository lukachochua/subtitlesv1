<?php

namespace App\Actions;

use App\ValueObjects\TranscriptionEvaluation;

class EvaluateTranscription
{
    public function __construct(
        private NormalizeGeorgianTranscriptionText $normalizeGeorgianTranscriptionText,
    ) {}

    public function handle(string $reference, string $transcript): TranscriptionEvaluation
    {
        $normalizedReference = $this->normalizeGeorgianTranscriptionText->handle($reference);
        $normalizedTranscript = $this->normalizeGeorgianTranscriptionText->handle($transcript);
        $referenceWords = $this->words($normalizedReference);
        $transcriptWords = $this->words($normalizedTranscript);
        $wordOperations = $this->wordOperations($referenceWords, $transcriptWords);
        $referenceCharacters = $this->characters($normalizedReference);
        $transcriptCharacters = $this->characters($normalizedTranscript);

        return new TranscriptionEvaluation(
            normalizedReference: $normalizedReference,
            normalizedTranscript: $normalizedTranscript,
            referenceWords: count($referenceWords),
            referenceCharacters: count($referenceCharacters),
            substitutions: $wordOperations['substitutions'],
            insertions: $wordOperations['insertions'],
            deletions: $wordOperations['deletions'],
            characterEdits: $this->editDistance($referenceCharacters, $transcriptCharacters),
        );
    }

    /** @return list<string> */
    private function words(string $text): array
    {
        return $text === '' ? [] : explode(' ', $text);
    }

    /** @return list<string> */
    private function characters(string $text): array
    {
        if ($text === '') {
            return [];
        }

        return preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /**
     * @param  list<string>  $reference
     * @param  list<string>  $transcript
     * @return array{substitutions: int, insertions: int, deletions: int}
     */
    private function wordOperations(array $reference, array $transcript): array
    {
        $rows = count($reference);
        $columns = count($transcript);
        $costs = array_fill(0, $rows + 1, array_fill(0, $columns + 1, 0));
        $operations = array_fill(0, $rows + 1, array_fill(0, $columns + 1, null));

        for ($row = 1; $row <= $rows; $row++) {
            $costs[$row][0] = $row;
            $operations[$row][0] = 'deletion';
        }

        for ($column = 1; $column <= $columns; $column++) {
            $costs[0][$column] = $column;
            $operations[0][$column] = 'insertion';
        }

        for ($row = 1; $row <= $rows; $row++) {
            for ($column = 1; $column <= $columns; $column++) {
                if ($reference[$row - 1] === $transcript[$column - 1]) {
                    $costs[$row][$column] = $costs[$row - 1][$column - 1];
                    $operations[$row][$column] = 'match';

                    continue;
                }

                $candidates = [
                    'substitution' => $costs[$row - 1][$column - 1] + 1,
                    'deletion' => $costs[$row - 1][$column] + 1,
                    'insertion' => $costs[$row][$column - 1] + 1,
                ];
                $minimum = min($candidates);
                $costs[$row][$column] = $minimum;
                $operations[$row][$column] = array_search($minimum, $candidates, true);
            }
        }

        $counts = ['substitutions' => 0, 'insertions' => 0, 'deletions' => 0];
        $row = $rows;
        $column = $columns;

        while ($row > 0 || $column > 0) {
            $operation = $operations[$row][$column];

            if ($operation === 'match') {
                $row--;
                $column--;
            } elseif ($operation === 'substitution') {
                $counts['substitutions']++;
                $row--;
                $column--;
            } elseif ($operation === 'deletion') {
                $counts['deletions']++;
                $row--;
            } else {
                $counts['insertions']++;
                $column--;
            }
        }

        return $counts;
    }

    /**
     * @param  list<string>  $reference
     * @param  list<string>  $transcript
     */
    private function editDistance(array $reference, array $transcript): int
    {
        $previous = range(0, count($transcript));

        foreach ($reference as $referenceIndex => $referenceUnit) {
            $current = [$referenceIndex + 1];

            foreach ($transcript as $transcriptIndex => $transcriptUnit) {
                $current[] = min(
                    $current[$transcriptIndex] + 1,
                    $previous[$transcriptIndex + 1] + 1,
                    $previous[$transcriptIndex] + ($referenceUnit === $transcriptUnit ? 0 : 1),
                );
            }

            $previous = $current;
        }

        return $previous[array_key_last($previous)];
    }
}
