<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class TranscriptionWord
{
    public function __construct(
        public string $text,
        public int $startMs,
        public int $endMs,
    ) {
        if (trim($this->text) === '') {
            throw new InvalidArgumentException('Transcription word text must not be empty.');
        }

        if ($this->startMs < 0) {
            throw new InvalidArgumentException('Transcription word start must not be negative.');
        }

        if ($this->endMs <= $this->startMs) {
            throw new InvalidArgumentException('Transcription word end must be after its start.');
        }
    }
}
