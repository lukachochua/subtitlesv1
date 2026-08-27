<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class CaptionCue
{
    /** @param list<TranscriptionWord> $words */
    public function __construct(
        public int $order,
        public string $text,
        public int $startMs,
        public int $endMs,
        /** @var list<TranscriptionWord> */
        public array $words = [],
    ) {
        if ($this->order < 1) {
            throw new InvalidArgumentException('Caption cue order must be a positive integer.');
        }

        if (trim($this->text) === '') {
            throw new InvalidArgumentException('Caption cue text must not be empty.');
        }

        if ($this->startMs < 0) {
            throw new InvalidArgumentException('Caption cue start must not be negative.');
        }

        if ($this->endMs <= $this->startMs) {
            throw new InvalidArgumentException('Caption cue end must be after its start.');
        }

    }
}
