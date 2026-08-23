<?php

namespace App\Actions;

use App\ValueObjects\TranscriptionWord;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use UnexpectedValueException;

class ConvertNemoTranscriptionWords
{
    private const MAXIMUM_DURATION_OVERRUN_MS = 100;

    /**
     * @param  array<string, mixed>  $transcription
     * @return list<TranscriptionWord>
     */
    public function handle(array $transcription, int $durationMs): array
    {
        if ($durationMs <= 0) {
            throw new InvalidArgumentException('Transcription duration must be positive.');
        }

        $entries = Arr::get($transcription, 'timestamp.word');

        if (! is_array($entries) || $entries === [] || ! array_is_list($entries)) {
            throw new UnexpectedValueException('NeMo did not return a valid word timestamp list.');
        }

        $words = [];
        $previousStartMs = null;
        $previousEndMs = null;

        foreach ($entries as $index => $entry) {
            if (! is_array($entry)) {
                throw new UnexpectedValueException("NeMo word at index {$index} is invalid.");
            }

            $text = $entry['word'] ?? null;

            if (! is_string($text) || trim($text) === '') {
                throw new UnexpectedValueException("NeMo word text at index {$index} is invalid.");
            }

            $startMs = $this->normalizeBoundary(
                value: $entry['start'] ?? null,
                durationMs: $durationMs,
                index: $index,
                boundary: 'start',
            );
            $endMs = $this->normalizeBoundary(
                value: $entry['end'] ?? null,
                durationMs: $durationMs,
                index: $index,
                boundary: 'end',
            );

            if ($endMs <= $startMs) {
                throw new UnexpectedValueException("NeMo word timing at index {$index} is invalid.");
            }

            if ($previousStartMs !== null && $startMs < $previousStartMs) {
                throw new UnexpectedValueException('NeMo word start times are not ordered.');
            }

            if ($previousEndMs !== null && $endMs < $previousEndMs) {
                throw new UnexpectedValueException('NeMo word end times are not ordered.');
            }

            $words[] = new TranscriptionWord($text, $startMs, $endMs);
            $previousStartMs = $startMs;
            $previousEndMs = $endMs;
        }

        return $words;
    }

    private function normalizeBoundary(
        mixed $value,
        int $durationMs,
        int $index,
        string $boundary,
    ): int {
        if ((! is_int($value) && ! is_float($value)) || ! is_finite((float) $value)) {
            throw new UnexpectedValueException(
                "NeMo word {$boundary} at index {$index} is invalid.",
            );
        }

        $milliseconds = round((float) $value * 1_000);

        if (! is_finite($milliseconds) || $milliseconds > PHP_INT_MAX) {
            throw new UnexpectedValueException(
                "NeMo word {$boundary} at index {$index} is invalid.",
            );
        }

        $milliseconds = (int) $milliseconds;

        if ($milliseconds < 0) {
            throw new UnexpectedValueException(
                "NeMo word {$boundary} at index {$index} is negative.",
            );
        }

        if ($milliseconds <= $durationMs) {
            return $milliseconds;
        }

        if ($milliseconds - $durationMs > self::MAXIMUM_DURATION_OVERRUN_MS) {
            throw new UnexpectedValueException(
                "NeMo word {$boundary} at index {$index} exceeds the audio duration.",
            );
        }

        return $durationMs;
    }
}
