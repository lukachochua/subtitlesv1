<?php

namespace App\Enums;

enum VideoRenderQuality: string
{
    case High = 'high';
    case Balanced = 'balanced';
    case Smaller = 'smaller';

    public function crf(): int
    {
        return match ($this) {
            self::High => 14,
            self::Balanced => 18,
            self::Smaller => 23,
        };
    }

    public function preset(): string
    {
        return match ($this) {
            self::High => 'slow',
            self::Balanced => 'medium',
            self::Smaller => 'fast',
        };
    }
}
