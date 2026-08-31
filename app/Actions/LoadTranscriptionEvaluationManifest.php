<?php

namespace App\Actions;

use JsonException;
use UnexpectedValueException;

class LoadTranscriptionEvaluationManifest
{
    /**
     * @return array{dataset_version: string, clips: list<array{id: string, audio: string, reference: string, category: string, notes: string, nemo_result: string, audio_duration_seconds?: float, manual_correction_seconds?: float}>}
     */
    public function handle(string $manifestPath): array
    {
        $resolvedManifestPath = realpath($manifestPath);

        if ($resolvedManifestPath === false || ! is_file($resolvedManifestPath)) {
            throw new UnexpectedValueException("Evaluation manifest does not exist: {$manifestPath}");
        }

        try {
            $manifest = json_decode(file_get_contents($resolvedManifestPath) ?: '', true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('Evaluation manifest contains invalid JSON.', previous: $exception);
        }

        if (! is_array($manifest) || ! is_string($manifest['dataset_version'] ?? null) || trim($manifest['dataset_version']) === '') {
            throw new UnexpectedValueException('Evaluation manifest requires a dataset_version.');
        }

        $clips = $manifest['clips'] ?? null;

        if (! is_array($clips) || ! array_is_list($clips) || $clips === []) {
            throw new UnexpectedValueException('Evaluation manifest requires a non-empty clips list.');
        }

        $manifestDirectory = dirname($resolvedManifestPath);
        $validatedClips = [];
        $ids = [];

        foreach ($clips as $index => $clip) {
            if (! is_array($clip)) {
                throw new UnexpectedValueException("Evaluation clip at index {$index} must be an object.");
            }

            foreach (['id', 'audio', 'reference', 'category', 'nemo_result'] as $field) {
                if (! is_string($clip[$field] ?? null) || trim($clip[$field]) === '') {
                    throw new UnexpectedValueException("Evaluation clip at index {$index} requires {$field}.");
                }
            }

            if (isset($ids[$clip['id']])) {
                throw new UnexpectedValueException("Evaluation clip ID must be unique: {$clip['id']}");
            }

            $ids[$clip['id']] = true;
            $validatedClip = [
                'id' => $clip['id'],
                'audio' => $this->resolvePath($manifestDirectory, $clip['audio']),
                'reference' => $clip['reference'],
                'category' => $clip['category'],
                'notes' => is_string($clip['notes'] ?? null) ? $clip['notes'] : '',
                'nemo_result' => $this->resolvePath($manifestDirectory, $clip['nemo_result']),
            ];

            foreach (['audio_duration_seconds', 'manual_correction_seconds'] as $field) {
                if (array_key_exists($field, $clip)) {
                    if ((! is_int($clip[$field]) && ! is_float($clip[$field])) || $clip[$field] < 0) {
                        throw new UnexpectedValueException("Evaluation clip {$clip['id']} has invalid {$field}.");
                    }

                    $validatedClip[$field] = (float) $clip[$field];
                }
            }

            if (isset($validatedClip['manual_correction_seconds']) && (! isset($validatedClip['audio_duration_seconds']) || $validatedClip['audio_duration_seconds'] <= 0)) {
                throw new UnexpectedValueException("Evaluation clip {$clip['id']} requires a positive audio_duration_seconds for manual correction timing.");
            }

            $validatedClips[] = $validatedClip;
        }

        return ['dataset_version' => $manifest['dataset_version'], 'clips' => $validatedClips];
    }

    private function resolvePath(string $manifestDirectory, string $path): string
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return $manifestDirectory.DIRECTORY_SEPARATOR.$path;
    }
}
