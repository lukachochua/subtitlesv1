<?php

namespace App\Actions;

use Normalizer;

class NormalizeGeorgianTranscriptionText
{
    public function handle(string $text): string
    {
        $normalized = Normalizer::normalize($text, Normalizer::FORM_C);

        if ($normalized === false) {
            $normalized = $text;
        }

        $withoutPunctuation = preg_replace('/[\p{P}]+/u', ' ', $normalized);

        if ($withoutPunctuation === null) {
            $withoutPunctuation = $normalized;
        }

        return preg_replace('/\s+/u', ' ', trim($withoutPunctuation)) ?? trim($withoutPunctuation);
    }
}
